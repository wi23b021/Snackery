<?php
// === sessionStatus.php ===
// API-Endpunkt, um den aktuellen Login-Status per fetch() im Frontend abzufragen

// Konfiguration der Session-Cookies
session_set_cookie_params([
    'lifetime' => 0,             // Session endet mit dem Schließen des Browsers
    'path' => '/',               // Cookie gilt für das gesamte Projekt
    'domain' => 'localhost',     // Nur lokal gültig – in Produktion anpassen
    'secure' => false,           // HTTPS nur bei Live-Seiten – lokal nicht notwendig
    'httponly' => true,          // Erhöhte Sicherheit: Kein Zugriff per JavaScript
    'samesite' => 'Lax'          // Schutz gegen CSRF bei externen Anfragen
]);

session_start(); // Session starten

// Header für Cross-Origin-Requests (CORS)
$allowedOrigin = "http://localhost";
header("Access-Control-Allow-Origin: $allowedOrigin");     // Erlaubt Frontend-Zugriff von lokal
header("Access-Control-Allow-Credentials: true");          // Cookies mitschicken erlauben
header("Access-Control-Allow-Headers: Content-Type");      // JSON senden erlauben
header("Content-Type: application/json");                  // Antwort ist JSON

// OPTION-Anfrage abfangen (CORS Preflight bei fetch())
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // 204 = Kein Inhalt notwendig
    exit();
}

// Prüfung ob ein Benutzer in der Session gespeichert ist
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    // Wenn eingeloggt → Login-Status und Rolle als JSON senden
    echo json_encode([
        "loggedIn" => true,
        "role" => $_SESSION['user']['role'] ?? "user"
    ]);
} else {
    // Wenn nicht eingeloggt → Rückmeldung mit false
    echo json_encode(["loggedIn" => false]);
}
?>
