<?php
// === requestHandler.php ===
// Zentrale Verteilerstelle für alle Anfragen – leitet sie an den passenden Controller weiter

session_start(); // Startet die Session für Authentifizierungsprüfung

// CORS-Header setzen, damit Anfragen vom Frontend durch fetch() erlaubt sind
header("Access-Control-Allow-Origin: http://localhost"); // Nur lokale Anfragen erlaubt
header("Access-Control-Allow-Credentials: true");        // Cookies/Sessions erlaubt
header("Content-Type: application/json");                // Rückgabeformat ist JSON
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS"); // Erlaubte Methoden
header("Access-Control-Allow-Headers: Content-Type");    // Erlaubte Header

// OPTIONS-Anfrage wird sofort beantwortet (Preflight bei CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verbindung zur Datenbank wird eingebunden (wird evtl. vom Controller genutzt)
require_once __DIR__ . '/../config/dbaccess.php';

// Die Aktion wird aus der URL gelesen – z. B. ?action=getProducts
$action = $_GET['action'] ?? '';

// === SPEZIALFALL: updateUser auch für normale Nutzer zulassen ===
if ($action === 'updateUser') {
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Nicht eingeloggt."]);
        exit;
    }

    $currentUser = $_SESSION['user'];

    // Nur Admins dürfen andere Nutzer bearbeiten – normale User nur sich selbst
    if ($currentUser['role'] !== 'admin' && $currentUser['id'] != $requestData['id']) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "❌ Du darfst nur dein eigenes Profil bearbeiten."]);
        exit;
    }

    // Wenn kein Admin: Passwort prüfen
    if ($currentUser['role'] !== 'admin') {
        $db = new DbAccess();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $userData = $stmt->fetch();

        if (!$userData || !password_verify($requestData['current_password'], $userData['password'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "❌ Falsches aktuelles Passwort."]);
            exit;
        }
    }
}

// === Anhand der Aktion wird der richtige Controller eingebunden ===
switch (true) {

    // === Produktbezogene Aktionen ===
    case str_starts_with($action, 'getProduct'):
    case str_starts_with($action, 'updateProduct'):
    case str_starts_with($action, 'addProduct'):
    case str_starts_with($action, 'deleteProduct'):
    case $action === 'getProducts':
        require_once __DIR__ . '/controller/productController.php';
        $controller = new productController();  // Erstelle Produkt-Controller
        $controller->handle($action);           // Führt passende Methode aus
        break;

    // === Bestellungen und Rechnungen ===
    case str_starts_with($action, 'getOrders'):
    case str_starts_with($action, 'editOrder'):
    case str_starts_with($action, 'deleteOrder'):
    case $action === 'placeOrder':
    case $action === 'getMyOrders':
    case $action === 'getInvoiceData':
        require_once __DIR__ . '/controller/orderController.php';
        $controller = new orderController();    // Erstelle Bestell-Controller
        $controller->handle($action);           // Übergibt Aktion
        break;

    // === Benutzerbezogene Aktionen (Admin & Profil) ===
    case str_starts_with($action, 'getUsers'):
    case str_starts_with($action, 'updateUser'):
    case str_starts_with($action, 'toggleUserActive'):
    case str_starts_with($action, 'deleteUser'):
    case $action === 'getProfile':
    case $action === 'updateProfile':

        require_once __DIR__ . '/controller/userController.php';
        $controller = new userController();     // Benutzercontroller erzeugen
        $controller->handle($action);           // Führt Aktion aus
        break;

    // === Falls keine bekannte Aktion gefunden wurde ===
    default:
        http_response_code(400); // Fehler: Aktion nicht erlaubt
        echo json_encode(['success' => false, 'message' => 'Ungültige Aktion.']);
        exit;
}
