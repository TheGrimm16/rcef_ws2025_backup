import fitz  # PyMuPDF

# Common page sizes (ISO A-series and US) in points (width, height)
# 1 in = 72 pt ; 1 mm ≈ 2.83465 pt
PAGE_SIZES = {
    "A0":      (2383.94, 3370.39),  # 841 × 1189 mm
    "A1":      (1683.78, 2383.94),  # 594 × 841 mm
    "A2":      (1190.55, 1683.78),  # 420 × 594 mm
    "A3":      (841.89, 1190.55),   # 297 × 420 mm
    "A4":      (595.28, 841.89),    # 210 × 297 mm
    "A5":      (419.53, 595.28),    # 148 × 210 mm
    "LETTER":  (612.0, 792.0),      # 8.5 × 11 in
    "LEGAL":   (612.0, 1008.0),     # 8.5 × 14 in
    "TABLOID": (792.0, 1224.0),     # 11 × 17 in
}

def page_format_set(doc: fitz.Document, size: str = "A4", orientation: str = "portrait") -> fitz.Page:
    """
    Add a new page to the document with the given size and orientation.

    Args:
        doc: The fitz.Document object
        size: Page size name (e.g. 'A4', 'LETTER', etc.)
        orientation: 'portrait' or 'landscape'

    Returns:
        The created fitz.Page object
    """
    size = size.upper()
    if size not in PAGE_SIZES:
        raise ValueError(f"Unsupported page size '{size}'. Choose from {list(PAGE_SIZES.keys())}")

    w, h = PAGE_SIZES[size]
    if orientation.lower() == "landscape":
        w, h = h, w

    return doc.new_page(width=w, height=h)