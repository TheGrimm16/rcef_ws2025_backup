import fitz
from pathlib import Path

def save_relative(doc: fitz.Document, filename: str) -> str:
    """
    Save the PDF under the project's storage/pdf folder.
    Assumes this script lives somewhere under the project root.
    """
    project_root = Path(__file__).resolve().parents[4]
    out_dir = project_root / "storage" / "pdf"
    out_dir.mkdir(parents=True, exist_ok=True)

    out_path = out_dir / filename
    doc.save(str(out_path))
    return str(out_path)
