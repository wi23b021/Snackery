<?php
// === userModel.php ===
// Diese Klasse verwaltet alle Datenbankzugriffe zur Benutzerverwaltung

require_once __DIR__ . '/../../config/dbaccess.php'; // Verbindung zur Datenbank laden

class UserModel {
    private $conn;

    public function __construct() {
        // Bei jedem Aufruf der Klasse wird die Datenbankverbindung hergestellt
        $db = new DbAccess();
        $this->conn = $db->connect();
    }

    public function getProfile($userId) {
        // Holt das Benutzerprofil anhand der ID – wird z. B. auf der Profilseite angezeigt
        $stmt = $this->conn->prepare("
            SELECT id, username, email, firstname, lastname, street, housenumber, postalcode, city, role, iban, cardnumber, bankname 
            FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['success' => false, 'message' => 'Benutzer nicht gefunden.'];
    }

    public function getAllUsers() {
        // Ruft alle Benutzer ab – wird im Adminbereich für Benutzerübersicht verwendet
        $stmt = $this->conn->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($data) {
        // Aktualisiert Benutzerdaten – Admin kann damit Benutzerprofile bearbeiten
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Ungültige Benutzerdaten.'];
        }

        $fields = [
            "username", "email", "password", "firstname", "lastname",
            "street", "housenumber", "postalcode", "city",
            "iban", "cardnumber", "bankname", "role"
        ];

        $updates = [];
        $values = [];

        // Jedes übergebene Feld wird geprüft und vorbereitet
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $field === "password"
                    ? password_hash($data[$field], PASSWORD_DEFAULT)
                    : $data[$field];
            }
        }

        // SQL-Update ausführen, falls Änderungen vorliegen
        if (count($updates) > 0) {
            $values[] = $data['id'];
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
        }

        return ['success' => true, 'message' => 'Benutzerdaten aktualisiert.'];
    }

    public function toggleUserActive($data) {
        // Setzt Benutzer auf aktiv oder inaktiv – zur Sperrung von Konten im Adminbereich
        if (!isset($data['id']) || !isset($data['active'])) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Benutzer-ID oder Aktiv-Status fehlt.'];
        }

        $stmt = $this->conn->prepare("UPDATE users SET active = ? WHERE id = ?");
        $stmt->execute([$data['active'] ? 1 : 0, $data['id']]);

        return ['success' => true, 'message' => 'Status erfolgreich geändert.'];
    }

    public function deleteUser($userId) {
        // Löscht einen Benutzer aus der Datenbank (Adminfunktion)
        if (!$userId) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Benutzer-ID fehlt.'];
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            return ['success' => true, 'message' => 'Benutzer erfolgreich gelöscht.'];
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Fehler beim Löschen: ' . $e->getMessage()];
        }
    }
}
