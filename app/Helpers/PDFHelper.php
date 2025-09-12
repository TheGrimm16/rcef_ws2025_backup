<?php

namespace App\Helpers;

use Mpdf\Mpdf;

class PDFHelper
{
    public static function generateFromView($view, $data, $output = 'download', $filename = 'document.pdf')
    {
        $html = view($view, $data)->render();

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf-temp'), // Laravel 5.6 needs writable temp dir
            'format' => 'A4',
            'orientation' => 'P'
        ]);

        $mpdf->WriteHTML($html);

        if ($output === 'stream') {
            return response($mpdf->Output($filename, \Mpdf\Output\Destination::INLINE), 200)
                ->header('Content-Type', 'application/pdf');
        }

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
