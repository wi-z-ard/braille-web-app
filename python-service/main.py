from fastapi import FastAPI, HTTPException, Depends, BackgroundTasks
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from pydantic import BaseModel
import os, secrets

from app.processors.pipeline import ProcessingPipeline

app = FastAPI(docs_url=None, redoc_url=None)  # disable docs in prod
bearer = HTTPBearer()

SECRET = os.getenv("PYTHON_SECRET", "change-this-in-production-32chars")
UPLOAD_PATH = os.getenv("UPLOAD_PATH", "/var/www/html/brf-helper/storage/uploads")
PROCESSED_PATH = os.getenv("PROCESSED_PATH", "/var/www/html/brf-helper/storage/processed")
PHP_CALLBACK_URL = os.getenv("PHP_CALLBACK_URL", "http://127.0.0.1/api/internal/job-update")


def verify_secret(creds: HTTPAuthorizationCredentials = Depends(bearer)):
    if not secrets.compare_digest(creds.credentials, SECRET):
        raise HTTPException(status_code=403, detail="Forbidden")


class JobRequest(BaseModel):
    job_id: str
    filename: str
    file_type: str
    language: str = "auto"


@app.post("/process", dependencies=[Depends(verify_secret)])
async def process_job(req: JobRequest, bg: BackgroundTasks):
    bg.add_task(run_pipeline, req)
    return {"status": "queued", "job_id": req.job_id}


@app.get("/health")
def health():
    return {"ok": True}


async def run_pipeline(req: JobRequest):
    import httpx
    import traceback
    pipeline = ProcessingPipeline(UPLOAD_PATH, PROCESSED_PATH)
    try:
        result = pipeline.run(req.job_id, req.filename, req.file_type, req.language)
        payload = {"job_id": req.job_id, "status": "done", "result_path": result}
        print(f"✓ Job {req.job_id} completed: {result}")
    except Exception as e:
        payload = {"job_id": req.job_id, "status": "error", "error": str(e)}
        print(f"✗ Job {req.job_id} failed: {e}")
        traceback.print_exc()

    try:
        async with httpx.AsyncClient() as client:
            resp = await client.post(
                PHP_CALLBACK_URL,
                json=payload,
                headers={"X-Internal-Secret": SECRET},
                timeout=10,
            )
            print(f"→ Callback to PHP: {resp.status_code} - {resp.text[:100]}")
    except Exception as e:
        print(f"✗ Callback failed: {e}")
        traceback.print_exc()
