<?php
// 
// Snackery – Benutzer-Logout (logout.php)
// Dieses Skript beendet die aktive Benutzer-Session und leitet zur Startseite weiter.
// 

session_start();         // 1. Session starten, um sie anschließend löschen zu können
session_unset();         // 2. Alle Session-Variablen löschen
session_destroy();       // 3. Session endgültig zerstören

// 4. Weiterleitung auf die Startseite
header("Location: /Snackery/index.html");
exit;
?>
