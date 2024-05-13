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
    LA_DEFENSE => array(CHARLES_DE_GAULLE_ETOILE),
    CHARLES_DE_GAULLE_ETOILE => array(LA_DEFENSE, AUBER),
    AUBER => array(CHARLES_DE_GAULLE_ETOILE, CHATELET_LES_HALLES),
    CHATELET_LES_HALLES => array(AUBER, GARE_DE_LYON, GARE_DU_NORD, SAINT_MICHEL_NOTRE_DAME),
    GARE_DE_LYON => array(CHATELET_LES_HALLES, NATION),
    NATION => array(GARE_DE_LYON),
    GARE_DU_NORD => array(CHATELET_LES_HALLES),
    SAINT_MICHEL_NOTRE_DAME => array(LUXEMBOURG, CHATELET_LES_HALLES),
    LUXEMBOURG => array(PORT_ROYAL, SAINT_MICHEL_NOTRE_DAME),
    PORT_ROYAL => array(DENFERT_ROCHEREAU, LUXEMBOURG),
    DENFERT_ROCHEREAU => array(CITE_UNIVERSITAIRE, PORT_ROYAL),
    CITE_UNIVERSITAIRE => array(DENFERT_ROCHEREAU),
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

$ArretActuel = CITE_UNIVERSITAIRE;
$visited = array();
$path = array();
exploreBranch($ArretActuel, $visited, $path, $graph);
$pathCroisement = array();

?>
