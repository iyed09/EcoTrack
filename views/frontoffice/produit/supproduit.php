<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Controller/ProduitController.php";

$conn = Database::getConnexion();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $result = ProduitController::removeProduit($conn, $id);

    if ($result === true) {
        header("Location: listproduit.php?deleted=1");
        exit;
    } elseif ($result === "FK_CONSTRAINT") {
        header("Location: listproduit.php?error=foreign");
        exit;
    } else {
        header("Location: listproduit.php?error=unknown");
        exit;
    }
} else {
    header("Location: listproduit.php?error=noid");
    exit;
}
?>