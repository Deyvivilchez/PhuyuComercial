<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH."/third_party/phuyu_tcpdf/tcpdf.php"; 
 
class Pdf extends TCPDF { 
    public function __construct() { 
        parent::__construct(); 
    }
    
    // Nuevo método para compatibilidad
    public function generate($html, $filename = 'document.pdf', $download = true) {
        $this->AddPage();
        $this->writeHTML($html, true, false, true, false, '');
        $this->Output($filename, $download ? 'I' : 'F');
    }
}