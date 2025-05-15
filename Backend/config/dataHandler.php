<?php
//
// ==============================================
// Snackery – Datenhandler für Produktabfragen
// ==============================================
//
// Diese Datei stellt zwei zentrale Funktionen zur Verfügung:
// - getAllProducts() holt alle Produkte aus der Datenbank
// - getProductById($id) holt ein einzelnes Produkt anhand seiner ID
//

// ==============================================
// 1. Datenbankklasse einbinden
// ==============================================

require_once __DIR__ . '/dbaccess.php'; // Verbindung über PDO


// ==============================================
// 2. Alle Produkte aus der Datenbank abrufen
// ==============================================

/**
 * Holt alle Produkte aus der Datenbank, sortiert nach Erstellungsdatum (neueste zuerst).
 *
 * @return array Ein Array mit Produkten (jeweils als assoziatives Array)
 */
function getAllProducts() {
    try {
        $db = new DbAccess();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fehlerprotokollierung (nur optional in Produktivumgebungen)
        error_log("Fehler in getAllProducts(): " . $e->getMessage());
        return [];
    }
}


// ==============================================
// 3. Einzelnes Produkt anhand der ID abrufen
// ==============================================

/**
 * Holt ein einzelnes Produkt über dessen ID.
 *
 * @param int $productId Die ID des Produkts
 * @return array|null Das Produkt als assoziatives Array oder null, falls nicht gefunden
 */
function getProductById($productId) {
    try {
        $db = new DbAccess();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log("Fehler in getProductById(): " . $e->getMessage());
        return null;
    }
}
?>
