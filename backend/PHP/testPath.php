<?php
require '../../vendor/autoload.php';
require_once '../../frontend/PHP/class/bicycle.php';

use React\EventLoop\Loop;
use React\Promise\Promise;
use React\ChildProcess\Process;

include '../../frontend/PHP/graph.php';
include '../../pdo.php';

function simulateBicycles($pdo, $graph) {
    $loop = Loop::get();
    $bicycles = [];
    
    // Initialiser les vélos
    for ($i = 1; $i < 5; $i++) {
        $stmt = $pdo->prepare("SELECT s.name FROM `bicycles` b LEFT JOIN stops s on s.id = b.stop_id WHERE b.id = ?");
        $stmt->execute([$i]);
        $bicycleInfo = $stmt->fetch();

        if ($bicycleInfo) {
            $bicycles[] = new Bicycle($i, $bicycleInfo['name'], $pdo);
        }
    }

    // Récupérer les itinéraires non assignés
    $unassignedPaths = getUnassignedPaths($pdo);

    foreach ($bicycles as $bicycle) {
        $loop->futureTick(function () use ($bicycle, &$unassignedPaths, $pdo, $graph) {
            handleBicycleTask($bicycle, $unassignedPaths, $pdo, $graph);
        });
    }

    $loop->run();
    echo "\nTous les itinéraires ont été assignés ou tous les vélos ont atteint leur capacité maximale.\n";
}

function handleBicycleTask($bicycle, &$unassignedPaths, $pdo, $graph) {
    assignPathToBicycle($bicycle, $unassignedPaths, $graph, $pdo)
        ->then(function ($path) use ($bicycle, &$unassignedPaths, $pdo, $graph) {
            if ($path) {
                $bicycle->moveToDestination($path["start_stop_id"], $graph)
                    ->then(function () use ($bicycle, $path, &$unassignedPaths, $pdo, $graph) {
                        completePathTask($bicycle, $path, $pdo, $graph);
                        handleBicycleTask($bicycle, $unassignedPaths, $pdo, $graph);
                    });
            } else {
                echo "\nAucun itinéraire assigné pour le vélo " . $bicycle->id;
                if ($bicycle->currentLoad > 0) {
                    $bicycle->returnToBase($graph);
                }
            }
        });
}

function completePathTask($bicycle, $path, $pdo, $graph) {
    echo "\nVélo " . $bicycle->id . " effectue le chemin.";
    $bicycle->moveToDestination($path["stop2_id"], $graph)
        ->then(function () use ($bicycle, $pdo, $graph) { // Ajout de $graph ici
            $bicycle->updateLoad();
            $bicycle->returnToBase($graph); // $graph doit être transmis ici
        });
}

function getUnassignedPaths($pdo) {
    $unassignedPaths = [];
    try {
        $stmt = $pdo->query("SELECT id, start_stop_id, stop2_id, stop3_id, stop4_id, distance FROM paths WHERE is_assigned = 0");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unassignedPaths[] = $row;
        }
    } catch (PDOException $e) {
        echo "\nErreur lors de la récupération des itinéraires non attribués : " . $e->getMessage();
    }
    return $unassignedPaths;
}

function assignPathToBicycle($bicycle, &$unassignedPaths, $graph, $pdo) {
    return new Promise(function ($resolve) use ($bicycle, &$unassignedPaths, $graph, $pdo) {
        foreach ($unassignedPaths as $index => $path) {
            if (canCompletePath($bicycle, $path, $graph, $pdo)) {
                // Marquer le chemin comme en cours d'assignation
                unset($unassignedPaths[$index]);
                $stmt = $pdo->prepare("UPDATE paths SET is_assigned = 1 WHERE id = ?");
                $stmt->execute([$path['id']]);
                $stmt = $pdo->prepare("UPDATE bicycles SET path_id = ? WHERE id = ?");
                $stmt->execute([$path['id'], $bicycle->id]);
                $resolve($path);
                return;
            }
        }
        $resolve(null); // Aucun chemin disponible
    });
}

function canCompletePath($bicycle, $path, $graph, $pdo) {
    $currentStop = $bicycle->position;
    $stops = ['stop4_id', 'stop3_id', 'stop2_id', 'start_stop_id'];

    foreach ($stops as $stop) {
        if (isset($path[$stop]) && !is_null($path[$stop])) {
            $end_stop_id = $path[$stop];
            break;
        }
    }

    $end_stop_name = getStopName($pdo, $end_stop_id);
    $start_stop_name = getStopName($pdo, $path['start_stop_id']);
    $distanceToPath = calculatePathDistance(findShortestPath($graph, $currentStop, $start_stop_name));
    $returnDistance = calculatePathDistance(findShortestPath($graph, $end_stop_name, $bicycle->baseLocation));
    $totalDistanceNeeded = $path['distance'] + $returnDistance + $distanceToPath;

    $autonomyAfterPath = $bicycle->autonomy - ($totalDistanceNeeded / 1000);
    $loadAfterPath = $bicycle->currentLoad + (50 * count(array_filter($stops, fn($stop) => isset($path[$stop]))));

    return $autonomyAfterPath >= 0 && $loadAfterPath <= $bicycle->loadCapacity;
}

function getStopName($pdo, $stopId) {
    if (!$stopId) return null;
    $stmt = $pdo->prepare("SELECT `name` FROM stops WHERE `id` = ?");
    $stmt->execute([$stopId]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['name'] ?? null;
}

function calculatePathDistance($path, $distancePerEdge = 500) {
    if (empty($path)) {
        return 0;
    }
    return (count($path) - 1) * $distancePerEdge;
}

function findShortestPath($graph, $start, $end) {
    $distances = [];
    $previous = [];
    $queue = [];

    foreach ($graph as $node => $neighbors) {
        $distances[$node] = INF;
        $previous[$node] = null;
        $queue[$node] = INF;
    }

    $distances[$start] = 0;
    $queue[$start] = 0;
    while (!empty($queue)) {
        $minNode = array_search(min($queue), $queue);
        unset($queue[$minNode]);

        if ($minNode === $end) {
            break;
        }

        foreach ($graph[$minNode] as $neighbor) {
            $alt = $distances[$minNode] + 1;
            if ($alt < $distances[$neighbor]) {
                $distances[$neighbor] = $alt;
                $previous[$neighbor] = $minNode;
                $queue[$neighbor] = $alt;
            }
        }
    }

    $path = [];
    $u = $end;
    while ($previous[$u] !== null) {
        array_unshift($path, $u);
        $u = $previous[$u];
    }
    if (!empty($path) || $start === $end) {
        array_unshift($path, $start);
    }

    return $path;
}

try {
    simulateBicycles($pdo, $graph);
} catch (Throwable $e) {
    echo "\nErreur : " . $e->getMessage();
}
?>
