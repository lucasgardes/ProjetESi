<?php

define('LA_DEFENSE', 'La Défense');
define('CHARLES_DE_GAULLE_ETOILE', 'Charles de Gaulle-Étoile');
define('AUBER', 'Auber');
define('CHATELET_LES_HALLES', 'Châtelet-Les Halles');
define('GARE_DE_LYON', 'Gare de Lyon');
define('NATION', 'Nation');
define('GARE_DU_NORD', 'Gare du Nord');
define('SAINT_MICHEL_NOTRE_DAME', 'Saint-Michel-Notre-Dame');
define('LUXEMBOURG', 'Luxembourg');
define('PORT_ROYAL', 'Port-Royal');
define('DENFERT_ROCHEREAU', 'Denfert-Rochereau');
define('CITE_UNIVERSITAIRE', 'Cité Universitaire');

$graph = array(
    'LA_DEFENSE' => array('CHARLES_DE_GAULLE_ETOILE'),
    'CHARLES_DE_GAULLE_ETOILE' => array('LA_DEFENSE', 'AUBER'),
    'AUBER' => array('CHARLES_DE_GAULLE_ETOILE', 'CHATELET_LES_HALLES'),
    'CHATELET_LES_HALLES' => array('AUBER', 'GARE_DE_LYON', 'GARE_DU_NORD', 'SAINT_MICHEL_NOTRE_DAME'),
    'GARE_DE_LYON' => array('CHATELET_LES_HALLES', 'NATION'),
    'NATION' => array('GARE_DE_LYON'),
    'GARE_DU_NORD' => array('CHATELET_LES_HALLES'),
    'SAINT_MICHEL_NOTRE_DAME' => array('LUXEMBOURG', 'CHATELET_LES_HALLES'),
    'LUXEMBOURG' => array('PORT_ROYAL', 'SAINT_MICHEL_NOTRE_DAME'),
    'PORT_ROYAL' => array('DENFERT_ROCHEREAU', 'LUXEMBOURG'),
    'DENFERT_ROCHEREAU' => array('CITE_UNIVERSITAIRE', 'PORT_ROYAL'),
    'CITE_UNIVERSITAIRE' => array('DENFERT_ROCHEREAU'),
);

$streets = array(
    'MERLANE' => array('LA_DEFENSE', 'CHARLES_DE_GAULLE_ETOILE', 'AUBER', 'CHATELET_LES_HALLES', 'GARE_DE_LYON', 'NATION'),
    'VELANE' => array('GARE_DU_NORD', 'CHATELET_LES_HALLES', 'SAINT_MICHEL_NOTRE_DAME', 'LUXEMBOURG', 'PORT_ROYAL', 'DENFERT_ROCHEREAU', 'CITE_UNIVERSITAIRE'),
    'TOURNEURS' => array('GARE_DU_NORD', 'CHATELET_LES_HALLES', 'GARE_DE_LYON'),
);

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
