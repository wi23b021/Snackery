<?php
// 
// Snackery – Datenbankzugriffsklasse (dbaccess.php)
// Diese Klasse stellt eine Verbindung zur MySQL-Datenbank her.
// 

class DbAccess {
    // Verbindungsdaten (lokale Entwicklungsumgebung)
    private $host = "localhost";      // Server
    private $dbname = "snackery";      // Datenbankname
    private $username = "root";        // DB-Benutzername
    private $password = "";            // DB-Passwort

    // Funktion: Aufbau einer PDO-Verbindung
    public function connect() {
        try {
            $pdo = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8",
                $this->username,
                $this->password
            );
            // Fehler als Exception werfen, wenn etwas schiefgeht
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // ❌ Fehler beim Verbindungsaufbau
            die("❌ Verbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
}
?>
