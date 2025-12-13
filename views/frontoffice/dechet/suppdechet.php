<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/DechetController.php";

$conn = Database::getConnexion();

if (isset($_GET['idDechet'])) {
    $idDechet = intval($_GET['idDechet']);

    $result = DechetController::removeDechet($conn, $idDechet);

    if ($result === true) {
        header("Location: listdechet.php?deleted=1");
        exit;
    } elseif ($result === "FK_CONSTRAINT") {
        header("Location: listdechet.php?error=foreign");
        exit;
    } else {
        header("Location: listdechet.php?error=unknown");
        exit;
    }
} else {
    header("Location: listdechet.php?error=noid");
    exit;
}
?>