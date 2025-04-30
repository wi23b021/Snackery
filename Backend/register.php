<?php
// Snackery – Benutzerregistrierung (register.php)
// Dieses PHP-Skript verarbeitet Registrierungen und speichert neue Benutzer in der Datenbank.

require_once __DIR__ . '/config/dbaccess.php';

session_start(); // Session starten

// POST-Daten aus Formular abholen
$firstname    = $_POST['firstname'] ?? '';
$lastname     = $_POST['lastname'] ?? '';
$username     = $_POST['username'] ?? '';
$email        = $_POST['email'] ?? '';
$street       = $_POST['street'] ?? '';
$housenumber  = $_POST['housenumber'] ?? '';
$postalcode   = $_POST['postalcode'] ?? '';
$city         = $_POST['city'] ?? '';
$password     = $_POST['password'] ?? '';
$password2    = $_POST['password_repeat'] ?? '';

// Passwortvergleich
if ($password !== $password2) {
    header("Location: ../Frontend/sites/register.html?error=passwort");
    exit;
}

// Passwort verschlüsseln
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verbindung zur Datenbank
$db = new DbAccess();
$conn = $db->connect();

// Prüfen ob Benutzername oder E-Mail schon existiert
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    header("Location: ../Frontend/sites/register.html?error=existiert");
    exit;
}

// Benutzer in die Datenbank einfügen
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, street, housenumber, postalcode, city, password, role) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$role = "user"; // Standardrolle

$success = $stmt->execute([
    $firstname, $lastname, $username, $email,
    $street, $housenumber, $postalcode, $city,
    $hashedPassword, $role
]);

if ($success) {
    header("Location: ../Frontend/sites/login.html?registered=true");
    exit;
} else {
    header("Location: ../Frontend/sites/register.html?error=datenbank");
    exit;
}
?>
