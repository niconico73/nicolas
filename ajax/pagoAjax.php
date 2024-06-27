<?php
$peticion_ajax = true;
require_once "../config/APP.php";
include "../vistas/inc/session_start.php";

if (isset($_POST['modulo_pago'])) {
    require_once "../controladores/pagoControlador.php";
    $ins_pago = new pagoControlador();

    switch ($_POST['modulo_pago']) {
        case 'editar':
            echo $ins_pago->editar_detalles_pago_controlador();
            break;
        
        // Otros casos...

        default:
            echo json_encode(array("Alerta" => "simple", "Titulo" => "Error", "Texto" => "Módulo de pago no válido", "Tipo" => "error"));
            break;
    }
} else {
    session_destroy();
    header("Location: " . SERVERURL . "login/");
}
?>
