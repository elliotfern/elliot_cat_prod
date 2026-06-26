<?php

namespace App\Utils;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private function create(): Dompdf
    {
        $chroot = rtrim($_ENV['MEDIA_CHROOT'] ?? '/', '/');

        $options = new Options();

        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', $chroot);

        return new Dompdf($options);
    }

    public function output(
        string $html,
        string $paper = 'A4',
        string $orientation = 'portrait'
    ): string {

        $dompdf = $this->create();

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    public function download(
        string $html,
        string $filename,
        string $paper = 'A4',
        string $orientation = 'portrait'
    ): void {

        $pdf = $this->output(
            $html,
            $paper,
            $orientation
        );

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));

        echo $pdf;
        exit;
    }

    public function save(
        string $html,
        string $path
    ): bool {

        return file_put_contents(
            $path,
            $this->output($html)
        ) !== false;
    }
}
