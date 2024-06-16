<?php
include 'pdo.php';
include 'graph.php';

// try {
//     $pdo->beginTransaction();

//     foreach ($streets as $streetName => $stops) {
//         // Obtenir l'ID de la rue
//         $stmt = $pdo->prepare("SELECT id FROM streets WHERE name = :streetName");
//         $stmt->execute(['streetName' => $streetName]);
//         $streetId = $stmt->fetchColumn();

//         if ($streetId !== false) { // Assurez-vous qu'un ID a été retourné
//             foreach ($stops as $stopName) {
//                 // Obtenir l'ID de l'arrêt
//                 $stmt = $pdo->prepare("SELECT id FROM stops WHERE name = :stopName");   
//                 $stmt->execute(['stopName' => $stopName]);
//                 $stopId = $stmt->fetchColumn();

//                 if ($stopId !== false) { // Assurez-vous qu'un ID a été retourné
//                     // Insérer la relation dans streets_stops
//                     $insertStmt = $pdo->prepare("INSERT INTO streets_stops (stop_id, street_id) VALUES (:stopId, :streetId)");
//                     $insertStmt->execute(['stopId' => $stopId, 'streetId' => $streetId]);
//                 } else {
//                     echo "Aucun arrêt trouvé pour le nom : $stopName\n";
//                 }
//             }
//         } else {
//             echo "Aucune rue trouvée pour le nom : $streetName\n";
//         }
//     }

//     $pdo->commit();
// } catch (Exception $e) {
//     $pdo->rollBack();
//     echo "Erreur lors de l'insertion : " . $e->getMessage();
// }
function findPaths($graph, $start, $depth = 0, $visited = [], $path = [], $paths = []) {
    if ($depth > 3) { // Limit depth to 3 to allow 4 stops including the start
        return $paths;
    }
    $visited[$start] = true;
    $path[] = $start;
    foreach ($graph[$start] as $stop) {
        if (!isset($visited[$stop])) {
            $paths[] = $path;
            $paths = findPaths($graph, $stop, $depth + 1, $visited, $path, $paths);
        }
    }
    return $paths;
}

try {
    $pdo->beginTransaction();

    // Populate graph from database
    $stmt = $pdo->query("SELECT * FROM streets_stops");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $graph[$row['start_stop_id']][] = $row['end_stop_id'];
    }

    foreach (array_keys($graph) as $startStopId) {
        $allPaths = findPaths($graph, $startStopId);
        foreach ($allPaths as $path) {
            if (count($path) >= 2) {
                $startId = $path[0];
                $endId = $path[count($path) - 1];
                $distance = count($path) * 500;
                $pdo->prepare("INSERT INTO paths (start_stop_id, end_stop_id, distance) VALUES (?, ?, ?)")
                    ->execute([$startId, $endId, $distance]);
            }
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erreur lors de l'insertion des itinéraires : " . $e->getMessage();
}
?>
