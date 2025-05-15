<?php
//
// ==============================================
// Snackery – Datenbankzugriffsklasse (PDO-basiert)
// ==============================================
//
// Diese Klasse stellt eine sichere und wiederverwendbare Verbindung zur Datenbank her.
// Sie wird überall im Projekt verwendet: Registrierung, Login, Produkte, Bestellungen etc.
//

class DbAccess {

    // ==============================
    // 1. Verbindungsdaten
    // ==============================

    private $host = "localhost";      // Hostname des MySQL-Servers (lokal: localhost)
    private $dbname = "snackery";     // Name deiner Datenbank (muss existieren)
    private $username = "root";       // Datenbank-Benutzer (standardmäßig root in XAMPP)
    private $password = "";           // Leeres Passwort – Standard in XAMPP

    // ==============================
    // 2. Funktion zum Aufbau einer Verbindung
    // ==============================

    public function connect() {
        try {
            // Verbindung mit PDO aufbauen (utf8mb4 für volle Unicode-Unterstützung)
            $pdo = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
                $this->username,
                $this->password
            );

            // Fehler sollen als Exception behandelt werden (besseres Error-Handling)
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Ergebnis als assoziatives Array zurückgeben (Standard)
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Die PDO-Verbindung erfolgreich zurückgeben
            return $pdo;

        } catch (PDOException $e) {
            // Falls Verbindung fehlschlägt, wird eine Exception geworfen
            // Vorteil: zentrale Fehlerbehandlung in der aufrufenden Datei möglich
            throw new Exception("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
        }
    }

    // ==============================
    // 3. (Optional) Debug-Funktion – Verbindungsinfo ausgeben
    // ==============================

    public function getConnectionInfo() {
        // Kann z. B. im Adminbereich zum Debugging ausgegeben werden
        return [
            'host' => $this->host,
            'database' => $this->dbname
        ];
    }
}

?>
