<?php
// Session beenden und alles löschen
session_start();
session_unset();
session_destroy();

// Weiterleitung zur Startseite
header("Location: ../index.php");
exit;
?>
