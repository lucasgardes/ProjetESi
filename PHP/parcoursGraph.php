<?php

define('LA_DEFENSE', 'La Défense');
define('CHARLES_DE_GAULLE_ETOILE', 'Charles de Gaulle-Étoile');
define('AUBER', 'Auber');
define('CHATELET',"Châtelet");
define('GARE_DE_LYON', 'Gare de Lyon');
define('NATION', 'Nation');
define('GARE_DU_NORD', 'Gare du Nord');
define('SAINT_MICHEL_NOTRE_DAME', 'Saint-Michel-Notre-Dame');
define('LUXEMBOURG', 'Luxembourg');
define('PORT_ROYAL', 'Port-Royal');
define('DENFERT_ROCHEREAU', 'Denfert-Rochereau');
define('CITE_UNIVERSITAIRE', 'Cité Universitaire');
define('LA_COURNEUVE_8_MAI_1945', "La Courneuve-8 Mai 1945");
define('FORT_DAUBERVILLIERS',"Fort d'Aubervilliers");
define('AUBERVILLIERS-PANTIN-QUATRE',"Aubervilliers-Pantin-Quatre");
define('CHEMINS',"Chemins");
define('PORTE DE LA VILLETTE',"Porte de la Villette");
define('CORENTIN_CARIOU',"Corentin Cariou");
define('CRIMEE',"Crimée");
define('RIQUET',"Riquet");
define('STALINGRAD',"Stalingrad");
define('LOUIS_BLANC',"Louis Blanc");
define('CHATEAU-LANDON',"Château-Landon");
define('GARE_DE_LEST',"Gare de l'Est");
define('POISSONNIERE',"Poissonnière");
define('CADET',"Cadet");
define('LE_PELETIER',"Le Peletier");
define('CHAUSSEE_DANTIN-LA_FAYETTE',"Chaussée d'Antin-La Fayette");
define('OPERA',"Opéra");
define('PYRAMIDES',"Pyramides");
define('PALAIS_ROYAL-MUSEE_DU_LOUVRE',"Palais Royal-Musée du Louvre");
define('PONT_NEUF',"Pont Neuf");
define('PONT_MARIE',"Pont Marie");
define('SULLY-MORLAND',"Sully-Morland");
define('JUSSIEU',"Jussieu");
define('PLACE_MONGE',"Place Monge");
define('CENSIER-DAUBENTON',"Censier-Daubenton");
define('LES_GOBELINS',"Les Gobelins");
define('PLACE_DITALIE',"Place d'Italie");
define('TOLBIAC',"Tolbiac");
define('MAISON_BLANCHE',"Maison Blanche");
define('PORTE_DITALIE',"Porte d'Italie");
define('PORTE_DE_CHOISY',"Porte de Choisy");
define('PORTE_DIVRY',"Porte d'Ivry");
define('PIERRE_ET_MARIE_CURIE',"Pierre et Marie Curie");
define('MAIRIE_DIVRY',"Mairie d'Ivry");

$graph = array(
    'LA_DEFENSE' => array('CHARLES_DE_GAULLE_ETOILE'),
    'CHARLES_DE_GAULLE_ETOILE' => array('LA_DEFENSE', 'AUBER'),
    'AUBER' => array('CHARLES_DE_GAULLE_ETOILE', 'CHATELET'),
    'CHATELET' => array('AUBER', 'GARE_DE_LYON', 'GARE_DU_NORD', 'SAINT_MICHEL_NOTRE_DAME'),
    'GARE_DE_LYON' => array('CHATELET', 'NATION'),
    'NATION' => array('GARE_DE_LYON'),
    'GARE_DU_NORD' => array('CHATELET'),
    'SAINT_MICHEL_NOTRE_DAME' => array('LUXEMBOURG', 'CHATELET'),
    'LUXEMBOURG' => array('PORT_ROYAL', 'SAINT_MICHEL_NOTRE_DAME'),
    'PORT_ROYAL' => array('DENFERT_ROCHEREAU', 'LUXEMBOURG'),
    'DENFERT_ROCHEREAU' => array('CITE_UNIVERSITAIRE', 'PORT_ROYAL'),
    'CITE_UNIVERSITAIRE' => array('DENFERT_ROCHEREAU'),
    'LA_COURNEUVE_8_MAI_1945' => array('FORT_DAUBERVILLIERS'),
    'FORT_DAUBERVILLIERS' => array('LA_COURNEUVE_8_MAI_1945', 'AUBERVILLIERS_PANTIN_QUATRE')

);

$streets = array(
    'MERLANE' => array('LA_DEFENSE', 'CHARLES_DE_GAULLE_ETOILE', 'AUBER', 'CHATELET', 'GARE_DE_LYON', 'NATION'),
    'VELANE' => array('GARE_DU_NORD', 'CHATELET', 'SAINT_MICHEL_NOTRE_DAME', 'LUXEMBOURG', 'PORT_ROYAL', 'DENFERT_ROCHEREAU', 'CITE_UNIVERSITAIRE'),
    'TOURNEURS' => array('GARE_DU_NORD', 'CHATELET', 'GARE_DE_LYON'),
    'GENTY-MAGRE' =>  array('LA_COURNEUVE_8_MAI_1945', 'FORT_DAUBERVILLIERS', 'AUBERVILLIERS_PANTIN_QUATRE', 'CHEMINS', 'PORTE_DE_LA_VILLETTE', 'CORENTIN_CARIOU', 'CRIMEE', 'RIQUET', 'STALINGRAD', 'LOUIS_BLANC', 'CHATEAU_LANDON', 'GARE_DE_L_EST', 'POISSONNIERE', 'CADET', 'LE_PELETIER', 'CHAUSSEE_DANTIN_LA_FAYETTE', 'OPERA', 'PYRAMIDES', 'PALAIS_ROYAL_MUSEE_DU_LOUVRE', 'PONT_NEUF', 'PONT_MARIE', 'SULLY_MORLAND', 'JUSSIEU', 'PLACE_MONGE', 'CENSIER_DAUBENTON', 'LES_GOBELINS', 'PLACE_D_ITALIE', 'TOLBIAC', 'MAISON_BLANCHE', 'PORTE_D_ITALIE', 'PORTE_DE_CHOISY', 'PORTE_D_IVRY', 'PIERRE_ET_MARIE_CURIE', 'MAIRIE_D_IVRY'),
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

function bikeBatterieLevel() {

}

function getDistanceFromDepot($currentPosition) {
    pathToDepot = findShortestPath($graph, $currentPosition, porte d’Ivry);
}


$user1 = new User(1, "John", "Doe", "john.doe@gmail.com", "Nation", 100, "Station B");

// affichage des infos de $user1 :
echo "User ID: " . $user1->getId() . "<br>";
echo "First Name: " . $user1->getFirstname() . "<br>";
echo "Last Name: " . $user1->getLastname() . "<br>";
echo "Email: " . $user1->getEmail() . "<br>";
echo "Start Location: " . $user1->getStartLocation() . "<br>";
echo "Battery Level: " . $user1->getBatteryLevel() . "<br>";
echo "Current Location: " . $user1->getCurrentLocation() . "<br>";




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
