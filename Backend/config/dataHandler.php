<?php
//
// Snackery – Datenhandler für Produktabfragen (dataHandler.php)
// Stellt Funktionen zur Verfügung, um Produktdaten aus der Datenbank abzurufen.
//

// Die Datei "dbaccess.php" wird eingebunden, damit auf die Datenbankverbindung zugegriffen werden kann.
require_once __DIR__ . '/dbaccess.php';

/**
 * Holt alle Produkte aus der Datenbank.
 *
 * @return array Ein Array mit allen Produkten
 */
function getAllProducts() {
    // Erstellt ein neues Objekt der Klasse DbAccess, um auf die Datenbank zuzugreifen.
    $db = new DbAccess();

    // Baut die Verbindung zur Datenbank auf.
    $conn = $db->connect();

    // Bereitet eine SQL-Abfrage vor, die alle Produkte nach dem Erstellungsdatum sortiert (neueste zuerst).
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");

    // Führt die vorbereitete SQL-Abfrage aus.
    $stmt->execute();

    // Gibt alle Ergebnisse als assoziatives Array zurück.
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Holt ein einzelnes Produkt anhand der ID.
 *
 * @param int $productId Die Produkt-ID
 * @return array|null Produktdaten oder null, falls nicht gefunden
 */
function getProductById($productId) {
    // Erstellt ein neues Objekt der Klasse DbAccess für die Datenbankverbindung.
    $db = new DbAccess();

    // Stellt eine Verbindung zur Datenbank her.
    $conn = $db->connect();

    // Bereitet eine SQL-Abfrage vor, die ein Produkt mit einer bestimmten ID auswählt.
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");

    // Bindet den Parameter ':id' an die übergebene Produkt-ID als Integer.
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);

    // Führt die Abfrage aus.
    $stmt->execute();

    // Gibt das Ergebnis als assoziatives Array zurück, oder null, wenn kein Produkt gefunden wurde.
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
?>
