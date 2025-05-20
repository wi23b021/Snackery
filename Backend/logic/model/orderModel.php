<?php
// === orderModel.php ===
// Stellt alle Datenbankfunktionen für Bestellungen zur Verfügung

require_once __DIR__ . '/../../config/dbaccess.php'; // Datenbankverbindung über DbAccess

class OrderModel {
    private $conn;

    public function __construct() {
        // Erstellt beim Aufruf eine aktive Datenbankverbindung
        $db = new DbAccess();
        $this->conn = $db->connect();
    }

    public function getAllOrders() {
        // Gibt alle Bestellungen zurück (nur für Admin sichtbar)
        $stmt = $this->conn->prepare("SELECT * FROM orders");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($orderId, $status) {
        // Ändert den Status einer Bestellung (z. B. auf 'versendet')
        if (!$orderId || !$status) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Bestell-ID oder Status fehlt.'];
        }

        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        return ['success' => true, 'message' => 'Status aktualisiert.'];
    }

    public function deleteOrder($orderId) {
        // Löscht eine Bestellung inkl. ihrer Positionen (Admin)
        if (!$orderId) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Bestell-ID fehlt.'];
        }

        try {
            $this->conn->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
            $this->conn->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
            return ['success' => true, 'message' => 'Bestellung gelöscht.'];
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()];
        }
    }

    public function placeOrder($userId, $postData) {
        // Speichert eine neue Bestellung inkl. aller Positionen (Warenkorb)
        $cart = json_decode($postData['cart'] ?? '', true);

        if (!is_array($cart) || count($cart) === 0) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Warenkorb ist ungültig oder leer.'];
        }

        $requiredFields = ['street', 'housenumber', 'postalcode', 'city', 'payment_method'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                http_response_code(400);
                return ['success' => false, 'message' => "Feld '$field' fehlt."];
            }
        }

        try {
            // Hauptbestellung speichern
            $stmt = $this->conn->prepare("
                INSERT INTO orders (user_id, street, housenumber, postalcode, city, payment_method, created_at, status)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'offen')
            ");
            $stmt->execute([
                $userId,
                $postData['street'],
                $postData['housenumber'],
                $postData['postalcode'],
                $postData['city'],
                $postData['payment_method']
            ]);

            $orderId = $this->conn->lastInsertId(); // Letzte ID (Bestellung)

            // Alle Produkte der Bestellung speichern
            foreach ($cart as $item) {
                $this->conn->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ")->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            }

            http_response_code(201);
            return ['success' => true, 'message' => 'Bestellung erfolgreich gespeichert.', 'orderId' => $orderId];
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Fehler: ' . $e->getMessage()];
        }
    }

    public function getOrdersByUser($userId) {
        // Gibt alle Bestellungen eines bestimmten Users zurück (für Profil-Seite)
        $stmt = $this->conn->prepare("
            SELECT 
                o.id,
                o.created_at AS order_date,
                o.status,
                (
                    SELECT SUM(oi.price * oi.quantity)
                    FROM order_items oi
                    WHERE oi.order_id = o.id
                ) AS total_price
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInvoiceData($userId, $orderId) {
        // Holt Rechnungsdaten für eine Bestellung (zur Darstellung in HTML-Rechnung)
        if (!$orderId || !is_numeric($orderId)) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Ungültige Bestell-ID.'];
        }

        try {
            // Kundendaten
            $userStmt = $this->conn->prepare("
                SELECT firstname, lastname, street, housenumber, postalcode, city
                FROM users WHERE id = ?
            ");
            $userStmt->execute([$userId]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

            // Bestelldatum
            $orderStmt = $this->conn->prepare("
                SELECT created_at FROM orders WHERE id = ? AND user_id = ?
            ");
            $orderStmt->execute([$orderId, $userId]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);

            // Prüfung ob Bestellung existiert
            if (!$userData || !$orderData) {
                http_response_code(404);
                return ['success' => false, 'message' => 'Benutzer oder Bestellung nicht gefunden.'];
            }

            // Bestellpositionen abrufen
            $itemsStmt = $this->conn->prepare("
                SELECT p.name, oi.quantity, oi.price
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                http_response_code(404);
                return ['success' => false, 'message' => 'Keine Bestellpositionen gefunden.'];
            }

            return [
                'success' => true,
                'user' => $userData,
                'order' => $orderData,
                'items' => $items
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Serverfehler: ' . $e->getMessage()];
        }
    }
}
