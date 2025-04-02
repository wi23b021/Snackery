<?php
class DbAccess {
    private $host = "localhost";
    private $dbname = "snackery";
    private $username = "root";
    private $password = "";

    public function connect() {
        try {
            $pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8",
                           $this->username,
                           $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("❌ Verbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
}
?>
