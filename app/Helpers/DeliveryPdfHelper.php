<?php

namespace App\Helpers;

use TCPDF;

class DeliveryPdfHelper
{   
    private $coordX, $coordY;

    public static $parts = [
        'part1' => [
            'title' => 'INSPECTION AND ACCEPTANCE RECEIPT ',
            'subtitle' => 'RCEF Seed Program',
        ],
        'part2' => [
            'title' => 'DELIVERY SCHEDULE',
            'subtitle' => 'RCEF Seed Program',
        ],
        'part3' => [
            'title' => 'SEED ACKNOWLEDGEMENT RECEIPT',
            'subtitle' => 'RCEF Seed Program',
        ],
    ];

    public static function renderPartContent($part, $pdf, $data){
        switch ($part) {
            case 'part1':
                self::renderIAR($pdf, $data);
                break;
            case 'part2':
                self::renderDeliverySchedule($pdf, $data);
                break;
            case 'part3':
                self::renderSAR($pdf, $data);
                break;
        }
    }

    private static function getUsablePageArea($pdf){
        // Get left, top, right margins from TCPDF
        $margins = $pdf->getMargins();
        $leftMargin   = $margins['left'];
        $topMargin    = $margins['top'];
        $rightMargin  = $margins['right'];

        // Get bottom margin from AutoPageBreak settings
        list($apbStatus, $bottomMargin) = $pdf->getAutoPageBreak();

        // Calculate total usable width and height
        $pageWidth  = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        $usableWidth  = $pageWidth - $leftMargin - $rightMargin;
        $usableHeight = $pageHeight - $topMargin - $bottomMargin;

        return [
            'width'        => $usableWidth,
            'height'       => $usableHeight,
            'top'          => $topMargin,
            'bottom'       => $bottomMargin,
            'left'         => $leftMargin,
            'right'        => $rightMargin,
            'pageWidth'    => $pageWidth,
            'pageHeight'   => $pageHeight,
            'apbEnabled'   => $apbStatus
        ];
    }

    //unused custom function.
    public static function renderLabelValueRow($pdf, $label, $value, $labelBg = true, $paddingW = 1, $paddingH = 1){
        $area = self::getUsablePageArea($pdf);
        $printableWidth = $area['width'];

        // Measure max width of lines (handle manual \n)
        $labelLines = explode("\n", $label);
        $valueLines = explode("\n", $value);

        $labelMaxWidth = 0;
        foreach ($labelLines as $line) {
            $lineWidth = $pdf->GetStringWidth($line);
            if ($lineWidth > $labelMaxWidth) {
                $labelMaxWidth = $lineWidth;
            }
        }

        // Add horizontal padding (both sides)
        $labelWidth = $labelMaxWidth + (2 * $paddingW)+.002;

        // Ensure label width does not exceed 50% of printable area
        $labelWidth = min($labelWidth, $printableWidth * 0.5);

        $valueWidth = $printableWidth - $labelWidth;

        // Compute text heights (with vertical padding)
        $labelHeight = $pdf->getStringHeight($labelWidth - 2 * $paddingW, $label) + $paddingH;
        $valueHeight = $pdf->getStringHeight($valueWidth - 2 * $paddingW, $value) + $paddingH;

        $finalHeight = max($labelHeight, $valueHeight);

        // Draw cells
        $pdf->MultiCell($labelWidth, $finalHeight, $label, 1, 'L', $labelBg, 0, '', '', true, 0, false, false, $finalHeight, 'M');
        $pdf->MultiCell(0,           $finalHeight, $value, 1, 'L', false, 1, '', '', true, 0, false, false, $finalHeight, 'M');
    }

    public static function getMaxRowHeightFromCells($pdf, array $cells, $paddingW = 1, $paddingH = 1){
        $area = self::getUsablePageArea($pdf);
        $printableWidth = $area['width'];

        $maxHeight = 0;

        foreach ($cells as $cell) {
            $width = $cell['width'];
            $text = $cell['text'];

            // If width is percentage (0 < width <= 1), convert to absolute
            if ($width > 0 && $width <= 1) {
                $width = $printableWidth * $width;
            }

            $textWidth = $width - (2 * $paddingW);
            $textHeight = $pdf->getStringHeight($textWidth, $text) + $paddingH;
            $maxHeight = max($maxHeight, $textHeight);
        }

        return $maxHeight;
    }

