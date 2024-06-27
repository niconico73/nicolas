<?php

if ($peticion_ajax) {
    require_once "../modelos/pagoModelo.php";
} else {
    require_once "./modelos/pagoModelo.php";
    require_once '../modelos/mainModel.php';
}

class pagoControlador {

    public function __construct() {
    }

    public function editar_detalles_pago_controlador() {
        try {
            if ($_POST['modulo_pago'] == 'editar') {
                $pago_id = mainModel::limpiar_cadena($_POST['pago_id']);
                $numero_operacion = mainModel::limpiar_cadena($_POST['numero_operacion']);
                $bancos = $_POST['banco'];

                if (!empty($pago_id) && !empty($numero_operacion) && !empty($bancos)) {
                    $datos_pago = [
                        "ID" => $pago_id,
                        "NumeroOperacion" => $numero_operacion,
                        "Banco" => implode(", ", $bancos) // Convertir arreglo en cadena separada por comas
                    ];

                    $editar_pago = pagoModelo::editar_detalles_pago_modelo($datos_pago);

                    if ($editar_pago->rowCount() >= 1) {
                        $alerta = [
                            "Alerta" => "recargar",
                            "Titulo" => "Datos actualizados",
                            "Texto" => "El pago se ha actualizado correctamente.",
                            "Tipo" => "success"
                        ];
                    } else {
                        throw new Exception("No se pudo actualizar los datos del pago.");
                    }
                } else {
                    throw new Exception("Datos incompletos.");
                }
            } else {
                throw new Exception("Acción no permitida.");
            }
        } catch (Exception $e) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => $e->getMessage(),
                "Tipo" => "error"
            ];
        }

        return json_encode($alerta);
    }
}

?>