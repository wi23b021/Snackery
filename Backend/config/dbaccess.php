<?php
// Datenbankverbindungsklasse für das gesamte Projekt
// Wird in allen PHP-Dateien verwendet, die auf die Datenbank zugreifen (Login, Produkte, Bestellungen etc.)

class DbAccess {

    // Verbindungsparameter für die lokale Datenbank (XAMPP)
    private $host = "localhost";      // Datenbankserver (meist localhost)
    private $dbname = "snackery";     // Name der verwendeten Datenbank
    private $username = "root";       // Standard-Username in XAMPP
    private $password = "";           // In XAMPP meist kein Passwort gesetzt

    // Diese Methode stellt eine Verbindung zur Datenbank her und gibt das PDO-Objekt zurück
    public function connect() {
        try {
            // PDO-Verbindung mit UTF-8 Zeichensatz aufbauen
            $pdo = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
                $this->username,
                $this->password
            );

            // Fehlerbehandlung aktivieren (wirft Exceptions statt nur Warnungen)
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Standard-Rückgabeformat: assoziatives Array
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Erfolgreiche Verbindung wird zurückgegeben
            return $pdo;

        } catch (PDOException $e) {
            // Bei Fehler wird eine Exception mit genauer Beschreibung geworfen
            throw new Exception("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
        }
    }

    // Optionale Debug-Funktion: gibt Verbindungsdaten zurück (z. B. für Adminbereich)
    public function getConnectionInfo() {
        return [
            'host' => $this->host,
            'database' => $this->dbname
        ];
    }
}

?>