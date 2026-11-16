<?php
// Dompdf es la única librería nueva: convierte la plantilla HTML a PDF.
class PdfService {
    public function generar(string $html, string $nombre): void {
        $raiz = dirname(__DIR__, 2);
        $autoload = $raiz.'/vendor/autoload.php';
        if (!is_file($autoload)) {
            throw new RuntimeException('Falta instalar Dompdf con Composer.');
        }
        require_once $autoload;

        $pdf = new \Dompdf\Dompdf([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'chroot' => $raiz,
        ]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('letter', 'portrait');
        $pdf->render();
        $pdf->stream($nombre, ['Attachment' => false]);
    }
}
