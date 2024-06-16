<?php
include 'graph.php';
function exploreBranch($ArretActuel, &$visited, &$path, $graph) {
    $visited[] = $ArretActuel;
    $path[] = $ArretActuel;
    do {
        $a_visiter = array();
        $arretADJACENTS = $graph[$ArretActuel];

        foreach ($arretADJACENTS as $station) {
            if (!in_array($station, $visited)) {
                $a_visiter[] = $station;
            }
        }

        if (!empty($a_visiter)) {
            if (count($a_visiter) > 2) {
                echo 'croizmant';
                croisementRencontre($a_visiter, $ArretActuel, $visited, $path, $graph);
            } else {
                $ArretActuel = $a_visiter[0];
                $visited[] = $ArretActuel;
                $path[] = $ArretActuel;
                print_r($path);
            }
        } else {
            return $path;
            
        }
    } while (true);
}

function croisementRencontre($a_visiter, $ArretActuel, &$visited, &$path, $graph) {
    global $pathCroisement;

    $pathlist = array();
    foreach ($a_visiter as $station) {
        $new_path = exploreBranch($station, $visited, $path, $graph);
        if (!empty($new_path)) {
            $pathlist[] = $new_path;
        }
    }
    usort($pathlist, 'comparePathsLength');

    foreach ($pathlist as $pathC) {
        if (is_array($pathC)) {
            $pathCroisement[] = $pathC;
            #echo "Chemin " . (count($pathCroisement)) . ": " . implode(', ', $pathC) . "\n";
        }
    }
}

function comparePathsLength($path1, $path2) {
    return count($path1) - count($path2);
}

function findShortestPath($graph, $start, $end) {
    $queue = new SplQueue();

    $queue->enqueue([$start]);

    $visited = [];

    while (!$queue->isEmpty()) {
        $path = $queue->dequeue();
        $node = $path[count($path) - 1];

        if ($node == $end) {
            return $path;
        }

        if (!in_array($node, $visited)) {
            array_push($visited, $node);

            foreach ($graph[$node] as $adjacent) {
                $new_path = $path;
                array_push($new_path, $adjacent);
                $queue->enqueue($new_path);
            }
        }
    }

    return "No path found";
}

function getAdjacentStops($graph, $currentStop) {
    if (array_key_exists($currentStop, $graph)) {
        return $graph[$currentStop];
    } else {
        return "No stops found for this station.";
    }
}

function findStreetsByStop($streets, $stop) {
    $foundStreets = [];

    foreach ($streets as $streetName => $stops) {
        if (in_array($stop, $stops)) {
            $foundStreets[] = $streetName;
        }
    }

    return $foundStreets;
}

function moveBikeAlongPath($path) {
    if ($path === null) {
        echo "Aucun chemin disponible pour déplacer le vélo.\n";
        return;
    }
    echo "Déplacement du vélo le long du chemin:\n";
    foreach ($path as $index => $stop) {
        echo ($index + 1) . ". Arrêté à : " . $stop . "\n";
        sleep(1);
    }
    echo "Le vélo a atteint sa destination finale : " . end($path) . "\n";
}

function bikeBatterieLevel() {

}

function getDistanceFromDepot($currentPosition, $graph) {
    $pathToDepot = findShortestPath($graph, $currentPosition, "porte d’Ivry");
}


$test = findShortestPath($graph, 'LA_DEFENSE', 'CITE_UNIVERSITAIRE');
$test1 = getAdjacentStops($graph, 'CHATELET_LES_HALLES');
$test2 = findStreetsByStop($streets, 'GARE_DU_NORD');
var_dump($test);
var_dump($test1);
var_dump($test2);die();
moveBikeAlongPath($test);
// $ArretActuel = CITE_UNIVERSITAIRE;
// $visited = array();
// $path = array();
// exploreBranch($ArretActuel, $visited, $path, $graph);
// $pathCroisement = array();

?>
