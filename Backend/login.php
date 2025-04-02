<?php
session_start(); // Session starten für spätere Benutzung

require_once 'config/dbaccess.php'; // Verbindung zur DB

// Formularwerte holen
$usernameOrEmail = $_POST['username'];
$password = $_POST['password'];

// Verbindung aufbauen
$db = new DbAccess();
$conn = $db->connect();

// Benutzer anhand von Username ODER E-Mail finden
$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);

$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Login erfolgreich
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // „Login merken“-Funktion über Cookie
    if (isset($_POST['remember'])) {
        setcookie("username", $user['username'], time() + (86400 * 30), "/");
    }

    // Weiterleitung zur Startseite oder Dashboard
    header("Location: ../Frontend/index.html");
    exit;
} else {
    // Fehlerhafte Anmeldung
    echo "<script>alert('❌ Benutzername oder Passwort ist falsch!'); window.location.href = '../Frontend/sites/login.html';</script>";
}
?>
