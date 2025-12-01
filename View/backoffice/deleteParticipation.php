<?php
require_once __DIR__."/../../Controller/ParticipationController.php";
$participationC = new ParticipationController();
$participationC->DeleteParticipation($_GET['id']);

header('Location: listParticipation.php');
exit();
?>