<?php
$peticion_ajax = true;
require_once "../config/APP.php";
include "../vistas/inc/session_start.php";

if (isset($_POST['modulo_venta'])) {
    // Establecer la conexión a la base de datos aquí, antes del switch
    require_once "../modelos/mainModel.php"; // Asegúrate de incluir el modelo principal que tiene la función 'conectar()'
    $conexion = mainModel::conectar(); // Establecer la conexión

    require_once "../controladores/ventaControlador.php";
    $ins_venta = new ventaControlador();

    switch ($_POST['modulo_venta']) {
        case 'agregar_producto':
            echo $ins_venta->agregar_producto_carrito_controlador();
            break;
            case 'actualizar_estado_venta_y_factura':
                try {
                    $venta_id = $_POST['venta_id'];
                    $nuevo_estado_factura = $_POST['nuevo_estado_factura'];
            
                    // Validar el nuevo estado (opcional)
                    $estados_validos = ['No Facturado', 'Facturado'];
                    if (!in_array($nuevo_estado_factura, $estados_validos)) {
                        throw new Exception("Estado de factura no válido");
                    }
            
                    // Actualizar la columna 'venta_estado' y 'factura_impresa' en la base de datos
                    $sql = "UPDATE venta SET venta_estado = ?, factura_impresa = ? WHERE venta_id = ?";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ($nuevo_estado_factura === 'Facturado' ? 'Impreso' : 'Cancelado'), // Actualiza venta_estado
                        ($nuevo_estado_factura === 'Facturado' ? 1 : 0), // Actualiza factura_impresa
                        $venta_id
                    ]);
            
                    echo json_encode(array("success" => true, "message" => "Estado de la venta y facturación actualizados"));
                } catch (PDOException $e) {
                    error_log("Error al actualizar el estado de la venta: " . $e->getMessage()); // Registrar el error en el servidor
                    echo json_encode(array("success" => false, "message" => "Error al actualizar el estado de la venta"));
                }
                break;
        case 'eliminar_producto':
            echo $ins_venta->eliminar_producto_carrito_controlador();
            break;

        case 'actualizar_producto':
            echo $ins_venta->actualizar_producto_carrito_controlador();
            break;

        case 'buscar_cliente':
            echo $ins_venta->buscar_cliente_venta_controlador();
            break;

        case 'agregar_cliente':
            echo $ins_venta->agregar_cliente_venta_controlador();
            break;

        case 'eliminar_cliente':
            echo $ins_venta->eliminar_cliente_venta_controlador();
            break;

        case 'buscar_codigo':
            echo $ins_venta->buscar_codigo_venta_controlador();
            break;

        case 'aplicar_descuento':
            echo $ins_venta->aplicar_descuento_venta_controlador();
            break;

        case 'remover_descuento':
            echo $ins_venta->remover_descuento_venta_controlador();
            break;

        case 'registrar_venta':
            echo $ins_venta->registrar_venta_controlador();
            break;

        case 'agregar_pago':
            echo $ins_venta->agregar_pago_venta_controlador();
            break;

        case 'eliminar_venta':
            echo $ins_venta->eliminar_venta_controlador();
            break;

        case 'editar_pago':
            echo $ins_venta->editar_pago_controlador();
            break;

        default:
            echo json_encode(array("success" => false, "message" => "Módulo de venta no válido"));
            break;
    }

    // Cerrar la conexión a la base de datos después de procesar la solicitud
    $conexion = null; 
} else {
    session_destroy();
    header("Location: " . SERVERURL . "login/");
}
?>
