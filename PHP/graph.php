<?php
// Supposons que ceci est votre graphe, déjà défini dans votre script PHP
$graph = [
    "nodes" => [
        ["id" => 1, "label" => "La Défense"],
        ["id" => 2, "label" => "Esplanade de la Défense"],
    ],
    "edges" => [
        ["from" => 1, "to" => 2],
    ]
];

// Encodez votre graphe en JSON
echo json_encode($graph);
?>
