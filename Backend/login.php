<?php
session_start();
header("Content-Type: application/json");

// Verbindung zur Datenbank
require_once __DIR__ . '/config/dbaccess.php';

// POST-Daten prüfen
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Benutzername und Passwort erforderlich."]);
    exit;
}

$usernameOrEmail = $_POST['username'];
$password = $_POST['password'];

$db = new DbAccess();
$conn = $db->connect();

// Benutzer anhand von Benutzername oder E-Mail suchen
$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // ✅ Login erfolgreich – Session starten
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user'] = $user;

    // Optional: Cookie setzen
    if (isset($_POST['remember'])) {
        setcookie("username", $user['username'], time() + (86400 * 30), "/");
    }

    echo json_encode([
        "success" => true,
        "message" => "Login erfolgreich!",
        "role" => $user['role']
    ]);
    exit;
} else {
    // ❌ Falsche Login-Daten
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "❌ Benutzername oder Passwort ist falsch."]);
    exit;
}
?>
