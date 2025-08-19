<?php

namespace App\Services;

use Spatie\PdfToText\Pdf;
use Smalot\PdfParser\Parser;

class PdfService
{
    public function pages(string $path): array
    {
        // Intento con pdftotext (más preciso)
        $pages = [];
        try {
            // Obtener número de páginas con smalot (rápido)
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $count = count($pdf->getPages());

            $bin = config('pdf.pdftotext_bin', '/usr/bin/pdftotext');
            for ($i=1; $i<=$count; $i++) {
                $txt = Pdf::getText($path, $bin, [
                    "-layout", "-f", (string)$i, "-l", (string)$i
                ]);
                $pages[] = trim($txt ?? '');
            }
            return $pages;
        } catch (\Throwable $e) {
            // Fallback: smalot (mantiene páginas)
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            return array_map(fn($p) => trim($p->getText()), $pdf->getPages());
        }
    }
}
