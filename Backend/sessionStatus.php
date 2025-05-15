<?php
// =======================================
// sessionStatus.php
// Snackery – Sessionstatus prüfen für JavaScript-Requests
// =======================================

// ✅ SESSION-KONFIGURATION (Korrektur: Pfad global gültig)
session_set_cookie_params([
    'lifetime' => 0,             // Session nur für die Dauer der Browsersitzung
    'path' => '/',               // <<< global gültig für alle Pfade!
    'domain' => 'localhost',     // In Produktion anpassen (z. B. deine-domain.at)
    'secure' => false,           // true = nur über HTTPS (für localhost: false)
    'httponly' => true,          // Schutz: Kein Zugriff via JavaScript
    'samesite' => 'Lax'          // CSRF-Schutz für Fremdanfragen
]);

session_start();

// === CORS-KONFIGURATION (Frontend-Zugriff ermöglichen) ===
$allowedOrigin = "http://localhost";
header("Access-Control-Allow-Origin: $allowedOrigin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// === Preflight-Request (für OPTIONS-Anfragen durch fetch()) ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // Kein Inhalt notwendig
    exit();
}

// === SESSIONSTATUS PRÜFEN ===
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    echo json_encode([
        "loggedIn" => true,
        "role" => $_SESSION['user']['role'] ?? "user"
    ]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>
