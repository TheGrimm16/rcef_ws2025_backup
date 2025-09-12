import fitz
from typing import List, Optional, Union, Tuple

Color = Tuple[float, float, float]  # RGB 0..1
BorderSpec = Union[bool, dict]      # True=default, False=no border, dict={"color":..., "width":...}

class PageContext:
    """Wraps a fitz.Page with margins and content box convenience."""
    def __init__(self, page: fitz.Page, margin: float = 72):
        self.page = page
        self.margin = margin

    @property
    def content_box(self) -> fitz.Rect:
        r = self.page.rect
        m = self.margin
        return fitz.Rect(r.x0 + m, r.y0 + m, r.x1 - m, r.y1 - m)

    @property
    def width(self) -> float:
        return self.content_box.width

    @property
    def height(self) -> float:
        return self.content_box.height


class Grid:
    """Flexible grid layout with per-cell borders, text, and nested grids."""
    def __init__(
        self,
        context: PageContext,
        rows: int,
        cols: int,
        rect: Optional[fitz.Rect] = None
    ):
        self.context = context
        self.rows = rows
        self.cols = cols
        self.rect = rect or context.content_box
        self.cells = self._compute_cells()

    def _compute_cells(self) -> List[List[fitz.Rect]]:
        """Compute cell rectangles based on grid size."""
        w, h = self.rect.width / self.cols, self.rect.height / self.rows
        return [
            [
                fitz.Rect(self.rect.x0 + c*w, self.rect.y0 + r*h, self.rect.x0 + (c+1)*w, self.rect.y0 + (r+1)*h)
                for c in range(self.cols)
            ]
            for r in range(self.rows)
        ]

    def draw(
        self,
        data: Optional[List[List[str]]] = None,
        borders: Optional[List[List[BorderSpec]]] = None,
        fontsize: float = 10,
        default_border: BorderSpec = True
    ):
        """
        Draw the grid.

        - data: optional 2D list of text
        - borders: optional 2D list of border specs per cell
        - default_border: fallback border spec if borders not provided
        """
        page = self.context.page
        for r, row in enumerate(self.cells):
            for c, cell in enumerate(row):
                # Draw border
                spec = default_border
                if borders and r < len(borders) and c < len(borders[r]) and borders[r][c] is not None:
                    spec = borders[r][c]

                if isinstance(spec, dict):
                    page.draw_rect(cell, color=spec.get("color", (0,0,0)), width=spec.get("width", 1))
                elif spec is True:
                    page.draw_rect(cell, color=(0,0,0), width=1)
                # else False: no border

                # Insert text
                if data and r < len(data) and c < len(data[r]) and data[r][c] is not None:
                    page.insert_textbox(cell, str(data[r][c]), fontsize=fontsize, align=1)

    def subgrid(self, row: int, col: int, rows: int, cols: int) -> "Grid":
        """Return a nested grid inside a specific cell."""
        return Grid(self.context, rows, cols, rect=self.cells[row][col])
