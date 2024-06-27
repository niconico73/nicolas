<?php

$peticion_ajax = true;
$fecha_inicio = (isset($_GET['fi'])) ? $_GET['fi'] : "";
$fecha_final = (isset($_GET['ff'])) ? $_GET['ff'] : "";
$error_fechas = "";

/*---------- Incluyendo configuraciones ----------*/
require_once "../config/APP.php";
require_once "../config/mainModel.php"; // Asegúrate de incluir el modelo principal

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

    // Instanciamos la clase TCPDF para generar el PDF
    require_once('../vendor/tecnickcom/tcpdf/tcpdf.php'); // Ruta ajustada según tu estructura

    class PDFReport extends TCPDF
    {

        // Encabezado del reporte
        public function Header()
        {
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 15, 'Reporte de Pagos', 0, true, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Pie de página con el número de página
        public function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    // Creación de instancia de PDFReport
    $pdf = new PDFReport(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Reporte de Pagos');
    $pdf->SetSubject('Reporte');
    $pdf->SetKeywords('TCPDF, PDF, report, pagos');

    // Información del documento
    $pdf->SetHeaderData('', '', 'Reporte de Pagos', 'Generado: ' . date('d-m-Y'));

    // Fuente y márgenes
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Añadir una página
    $pdf->AddPage();

    // Encabezado de la tabla
    $html = '<h2>Lista de Pagos</h2>';
    $html .= '<table border="1" cellpadding="4">
        <thead>
            <tr>
                <th style="background-color: #f2f2f2;">N° Venta</th>
                <th style="background-color: #f2f2f2;">Cliente</th>
                <th style="background-color: #f2f2f2;">Monto</th>
                <th style="background-color: #f2f2f2;">Banco</th>
                <th style="background-color: #f2f2f2;">Numero de Operacion</th>
            </tr>
        </thead>
        <tbody>';

    // Conexión a la base de datos y ejecución de la consulta
    $conexion = mainModel::conectar();
    $consulta = $conexion->query("
        SELECT
            v.venta_id AS numero_venta,
            CONCAT(c.cliente_nombre, ' ', c.cliente_apellido) AS cliente,
            p.pago_monto AS monto,
            p.pago_banco AS banco,
            p.pago_numero_operacion AS numero_operacion
        FROM
            pago p
            INNER JOIN venta v ON p.venta_id = v.venta_id
            INNER JOIN cliente c ON v.cliente_id = c.cliente_id
        WHERE
            p.pago_estado = 'aprobado' AND
            v.venta_fecha BETWEEN '$fecha_inicio' AND '$fecha_final'
    ");

    $pagos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pagos as $pago) {
        $html .= '<tr>
            <td>' . $pago['numero_venta'] . '</td>
            <td>' . $pago['cliente'] . '</td>
            <td>' . $pago['monto'] . '</td>
            <td>' . $pago['banco'] . '</td>
            <td>' . $pago['numero_operacion'] . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    // Salida del contenido HTML al PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Cerrar y generar el documento PDF
    $pdf->Output('reporte_pagos.pdf', 'I');

} else {
    // Si hay errores en las fechas, mostramos un mensaje de error
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
<?php } ?>
