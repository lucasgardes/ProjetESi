<?php
require '../../vendor/autoload.php';
use React\EventLoop\Loop;
use React\Promise\Promise;
include '../../pdo.php';
class Bicycle {
    public $id;
    public $autonomy = 50;
    public $loadCapacity = 200;
    public $currentLoad = 0;
    public $position;
    public $picking = false;
    public $returnToBase = false;
    public $baseLocation = 'PORTE_D_IVRY';
    public $isWinter = false;


    private $pdo;


    public function __construct($id, $position, $pdo) {
        $this->id = $id;
        $this->position = $position;
        $this->pdo = $pdo;
        $this->loadFromDB();
    }

    private function applyWinterAutonomyReduction() {
        if ($this->isWinter) {
            $this->autonomy = $this->autonomy * 0.9; // Reduce autonomy by 10%
        }
    }

    private function loadFromDB() {
        $stmt = $this->pdo->prepare("SELECT autonomy, `load`, s.name as position, winter FROM bicycles LEFT JOIN stops s ON s.id = bicycles.stop_id WHERE bicycles.id = ?");
        $stmt->execute([$this->id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->autonomy = $data['autonomy'];
            $this->currentLoad = $data['load'];
            $this->position = $data['position'];
            $this->isWinter = $data['winter'];
        }
    }


    public function saveToDB() {
        $stmt = $this->pdo->prepare("SELECT s.id FROM stops s WHERE s.name = ?");
        $stmt->execute([$this->position]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->pdo->prepare("UPDATE bicycles SET autonomy = ?, `load` = ?, stop_id = ? WHERE id = ?");
        $stmt->execute([$this->autonomy, $this->currentLoad, $data['id'], $this->id]);
    }

    public function updateStopLoad() {
        $stmt = $this->pdo->prepare("SELECT s.id FROM stops s WHERE s.name = ?");
        $stmt->execute([$this->position]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->pdo->prepare("UPDATE stops SET `empty` = 1 WHERE id = ?");
        $stmt->execute([$data['id']]);
    }

    public function updatePath() {
        $stmt = $this->pdo->prepare("UPDATE bicycles SET path_id = NULL WHERE id = ?");
        $stmt->execute([$this->id]);
    }


    public function updatePosition($newPosition) {
        $this->position = $newPosition;
        if ($this->position == $this->baseLocation) {
            $this->autonomy = 50;
            $this->applyWinterAutonomyReduction();
        }
        $this->saveToDB();
        echo "\nBicycle " . $this->id . "On " . $this->position;
        echo "\nBicycle " . $this->id . "Autonomy : " . $this->autonomy;
        echo "\nBicycle " . $this->id . "Load : " . $this->currentLoad;
    }


    public function updateLoad($emptying = false) {
        if ($emptying) {
            $this->currentLoad = 0;
            echo "\nBicycle " . $this->id . "emptying load";
        } else {
            $this->currentLoad += 50;
            $this->updateStopLoad();
            echo "\nBicycle " . $this->id . "picking, new load : " . $this->currentLoad;
        }
        $this->saveToDB();
    }


    public function addBidirectionalEdges(&$graph) {
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


    public function determinPathToDestination($graph, $start, $end) {
        $this->addBidirectionalEdges($graph); // Ensure all connections are bidirectional
        // Initialize distances and previous nodes
        $distances = [];
        $previous = [];
        $queue = [];

        // Fetch blocked stops from the `blocked_stop` table
        $stmt = $this->pdo->query("SELECT name FROM blocked_stop WHERE etat = 'blocked'");
        $blockedStops = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($graph as $node => $neighbors) {
            if (in_array($node, $blockedStops)) {
                continue; // Skip blocked stops
            }
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
                if (in_array($neighbor, $blockedStops)) {
                    continue; // Skip blocked neighbors
                }
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


    public function moveToDestination($destination, $graph, $picking = false, $path = null) {
        return new Promise(function ($resolve) use ($destination, $graph, $picking, $path) {
            $loop = Loop::get();
            if (is_int($destination) || (is_string($destination) && ctype_digit($destination))) {
                $stmt = $this->pdo->prepare("SELECT s.name FROM stops s WHERE s.id = ?");
                $stmt->execute([$destination]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $destination = $data['name'];
            }
    
            if ($picking) {
                $this->picking = true;
            } else {
                $this->picking = false;
            }
    
            if (is_null($path)) {
                $path = $this->determinPathToDestination($graph, $this->position, $destination);
            }
            $this->executeNextMove($graph, $path, $destination, 1, $resolve);
        });
    }

    public function executeNextMove($graph, $path, $destination, $i, $resolve) {
        $loop = Loop::get();
        if ($this->position != $destination && $this->autonomy > 0) {
            if (isset($path[$i])) {
                $nextStop = $path[$i];
                if (is_int($nextStop)) {
                    $stmt = $this->pdo->prepare("SELECT s.name FROM stops s WHERE id = ?");
                    $stmt->execute([$nextStop]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $nextStop = $data['name'];
                }
                $loop->addTimer(3, function () use ($graph, $path, $destination, $nextStop, $i, $resolve) {
                    $this->moveToNextStop($graph, $nextStop)->then(function ($success) use ($graph, $path, $destination, $i, $resolve) {
                        if ($success) {
                            // Continue vers l'arrêt suivant
                            $this->executeNextMove($graph, $path, $destination, $i + 1, $resolve);
                        } else {
                            // Arrêter si l'opération a échoué
                            $resolve(false);
                        }
                    });
                });
            }
        } else {
            $this->updatePath(); // Update path once arrived
            if ($this->returnToBase) {
                $this->updateLoad(true); // Unload if necessary
            }
            $resolve(true); // Resolve the promise when finished
        }
    }


    public function returnToBase($graph)
    {
        echo "\nBicycle " . $this->id . "return to base !";
        echo "\nBicycle " . $this->id . "Autonomy : " . $this->autonomy;
        echo "\nBicycle " . $this->id . "Load : " . $this->currentLoad;
        $this->returnToBase = true;
        $this->moveToDestination($this->baseLocation, $graph);
    }


    public function moveToNextStop($graph, $nextStop) {
        return new Promise(function ($resolve) use ($graph, $nextStop) {
            $stmt = $this->pdo->prepare("SELECT etat FROM blocked_stop WHERE name = ?");
            $stmt->execute([$nextStop]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($data && $data['etat'] === 'blocked') {
                echo "\nStop " . $nextStop . " is blocked. Bicycle " . $this->id . " cannot proceed.";
                $this->returnToBase($graph);
                $resolve(false); // La promesse échoue si l'arrêt est bloqué
                return;
            }
    
            if ($this->returnToBase) {
                $this->autonomy -= 0.5;
                $this->updatePosition($nextStop);
            } else {
                $pathToBase = $this->determinPathToDestination($graph, $nextStop, $this->baseLocation);
                $stopsToBase = count($pathToBase);
                $requiredAutonomy = $stopsToBase * 0.5;
                echo "\nrequiredAutonomy " . $requiredAutonomy;
                if ($this->autonomy - 0.5 >= $requiredAutonomy) {
                    echo "\nBicycle " . $this->id . "move to " . $nextStop;
                    echo "\nBicycle " . $this->id . "Autonomy : " . $this->autonomy;
                    echo "\nBicycle " . $this->id . "Load : " . $this->currentLoad;
                    $this->autonomy -= 0.5;
                    $this->updatePosition($nextStop);
                    if ($this->picking) {
                        $this->updateLoad();
                    }
                } else {
                    $this->returnToBase($graph);
                }
            }
    
            // Fin de l'opération
            $resolve(true); // La promesse est résolue avec succès
        });
    }
}
?>
