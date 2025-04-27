<?php
// 
// Snackery – Datenhandler für Produktabfragen (dataHandler.php)
// Stellt Funktionen zur Verfügung, um Produktdaten aus der Datenbank abzurufen.
// 

// Verbindung zur Datenbank importieren
require_once __DIR__ . '/dbaccess.php';

/**
 * Holt alle Produkte aus der Datenbank.
 *
 * @return array Ein Array mit allen Produkten
 */
function getAllProducts() {
    // 1. Verbindung zur Datenbank aufbauen
    $db = new DbAccess();
    $conn = $db->connect();

    // 2. SQL-Statement vorbereiten
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
    
    // 3. Statement ausführen
    $stmt->execute();

    // 4. Alle Produkte als assoziatives Array zurückgeben
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
