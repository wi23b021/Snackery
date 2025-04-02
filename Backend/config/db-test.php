<?php
require_once 'dbaccess.php';

$db = new DbAccess();
$conn = $db->connect();

if ($conn) {
    echo "✅ Verbindung zur Datenbank war erfolgreich!";
}
?>
