import re
import unicodedata


def clean_text(text: str, language: str) -> str:
    # Normalize unicode
    text = unicodedata.normalize("NFC", text)

    # Remove null bytes and control chars (keep newlines/tabs)
    text = re.sub(r"[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]", "", text)

    # Collapse excessive whitespace
    text = re.sub(r"[ \t]{2,}", " ", text)
    text = re.sub(r"\n{3,}", "\n\n", text)

    if language == "ar":
        text = _clean_arabic(text)
    else:
        text = _clean_english(text)

    return text.strip()


def _clean_arabic(text: str) -> str:
    # Remove tatweel (kashida) — OCR artifact
    text = text.replace("\u0640", "")
    # Normalize Arabic presentation forms to base chars
    text = _normalize_arabic_forms(text)
    return text


def _clean_english(text: str) -> str:
    # Fix common OCR errors: l→1, O→0 in numeric context
    text = re.sub(r"(?<=\d)l(?=\d)", "1", text)
    text = re.sub(r"(?<=\d)O(?=\d)", "0", text)
    # Remove hyphenation at line breaks
    text = re.sub(r"-\n(\w)", r"\1", text)
    return text


def _normalize_arabic_forms(text: str) -> str:
    """Map Arabic presentation forms (FB50–FDFF, FE70–FEFF) to base forms."""
    result = []
    for ch in text:
        cp = ord(ch)
        if 0xFB50 <= cp <= 0xFDFF or 0xFE70 <= cp <= 0xFEFF:
            normalized = unicodedata.normalize("NFKC", ch)
            result.append(normalized)
        else:
            result.append(ch)
    return "".join(result)
