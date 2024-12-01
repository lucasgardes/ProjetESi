<?php
require 'pdo.php';

$stmt = $pdo->prepare("SELECT * FROM stops");
$stmt->execute();
$stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM streets");
$stmt->execute();
$streets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT s.name, b.* FROM bicycles b LEFT JOIN stops s ON s.id = b.stop_id");
$stmt->execute();
$bicycles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'stops' => $stops,
    'streets' => $streets,
    'bicycles' => $bicycles
]);
?>