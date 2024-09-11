<?php
require '../vendor/autoload.php';
require_once 'class/bicycle.php';


use React\EventLoop\Loop;
use React\Promise\Promise;
use React\ChildProcess\Process;


include 'graph.php';
include 'pdo.php';


function simulateBicycles($pdo, $graph) {
    $stops = ['stop4_id', 'stop3_id', 'stop2_id', 'start_stop_id'];
    $loop = Loop::get();
    for ($i = 1; $i < 5; $i++) {
        $stmt = $pdo->prepare("SELECT s.name
                        FROM `bicycles` b
                        LEFT JOIN stops s on s.id = b.stop_id
                        WHERE b.id = ?");
        $stmt->execute([$i]);
        $bicycleInfo = $stmt->fetch();
        $bicycles[] = new Bicycle($i, $bicycleInfo['name'], $pdo);
    }
    $unassignedPaths = getUnassignedPaths($pdo);


    foreach ($bicycles as $bicycle) {
        $loop->futureTick(function () use ($bicycle, &$unassignedPaths, $pdo, $graph) {
            handleBicycleTask($bicycle, $unassignedPaths, $pdo, $graph);
        });
        $loop->addTimer(0.100, function () use ($bicycle, &$unassignedPaths, $pdo, $graph) {
            while (!empty($unassignedPaths)) {
                // find a path
                $path = assignPathToBicycle($bicycle, $unassignedPaths, $graph, $pdo);
                echo "\npath : ";
                foreach ($path as $key => $value) {
                    if ($key != "id" && $key != "distance") {
                        echo $value . " -> ";
                    }
                }
                echo " assigned to bicycle " . $bicycle->id;
                $stmt = $this->pdo->prepare("UPDATE bicycles SET path_id = ? WHERE id = ?");
                $stmt->execute([$path['id'], $bicycle->id]);
                if ($path) {
                    // Mark path as assigned and remove it from the list
                    $stmt = $pdo->prepare("UPDATE paths SET is_assigned = 1 WHERE id = ?");
                    $stmt->execute([$path['id']]);
                    $unassignedPaths = array_filter($unassignedPaths, function ($p) use ($path) {
                        return $p['id'] !== $path['id'];
                    });


                    // go to path
                    echo "\nbicycle " . $bicycle->id . " going to path.";
                    $bicycle->moveToDestination($path["start_stop_id"], $graph);
                    // perform path
                    echo "\nbicycle " . $bicycle->id . " arrived to path.";
                    $stops = ['stop4_id', 'stop3_id', 'stop2_id'];
                    foreach ($stops as $stop) {
                        if (isset($path[$stop])) {
                            $bicycle->moveToDestination($path[$stop], $graph, true, $path);
                            break;
                        }
                    }
                    if ($bicycle->currentLoad >= $bicycle->loadCapacity) {
                        // return to base
                        $bicycle->returnToBase($graph);
                    } else {
                        do {
                            // calculate action
                            $anotherPath = assignPathToBicycle($bicycle, $unassignedPaths, $graph, $pdo);
                            if ($anotherPath) {
                                echo "\npath : ";
                                foreach ($anotherPath as $key => $value) {
                                    if ($key != "id" && $key != "distance") {
                                        echo $value . " -> ";
                                    }
                                }
                                echo " assigned to bicycle " . $bicycle->id;
                                $stmt = $this->pdo->prepare("UPDATE bicycles SET path_id = ? WHERE id = ?");
                                $stmt->execute([$anotherPath['id'], $bicycle->id]);
                                // Mark path as assigned and remove it from the list
                                $stmt = $pdo->prepare("UPDATE paths SET is_assigned = 1 WHERE id = ?");
                                $stmt->execute([$anotherPath['id']]);
                                $unassignedPaths = array_filter($unassignedPaths, function ($p) use ($anotherPath) {
                                    return $p['id'] !== $anotherPath['id'];
                                });
    
    
                                // go to path
                                echo "\nbicycle " . $bicycle->id . " going to path.";
                                $bicycle->moveToDestination($path["start_stop_id"], $graph);
                                // perform path
                                echo "\nbicycle " . $bicycle->id . " arrived to path.";
                                foreach ($stops as $stop) {
                                    if (isset($path[$stop])) {
                                        $bicycle->moveToDestination($path[$stop], $graph, true, $path);
                                        break;
                                    }
                                }
                            } else {
                                // return to base
                                $bicycle->returnToBase($graph);
                                break;
                            }
                        } while ($anotherPath);
                    }
                } else {
                    break;
                }
            }
        });
    }
    $loop->run();
    echo "\nAll paths have been assigned and cleaned, or all bicycles are at full capacity.\n";
}

function handleBicycleTask($bicycle, &$unassignedPaths, $pdo, $graph) {
    assignPathToBicycle($bicycle, $unassignedPaths, $graph, $pdo)
        ->then(function ($path) use ($bicycle, &$unassignedPaths, $pdo, $graph) {
            if ($path) {
                $bicycle->moveToDestination($path["start_stop_id"], $graph)
                    ->then(function () use ($bicycle, $path, &$unassignedPaths, $pdo, $graph) {
                        // Continue the task after the first move
                        $bicycle->moveToDestination($path["stop2_id"], $graph)
                            ->then(function () use ($bicycle, &$unassignedPaths, $pdo, $graph) {
                                // Process the next task
                                handleBicycleTask($bicycle, $unassignedPaths, $pdo, $graph);
                            });
                    });
            } else {
                // No more paths, the bicycle can return to base or stop
                echo "No path assigned for bicycle " . $bicycle->id;
            }
        });
}


function getUnassignedPaths($pdo) {
    $unassignedPaths = [];
    try {
        $stmt = $pdo->query("SELECT id, start_stop_id, stop2_id, stop3_id, stop4_id , distance FROM paths WHERE is_assigned = 0");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unassignedPaths[] = $row;
        }
    } catch (PDOException $e) {
        echo "\nErreur lors de la récupération des itinéraires non attribués : " . $e->getMessage();
    }
    return $unassignedPaths;
}


