<?php
require_once __DIR__."/../../Controller/EvenementController.php";
$evenementC = new EvenementController();
$evenementC->DeleteEvenement($_GET['id']);

header('Location: listEvenement.php');
?>