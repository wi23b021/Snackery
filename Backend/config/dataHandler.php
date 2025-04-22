<?php
// Verbindung zur Datenbank einbinden
require_once __DIR__ . '/dbaccess.php';

/**
 * Holt alle Produkte aus der Datenbank.
 * @return array Ein Array mit allen Produktdaten
 */
function getAllProducts() {
    // Datenbankverbindung aufbauen
    $db = new DbAccess();
    $conn = $db->connect();

    // SQL-Befehl zum Abrufen aller Produkte (neueste zuerst)
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
    $stmt->execute();

    // Produkte als assoziatives Array zurückgeben
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
