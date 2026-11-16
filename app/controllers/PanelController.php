<?php
require_once __DIR__.'/../repositories/PanelRepository.php';

class PanelController extends BaseController {
    public function resumen(): void {
        $this->auth();
        $panel = new PanelRepository($this->db);
        $this->render('panel/resumen', $panel->datosResumen());
    }
}
