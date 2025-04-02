<?php
// Verbindung zur Datenbank herstellen
require_once 'config/dbaccess.php';

// Formulardaten abholen
$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

// Passwort sicher verschlüsseln
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verbindung aufbauen
$db = new DbAccess();
$conn = $db->connect();

// SQL vorbereiten – angepasste Spaltennamen!
$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

// Wenn erfolgreich registriert, zeige Bestätigung & leite weiter
if ($stmt->execute([$name, $email, $hashedPassword])) {
    echo "
        <!DOCTYPE html>
        <html lang='de'>
        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='refresh' content='3;url=../Frontend/index.html'>
            <title>Registrierung erfolgreich</title>
            <style>
                body {
                    text-align: center;
                    padding: 100px;
                    font-family: Arial, sans-serif;
                    background-color: #fffaf4;
                }
                .message {
                    font-size: 1.5em;
                    color: green;
                }
            </style>
        </head>
        <body>
            <p class='message'>✅ Du hast dich erfolgreich registriert!<br>Du wirst in wenigen Sekunden zur Startseite weitergeleitet...</p>
        </body>
        </html>
    ";
} else {
    echo "❌ Fehler bei der Registrierung.";
}
?>