    public static function renderPartOne($pdf, $data){
        $area           = self::getUsablePageArea($pdf);
        $rows           = isset($data['delivery']) ? $data['delivery'] : [];

        // Page layout metrics
        $pageWidth      = $pdf->getPageWidth();
        $margins        = $pdf->getMargins();
        $leftMargin     = $margins['left'];
        $rightMargin    = $margins['right'];
        $printableWidth = $pageWidth - $leftMargin - $rightMargin;

        // Table column widths
        $tableColumnWidth  = [22, 0, 20, 15, 25, 15, 45];
        $tableColumnWidth[1] = $printableWidth - array_sum($tableColumnWidth);
        $tableColumnHeight = 6;

        $pdf->SetFillColor(200, 200, 200); // gray

        // Add a single page (autopagebreak will take over)
        self::addCustomPage($pdf);

        // Header
        self::renderHeader($pdf, 'part1');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        // Coop info row setup
        $leftLabelWidth      = 38;
        $middleContentWidth  = 105;
        $rightLabelWidth     = 17;
        $rightValueWidth     = 25;

        $fill = true;

        // 1st row: Coop Name
        $cells = [
            ['width' => $leftLabelWidth, 'text' => 'Name of Seed Grower Coop/Ass'],
            ['width' => 0,               'text' => $data['coop-name']],
        ];
        $row0finalHeight = self::getMaxRowHeightFromCells($pdf, $cells);
        $pdf->MultiCell($leftLabelWidth, $row0finalHeight, $cells[0]['text'], 1, 'L', true, 0, '', '', true, 0, false, true, $row0finalHeight, 'M');
        $pdf->MultiCell(0, $row0finalHeight, $cells[1]['text'], 1, 'L', false, 1, '', '', true, 0, false, true, $row0finalHeight, 'M');

        // 2nd row: Address

        $addressValue = $data['coop-address'];
        $cells1 = [
            ['width' => $leftLabelWidth,     'text' => 'Address'],
            ['width' => $middleContentWidth, 'text' => $addressValue ],
            ['width' => $rightLabelWidth,    'text' => 'IAR No.'],
            ['width' => 0,                   'text' => $data['IAR_no']],
        ];
        $row1finalHeight = self::getMaxRowHeightFromCells($pdf, $cells1);
        $pdf->MultiCell($leftLabelWidth, $row1finalHeight, 'Address', 1, 'L', true, 0, '', '', true, 0, false, true, $row1finalHeight, 'M');
        $pdf->MultiCell($middleContentWidth, $row1finalHeight, $cells1[1]['text'], 1, 'L', false, 0, '', '', true, 0, false, true, $row1finalHeight, 'M');
        $pdf->SetFont('', 'B');
        $pdf->MultiCell($rightLabelWidth, $row1finalHeight, 'IAR No.', 1, 'L', true, 0, '', '', true, 0, false, true, $row1finalHeight, 'M');
        $pdf->SetFont('', '');
        $pdf->MultiCell(0, $row1finalHeight, $data['IAR_no'], 1, 'L', false, 1, '', '', true, 0, false, true, $row1finalHeight, 'M');

        // 3rd row: Office Division + Date
        $pdf->MultiCell($leftLabelWidth, 10, "Office Division/\nPhilRice Station", 1, 'L', true, 0, '', '', true, 0, false, true, 10, 'M');
        $pdf->MultiCell($middleContentWidth, 10, "RCEP PMO", 1, 'L', false, 0, '', '', true, 0, false, true, 10, 'M');
        $pdf->SetFont('', 'B');
        $pdf->MultiCell($rightLabelWidth, 10, "Date", 1, 'L', true, 0, '', '', true, 0, false, true, 10, 'M');
        $pdf->SetFont('', '');
        $pdf->MultiCell(0, 10, $data['Date'], 1, 'L', false, 1, '', '', true, 0, false, true, 10, 'M');

        // 4th row: DOCS
        $pdf->MultiCell($leftLabelWidth, 8, "DOCS", 1, 'L', true, 0, '', '', true, 0, false, true, 8, 'M');
        $pdf->MultiCell(0, 8, "", 1, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');

        $pdf->Ln(1);

        // Main table (assumed to support autopagebreak)
        self::partOneTable($pdf, $data);

        // Signatures
        $pdf->SetFont('helvetica', 'B', 10);
        $halfWidth = $printableWidth / 2;
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell($halfWidth, 8, 'INSPECTION', 1, 0, 'C', true);
        $pdf->Cell($halfWidth, 8, 'ACCEPTANCE', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 9);
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();
        $lineWidth = ($halfWidth) * 0.7;
        $spaceSignatory = 4;
        $dateLine = $lineWidth/3.5;
        $largeBoxHeight = 48;


        // Inspection
        $inspectX = $startX + 3;
        $inspectY = $startY + 20;
        $pdf->SetXY($inspectX, $inspectY);
        $pdf->Cell($lineWidth, 6, '', 'B', 0, 'C');
        $pdf->Cell($spaceSignatory, 6, '', 0, 0);
        $pdf->Cell($dateLine, 6, '', 'B', 0, 'C');
        $pdf->SetXY($inspectX, $inspectY + 6);
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->MultiCell($lineWidth, 15, $data['sig-name-left'], 0, 'C', false, 0);
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->MultiCell($spaceSignatory, 6, '', 0, 'C', false, 0);
        $pdf->MultiCell($dateLine, 15, "Date Inspected\n(mm/dd/yy)", 0, 'C', false, 1);
        $pdf->SetXY($inspectX, $inspectY + 15);
        $pdf->Cell($lineWidth, 6, $data['sig-pos-left'], 'B', 1, 'C');
        $pdf->Cell($lineWidth, 6, '     Designation and Office', 0, 0, 'C');

        // Acceptance
        $acceptX = $startX + $halfWidth + 3;
        $acceptY = $startY + 20;
        $pdf->SetXY($acceptX, $acceptY);
        $pdf->Cell($lineWidth, 6, '', 'B', 0, 'C');
        $pdf->Cell($spaceSignatory, 6, '', 0, 0);
        $pdf->Cell($dateLine, 6, '', 'B', 0, 'C');
        $pdf->SetXY($acceptX, $acceptY + 6);
        // $pdf->setCellPaddings(0, 1, 0, 0); // 2mm top padding
        $pdf->MultiCell($lineWidth, 15, $data['sig-name-right'], 0, 'C', false, 0);
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->MultiCell($spaceSignatory, 6, '', 0, 'C', false, 0);
        $pdf->MultiCell($dateLine, 15, "Date Inspected\n(mm/dd/yy)", 0, 'C', false, 1);
        $pdf->SetXY($acceptX, $acceptY + 15);
        $pdf->Cell($lineWidth, 6, $data['sig-pos-right'], 'B', 1, 'C');
        $pdf->SetX($acceptX);
        $pdf->Cell($lineWidth, 6, 'Designation and Office', 0, 1, 'C');


        $pdf->SetXY($startX, $startY);
        $pdf->setCellPaddings(0, 1, 0, 0);
        $pdf->MultiCell($halfWidth, $largeBoxHeight, "INSPECTED, verified and found in order as to the\nquantity and specifications.", 1, 'C', false, 0, '', '', true, 0, false, true, 60, 'T');
        $pdf->MultiCell($halfWidth, $largeBoxHeight, "Complete Delivery", 1, 'C', false, 1, '', '', true, 0, false, true, 60, 'T');
        $pdf->setCellPaddings(1, 1, 1, 1);
        self::renderPartOneChecklist($pdf);
        // self::renderPartOneQR($pdf, $data['IAR_no']);
    }

    private static function partOneTable($pdf, $data){
        $area = self::getUsablePageArea($pdf);
        $printableWidth = $area['width'];
        // ====== Define columns ======
        // 6 columns: Code for CS, Item Description, Unit, Qty, Cost, Amount, MOA No.
        $tableColumns = [11, 32, 9, 7, 10, 13, 18];
        $tableColumnsPixel = [];
        for ($i = 0; $i < count($tableColumns); $i++) {
            $tableColumnsPixel[$i] = ($printableWidth * $tableColumns[$i]) / 100;;
        }

        $tableColumnsFooter = [$tableColumns[0], 1];// last "1" = auto-size
        $tableColumnsFooterFill = [true,false];
        $tableColumnHeight = 6;
        $purposeLabel = "Purpose";
        $prefixText = '';
        $codeForCS = '';

        $seedType = $data['seed-type'];
        if ($seedType ==='Regular'|| $seedType === 'Binhi e-Padala'){
            $prefixText = "For the CS";
            $codeForCS = "CS SEED";
        }
            
        elseif ($seedType === 'NRP'){
            $prefixText = "For Seed Reserve";
            
        }
        elseif ($seedType === 'Good Quality Seeds')
            $prefixText = "For Good Quality Seeds";//
        else
            $prefixText = "ERROR";//DO NOT PRINT IAR IF SEED TYPE IS EMPTY OR NULL

        $purpose = $prefixText . " delivery in " . $data['province'] . " , " . $data['municipality'] . " , " . $data['dop'];

        // ====== Header ======
        $pdf->SetFont('', 'B', 10);
        self::renderFlexibleRow(
            $pdf,
            ['Code for CS', 'Item Description', 'Unit', 'Qty', 'Cost', 'Amount', 'MOA No.'],
            $tableColumns,
            $tableColumnHeight,
            ['C', 'C', 'C', 'C', 'C', 'C', 'C'],
            true // fill all header cells
        );

        // ====== Body ======
        $pdf->SetFont('', '', 10);

        // 1st row of table: With MOA
        $codeVal = !empty($codeForCS) ? $codeForCS : '';
        $moaValue = !empty($data['MOA']) ? $data['MOA'] : '';
        
        $tableColumnsPixelFive = $printableWidth - array_sum($tableColumnsPixel);
        // Compute height based on content cell
        $height = $pdf->getStringHeight($tableColumnsPixel[6], $moaValue);
        $maxHeight = empty($moaValue) ? 6 : max($height - 4, 6);

        // Apply the same height to all
        $pdf->MultiCell($tableColumnsPixel[0], $maxHeight, $codeVal,   1, 'C', 0, 0, '', '', true, 0, false, false, $maxHeight, 'M');
        $pdf->MultiCell($tableColumnsPixel[1], $maxHeight, 'Variety 1:',   1, 'L', 0, 0, '', '', true, 0, false, false, $maxHeight, 'M');
        $pdf->MultiCell($tableColumnsPixel[2], $maxHeight, '20kg/Bag',   1, 'C', 0, 0, '', '', true, 0, false, false, $maxHeight, 'M');
        $pdf->MultiCell($tableColumnsPixel[3], $maxHeight, '',   1, 'C', 0, 0, '', '', true, 0, false, false, $maxHeight, 'M');
        $pdf->MultiCell($tableColumnsPixel[4], $maxHeight, '',   1, 'C', 0, 0, '', '', true, 0, false, false, $maxHeight, 'M');
        $pdf->MultiCell($tableColumnsPixel[5], $maxHeight, '',   1, 'C', 0, 0, '', '', true, 0, false, false, $maxHeight, 'M');
        $pdf->MultiCell($tableColumnsPixel[6], $maxHeight, $moaValue, 1, 'C', 0, 1, '', '', true, 0, false, false, $maxHeight, 'M');

        ////////////////////////////////

        $totalRows = 9; // fixed 9 rows (or change if you want dynamic)
        for ($i = 0; $i < $totalRows; $i++) {
            $ir = $i + 2;
            self::renderFlexibleRow(
                $pdf,
                ['', "Variety " . $ir . ":", '20kg/Bag', '', '', '', ''],
                $tableColumns,
                $tableColumnHeight,
                ['C', 'L', 'C', 'C', 'C', 'C', 'C']
            );
        }
        // self::renderFlexibleRow(
        //     $pdf,
        //     ['Total', '', '', '', '', '', ''],
        //     $tableColumns,
        //     $tableColumnHeight,
        //     ['C', 'L', 'L', 'L', 'L', 'L', 'L'],
        //     [True, True, True, False, False, False, True]
        // );

        $pdf->SetFont('', 'B', 10);
        $pdf->Cell($tableColumnsPixel[0]+$tableColumnsPixel[1]+$tableColumnsPixel[2], $tableColumnHeight, 'Total', 1, 0, 'C', 1);
        $pdf->Cell($tableColumnsPixel[3], $tableColumnHeight, '', 1, 0, 'C', 0);
        $pdf->Cell($tableColumnsPixel[4], $tableColumnHeight, '', 1, 0, 'C', 0);
        $pdf->Cell($tableColumnsPixel[5], $tableColumnHeight, '', 1, 0, 'C', 0);
        $pdf->Cell($tableColumnsPixel[6], $tableColumnHeight, '', 1, 1, 'C', 0); // last cell, ln=1

        $columnWidth = ($printableWidth * $tableColumns[0]) / 100;
        $contentWidth = $printableWidth - $columnWidth;

        // Compute height based on content cell
        $height = $pdf->getStringHeight($contentWidth, $purpose)+1;

        // Apply the same height to both
        $pdf->MultiCell($columnWidth, $height, $purposeLabel,   1, 'C', 1, 0, '', '', true, 0, false, false, $height, 'M');
        $pdf->SetFont('', '', 10);
        $pdf->MultiCell(0,            $height, $purpose, 1, 'L', 0, 1, '', '', true, 0, false, false, $height, 'M');
        
    }

    public static function renderPartTwo($pdf, $data){   
        self::addCustomPage($pdf);
        self::renderHeader($pdf, 'part2');

        // ====================== PAGE 2 – DELIVERY SCHEDULE ======================

        // Array: [ label, dynamic value key ]
        $labelData = [
            ["Seed Grower\nCooperative/Association : ", $data['coopName']],
            ["Province : ", $data['province']],
            ["Municipality/City : ", $data['municipality']],
            ["Drop-off Point : ", $data['drop_off_point']],
            ["Delivery Date : ", $data['date']],
            ["Batch Delivery Ticket : ", $data['ticket']]
        ];

        $currentFont = 13;

        $pdf->Ln(4);

        // Remove paddings for tighter layout
        $pdf->SetFont('helvetica', 'B', $currentFont);
        $pdf->setCellPaddings(0, 0, 0, 0);

        // Calculate max label width — account for \n by splitting lines
        $maxLabelWidth = 0;
        foreach ($labelData as $item) {
            $lines = explode("\n", $item[0]);
            foreach ($lines as $line) {
                $w = $pdf->GetStringWidth($line) + 5; // small padding
                if ($w > $maxLabelWidth) {
                    $maxLabelWidth = $w;
                }
            }
        }

        // Render labels & values
        foreach ($labelData as $item) {
            $label = $item[0];
            $value = $item[1];

            // Measure how many lines each will need
            $labelLines = $pdf->getNumLines($label, $maxLabelWidth);
            $valueLines = $pdf->getNumLines(
                $value,
                $pdf->getPageWidth() - $maxLabelWidth - $pdf->getMargins()['right'] - $pdf->getMargins()['left']
            );

            // Row height = max lines × line height
            $lineHeight = 6;
            $rowHeight  = max($labelLines, $valueLines) * $lineHeight;

            // Label (bold, fixed width)
            $pdf->SetFont('helvetica', 'B', $currentFont);
            $pdf->MultiCell($maxLabelWidth, $rowHeight, $label, 0, 'L', false, 0);

            // Value (underlined)
            $pdf->SetFont('helvetica', 'U', $currentFont);
            $pdf->MultiCell(0, $rowHeight, $value, 0, 'L', false, 1, '', '', true, 0, false, true, $rowHeight, 'B');

            $pdf->Ln(2);
            
        }
        // Restore default paddings and text
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Ln(3);

        // Render the delivery table
        self::renderDeliveryTable($pdf, $data);
    }

    private static function renderDeliveryTable($pdf, $data){
        $pdf->Ln(2); // Small gap before table

        // Define your column widths in % (0 = auto-fill)
        $tableColumns = [20, 17, 15, 0]; // Variety, Lab/Lot, Bags, Remarks
        $tableFooterColumns = [[$tableColumns[0], $tableColumns[1]], $tableColumns[2], $tableColumns[3]];
        $tableFooterColumnsFill = [true,false,false];
        $totalRows    = max(10, count($data['delivery'])); // at least 10 rows

        
        // ====== HEADER ======
        $pdf->SetFont('', 'B', 12);
        $area = self::getUsablePageArea($pdf);
        $pageWidth = $area['width'];

        $remainingWidth = $pageWidth-($pageWidth*array_sum($tableColumns)/100);
        
        $columnWidths = [];

        foreach ($tableColumns as $w) {
            $columnWidths[] = $w > 0
                ? ($w / 100) * $pageWidth
                : $remainingWidth;
        }

        $pdf->MultiCell($columnWidths[0], 14, 'Variety', 1, 'C', true, 0, '', '', true, 0, false, true, 14, 'M');
        $pdf->MultiCell($columnWidths[1], 14, 'Lab/Lot No.', 1, 'C', true, 0, '', '', true, 0, false, true, 14, 'M');
        $pdf->MultiCell($columnWidths[2], 14, "Quantity\n(no. of bags)", 1, 'C', true, 0, '', '', true, 0, false, true, 14, 'M');
        $pdf->MultiCell($columnWidths[3], 14, 'Remarks', 1, 'C', true, 1, '', '', true, 0, false, true, 14, 'M');

        // ====== BODY ======
        $pdf->SetFont('', '', 10);
        for ($i = 0; $i < $totalRows; $i++) {
            if (isset($data['delivery'][$i])) {
                $row = $data['delivery'][$i];
                self::renderFlexibleRow(
                    $pdf,
                    [$row->seedVariety, $row->seedTag, $row->totalBagCount, ''],
                    $tableColumns,
                    6,
                    ['C', 'C', 'C', 'L']
                );
            } else {
                self::renderFlexibleRow(
                    $pdf,
                    ['', '', '', ''],
                    $tableColumns,
                    6,
                    ['C', 'L', 'C', 'L']
                );
            }
        }

        // ====== TOTAL ROW ======
        $totalBags = array_sum(array_map(function ($row) {
            return $row->totalBagCount;
        }, $data['delivery']));

        self::renderFlexibleRow(
            $pdf,
            ['TOTAL EXPECTED DELIVERY', $totalBags, '***Nothing Follows***'],
            $tableFooterColumns,
            6,
            ['C', 'C', 'C'],
            $tableFooterColumnsFill// Fill background for total row
        );

        $pdf->Ln(3);
    }

    public static function renderPartThree($pdf, $data){
        self::addCustomPage($pdf);
        self::renderHeader($pdf, 'part3');
        self::renderCheckBoxes2($pdf);
        self::renderReceivingEntityForm($pdf, $data);
        $pdf->Ln(4);

        // 5. Acknowledgement text
        $pdf->SetFont('', '', 12);
        $ack = 'The undersigned hereby acknowledge receipt of seeds described above for temporary safekeeping until distribution and/or full retrieval.';
        $pdf->MultiCell(0, 5, $ack, 0, 'L', false, 1);

        $pdf->Ln(4);
        self::renderSigningForm($pdf, $data);
    }

    public static function renderSigningForm($pdf, $data){

            $usableWidth = self::getUsablePageArea($pdf)['width'];

            // Signatures

            $pdf->SetFont('helvetica', '', 12);
            $halfWidth = $usableWidth / 2;
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell($halfWidth, 8, 'Received By', 1, 0, 'C', true);
            $pdf->Cell($halfWidth, 8, 'Received From', 1, 1, 'C', true);

            $startX = $pdf->GetX();
            $startY = $pdf->GetY();
            $pdf->setCellPaddings(0, 0, 0, 0);
            $lineWidth = ($halfWidth) * 0.638;
            $spaceSignatory = 4;
            $dateLine = $lineWidth/2.5;
            $topMargin = 25;
            $signatoriesBoxHeight = 60;

            // Inspection
            $inspectX = $startX + 3;
            $inspectY = $startY + $topMargin;
            $pdf->SetXY($inspectX, $inspectY);
            $pdf->Cell($lineWidth, 6, '', 'B', 0, 'C');
            $pdf->Cell($spaceSignatory, 6, '', 0, 0);
            $pdf->Cell($dateLine, 6, '', 'B', 0, 'C');
            $pdf->SetXY($inspectX, $inspectY + 6);
            $pdf->MultiCell($lineWidth, 15, 'Name and Signature', 0, 'C', false, 0);
            $pdf->MultiCell($spaceSignatory, 6, '', 0, 'C', false, 0);
            $pdf->MultiCell($dateLine, 15, "Date\n(mm/dd/yy)", 0, 'C', false, 1);
            $pdf->SetXY($inspectX, $inspectY + 15);
            $pdf->Cell($lineWidth, 6, '', 'B', 1, 'C');
            $pdf->Cell($lineWidth, 6, '     Position/Affiliation', 0, 0, 'C');

            // Acceptance
            $acceptX = $startX + $halfWidth + 3;
            $acceptY = $startY + $topMargin;
            $pdf->SetXY($acceptX, $acceptY);
            $pdf->Cell($lineWidth, 6, '', 'B', 0, 'C');
            $pdf->Cell($spaceSignatory, 6, '', 0, 0);
            $pdf->Cell($dateLine, 6, '', 'B', 0, 'C');
            $pdf->SetXY($acceptX, $acceptY + 6);
            $pdf->MultiCell($lineWidth, 15, 'Name and Signature', 0, 'C', false, 0);
            $pdf->MultiCell($spaceSignatory, 6, '', 0, 'C', false, 0);
            $pdf->MultiCell($dateLine, 15, "Date\n(mm/dd/yy)", 0, 'C', false, 1);
            $pdf->SetXY($acceptX, $acceptY + 15);
            $pdf->Cell($lineWidth, 6, '', 'B', 1, 'C');
            $pdf->SetX($acceptX);
            $pdf->Cell($lineWidth, 6, 'Position/Affiliation', 0, 1, 'C');

            $pdf->SetXY($startX,$startY);
            $pdf->setCellPaddings(0, 1, 0, 0);
            $pdf->Cell($halfWidth, $signatoriesBoxHeight, '', 1, 0, 'C');
            $pdf->Cell($halfWidth, $signatoriesBoxHeight, '', 1, 1, 'C');
    }

    public static function renderCheckBoxes2($pdf){
        // --- Config ---
        $fontSize   = 15;
        $gapWidth   = 2; // gap between box and label
        $groupGap   = 5; // gap between LGU group and PhilRice group

        // Force no padding for clean alignment
        $pdf->setCellPaddings($gapWidth, 0, 0, 0);
        $pdf->SetFont('', '', $fontSize);
        $pdf->SetTextColor(0, 0, 0);

        // --- Box size matches text height ---
        $boxSize = $pdf->getStringHeight(0, 'A');

        // --- Measure label widths ---
        $lguWidth      = $pdf->GetStringWidth('LGU');
        $philRiceWidth = $pdf->GetStringWidth('PhilRice');

        // --- Calculate total component width ---
        $totalWidth = ($boxSize + $gapWidth + $lguWidth) +
                    $groupGap +
                    ($boxSize + $gapWidth + $philRiceWidth);

        // --- Center horizontally ---
        $centerX = ($pdf->getPageWidth() / 2) - ($totalWidth / 2);
        $pdf->SetX($centerX);

        // --- Draw LGU ---
        $pdf->Cell($boxSize, $boxSize, '', 1, 0, 'C'); // box
        $pdf->Cell($gapWidth + $lguWidth, $boxSize, 'LGU', 0, 0, 'L');

        // Gap between groups
        $pdf->Cell($groupGap, $boxSize, '', 0, 0);

        // --- Draw PhilRice ---
        $pdf->Cell($boxSize, $boxSize, '', 1, 0, 'C'); // box
        $pdf->Cell($gapWidth + $philRiceWidth, $boxSize, 'PhilRice', 0, 1, 'L');

        // Restore default padding
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->Ln(2);
    }

    public static function renderReceivingEntityForm($pdf, $data){   
        $receiving_entity = $data['municipality'] . ", " . $data['province'];
        $totalRows    = max(10, count($data['delivery'])); // at least 10 rows

        // Define your column widths in % (0 = auto-fill)
        $tableColumns = [20, 17, 15, 0]; // Variety, Lab/Lot, Bags, Remarks
        $firstColumns = [20, 0, 12, 13];

        $receiving_entity = $data['municipality'] . ", " . $data['province'];

        $pdf->Ln(4); // small gap before "Name of Receiving Entity"
        $pdf->SetFont('', '', 10);

        $area = self::getUsablePageArea($pdf);
        $pageWidth = $area['width'];

        // Compute remaining width from `0`
        $remainingWidth = $pageWidth - ($pageWidth * array_sum($firstColumns) / 100);

        // Convert % widths to absolute units
        $columnWidths = [];
        foreach ($firstColumns as $w) {
            $columnWidths[] = $w > 0
                ? ($w / 100) * $pageWidth
                : $remainingWidth;
        }

        // Define your cells
        $cells = [
            ['width' => $columnWidths[0], 'text' => 'Name of Receiving Entity:'],
            ['width' => $columnWidths[1], 'text' => strtoupper($receiving_entity)],
            ['width' => $columnWidths[2], 'text' => 'Date (mm/dd/yy)'],
            ['width' => $columnWidths[3], 'text' => ''],
        ];

        // Compute height based on tallest cell
        $rowHeight = self::getMaxRowHeightFromCells($pdf, $cells);

        // Render cells
        $pdf->MultiCell($cells[0]['width'], $rowHeight, $cells[0]['text'], 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell($cells[1]['width'], $rowHeight, $cells[1]['text'], 1, 'L', false, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell($cells[2]['width'], $rowHeight, $cells[2]['text'], 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell($cells[3]['width'], $rowHeight, $cells[3]['text'], 1, 'C', false, 1, '', '', true, 0, false, true, $rowHeight, 'M');

        $pdf->Ln(4); // small gap before "Table"

        // Table header
                // ====== HEADER ======
        $pdf->SetFont('', 'B', 12);
        $area = self::getUsablePageArea($pdf);
        $pageWidth = $area['width'];

        $remainingWidth = $pageWidth-($pageWidth*array_sum($tableColumns)/100);
        
        $columnWidths = [];

        foreach ($tableColumns as $w) {
            $columnWidths[] = $w > 0
                ? ($w / 100) * $pageWidth
                : $remainingWidth;
        }

        $pdf->MultiCell($columnWidths[0], 14, 'Variety', 1, 'C', true, 0, '', '', true, 0, false, true, 14, 'M');
        $pdf->MultiCell($columnWidths[1], 14, 'Lab/Lot No.', 1, 'C', true, 0, '', '', true, 0, false, true, 14, 'M');
        $pdf->MultiCell($columnWidths[2], 14, "Quantity\n(no. of bags)", 1, 'C', true, 0, '', '', true, 0, false, true, 14, 'M');
        $pdf->MultiCell($columnWidths[3], 14, 'Remarks', 1, 'C', true, 1, '', '', true, 0, false, true, 14, 'M');

        // Table rows
        $pdf->SetFont('', '', 10);
        for ($i = 0; $i < $totalRows; $i++) {
            if (isset($data['delivery'][$i])) {
                // Actual data row
                $row = $data['delivery'][$i];
                self::renderFlexibleRow(
                    $pdf,
                    [$row->seedVariety, $row->seedTag, $row->totalBagCount, ''],
                    $tableColumns,
                    6,
                    ['C', 'C', 'C', 'C']
                );
            } else {
                // Empty row
                self::renderFlexibleRow(
                    $pdf,
                    ['', '', '', ''],
                    $tableColumns,
                    6,
                    ['C', 'C', 'C', 'C']
                );
            }
        }

    }

    private static function renderFlexibleRow($pdf, $contents, $widths = null, $height = 6, $align = null, $fill = false){
        // Get usable printable area
        $area = self::getUsablePageArea($pdf);
        $printableWidth = $area['width'];

        if (!$widths) {
            $widths = array_fill(0, count($contents), 1); // default: auto
        }
        if (!$align) {
            $align = array_fill(0, count($contents), 'L');
        }

        // Normalize fill — make it an array
        if (!is_array($fill)) {
            $fill = array_fill(0, count($contents), (bool)$fill);
        }

        // Flatten widths for auto calculation
        $flatWidths = [];
        foreach ($widths as $w) {
            if (is_array($w)) {
                foreach ($w as $part) {
                    $flatWidths[] = $part;
                }
            } else {
                $flatWidths[] = $w;
            }
        }

        // Calculate auto widths
        $fixedTotal = 0;
        $autoCols   = 0;
        foreach ($flatWidths as $w) {
            if ($w > 1) {
                $fixedTotal += ($printableWidth * ($w / 100));
            } elseif ($w === 1) {
                $autoCols++;
            }
        }
        $autoWidth = $autoCols > 0 ? ($printableWidth - $fixedTotal) / $autoCols : 0;

        // Render
        foreach ($widths as $i => $w) {
            $cellWidth = 0;
            if (is_array($w)) {
                foreach ($w as $part) {
                    $cellWidth += ($part > 1) ? ($printableWidth * ($part / 100)) : $autoWidth;
                }
            } else {
                $cellWidth = ($w > 1) ? ($printableWidth * ($w / 100)) : $autoWidth;
            }

            $text = isset($contents[$i]) ? $contents[$i] : '';
            $pdf->Cell($cellWidth, $height, $text, 1, 0, $align[$i], $fill[$i]);
        }

        $pdf->Ln();
    }

    public static function renderPartOneChecklist($pdf){
        // Example checklist items
        $items = [
            "Check DR VS Delivery Schedule",
            "Check RLA with DR",
            "Conduct Random weighing",
            "Count total delivery = Delivery schedule",
            "Filled-up IAR form",
            "Signed acknowledgement receipt",
            "Completely filled-up apps",
            "Sent app data to server (local/web)"
        ];

        $boxSize = 6; // mm size of checkbox
        $space = 2;

        // Title
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->Cell(0, 8, 'CHECKLIST', 0, 1, 'L');

        // $pdf->Ln(1); // Small space
        $pdf->SetFont('helvetica', '', 12);
        $border_style = array(
            'LTRB' => array('width' => 0.8, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
        );

        $pdf->SetFont('', '', 10);
        for ($i = 0; $i < count($items); $i++) {
            // Draw checkbox     
            $pdf->Cell($boxSize, $boxSize, '', $border_style); // box
            //padding
            $pdf->Cell($space, $space); // box
            // Move right and write the text
            $pdf->Cell(0, $boxSize, $items[$i],0, 1, 'L');
            $pdf->Ln(1.5);
        }

        $pdf->SetLineStyle([
            'width' => 0.1,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => [0, 0, 0]
        ]);

    }

    public static function renderPartOneQR($pdf, $IAR_no){
        // Generate PNG QR code as raw binary
        $qrBinary = \QrCode::format('png')
            ->size(240)
            ->margin(0)
            ->generate($IAR_no);

        if ($qrBinary instanceof \Symfony\Component\HttpFoundation\Response) {
            $qrBinary = $qrBinary->getContent();
        }
        $qrBinary = (string) $qrBinary;

        // Page layout metrics
        $pageWidth   = $pdf->getPageWidth();
        $pageHeight  = $pdf->getPageHeight();
        $margins     = $pdf->getMargins();
        $rightMargin = $margins['right'];
        $bottomMargin = $margins['bottom'];

        $logoWidth  = 40; // mm
        $logoHeight = 40; // mm
        $labelHeight = 8; // mm

        // Position so label is above QR, both near bottom-right
        $xPos = $pageWidth - $rightMargin - $logoWidth - 5;
        $yPosQR = $pageHeight - $bottomMargin - $logoHeight - 5;  // QR bottom position
        $yPosLabel = $yPosQR - $labelHeight;                  // Label just above QR

        // Draw label ABOVE QR
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($xPos, $yPosLabel);

        $oldPadding = $pdf->getCellPaddings();
        $pdf->setCellPaddings(0, 1, 0, 1);
        $pdf->MultiCell($logoWidth, $labelHeight, "SCAN ME TO VIEW PAYMENT STATUS", 1, 'C', true, 1, '', '', true, 0, false, true, 0, 'T');
        // Restore original paddings
        $pdf->setCellPaddings(1, 1, 1, 1);

        // Draw QR BELOW the label
        $pdf->Image('@' . $qrBinary, $xPos, $yPosQR + 2, $logoWidth, $logoHeight, 'PNG');

    }

    public static function renderHeader($pdf, $partKey){   
        $pdf->SetTextColor(0, 0, 0);
        $headerFontSize = 16;
        $subHeaderFontSize = 14;

        $headerHeight = 25;
        $headerMargin = 1;
        $logoLeft        = public_path('images/da_philrice_iar.jpg');
        $logoRight       = public_path('images/rcef_logo_iar.jpg');
        $marginBottom    = 0; // space after header
        $logoHeight      = $headerHeight;
        $rightLogoWidth  = $headerHeight;

        $part = isset(self::$parts[$partKey]) ? self::$parts[$partKey] : self::$parts['part1'];

        // Absolute page positioning
        $currentPage = $pdf->getPage();
        $pageWidth   = $pdf->getPageWidth();
        $margins     = $pdf->getMargins();
        $usableWidth = $pageWidth - $margins['left'] - $margins['right'];

        // Force writing on the current page, starting at top margin
        $pdf->setPage($currentPage);
        $startY = $margins['top'];
        
        $pdf->SetXY($margins['left'], $startY);

        // Draw left logo
        $pdf->Image($logoLeft, $margins['left'], $startY, 0, $logoHeight);

        // Draw right logo
        $pdf->Image($logoRight, $pageWidth - $margins['right'] - $rightLogoWidth, $startY, $rightLogoWidth);

        // Move down slightly for title (absolute position)
        $pdf->SetY($startY + 2);
        $pdf->SetFont('helvetica', 'B', $headerFontSize);
        $pdf->Cell(0, 7, $part['title'], 0, 1, 'C', false);

        // Subtitle
        $pdf->SetFont('helvetica', '', $subHeaderFontSize);
        $pdf->Cell(0, 6, $part['subtitle'], 0, 1, 'C', false);

        // Collision check: pick whichever is lower (text bottom vs logo bottom)
        $currentY    = $pdf->GetY();
        $logoBottomY = $startY + $logoHeight;
        $nextBlockY  = max($currentY, $logoBottomY) + $marginBottom;

        // Now manually set Y for next content block
        $pdf->SetY($nextBlockY+$headerMargin);
    }

    public static function renderFooter($pdf){
        $footerHeight = 5;

        // Save cursor position
        $x = $pdf->GetX() ?: 0;
        $y = $pdf->GetY() ?: 0;


        // Get side margins
        $margins     = $pdf->getMargins();
        $leftMargin  = $margins['left'];
        $rightMargin = $margins['right'];

        // Calculate the printable width
        $printableWidth = $pdf->getPageWidth() - $leftMargin - $rightMargin;

        // Position footer using a fixed bottom margin
        $yPos = $pdf->getPageHeight() - $pdf->customBottom;

        // Temporarily disable auto page break
        list($apbStatus, $apbMargin) = $pdf->getAutoPageBreak();
        $apbMarginValue = $apbMargin;
        $pdf->SetAutoPageBreak(false, 0);

        // Draw footer text
        $pdf->SetXY($leftMargin, $yPos - $footerHeight/2);
        $pdf->SetFont('helvetica', 'I', $footerHeight * 2);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell($printableWidth, $footerHeight, 'PhilRice RCEF Seed IAR Rev 01 Effectivity Date: 29 June 2021', 0, 0, 'L', false);

        // Restore auto page break
        $pdf->SetAutoPageBreak($apbStatus, $pdf->customBottom);

        // Restore cursor position
        $pdf->SetXY($x, $y);
    }

    private static function addCustomPage($pdf){
        $pdf->AddPage();
        self::renderFooter($pdf);

        // Reapply stored margins & auto page break settings
        $pdf->SetMargins($pdf->customLeft, $pdf->customTop, $pdf->customRight);
        $pdf->SetAutoPageBreak(true, $pdf->customBottom);
    }

    public static function generateDeliveryPDF($data){
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Disable TCPDF's built-in header/footer completely
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins manually — these will be consistent for every AddPage()
        $pdf->customLeft   = 10;
        $pdf->customTop    = 10;
        $pdf->customRight  = 10;
        $pdf->customBottom = 10;

        // ====================== DATA SANITATION ======================
        if (isset($data['delivery']) && is_array($data['delivery'])) {
            foreach ($data['delivery'] as $row) {
                if (!isset($row->seedVariety))    $row->seedVariety = '';
                if (!isset($row->seedTag))        $row->seedTag = '';
                if (!isset($row->totalBagCount))  $row->totalBagCount = 0;
            }
        } else {
            $data['delivery'] = [];
        }

        $pdf->SetTitle('RCEF IAR - ' . $data['IAR_no']);

        // Render your parts manually
        self::renderPartOne($pdf, $data);
        
        self::renderPartTwo($pdf, $data);
 
        self::renderPartThree($pdf, $data);

        return $pdf->Output('', 'S');
    }
 
}