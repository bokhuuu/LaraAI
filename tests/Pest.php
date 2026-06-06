<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function createFakePdf(int $pages = 1): string
{
    $path = sys_get_temp_dir() . '/test_' . uniqid() . '.pdf';

    // Minimal valid PDF structure with the requested number of pages
    $kids = '';
    $pageObjects = '';

    for ($i = 0; $i < $pages; $i++) {
        $objectNumber = $i + 4;
        $kids .= $objectNumber . ' 0 R ';
        $pageObjects .= "{$objectNumber} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\n";
    }

    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $pdf .= "2 0 obj\n<< /Type /Pages /Kids [{$kids}] /Count {$pages} >>\nendobj\n";
    $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\n";
    $pdf .= $pageObjects;
    $pdf .= "xref\n0 " . ($pages + 4) . "\ntrailer\n<< /Size " . ($pages + 4) . " /Root 1 0 R >>\nstartxref\n0\n%%EOF";

    file_put_contents($path, $pdf);

    return $path;
}
