<?php
// === addToCart.php ===
// Fügt ein Produkt in den Session-basierten Warenkorb ein (für eingeloggte und nicht eingeloggte Nutzer)

session_start(); // Session starten, um auf $_SESSION['cart'] zugreifen zu können

// === CORS- und JSON-Konfiguration für fetch() ===
header("Access-Control-Allow-Origin: http://localhost");     // Frontend-Zugriff erlauben
header("Access-Control-Allow-Credentials: true");            // Cookies (Session) erlauben
header("Content-Type: application/json");                    // JSON als Antwortformat
header("Access-Control-Allow-Methods: POST");                // Nur POST erlaubt
header("Access-Control-Allow-Headers: Content-Type");        // Nur Content-Type im Header notwendig

// === Anfrage muss POST sein, sonst Fehlermeldung zurückgeben ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt.']);
    exit;
}

// === Die empfangenen Produktdaten als JSON auslesen ===
$input = json_decode(file_get_contents("php://input"), true);

// === Prüfen, ob alle notwendigen Felder vorhanden sind ===
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

// === Session-Warenkorb initialisieren, wenn er noch nicht existiert ===
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Leeres Array anlegen
}

// === Prüfen, ob das Produkt schon im Warenkorb existiert ===
$productId = $input['id'];
$existing = false;

foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] == $productId) {
        $item['quantity'] += $input['quantity']; // Menge erhöhen
        $existing = true;
        break;
    }
}

// === Wenn das Produkt noch nicht im Warenkorb war, neu hinzufügen ===
if (!$existing) {
    $_SESSION['cart'][] = [
        'id' => $input['id'],
        'name' => $input['name'],
        'price' => floatval($input['price']),
        'quantity' => intval($input['quantity'])
    ];
}

// === Erfolgsmeldung zurücksenden ===
echo json_encode(['success' => true, 'message' => 'Produkt in Session gespeichert.']);
