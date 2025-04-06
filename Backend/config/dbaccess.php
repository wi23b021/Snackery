<?php
// Diese Klasse stellt eine Verbindung zur MySQL-Datenbank "snackery" her
class DbAccess {
    // Verbindungsparameter (lokale Entwicklungsumgebung)
    private $host = "localhost";
    private $dbname = "snackery";
    private $username = "root";
    private $password = "";

    // Funktion zum Aufbau der PDO-Verbindung
    public function connect() {
        try {
            $pdo = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8",
                $this->username,
                $this->password
            );
            // Fehlerausgabe bei SQL-Problemen aktivieren
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // Wenn Verbindung fehlschlägt, abbrechen und Fehler anzeigen
            die("❌ Verbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
}
?>
