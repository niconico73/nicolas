<?php
// Obtén el RUC desde la solicitud
$ruc = $_GET['ruc'];

// URL de la API de NubeFacT
$apiUrl = "https://api.nubefact.com/api/v1/ruc/$ruc";

// Tu token de NubeFacT
$token = "TU_TOKEN_NUBEFACT"; // Reemplaza con tu token real

// Configuración de cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Token token=' . $token
));

// Ejecutar la petición
$response = curl_exec($ch);
curl_close($ch);

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo $response;