import os, subprocess, pathlib
from .extractor import extract_text
from .cleaner import clean_text
from .braille import convert_to_braille


class ProcessingPipeline:
    def __init__(self, upload_path: str, processed_path: str):
        self.upload_path = upload_path
        self.processed_path = processed_path

    def run(self, job_id: str, filename: str, file_type: str, language: str) -> str:
        src = os.path.join(self.upload_path, filename)
        if not os.path.isfile(src):
            raise FileNotFoundError(f"Upload not found: {filename}")

        # 1. Extract text
        raw_text = extract_text(src, file_type)

        # 2. Detect language if auto
        if language == "auto":
            language = detect_language(raw_text)

        # 3. Clean text
        cleaned = clean_text(raw_text, language)

        # 4. Convert to Braille
        brf_content, unicode_content = convert_to_braille(cleaned, language)

        # 5. Save outputs
        out_dir = os.path.join(self.processed_path, job_id)
        pathlib.Path(out_dir).mkdir(parents=True, exist_ok=True)

        brf_path = os.path.join(out_dir, "output.brf")
        uni_path = os.path.join(out_dir, "output.txt")

        with open(brf_path, "w", encoding="utf-8") as f:
            f.write(brf_content)
        with open(uni_path, "w", encoding="utf-8") as f:
            f.write(unicode_content)

        return out_dir


def detect_language(text: str) -> str:
    """Simple Arabic detection via Unicode range."""
    arabic_chars = sum(1 for c in text if "\u0600" <= c <= "\u06FF")
    return "ar" if arabic_chars / max(len(text), 1) > 0.2 else "en"
