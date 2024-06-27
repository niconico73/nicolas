<?php
$peticion_ajax = true;
$fecha_inicio = isset($_GET['fi']) ? $_GET['fi'] : "";
$fecha_final = isset($_GET['ff']) ? $_GET['ff'] : "";
$error_fechas = "";

/*---------- Incluyendo configuraciones ----------*/
require_once "../config/APP.php";

function verificar_fecha($fecha){
    $valores = explode('-', $fecha);
    if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
        return false;
    } else {
        return true;
    }
}

if(verificar_fecha($fecha_inicio) || verificar_fecha($fecha_final)){
    $error_fechas .= "Ha introducido fechas que no son correctas. ";
}

if($fecha_inicio > $fecha_final){
    $error_fechas .= "La fecha de inicio no puede ser mayor que la fecha final";
}

if($error_fechas == ""){

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
    
    $pdf->MultiCell(0, 9, mb_convert_encoding(strtoupper("Reporte de ventas " . $fecha_inicio . " a " . $fecha_final), 'ISO-8859-1', 'UTF-8'), 0, 'C', false);

    $pdf->SetFillColor(23, 83, 201);
    $pdf->SetDrawColor(23, 83, 201);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(45, 8, mb_convert_encoding('N° Venta', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
    $pdf->Cell(45, 8, mb_convert_encoding('Cliente', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, mb_convert_encoding('Monto', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, mb_convert_encoding('N° Operación', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
    $pdf->Ln(8);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(39, 39, 51);

    /*---------- Seleccionando datos de las ventas y pagos ----------*/
    $consulta = "SELECT
                    v.venta_id AS numero_venta,
                    CONCAT(c.cliente_nombre, ' ', c.cliente_apellido) AS cliente,
                    v.venta_total_final AS monto,
                    GROUP_CONCAT(p.numero_operacion) AS numeros_operacion
                FROM
                    venta v
                    INNER JOIN cliente c ON v.cliente_id = c.cliente_id
                    LEFT JOIN pago p ON v.venta_codigo = p.venta_codigo
                WHERE
                    v.venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final'
                GROUP BY
                    v.venta_id";

    $stmt = $ins_venta->ejecutar_consulta_simple($consulta);

    if ($stmt->rowCount() >= 1) {
        $datos_ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($datos_ventas as $ventas) {
            $pdf->Cell(45, 7, mb_convert_encoding($ventas['numero_venta'], 'ISO-8859-1', 'UTF-8'), 'LB', 0, 'C');
            $pdf->Cell(45, 7, mb_convert_encoding($ventas['cliente'], 'ISO-8859-1', 'UTF-8'), 'LB', 0, 'C');
            $pdf->Cell(30, 7, mb_convert_encoding(MONEDA_SIMBOLO . number_format($ventas['monto'], MONEDA_DECIMALES, MONEDA_SEPARADOR_DECIMAL, MONEDA_SEPARADOR_MILLAR) . ' ' . MONEDA_NOMBRE, 'ISO-8859-1', 'UTF-8'), 'LB', 0, 'C');
            $pdf->Cell(40, 7, mb_convert_encoding($ventas['numeros_operacion'], 'ISO-8859-1', 'UTF-8'), 'LRB', 0, 'C');
            $pdf->Ln(7);
        }
    } else {
        $pdf->Cell(190, 7, mb_convert_encoding("No hay datos de ventas para mostrar", 'ISO-8859-1', 'UTF-8'), 'LBR', 0, 'C');
    }

    // Limpiamos el buffer de salida antes de generar el PDF
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
                <p class="subtitle">Error en las fechas: <?php echo $error_fechas; ?></p>
                <p class="subtitle">Vuelve a intentarlo <a href="<?php echo SERVERURL; ?>reporte-ventas/">aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}
?>
