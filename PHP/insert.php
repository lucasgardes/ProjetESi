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
// function findPaths($graph, $start, $depth = 0, $visited = [], $path = [], $paths = []) {
//     if ($depth > 3) { // Limit depth to 3 to allow 4 stops including the start
//         return $paths;
//     }
//     $visited[$start] = true;
//     $path[] = $start;
//     foreach ($graph[$start] as $stop) {
//         if (!isset($visited[$stop])) {
//             $paths[] = $path;
//             $paths = findPaths($graph, $stop, $depth + 1, $visited, $path, $paths);
//         }
//     }
//     return $paths;
// }




$visitedStops = [];
$maxDistance = 50000; // 50 km
$maxLoad = 200; // 200 kg
$distanceTravelled = 0;
$loadAccumulated = 0;




function generateAllPaths($pdo, $graph) {
    global $visitedStops;




    $pdo->beginTransaction();




    $stmt = $pdo->query("SELECT `name` FROM stops");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $startStopName = $row['name'];
        if (!isset($visitedStops[$startStopName])) {
            findPaths($pdo, $startStopName, $graph);
        }
    }




    $pdo->commit();
}




function findPaths($pdo, $currentStop, $graph, $path = [], $distanceTravelled = 0, $loadAccumulated = 0) {
    global $visitedStops, $maxLoad, $maxDistance;

    $path[] = $currentStop;
    $visitedStops[$currentStop] = true;
    $loadAccumulated += 50;
    $distanceTravelled += 500;

    $allVisited = false;

    do {
        if (isset($graph[$currentStop])) {
            $allVisited = true;
            foreach ($graph[$currentStop] as $nextStop) {
                if (!isset($visitedStops[$nextStop])) {
                    $path[] = $nextStop;
                    $currentStop = $nextStop;
                    $allVisited = false;
                    $loadAccumulated += 50;
                    $distanceTravelled += 500;
                }
            }
        } else {
            break;
        }
    } while ($loadAccumulated < $maxLoad && $distanceTravelled < $maxDistance && $allVisited != true);

    savePath($path, $pdo);
}


function savePath($path, $pdo) {
    global $loadAccumulated, $distanceTravelled;




    $loadAccumulated = 0;
    $distanceTravelled = 0;
    $stmt = $pdo->query("SELECT `id` FROM stops WHERE `name` = '{$path[0]}'");
    $startId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    $distance = (count($path) - 1) * 500;




    // Initialisation des arrêts intermédiaires
    $stop2_id = NULL;
    $stop3_id = NULL;
    $stop4_id = NULL;




    if (isset($path[1])) {
        $stmt = $pdo->query("SELECT `id` FROM stops WHERE `name` = '{$path[1]}'");
        $stop2_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }
    if (isset($path[2])) {
        $stmt = $pdo->query("SELECT `id` FROM stops WHERE `name` = '{$path[2]}'");
        $stop3_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }
    if (isset($path[3])) {
        $stmt = $pdo->query("SELECT `id` FROM stops WHERE `name` = '{$path[3]}'");
        $stop4_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }



    $stmt = $pdo->prepare("INSERT INTO paths (start_stop_id, stop2_id, stop3_id, stop4_id, distance, is_assigned)
                           VALUES (:startId, :stop2_id, :stop3_id, :stop4_id, :distance, :is_assigned)");
    $stmt->execute([
        'startId' => $startId,
        'stop2_id' => $stop2_id,
        'stop3_id' => $stop3_id,
        'stop4_id' => $stop4_id,
        'distance' => $distance,
        'is_assigned' => 0
    ]);
}








try {
    generateallPaths($pdo, $graph);




    // // Populate graph from database
    // $stmt = $pdo->query("SELECT * FROM streets_stops");
    // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //     $graph[$row['start_stop_id']][] = $row['end_stop_id'];
    // }




    // foreach (array_keys($graph) as $startStopId) {
    //     $allPaths = findPaths($graph, 121);
    //     foreach ($allPaths as $path) {
    //         if (count($path) >= 2) {
    //             $startId = $path[0];
    //             $endId = $path[count($path) - 1];
    //             $distance = count($path) * 500;
    //             $pdo->prepare("INSERT INTO paths (start_stop_id, end_stop_id, distance) VALUES (?, ?, ?)")
    //                 ->execute([$startId, $endId, $distance]);
    //         }
    //     }
    // }




    // $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erreur lors de l'insertion des itinéraires : " . $e->getMessage();
}
?>
