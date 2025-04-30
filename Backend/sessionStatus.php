<?php
// Snackery – Session-Status-Prüfung für AJAX-Anfragen

session_start();

// Header für JSON-Ausgabe
header('Content-Type: application/json');

// Session prüfen
if (isset($_SESSION['user'])) {
    // Eingeloggt – Rolle zurückgeben
    echo json_encode([
        'loggedIn' => true,
        'role' => $_SESSION['user']['role'] ?? 'user' // fallback
    ]);
} else {
    // Nicht eingeloggt
    echo json_encode(['loggedIn' => false]);
}
?>
