<?php
session_start();         // Session starten, um sie löschen zu können
session_unset();         // Alle Session-Variablen löschen
session_destroy();       // Session endgültig zerstören
header("Location: /Snackery/index.html"); // Weiterleitung zur Startseite
exit;
