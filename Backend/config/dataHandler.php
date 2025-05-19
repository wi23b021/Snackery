<?php
// Diese Datei stellt zwei zentrale Funktionen bereit, um Produkte aus der Datenbank zu holen:
// - getAllProducts(): gibt alle Produkte zurück
// - getProductById($id): gibt ein einzelnes Produkt basierend auf der ID zurück

// Verbindung zur Datenbank wird über eine externe Klasse (dbaccess.php) hergestellt
require_once __DIR__ . '/dbaccess.php';

// Funktion: Alle Produkte aus der Datenbank holen (für die Produktübersicht)
function getAllProducts() {
    try {
        // Neue Verbindung zur Datenbank aufbauen
        $db = new DbAccess();
        $conn = $db->connect();

        // Alle Produkte holen, nach Erstellungsdatum absteigend sortiert
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
        $stmt->execute();

        // Ergebnis als Array zurückgeben
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Wenn ein Fehler passiert, wird dieser geloggt und ein leeres Array zurückgegeben
        error_log("Fehler in getAllProducts(): " . $e->getMessage());
        return [];
    }
}

// Funktion: Einzelnes Produkt anhand seiner ID holen (für die Detailseite)
function getProductById($productId) {
    try {
        // Verbindung zur Datenbank
        $db = new DbAccess();
        $conn = $db->connect();

        // Produkt mit der passenden ID auswählen
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        // Entweder ein Produkt zurückgeben oder null, wenn nichts gefunden wurde
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        // Fehlerbehandlung und Logging
        error_log("Fehler in getProductById(): " . $e->getMessage());
        return null;
    }
}
?>
