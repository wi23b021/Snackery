<?php
// API-Endpunkt für Logout-Anfragen via fetch()

// === Session starten ===
session_start(); // Startet die aktuelle Session (falls noch aktiv)

// === Alle Session-Daten löschen ===
session_unset();       // Entfernt alle Session-Variablen
session_destroy();     // Zerstört die Session komplett

// === Session-Cookie manuell löschen (optional, aber sicher) ===
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); // Cookie-Einstellungen holen
    setcookie(
        session_name(), '',               // Cookie leeren
        time() - 42000,                   // Ablaufzeitpunkt in der Vergangenheit
        $params["path"],                  // Gleiche Pfadangabe wie beim Setzen
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// === Header für fetch() und CORS setzen ===
header("Access-Control-Allow-Origin: http://localhost");   // Zugriff von Frontend erlauben
header("Access-Control-Allow-Credentials: true");          // Session-Cookie akzeptieren
header("Content-Type: application/json");                  // Antwort im JSON-Format

// === JSON-Antwort zurückgeben ===
http_response_code(200); // 200 OK
echo json_encode([
    "success" => true,
    "message" => "Logout erfolgreich."
]);
exit;
?>
