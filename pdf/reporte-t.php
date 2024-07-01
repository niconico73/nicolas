<?php
$peticion_ajax = true;
$fecha_inicio = (isset($_GET['fi'])) ? $_GET['fi'] : "";
$fecha_final = (isset($_GET['ff'])) ? $_GET['ff'] : "";
$error_fechas = "";
$usuario_seleccionado = (isset($_GET['usuario'])) ? $_GET['usuario'] : ""; // Recibir usuario seleccionado

require_once "../config/APP.php";
require_once "../controladores/ventaControlador.php";
require_once "../pdf/fpdf.php";

function verificar_fecha($fecha) {
    $valores = explode('-', $fecha);
    return !(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0]));
}

if (verificar_fecha($fecha_inicio) || verificar_fecha($fecha_final) || $fecha_inicio > $fecha_final) {
    $error_fechas = "Error en las fechas: ";
    if (verificar_fecha($fecha_inicio) || verificar_fecha($fecha_final)) {
        $error_fechas .= "Ha introducido fechas que no son correctas. ";
    }
    if ($fecha_inicio > $fecha_final) {
        $error_fechas .= "La fecha de inicio no puede ser mayor que la fecha final.";
    }
}

if ($error_fechas == "") {
    $ins_venta = new ventaControlador();
    $datos_empresa = $ins_venta->datos_tabla("Normal", "empresa LIMIT 1", "*", 0)->fetch();

    ob_start(); // Iniciar el buffer de salida

    $pdf = new FPDF('P', 'mm', 'Letter');
    $pdf->AddPage();
    $pdf->Image(SERVERURL.'vistas/assets/img/logo.png',165,12,35,35,'PNG');
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, mb_strtoupper($datos_empresa['empresa_nombre'], 'UTF-8'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, $datos_empresa['empresa_tipo_documento'] . ": " . $datos_empresa['empresa_numero_documento'], 0, 1, 'C');
    $pdf->Cell(0, 5, $datos_empresa['empresa_direccion'], 0, 1, 'C');
    $pdf->Cell(0, 5, "Telefono: " . $datos_empresa['empresa_telefono'], 0, 1, 'C');
    $pdf->Cell(0, 5, "Email: " . $datos_empresa['empresa_email'], 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    // Construir el título dinámico
    if (!empty($usuario_id) && $usuario_id !== "all") {
        $datos_usuario = $ins_venta->datos_tabla("Normal", "usuario WHERE usuario_id = '$usuario_id'", "usuario_nombre", 0)->fetch();
        $usuario_nombre = $datos_usuario['usuario_nombre'];
        $titulo_reporte = "Reporte de Pagos para el Usuario $usuario_nombre desde $fecha_inicio hasta $fecha_final";
    } else {
        $titulo_reporte = "Reporte de Pagos para Todos los Usuarios desde $fecha_inicio hasta $fecha_final";
    }

    $pdf->Cell(0, 10, $titulo_reporte, 0, 1, 'C'); // Mostrar el título dinámico
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 8); // Tamaño de fuente más pequeño
    $pdf->SetFillColor(0, 0, 0); // Color de fondo negro
    $pdf->SetTextColor(255, 255, 255); // Color de letras blanco

    $pdf->Cell(50, 8, 'Banco', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Total Pagado', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9); // Restaurar la fuente normal
    $pdf->SetTextColor(0, 0, 0); // Restaurar el color de texto a negro para las celdas restantes

    $consulta = "SELECT p.banco, SUM(p.pago_monto) AS total_pagado
                 FROM pago p
                 INNER JOIN usuario u ON p.usuario_id = u.usuario_id
                 WHERE p.pago_fecha BETWEEN '$fecha_inicio' AND '$fecha_final'";
    if ($usuario_seleccionado !== "all") {
        $consulta .= " AND u.usuario_id = '$usuario_seleccionado'";
    }
    $consulta .= " GROUP BY p.banco
                  ORDER BY p.banco";

    $stmt = $ins_venta->ejecutar_consulta_simple($consulta);

    if ($stmt->rowCount() >= 1) {
        while ($pago = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdf->Cell(50, 7, $pago['banco'], 1, 0, 'C');
            $pdf->Cell(50, 7, MONEDA_SIMBOLO . number_format($pago['total_pagado'], MONEDA_DECIMALES, MONEDA_SEPARADOR_DECIMAL, MONEDA_SEPARADOR_MILLAR), 1, 1, 'C');
        }
    } else {
        $pdf->Cell(100, 7, "No hay datos de pagos para mostrar", 1, 1, 'C');
    }

    ob_end_clean(); // Limpiar el buffer de salida antes de la salida del PDF
    $pdf->Output("I", "Reporte_pagos_" . $fecha_inicio . "_a_" . $fecha_final . ".pdf", true);
    
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum=1.0">
    <title>Reporte de Pagos por Entidad Bancaria</title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>vistas/css/main.css">
</head>
<body>
    <div class="main">
        <div class="content-page">
            <div class="title-page">
                <h1 class="title">Reporte de Pagos por Entidad Bancaria</h1>
                <p class="subtitle"><?php echo $error_fechas; ?></p>
                <p class="subtitle">Vuelve a intentarlo <a href="<?php echo SERVERURL; ?>reporte-pagos/">aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}
?>
