<?php
// Snackery – Benutzer-Logout (logout.php)
// Dieses Skript beendet die aktive Benutzer-Session

session_start();         // Session starten
session_unset();         // Alle Session-Variablen löschen
session_destroy();       // Session beenden

// Optional: Login-Cookie löschen (wenn gesetzt)
if (isset($_COOKIE['username'])) {
    setcookie("username", "", time() - 3600, "/"); // Ablauf rückwirkend
}

// 🔁 Sichere Weiterleitung zur Login-Seite (oder Startseite)
header("Location: ../Frontend/sites/login.html"); // ← Passe an, falls nötig
exit;
?>
