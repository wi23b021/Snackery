<?php
// ==============================================
// Snackery – Produkt in den Session-Warenkorb legen
// ==============================================

session_start();

// CORS und JSON-Header
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
    exit;
}

// JSON-Daten einlesen
$input = json_decode(file_get_contents("php://input"), true);

// Validierung
if (
    !isset($input['id']) ||
    !isset($input['name']) ||
    !isset($input['price']) ||
    !isset($input['quantity'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige Produktdaten.']);
    exit;
}

// Session-Warenkorb initialisieren, falls noch nicht vorhanden
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Produkt-ID als Schlüssel verwenden
$productId = $input['id'];
$existing = false;

// Prüfen, ob Produkt bereits im Warenkorb ist
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] == $productId) {
        $item['quantity'] += $input['quantity'];
        $existing = true;
        break;
    }
}

// Falls nicht vorhanden, hinzufügen
if (!$existing) {
    $_SESSION['cart'][] = [
        'id' => $input['id'],
        'name' => $input['name'],
        'price' => floatval($input['price']),
        'quantity' => intval($input['quantity'])
    ];
}

// Erfolgsmeldung senden
echo json_encode(['success' => true, 'message' => 'Produkt in Session gespeichert.']);
