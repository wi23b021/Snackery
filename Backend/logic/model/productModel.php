<?php
// === model/productModel.php ===
// Diese Klasse verwaltet alle Produkt-bezogenen Datenbankaktionen (CRUD)

require_once __DIR__ . '/../../config/dbaccess.php'; // Verbindung zur Datenbank aufrufen

class productModel {
    private $conn;

    public function __construct() {
        // Verbindung zur Datenbank initialisieren, sobald das Model aufgerufen wird
        $db = new DbAccess();
        $this->conn = $db->connect();
    }

    public function getAllProducts() {
        // Alle Produkte abrufen (für Produktübersicht auf Website)
        $stmt = $this->conn->prepare("SELECT * FROM products");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        // Einzelnes Produkt nach ID abrufen (z. B. für Detailseite)
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addProduct($data, $files) {
        // Neues Produkt hinzufügen (Adminfunktion)
        // Pflichtfelder prüfen
        if (!isset($data['name'], $data['price'], $data['category'])) {
            return ['success' => false, 'message' => 'Erforderliche Felder fehlen.'];
        }

        // Optionales Bild hochladen (falls vorhanden)
        $fileName = null;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($files['image']['name']);
            move_uploaded_file($files['image']['tmp_name'], __DIR__ . '/../../productpictures/' . $fileName);
        }

        // Produktdaten in Datenbank speichern
        $stmt = $this->conn->prepare("
            INSERT INTO products (name, description, price, image, category, origin_country, stock)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['description'] ?? '',           // falls leer, leerer String
            $data['price'],
            $fileName,
            $data['category'],
            $data['origin_country'] ?? '',        // falls leer, leerer String
            $data['stock'] ?? 0                   // falls leer, 0
        ]);

        return ['success' => true, 'message' => 'Produkt erfolgreich hinzugefügt.'];
    }

    public function updateProduct($id, $data) {
        // Produktdaten ändern (Name, Preis, Kategorie)
        $stmt = $this->conn->prepare("UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['price'],
            $data['category'],
            $id
        ]);

        return ['success' => true, 'message' => 'Produkt aktualisiert.'];
    }

    public function deleteProduct($id) {
        // Produkt aus Datenbank löschen
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Produkt gelöscht.'];
    }
}
