import subprocess
import tempfile
import os


# Liblouis table mapping
LOUIS_TABLES = {
    "en": "en-ueb-g2.ctb",   # English Unified Braille Grade 2
    "ar": "ar-ar-g1.utb",    # Arabic Grade 1
}

# BRF line/page limits (standard embosser)
BRF_CELLS_PER_LINE = 40
BRF_LINES_PER_PAGE = 25


def convert_to_braille(text: str, language: str) -> tuple[str, str]:
    """Returns (brf_content, unicode_braille_content)."""
    table = LOUIS_TABLES.get(language, LOUIS_TABLES["en"])

    brf = _liblouis_convert(text, table, output_format="brf")
    unicode_braille = _liblouis_convert(text, table, output_format="unicode")

    return brf, unicode_braille


def _liblouis_convert(text: str, table: str, output_format: str) -> str:
    with tempfile.NamedTemporaryFile(mode="w", suffix=".txt", encoding="utf-8", delete=False) as f:
        f.write(text)
        tmp_in = f.name

    tmp_out = tmp_in + ".out"
    
    # For unicode, use unicode.dis display table
    translate_table = f"unicode.dis,{table}" if output_format == "unicode" else table

    try:
        cmd = ["file2brl", "-t", translate_table, tmp_in, tmp_out]
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=120)

        if result.returncode != 0:
            # Fallback: use lou_translate directly
            return _lou_translate_fallback(text, table, output_format)

        with open(tmp_out, "r", encoding="utf-8", errors="replace") as f:
            return f.read()
    finally:
        for p in [tmp_in, tmp_out]:
            if os.path.exists(p):
                os.unlink(p)


def _lou_translate_fallback(text: str, table: str, output_format: str) -> str:
    """Line-by-line translation using lou_translate."""
    lines = text.split("\n")
    output_lines = []
    
    # For unicode output, prepend unicode.dis display table
    translate_table = f"unicode.dis,{table}" if output_format == "unicode" else table

    for line in lines:
        if not line.strip():
            output_lines.append("")
            continue
        try:
            result = subprocess.run(
                ["lou_translate", translate_table],
                input=line,
                capture_output=True,
                text=True,
                timeout=30,
            )
            translated = result.stdout.strip()
            output_lines.append(translated)
        except Exception:
            output_lines.append(line)  # keep original on failure

    return "\n".join(output_lines)
