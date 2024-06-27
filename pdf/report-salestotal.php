<?php

$peticion_ajax = true;
$fecha_inicio = (isset($_GET['fi'])) ? $_GET['fi'] : "";
$fecha_final = (isset($_GET['ff'])) ? $_GET['ff'] : "";
$usuario_id = (isset($_GET['usuario_id'])) ? $_GET['usuario_id'] : "";
$error_fechas = "";

/*---------- Incluyendo configuraciones ----------*/
require_once "../config/APP.php";

function verificar_fecha($fecha)
{
    $valores = explode('-', $fecha);
    if (count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])) {
        return false;
    } else {
        return true;
    }
}

if (verificar_fecha($fecha_inicio) || verificar_fecha($fecha_final)) {
    $error_fechas .= "Ha introducido fecha que no son correctas. ";
}

if ($fecha_inicio > $fecha_final) {
    $error_fechas .= "La fecha de inicio no puede ser mayor que la fecha final";
}

if ($error_fechas == "") {

    /*---------- Instancia al controlador venta ----------*/
    require_once "../controladores/ventaControlador.php";
    $ins_venta = new ventaControlador();

    /*---------- Seleccion de datos de la empresa ----------*/
    $datos_empresa = $ins_venta->datos_tabla("Normal", "empresa LIMIT 1", "*", 0);
    $datos_empresa = $datos_empresa->fetch();

    require "./code128.php";

    $pdf = new PDF_Code128('P', 'mm', 'Letter');
    $pdf->SetMargins(17, 17, 17);
    $pdf->AddPage();
    $pdf->Image(SERVERURL . 'vistas/assets/img/logo.png', 165, 12, 35, 35, 'PNG');

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0); // Cambiar a color negro
    $pdf->Cell(150, 10, mb_convert_encoding(strtoupper($datos_empresa['empresa_nombre']), 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

    $pdf->Ln(9);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Cambiar a color negro
    $pdf->Cell(150, 9, mb_convert_encoding($datos_empresa['empresa_tipo_documento'] . ": " . $datos_empresa['empresa_numero_documento'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

    $pdf->Ln(5);

    $pdf->Cell(150, 9, mb_convert_encoding($datos_empresa['empresa_direccion'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

    $pdf->Ln(5);

    $pdf->Cell(150, 9, mb_convert_encoding("Teléfono: " . $datos_empresa['empresa_telefono'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

    $pdf->Ln(5);

    $pdf->Cell(150, 9, mb_convert_encoding("Email: " . $datos_empresa['empresa_email'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');

    $pdf->Ln(15);

    // Obtener nombre del usuario seleccionado
    if ($usuario_id == "all") {
        $nombre_usuario = "Todos los usuarios";
    } else {
        $consulta_usuario = $ins_venta->datos_tabla("Normal", "usuario WHERE usuario_id='$usuario_id'", "usuario_nombre", 1);
        $nombre_usuario = $consulta_usuario->fetch()['usuario_nombre'];
    }

    // Agregar línea para mostrar el usuario seleccionado
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, mb_convert_encoding('VENTAS DEL USUARIO: ' . $nombre_usuario, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

    $pdf->MultiCell(0, 9, mb_convert_encoding(strtoupper("Reporte de totales " . $fecha_inicio . " a " . $fecha_final), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

    $pdf->SetFillColor(0, 0, 0); // Cambiar a color negro
    $pdf->SetDrawColor(0, 0, 0); // Cambiar a color negro
    $pdf->SetTextColor(255, 255, 255); // Cambiar a color blanco
    $pdf->Cell(90, 8, mb_convert_encoding('Producto', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
    $pdf->Cell(90, 8, mb_convert_encoding('Cantidad vendida', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);

    $pdf->Ln(8);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Cambiar a color negro

    /*----------  Seleccionando datos de las ventas del usuario seleccionado ----------*/
    if ($usuario_id == "all") {
        $consulta_ventas = $ins_venta->datos_tabla("Normal", "venta_detalle vd INNER JOIN venta v ON vd.venta_codigo = v.venta_codigo INNER JOIN usuario u ON v.usuario_id = u.usuario_id INNER JOIN producto p ON vd.producto_id = p.producto_id WHERE v.venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final'", "p.producto_nombre, SUM(vd.venta_detalle_cantidad) AS cantidad_vendida", 0, "GROUP BY p.producto_nombre");
    } else {
        $consulta_ventas = $ins_venta->datos_tabla("Normal", "venta_detalle vd INNER JOIN venta v ON vd.venta_codigo = v.venta_codigo INNER JOIN usuario u ON v.usuario_id = u.usuario_id INNER JOIN producto p ON vd.producto_id = p.producto_id WHERE v.venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final' AND u.usuario_id = '$usuario_id'", "p.producto_nombre, SUM(vd.venta_detalle_cantidad) AS cantidad_vendida", 0, "GROUP BY p.producto_nombre");
    }

    if ($consulta_ventas->rowCount() >= 1) {
        $datos_ventas = $consulta_ventas->fetchAll();

        foreach ($datos_ventas as $ventas) {
            $pdf->Cell(90, 7, mb_convert_encoding($ventas['producto_nombre'], 'ISO-8859-1', 'UTF-8'), 'LB', 0, 'C');
            $pdf->Cell(90, 7, mb_convert_encoding($ventas['cantidad_vendida'], 'ISO-8859-1', 'UTF-8'), 'LBR', 0, 'C');
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(180, 7, mb_convert_encoding("No hay datos de ventas para mostrar", 'ISO-8859-1', 'UTF-8'), 'LBR', 0, 'C');
    }

    // Limpiamos el buffer de salida antes de generar el PDF
    ob_clean();

    $pdf->Output("I", "Reporte totales " . $fecha_inicio . " a " . $fecha_final . ".pdf", true);

} else {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <title><?php echo COMPANY; ?></title>
        <?php include '../vistas/inc/Head.php'; ?>
    </head>
    <body>
    <div class="full-box container-404">
        <div>
            <p class="text-center"><i class="far fa-thumbs-down fa-10x"></i></p>
            <h1 class="text-center">¡Ocurrió un error!</h1>
            <p class="lead text-center"><?php echo $error_fechas; ?></p>
        </div>
    </div>
    <?php include '../vistas/inc/Script.php'; ?>
    </body>
    </html>
    <?php
}
?>
