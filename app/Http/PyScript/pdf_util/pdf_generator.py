import time
start_time = time.time()
import fitz
from pageformat import page_format_set
from saver import save_relative
from units import inch_to_pt
from layout import PageContext, Grid

if __name__ == "__main__":
    doc = fitz.open()
    pageSize = "A4"
    pageOrientation = "landscape"

    # ----------------- Page 1 -----------------
    page1 = page_format_set(doc, pageSize, pageOrientation)
    ctx1 = PageContext(page1, margin=inch_to_pt(0.1))

    main_grid = Grid(ctx1, 2, 2)

    # Per-cell border toggle demonstration:
    # True = default black, False = no border, dict = custom color/width
    borders_page1 = [
        [True, {"color": (1, 0, 0), "width": 2}],  # Top-right cell has red border
        [False, {"color": (0, 0, 1), "width": 1.5}]  # Bottom-left no border, bottom-right blue
    ]

    main_grid.draw(
        data=[["Top Left Section", "Top Right Section"], ["Bottom Left Section", "Bottom Right Section"]],
        borders=borders_page1
    )

    # Nested grid inside Top Right
    nested = main_grid.subgrid(0, 1, 3, 3)
    nested.tag = "TopRight_NestedGrid"
    
    # Nested grid per-cell borders: mix of True, False, custom
    nested_borders = [
        [True, False, {"color": (0, 1, 0), "width": 1}],   # Top row: black, no border, green
        [{"color": (0.5,0,0.5), "width": 1.5}, True, True], # Second row: purple, black, black
        [False, True, False]                                 # Third row: no, black, no
    ]
    nested_data = [
        ["TopRight A1", "TopRight B1", "TopRight C1"],
        ["TopRight A2", "TopRight B2", "TopRight C2"],
        ["TopRight A3", "TopRight B3", "TopRight C3"]
    ]
    nested.draw(data=nested_data, borders=nested_borders)

    # ----------------- Page 2 -----------------
    page2 = page_format_set(doc, pageSize, pageOrientation)
    ctx2 = PageContext(page2, margin=inch_to_pt(0.1))
    grid2 = Grid(ctx2, 3, 3)

    # Toggle example: top-left cell no border, middle custom blue
    borders_page2 = [
        [False, True, True],
        [True, {"color": (0, 0, 1), "width": 2}, True],
        [True, True, False]
    ]
    grid2.draw(
        data=[
            ["P2 Row1-Col1", "P2 Row1-Col2", "P2 Row1-Col3"],
            ["P2 Row2-Col1", "P2 Row2-Col2", "P2 Row2-Col3"],
            ["P2 Row3-Col1", "P2 Row3-Col2", "P2 Row3-Col3"]
        ],
        borders=borders_page2
    )

    nested2 = grid2.subgrid(1, 1, 2, 2)
    nested2.tag = "Page2_MiddleNested"
    
    # Nested2: all cells default border
    nested2.draw(
        data=[["Middle N1", "Middle N2"], ["Middle N3", "Middle N4"]],
        default_border=True
    )

    # ----------------- Page 3 -----------------
    page3 = page_format_set(doc, pageSize, pageOrientation)
    ctx3 = PageContext(page3, margin=inch_to_pt(1))
    grid3 = Grid(ctx3, 5, 2)

    # Toggle first row no border, last row custom red
    borders_page3 = [
        [False, False],
        [True, True],
        [True, True],
        [True, True],
        [{"color": (1,0,0), "width": 2}, {"color": (1,0,0), "width": 2}]
    ]
    grid3.draw(
        data=[
            ["Header Column1", "Header Column2"],
            ["Row1 Column1", "Row1 Column2"],
            ["Row2 Column1", "Row2 Column2"],
            ["Row3 Column1", "Row3 Column2"],
            ["Footer", "Notes Section"]
        ],
        borders=borders_page3
    )

    # ----------------- Page 4 -----------------
    page4 = page_format_set(doc, pageSize, pageOrientation)
    ctx4 = PageContext(page4, margin=inch_to_pt(0.75))
    grid4 = Grid(ctx4, 4, 4)

    # Toggle header row blue border, last column red border, others default
    borders_page4 = [
        [{"color": (0,0,1), "width": 1}, {"color": (0,0,1), "width": 1}, {"color": (0,0,1), "width": 1}, {"color": (1,0,0), "width": 1.5}],
        [True, True, True, {"color": (1,0,0), "width": 1.5}],
        [True, True, True, {"color": (1,0,0), "width": 1.5}],
        [True, True, True, {"color": (1,0,0), "width": 1.5}]
    ]
    grid4.draw(
        data=[
            ["Item Name", "Quantity", "Price", "Total Amount"],
            ["Apple", "10", "$1.50", "$15.00"],
            ["Banana", "5", "$0.80", "$4.00"],
            ["Orange", "8", "$1.20", "$9.60"]
        ],
        borders=borders_page4
    )

    # ----------------- Save PDF -----------------
    pdf_path = save_relative(doc, "page_format_demo_borders.pdf")
    doc.close()
    elapsed = (time.time() - start_time) * 1000
    print("Saved PDF at:", pdf_path)
    print(f"Total Elapsed Time: {elapsed:.2f} ms")