function assignPathToBicycle($bicycle, &$unassignedPaths, $graph, $pdo) {
    return new Promise(function ($resolve, $reject) use ($bicycle, &$unassignedPaths, $graph, $pdo) {
        Loop::addTimer(0, function () use ($bicycle, &$unassignedPaths, $graph, $pdo, $resolve) {
            foreach ($unassignedPaths as $path) {
                if (canCompletePath($bicycle, $path, $graph, $pdo) && !pathAlreadyAssigned($path, $pdo)) {
                    $resolve($path);
                    return;
                }
            }
            $resolve(null); // No path available
        });
    });
}


function pathAlreadyAssigned($path, $pdo) {
    $unassignedPaths = getUnassignedPaths($pdo);
    if (in_array($path, $unassignedPaths)) {
        return false;
    } else {
        return true;
    }
}


function canCompletePath($bicycle, $path, $graph, $pdo) {
    $currentStop = $bicycle->position;
    // var_dump($path);
    // var_dump($currentStop);
    $stops = [
        'stop4_id' => 4,
        'stop3_id' => 3,
        'stop2_id' => 2,
        'start_stop_id' => 1
    ];
    foreach ($stops as $stop => $number) {
        if (isset($path[$stop]) && !is_null($path[$stop])) {
            $end_stop_id = $path[$stop];
            $number_of_stops = $number;
            break;
        }
    }
    // var_dump($number_of_stops);
    $stmt = $pdo->query("SELECT `name` FROM stops WHERE `id` = $end_stop_id");
    $end_stop_name = $stmt->fetch(PDO::FETCH_ASSOC)['name'];
    $stmt = $pdo->query("SELECT `name` FROM stops WHERE `id` = {$path['start_stop_id']}");
    $start_stop_name = $stmt->fetch(PDO::FETCH_ASSOC)['name'];
    $returnDistance = calculatePathDistance(findShortestPath($graph, $end_stop_name, "PORTE_D_IVRY"));
    // var_dump($returnDistance);
    $distanceToPath = calculatePathDistance(findShortestPath($graph, $currentStop, $start_stop_name));
    $totalDistanceNeeded = $path['distance'] + $returnDistance + $distanceToPath;
    // var_dump($totalDistanceNeeded);
    $autonomyAfterPath = $bicycle->autonomy - ($totalDistanceNeeded / 1000);
    // var_dump($autonomyAfterPath);
    $loadAfterPath = $bicycle->currentLoad + ($number_of_stops * 50);
    // var_dump($loadAfterPath);
    // var_dump($autonomyAfterPath >= 0 && $loadAfterPath <= 200);


    return $autonomyAfterPath >= 0 && $loadAfterPath <= 200; // Checks both autonomy and load capacity
}


function addBidirectionalEdges(&$graph) {
    foreach ($graph as $node => $neighbors) {
        foreach ($neighbors as $neighbor) {
            if (!isset($graph[$neighbor])) {
                $graph[$neighbor] = [];
            }
            if (!in_array($node, $graph[$neighbor])) {
                $graph[$neighbor][] = $node;
            }
        }
    }
}


function findShortestPath($graph, $start, $end) {
    addBidirectionalEdges($graph); // Ensure all connections are bidirectional
    // Initialize distances and previous nodes
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
        // Get the node with the smallest distance
        $minNode = array_search(min($queue), $queue);
        unset($queue[$minNode]);
        if ($minNode === $end) {
            break;
        }
        if (!isset($graph[$minNode])) {
            continue; // Skip nodes that have no neighbors
        }
        foreach ($graph[$minNode] as $neighbor) {
            $alt = $distances[$minNode] + 1; // All edges have weight 1
            if ($alt < $distances[$neighbor]) {
                $distances[$neighbor] = $alt;
                $previous[$neighbor] = $minNode;
                $queue[$neighbor] = $alt;
            }
        }
    }
    // Reconstruct the shortest path
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


function calculatePathDistance($path, $distancePerEdge = 500) {
    if (empty($path)) {
        return 0;
    }
    $numberOfEdges = count($path) - 1;
    return $numberOfEdges * $distancePerEdge;
}


try {
    simulateBicycles($pdo, $graph);
} catch (\Throwable $th) {
    //throw $th;
}
?>
