<?php
require_once __DIR__.'/../repositories/PanelRepository.php';
require_once __DIR__.'/../services/PdfService.php';

class ReporteController extends BaseController {
    public function resumen(): void {
        $this->auth();
        $datos = (new PanelRepository($this->db))->datosReporte();
        $datos['generadoPor'] = $_SESSION['usuario_nombre'] ?? 'Administrador';

        ob_start();
        try {
            extract($datos, EXTR_SKIP);
            require __DIR__.'/../views/reportes/resumen-pdf.php';
            $html = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        (new PdfService())->generar($html, 'resumen-idp-d2-'.date('Y-m-d').'.pdf');
        exit;
    }
}
