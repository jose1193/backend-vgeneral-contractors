<?php

namespace App\Services;

use PDF;

class PdfService
{
    public function generatePdfContent(string $filePath, array $data)
    {
        return PDF::loadView($filePath, $data)->output();
    }
}
