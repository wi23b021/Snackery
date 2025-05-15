<?php
// ==============================================
// Snackery – Logout API für JavaScript (fetch)
// ==============================================

// 1. Session starten
session_start();

// 2. Session-Daten löschen
session_unset();
session_destroy();

// 3. Session-Cookie löschen (nur wenn verwendet)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. HTTP-Header für CORS und JSON-Antwort
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// 5. Antwort senden
http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Logout erfolgreich."
]);
exit;
?>
