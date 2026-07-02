<?php

class PdfReportHelper
{
    public static function buildHtmlToPdf(string $html, string $filename): string
    {
        $tempDir = sys_get_temp_dir() . '/estoque_reports';
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
                throw new RuntimeException('Não foi possível criar a pasta temporária para PDFs.');
            }
        }

        $htmlFile = $tempDir . '/' . uniqid('report_', true) . '.html';
        $pdfFile = $tempDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.pdf';

        file_put_contents($htmlFile, $html);

        $command = 'libreoffice --headless --nologo --convert-to pdf --outdir ' . escapeshellarg($tempDir) . ' ' . escapeshellarg($htmlFile) . ' 2>&1';
        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        @unlink($htmlFile);

        if ($exitCode !== 0) {
            throw new RuntimeException('Erro ao gerar PDF: ' . implode(PHP_EOL, $output));
        }

        $generatedFile = $tempDir . '/' . pathinfo($htmlFile, PATHINFO_FILENAME) . '.pdf';
        if (!file_exists($generatedFile)) {
            throw new RuntimeException('O arquivo PDF não foi criado.');
        }

        if (file_exists($pdfFile)) {
            @unlink($pdfFile);
        }

        rename($generatedFile, $pdfFile);

        return $pdfFile;
    }
}
