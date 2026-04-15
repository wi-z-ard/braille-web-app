import subprocess, tempfile, os


def extract_text(filepath: str, file_type: str) -> str:
    if file_type == "txt":
        with open(filepath, "r", encoding="utf-8", errors="replace") as f:
            return f.read()

    if file_type == "docx":
        return _extract_docx(filepath)

    if file_type == "pdf":
        text = _extract_pdf_text(filepath)
        if len(text.strip()) < 50:          # likely scanned
            text = _ocr_pdf(filepath)
        return text

    raise ValueError(f"Unsupported file type: {file_type}")


def _extract_docx(path: str) -> str:
    try:
        from docx import Document
        doc = Document(path)
        return "\n".join(p.text for p in doc.paragraphs)
    except Exception as e:
        raise RuntimeError(f"DOCX extraction failed: {e}")


def _extract_pdf_text(path: str) -> str:
    try:
        import pdfplumber
        with pdfplumber.open(path) as pdf:
            return "\n".join(
                page.extract_text() or "" for page in pdf.pages
            )
    except Exception as e:
        raise RuntimeError(f"PDF text extraction failed: {e}")


def _ocr_pdf(path: str) -> str:
    """Convert PDF pages to images then run Tesseract OCR."""
    try:
        from pdf2image import convert_from_path
        import pytesseract

        pages = convert_from_path(path, dpi=300)
        texts = []
        for page in pages:
            # Try Arabic + English
            text = pytesseract.image_to_string(page, lang="ara+eng")
            texts.append(text)
        return "\n".join(texts)
    except Exception as e:
        raise RuntimeError(f"OCR failed: {e}")
