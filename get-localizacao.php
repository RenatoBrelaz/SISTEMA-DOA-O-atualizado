<?php
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Coordenadas não enviadas."]);
    exit;
}

$lat = $_GET['lat'];
$lon = $_GET['lon'];

$url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&addressdetails=1";

$options = [
    "http" => [
        "header" => "User-Agent: DonationApp/1.0\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao consultar API."]);
    exit;
}

$data = json_decode($response, true);
$endereco = $data["address"] ?? [];

echo json_encode([
    "sucesso" => true,
    "rua" => $endereco["road"] ?? "",
    "bairro" => $endereco["suburb"] ?? $endereco["neighbourhood"] ?? "",
    "cidade" => $endereco["city"] ?? $endereco["town"] ?? $endereco["village"] ?? "",
    "estado" => $endereco["state"] ?? "",
    "cep" => $endereco["postcode"] ?? "",
    "pais" => $endereco["country"] ?? ""
]);
