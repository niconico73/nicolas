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
    $pdf->SetFont('Arial', 'B', 12);

// Verificar si se seleccionó un usuario o todos
if (!empty($usuario_id) && $usuario_id !== "all") {
    // Obtener el nombre del usuario desde la base de datos
    $datos_usuario = $ins_venta->datos_tabla("Normal", "usuario WHERE usuario_id = '$usuario_id'", "usuario_nombre", 0)->fetch();
    $usuario_nombre = $datos_usuario['usuario_nombre'];
    $titulo_reporte = "Reporte de Ventas para el Usuario $usuario_nombre desde $fecha_inicio hasta $fecha_final";
} else {
    $titulo_reporte = "Reporte de Ventas para Todos los Usuarios desde $fecha_inicio hasta $fecha_final";
}

$pdf->Cell(0, 10, $titulo_reporte, 0, 1, 'C'); // Mostrar el título dinámico
$pdf->Ln(5);
    // Tabla de ventas
$pdf->SetFont('Arial', 'B', 8); // Tamaño de fuente más pequeño

// Encabezados de las columnas
$pdf->SetFillColor(0, 0, 0); // Color de fondo negro
$pdf->SetTextColor(255, 255, 255); // Color de letras blanco

$pdf->Cell(15, 8, 'N', 1, 0, 'C', true); // Fondo negro para el encabezado
$pdf->Cell(30, 8, 'Codigo', 1, 0, 'C', true); // Fondo negro para el encabezado
$pdf->Cell(50, 8, 'Cliente', 1, 0, 'C', true); // Fondo negro para el encabezado
$pdf->Cell(20, 8, 'Monto', 1, 0, 'C', true); // Fondo negro para el encabezado
$pdf->Cell(20, 8, 'Fecha', 1, 0, 'C', true); // Fondo negro para el encabezado
$pdf->Cell(25, 8, 'Banco', 1, 0, 'C', true); // Fondo negro para el encabezado
$pdf->Cell(35, 8, 'N Operacion', 1, 1, 'C', true); // Fondo negro para el encabezado y salto de línea

$pdf->SetFont('Arial', '', 9); // Restaurar la fuente normal
$pdf->SetTextColor(0, 0, 0); // Restaurar el color de texto a negro para las celdas restantes

// Continuar con el contenido de la tabla


    // Consulta SQL con LEFT JOIN para pagos y filtro por usuario
    $consulta = "SELECT v.venta_id, v.venta_codigo, CONCAT(c.cliente_nombre, ' ', c.cliente_apellido) AS cliente, 
                        v.venta_total_final AS monto, v.venta_fecha, p.banco, p.numero_operacion, u.usuario_nombre AS usuario
                 FROM venta v 
                 INNER JOIN cliente c ON v.cliente_id = c.cliente_id
                 INNER JOIN usuario u ON v.usuario_id = u.usuario_id 
                 LEFT JOIN pago p ON v.venta_codigo = p.venta_codigo 
                 WHERE v.venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final' ";

if (!empty($usuario_id)) {
    $consulta .= " AND v.usuario_id = '$usuario_id'"; // Filtrar solo si hay un usuario específico
}

    $stmt = $ins_venta->ejecutar_consulta_simple($consulta);

    if ($stmt->rowCount() >= 1) {
        $contador = 1; // Inicializar contador para el número de fila
        while ($venta = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdf->Cell(15, 7, $contador, 1, 0, 'C'); // Mostrar número de fila
            $pdf->Cell(30, 7, $venta['venta_codigo'], 1, 0, 'C');
            $pdf->Cell(50, 7, $venta['cliente'], 1, 0, 'C');
            $pdf->Cell(20, 7, MONEDA_SIMBOLO . number_format($venta['monto'], MONEDA_DECIMALES, MONEDA_SEPARADOR_DECIMAL, MONEDA_SEPARADOR_MILLAR), 1, 0, 'C');
            $pdf->Cell(20, 7, $venta['venta_fecha'], 1, 0, 'C');
            $pdf->Cell(25, 7, $venta['banco'] ?? 'N/A', 1, 0, 'C'); 
            $pdf->Cell(35, 7, $venta['numero_operacion'] ?? 'N/A', 1, 1, 'C'); 
            $contador++; // Incrementar contador
        }
    } else {
        $pdf->Cell(190, 7, "No hay datos de ventas para mostrar", 1, 1, 'C');
    }

    ob_clean(); 
    $pdf->Output("I", "Reporte ventas " . $fecha_inicio . " a " . $fecha_final . ".pdf", true);
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Reporte de Ventas</title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>vistas/css/main.css">
</head>
<body>
    <div class="main">
        <div class="content-page">
            <div class="title-page">
                <h1 class="title">Reporte de Ventas</h1>
                <p class="subtitle"><?php echo $error_fechas; ?></p>
                <p class="subtitle">Vuelve a intentarlo <a href="<?php echo SERVERURL; ?>reporte-ventas/">aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}
?>
