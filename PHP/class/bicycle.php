<?php
include 'pdo.php';
class Bicycle {
    public $id;
    public $autonomy = 50;
    public $loadCapacity = 200;
    public $currentLoad = 0;
    public $position;
    public $picking = false;
    public $returnToBase = false;
    public $baseLocation = 'PORTE_D_IVRY';


    private $pdo;


    public function __construct($id, $position, $pdo) {
        $this->id = $id;
        $this->position = $position;
        $this->pdo = $pdo;
        $this->loadFromDB();
    }


    private function loadFromDB() {
        $stmt = $this->pdo->prepare("SELECT autonomy, `load`, s.name as position FROM bicycles LEFT JOIN stops s ON s.id = bicycles.stop_id WHERE bicycles.id = ?");
        $stmt->execute([$this->id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->autonomy = $data['autonomy'];
            $this->currentLoad = $data['load'];
            $this->position = $data['position'];
        }
    }


    public function saveToDB() {
        $stmt = $this->pdo->prepare("SELECT s.id FROM stops s WHERE s.name = ?");
        $stmt->execute([$this->position]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->pdo->prepare("UPDATE bicycles SET autonomy = ?, `load` = ?, stop_id = ? WHERE id = ?");
        $stmt->execute([$this->autonomy, $this->currentLoad, $data['id'], $this->id]);
    }


    public function updatePosition($newPosition) {
        $this->position = $newPosition;
        if ($this->position == $this->baseLocation) {
            $this->autonomy = 50;
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


    public function moveToDestination($destination, $graph, $picking = false, $path = null) {
        if (ctype_digit($destination)) {
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
        } else {
            $stopIds[] = $path['start_stop_id'];
            if (isset($path['stop2_id'])) {
                $stopIds[] = $path['stop2_id'];
            }
            if (isset($path['stop3_id'])) {
                $stopIds[] = $path['stop3_id'];
            }


            if (isset($path['stop4_id'])) {
                $stopIds[] = $path['stop4_id'];
            }

            $placeholders = str_repeat('?,', count($stopIds) - 1) . '?';
            $query = "SELECT id, name FROM stops WHERE id IN ($placeholders)";

            // Exécuter la requête
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($stopIds);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stopNamesMap = [];
            foreach ($results as $row) {
                $stopNamesMap[$row['id']] = $row['name'];
            }

            $stopsNames = [];
            // Remplir le tableau $stopsNames avec les noms correspondants
            foreach ($stopIds as $id) {
                $stopsNames[] = $stopNamesMap[$id];
            }
            $path = $stopsNames;
        }
        $i = 1;
        if ($this->picking) {
            $this->updateLoad();
        }
        if (!$this->returnToBase) {
            while ($this->position != $destination && $this->autonomy > 0 && !$this->returnToBase) {
                if (isset($path[$i])) {
                    $this->moveToNextStop($graph, $path[$i]);
                }
                $i++;
            }
        } else {
            while ($this->position != $destination && $this->autonomy > 0) {
                if (isset($path[$i])) {
                    $this->moveToNextStop($graph, $path[$i]);
                }
                $i++;
            }
            if ($this->position == $destination) {
                $this->updateLoad(true);
            }
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
    }
}
?>
