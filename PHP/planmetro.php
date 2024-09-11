<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plan de Rammassage des Poubelle</title>
    <link rel="stylesheet" href="../CSS/planmetro.css">
</head>
<body>
    <?php include 'header.php';?>
    <?php
        require 'pdo.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        $stmt = $pdo->prepare("SELECT s.* FROM stops s");
        $stmt->execute();
        $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT s.* FROM streets s");
        $stmt->execute();
        $streets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT s.name, b.*
        FROM bicycles b
        LEFT JOIN stops s ON s.id = b.stop_id");
        $stmt->execute();
        $bicycles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateWinter'])) {
            $winterStatus = isset($_POST['winter']) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE bicycles SET winter = ?");
            $success = $stmt->execute([$winterStatus]);
        }
        $stmt = $pdo->prepare("SELECT winter FROM bicycles WHERE id = 1");
        $stmt->execute();
        $currentWinterStatus = $stmt->fetchColumn();
    ?>
    <div id="mousePosition">Mouse Position: (x, y)</div>
    <div id="zoomLevel">Zoom Level: 1</div>
    <form method="post">
        <label for="winterToggle">Winter Mode:</label>
        <input type="checkbox" id="winterToggle" name="winter" value="1" <?= $currentWinterStatus ? 'checked' : '' ?>>
        <button type="submit" name="updateWinter">Update</button>
    </form>
    <div id="stationInfo">Station Info: </div>
    <canvas id="metroMap" width="1600" height="800"></canvas>
    <div id="controls">
        <button onclick="zoomIn()">Zoom In</button>
        <button onclick="zoomOut()">Zoom Out</button>
        <button onclick="toggleBackground()">Toggle Background</button>
    </div>
    <script>
        var stopsData = <?php echo json_encode($stops); ?>;
        var streetsData = <?php echo json_encode($streets); ?>;
        var bicyclesData = <?php echo json_encode($bicycles); ?>;
        var isPanning = false;
        var currentStreet = "Croix-Baragnon";               //AFFICHAGE RUE
        var currentStation = "VICTOR_HUGO";
        var canvas = document.getElementById("metroMap");
        var mousePositionDiv = document.getElementById("mousePosition");
        var zoomLevelDiv = document.getElementById("zoomLevel");
        var stationInfoDiv = document.getElementById("stationInfo");
        var ctx = canvas.getContext('2d');

        var zoomLevel = 1;
        var minZoom = 1;
        var maxZoom = 5;
        var zoomIncrement = 0.5;
        var offsetX = 0;
        var offsetY = 0;
        var showBackground = true;

        var data = {
                "lines": {
                    "Croix-Baragnon": {
                        "color": "#FFCD00",
                        "stations": [
                            {"name": "LA_DEFENSE", "x": 212, "y": 235},
                            {"name": "ESPLANADE_DE_LA_DEFENSE", "x": 236, "y": 248},
                            {"name": "PONT_DE_NEUILLY", "x": 262, "y": 263},
                            {"name": "LES_SABLONS", "x": 287, "y": 275},
                            {"name": "PORTE_MAILLOT", "x": 335, "y": 296},
                            {"name": "ARGENTINE", "x": 369, "y": 317},
                            {"name": "CHARLES_DE_GAULLE_ETOILE", "x": 401, "y": 333},
                            {"name": "GEORGE_V", "x": 447, "y": 357},
                            {"name": "FRANKLIN_D_ROOSEVELT", "x": 488, "y": 378},
                            {"name": "CHAMPS_ELYSEES_CLEMENCEAU", "x": 519, "y": 392},
                            {"name": "CONCORDE", "x": 546, "y": 400},
                            {"name": "TUILERIES", "x": 607, "y": 415},
                            {"name": "PALAIS_ROYAL_MUSEE_DU_LOUVRE", "x": 671, "y": 415},
                            {"name": "LOUVRE_RIVOLI", "x": 719, "y": 415},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "HOTEL_DE_VILLE", "x": 891, "y": 415},
                            {"name": "SAINT_PAUL", "x": 940, "y": 441},
                            {"name": "BASTILLE", "x": 1007, "y": 450},
                            {"name": "GARE_DE_LYON", "x": 1069, "y": 515},
                            {"name": "REUILLY_DIDEROT", "x": 1141, "y": 494},
                            {"name": "NATION", "x": 1231, "y": 468},
                            {"name": "PORTE_DE_VINCENNES", "x": 1290, "y": 477},
                            {"name": "SAINT_MANDE", "x": 1336, "y": 490},
                            {"name": "BERAULT", "x": 1370, "y": 508},
                            {"name": "CHATEAU_DE_VINCENNES", "x": 1404, "y": 525}
                        ]
                    },
                    "Arts": {
                        "color": "#003CA6",
                        "stations": [
                            {"name": "PORTE_DAUPHINE", "x": 275, "y": 337},
                            {"name": "VICTOR_HUGO", "x": 329, "y": 364},
                            {"name": "CHARLES_DE_GAULLE_ETOILE", "x": 401, "y": 333},
                            {"name": "TERNES", "x": 441, "y": 315},
                            {"name": "COURCELLES", "x": 474, "y": 299},
                            {"name": "MONCEAU", "x": 507, "y": 281},
                            {"name": "VILLIERS", "x": 540, "y": 266},
                            {"name": "ROME", "x": 578, "y": 246},
                            {"name": "PLACE_DE_CLICHY", "x": 626, "y": 241},
                            {"name": "BLANCHE", "x": 697, "y": 241},
                            {"name": "PIGALLE", "x": 772, "y": 241},
                            {"name": "ANVERS", "x": 830, "y": 241},
                            {"name": "BARBES_ROCHECHOUART", "x": 898, "y": 241},
                            {"name": "LA_CHAPELLE", "x": 971, "y": 241},
                            {"name": "STALINGRAD", "x": 1058, "y": 247},
                            {"name": "JAURES", "x": 1092, "y": 260},
                            {"name": "COLONEL_FABIEN", "x": 1092, "y": 287},
                            {"name": "BELLEVILLE", "x": 1092, "y": 311},
                            {"name": "COURONNES", "x": 1130, "y": 334},
                            {"name": "MENILMONTANT", "x": 1170, "y": 353},
                            {"name": "PERE_LACHAISE", "x": 1208, "y": 377},
                            {"name": "PHILIPPE_AUGUSTE", "x": 1208, "y": 397},
                            {"name": "ALEXANDRE_DUMAS", "x": 1208, "y": 420},
                            {"name": "AVRON", "x": 1211, "y": 448},
                            {"name": "NATION", "x": 1231, "y": 468}
                        ]
                    },
                    "Pargaminières": {
                        "color": "#837902",
                        "stations": [
                            {"name": "PONT_DE_LEVALLOIS_BECON", "x": 401, "y": 194},
                            {"name": "ANATOLE_FRANCE", "x": 419, "y": 205},
                            {"name": "LOUISE_MICHEL", "x": 435, "y": 214},
                            {"name": "PORTE_DE_CHAMPERRET", "x": 454, "y": 223},
                            {"name": "PEREIRE", "x": 481, "y": 236},
                            {"name": "WAGRAM", "x": 499, "y": 245},
                            {"name": "MALESHERBES", "x": 521, "y": 255},
                            {"name": "VILLIERS", "x": 540, "y": 266},
                            {"name": "EUROPE", "x": 581, "y": 285},
                            {"name": "SAINT_LAZARE", "x": 617, "y": 312},
                            {"name": "HAVRE_CAUMARTIN", "x": 644, "y": 339},
                            {"name": "OPERA", "x": 673, "y": 352},
                            {"name": "QUATRE_SEPTEMBRE", "x": 706, "y": 371},
                            {"name": "BOURSE", "x": 759, "y": 380},
                            {"name": "SENTIER", "x": 813, "y": 380},
                            {"name": "REAUMUR_SEBASTOPOL", "x": 884, "y": 380},
                            {"name": "ARTS_ET_METIERS", "x": 968, "y": 374},
                            {"name": "TEMPLE", "x": 993, "y": 361},
                            {"name": "REPUBLIQUE", "x": 1025, "y": 348},
                            {"name": "PARMENTIER", "x": 1135, "y": 368},
                            {"name": "RUE_SAINT_MAUR", "x": 1167, "y": 375},
                            {"name": "PERE_LACHAISE", "x": 1208, "y": 377},
                            {"name": "GAMBETTA", "x": 1271, "y": 375},
                            {"name": "PORTE_DE_BAGNOLET", "x": 1333, "y": 376},
                            {"name": "GALLIENI", "x": 1377, "y": 376}
                        ]
                    },
                    "Saint-Rome": {
                        "color": "#111111",
                        "stations": [
                            {"name": "GAMBETTA", "x": 1271, "y": 375},
                            {"name": "PELLEPORT", "x": 1308, "y": 349},
                            {"name": "SAINT_FARGEAU", "x": 1338, "y": 323},
                            {"name": "PORTE_DES_LILAS", "x": 1340, "y": 298}
                        ]
                    },
                    "Saint-Antoine du T": {
                        "color": "#000451",
                        "stations": [
                            {"name": "PORTE_DE_CLIGNANCOURT", "x": 813, "y": 177},
                            {"name": "SIMPLON", "x": 838, "y": 190},
                            {"name": "MARCADET_POISSONNIERS", "x": 857, "y": 207},
                            {"name": "CHATEAU_ROUGE", "x": 857, "y": 222},
                            {"name": "BARBES_ROCHECHOUART", "x": 898, "y": 241},
                            {"name": "GARE_DU_NORD", "x": 956, "y": 259},
                            {"name": "GARE_DE_L_EST", "x": 994, "y": 288},
                            {"name": "CHATEAU_D_EAU", "x": 933, "y": 305},
                            {"name": "STRASBOURG_SAINT_DENIS", "x": 918, "y": 339},
                            {"name": "REAUMUR_SEBASTOPOL", "x": 884, "y": 380},
                            {"name": "ETIENNE_MARCEL", "x": 831, "y": 388},
                            {"name": "LES_HALLES", "x": 830, "y": 401},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "CITE", "x": 807, "y": 438},
                            {"name": "SAINT_MICHEL_NOTRE_DAME", "x": 787, "y": 465},
                            {"name": "ODEON", "x": 775, "y": 481},
                            {"name": "SAINT_GERMAIN_DES_PRES", "x": 713, "y": 478},
                            {"name": "SAINT_SUPLICE", "x": 697, "y": 497},
                            {"name": "SAINT_PLACIDE", "x": 697, "y": 523},
                            {"name": "MONTPARNASSE_BIENVENUE", "x": 696, "y": 545},
                            {"name": "VAVIN", "x": 742, "y": 550},
                            {"name": "RASPAIL", "x": 776, "y": 559},
                            {"name": "DENFERT_ROCHEREAU", "x": 806, "y": 577},
                            {"name": "MOUTON_DUVERNET", "x": 791, "y": 596},
                            {"name": "ALESIA", "x": 774, "y": 623},
                            {"name": "PORTE_D_ORLEANS", "x": 755, "y": 633}
                        ]
                    },
                    "Fonderie": {
                        "color": "#168133",
                        "stations": [
                            {"name": "BOBIGNY_PABLO_PICASSO", "x": 1425, "y": 242},
                            {"name": "BOBIGNY_PANTIN_RAYMOND_QUENEAU", "x": 1337, "y": 205},
                            {"name": "EGLISE_DE_PANTIN", "x": 1311, "y": 220},
                            {"name": "HOCHE", "x": 1274, "y": 231},
                            {"name": "PORTE_DE_PANTIN", "x": 1222, "y": 231},
                            {"name": "OURCQ", "x": 1168, "y": 230},
                            {"name": "LAUMIERE", "x": 1125, "y": 244},
                            {"name": "JAURES", "x": 1092, "y": 260},
                            {"name": "STALINGRAD", "x": 1058, "y": 247},
                            {"name": "GARE_DU_NORD", "x": 956, "y": 259},
                            {"name": "JACQUES_BONSERGENT", "x": 994, "y": 321},
                            {"name": "REPUBLIQUE", "x": 1025, "y": 348},
                            {"name": "OBERKAMPF", "x": 1048, "y": 367},
                            {"name": "RICHARD_LENOIR", "x": 1072, "y": 408},
                            {"name": "BREQUET_SABIN", "x": 1041, "y": 424},
                            {"name": "BASTILLE", "x": 1007, "y": 450},
                            {"name": "QUAI_DE_LA_RAPEE", "x": 1009, "y": 501},
                            {"name": "GARE_D_AUSTERLITZ", "x": 1006, "y": 537},
                            {"name": "SAINT_MARCEL", "x": 1008, "y": 555},
                            {"name": "CAMPO_FORMIO", "x": 985, "y": 572},
                            {"name": "PLACE_D_ITALIE", "x": 947, "y": 598}
                        ]
                    },
                    "Peyrolières": {
                        "color": "#111111",
                        "stations": [
                            {"name": "CHARLES_DE_GAULLE_ETOILE", "x": 401, "y": 333},
                            {"name": "KLEBER", "x": 360, "y": 360},
                            {"name": "BOISSIERE", "x": 315, "y": 382},
                            {"name": "TROCADERO", "x": 311, "y": 415},
                            {"name": "PASSY", "x": 315, "y": 454},
                            {"name": "CHAMP_DE_MARS_TOUR_EIFFEL", "x": 357, "y": 475},
                            {"name": "DUPLEIX", "x": 395, "y": 494},
                            {"name": "LA_MOTTE_PICQUET_GRENELLE", "x": 448, "y": 512},
                            {"name": "CAMBRONNE", "x": 517, "y": 523},
                            {"name": "SEVRES_LECOURBE", "x": 555, "y": 543},
                            {"name": "PASTEUR", "x": 608, "y": 553},
                            {"name": "MONTPARNASSE_BIENVENUE", "x": 696, "y": 545},
                            {"name": "EDGAR_QUINET", "x": 714, "y": 553},
                            {"name": "RASPAIL", "x": 776, "y": 559},
                            {"name": "DENFERT_ROCHEREAU", "x": 806, "y": 577},
                            {"name": "SAINT_JACQUES", "x": 831, "y": 589},
                            {"name": "GLACIERE", "x": 869, "y": 598},
                            {"name": "CORVISART", "x": 916, "y": 598},
                            {"name": "PLACE_D_ITALIE", "x": 947, "y": 598},
                            {"name": "NATIONALE", "x": 1003, "y": 599},
                            {"name": "CHEVALERET", "x": 1049, "y": 598},
                            {"name": "QUAI_DE_LA_GARE", "x": 1098, "y": 588},
                            {"name": "BERCY", "x": 1148, "y": 562},
                            {"name": "DUGOMMIER", "x": 1179, "y": 547},
                            {"name": "DAUMESNIL", "x": 1214, "y": 531},
                            {"name": "BEL_AIR", "x": 1240, "y": 513},
                            {"name": "PICPUS", "x": 1269, "y": 500},
                            {"name": "NATION", "x": 1231, "y": 468},
                        ]
                    },
                    "Genty-Magre": {
                        "color": "#111111",
                        "stations": [
                            {"name": "LA_COURNEUVE_8_MAI_1945", "x": 1216, "y": 130},
                            {"name": "FORT_DAUBERVILLIERS", "x": 1216, "y": 152},
                            {"name": "AUBERVILLIERS_PANTIN_QUATRE_CHEMINS", "x": 1179, "y": 176},
                            {"name": "PORTE_DE_LA_VILLETTE", "x": 1144, "y": 194},
                            {"name": "CORENTIN_CARIOU", "x": 1117, "y": 208},
                            {"name": "CRIMEE", "x": 1094, "y": 217},
                            {"name": "RIQUET", "x": 1072, "y": 229},
                            {"name": "STALINGRAD", "x": 1058, "y": 247},
                            {"name": "LOUIS_BLANC", "x": 1050, "y": 264},
                            {"name": "CHATEAU_LANDON", "x": 1024, "y": 277},
                            {"name": "GARE_DE_L_EST", "x": 994, "y": 288},
                            {"name": "POISSONNIERE", "x": 869, "y": 286},
                            {"name": "CADET", "x": 797, "y": 292},
                            {"name": "LE_PELETIER", "x": 770, "y": 304},
                            {"name": "CHAUSSEE_DANTIN_LA_FAYETTE", "x": 709, "y": 338},
                            {"name": "OPERA", "x": 673, "y": 352},
                            {"name": "PYRAMIDES", "x": 672, "y": 383},
                            {"name": "PALAIS_ROYAL_MUSEE_DU_LOUVRE", "x": 671, "y": 415},
                            {"name": "PONT_NEUF", "x": 763, "y": 419},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "PONT_MARIE", "x": 898, "y": 442},
                            {"name": "SULLY_MORLAND", "x": 941, "y": 463},
                            {"name": "JUSSIEU", "x": 921, "y": 517},
                            {"name": "PLACE_MONGE", "x": 891, "y": 534},
                            {"name": "CENSIER_DAUBENTON", "x": 892, "y": 554},
                            {"name": "LES_GOBELINS", "x": 919, "y": 574},
                            {"name": "PLACE_D_ITALIE", "x": 947, "y": 598},
                            {"name": "TOLBIAC", "x": 945, "y": 614},
                            {"name": "MAISON_BLANCHE", "x": 986, "y": 638},
                            {"name": "PORTE_D_ITALIE", "x": 986, "y": 641},
                            {"name": "PORTE_DE_CHOISY", "x": 1036, "y": 641},
                            {"name": "PORTE_D_IVRY", "x": 1085, "y": 641},
                            {"name": "PIERRE_ET_MARIE_CURIE", "x": 1117, "y": 648},
                            {"name": "MAIRIE_D_IVRY", "x": 1141, "y": 661}
                        ]
                    },
                    "Alsace-Lorraine": {
                        "color": "#111111",
                        "stations": [
                            {"name": "LOUIS_BLANC", "x": 1050, "y": 264},
                            {"name": "JAURES", "x": 1092, "y": 260},
                            {"name": "BOLIVAR", "x": 1127, "y": 274},
                            {"name": "BUTTES_CHAUMONT", "x": 1183, "y": 277},
                            {"name": "BOTZARIS", "x": 1209, "y": 278},
                            {"name": "PLACE_DES_FETES", "x": 1257, "y": 297},
                            {"name": "PRE_SAINT_GERVAIS", "x": 1315, "y": 271}
                        ]
                    },
                    "Peyras": {
                        "color": "#111111",
                        "stations": [
                            {"name": "BALARD", "x": 360, "y": 617},
                            {"name": "LOURMEL", "x": 401, "y": 597},
                            {"name": "BOUCICAUT", "x": 431, "y": 580},
                            {"name": "FELIX_FAURE", "x": 446, "y": 558},
                            {"name": "COMMERCE", "x": 447, "y": 539},
                            {"name": "LA_MOTTE_PICQUET_GRENELLE", "x": 448, "y": 512},
                            {"name": "ECOLE_MILITAIRE", "x": 447, "y": 476},
                            {"name": "LA_TOUR_MAUBOURG", "x": 483, "y": 451},
                            {"name": "INVALIDES", "x": 520, "y": 434},
                            {"name": "MADELEINE", "x": 554, "y": 378},
                            {"name": "OPERA", "x": 673, "y": 352},
                            {"name": "RICHELIEU_DROUOT", "x": 751, "y": 341},
                            {"name": "GRANDS_BOULEVARDS", "x": 805, "y": 341},
                            {"name": "BONNE_NOUVELLE", "x": 878, "y": 341},
                            {"name": "STRASBOURG_SAINT_DENIS", "x": 918, "y": 339},
                            {"name": "REPUBLIQUE", "x": 1025, "y": 348},
                            {"name": "FILLES_DU_CALVAIRE", "x": 1001, "y": 385},
                            {"name": "SAINT_SEBASTIEN_FROISSART", "x": 999, "y": 404},
                            {"name": "CHEMIN_VERT", "x": 1001, "y": 425},
                            {"name": "BASTILLE", "x": 1007, "y": 450},
                            {"name": "LED_RU_ROLLIN", "x": 1044, "y": 465},
                            {"name": "FAIDHERBE_CHALIGNY", "x": 1108, "y": 476},
                            {"name": "REUILLY_DIDEROT", "x": 1141, "y": 494},
                            {"name": "MONTGALLET", "x": 1178, "y": 511},
                            {"name": "DAUMESNIL", "x": 1214, "y": 531},
                            {"name": "MICHEL_BIZOT", "x": 1238, "y": 540},
                            {"name": "PORTE_DOREE", "x": 1260, "y": 559},
                            {"name": "PORTE_DE_CHARENTON", "x": 1230, "y": 574},
                            {"name": "LIBERTE", "x": 1253, "y": 594},
                            {"name": "CHARENTON_ECOLES", "x": 1287, "y": 609},
                            {"name": "ECOLE_VETERINAIRE_DE_MAISONS_ALFORT", "x": 1321, "y": 627},
                            {"name": "MAISONS_ALFORT_STA", "x": 1346, "y": 641},
                            {"name": "MAISONS_ALFORT_LES_JUILLIOTTES", "x": 1347, "y": 654},
                            {"name": "CRETEIL_L_ECHAT", "x": 1347, "y": 669},
                            {"name": "CRETEIL_UNIVERSITE", "x": 1347, "y": 679},
                            {"name": "CRETEIL_PREFECTURE", "x": 1345, "y": 695}
                        ]
                    },
                    "Jean Jaurès": {
                        "color": "#111111",
                        "stations": [
                            {"name": "BOULOGNE_PONT_DE_SAINT_CLOUD", "x": 136, "y": 573},
                            {"name": "BOULOGNE_JEAN_JAURES", "x": 166, "y": 560},
                            {"name": "PORTE_D_AUTEUIL", "x": 193, "y": 545},
                            {"name": "MICHEL_ANGE_AUTEUIL", "x": 235, "y": 535},
                            {"name": "EGLISE_D_AUTEUIL", "x": 282, "y": 535},
                            {"name": "JAVEL_ANDRE_CITROEN", "x": 345, "y": 536},
                            {"name": "CHARLES_MICHELS", "x": 389, "y": 530},
                            {"name": "AVENUE_EMILE_ZOLA", "x": 413, "y": 517},
                            {"name": "LA_MOTTE_PICQUET_GRENELLE", "x": 448, "y": 512},
                            {"name": "SEGUR", "x": 535, "y": 511},
                            {"name": "DUROC", "x": 589, "y": 523},
                            {"name": "VANEAU", "x": 622, "y": 506},
                            {"name": "SEVRES_BABYLONE", "x": 653, "y": 492},
                            {"name": "MABILLON", "x": 679, "y": 462},
                            {"name": "ODEON", "x": 775, "y": 481},
                            {"name": "CLUNY_LA_SORBONNE", "x": 821, "y": 491},
                            {"name": "MAUBERT_MUTUALITE", "x": 847, "y": 506},
                            {"name": "CARDINAL_LEMOINE", "x": 873, "y": 515},
                            {"name": "JUSSIEU", "x": 921, "y": 517},
                            {"name": "GARE_D_AUSTERLITZ", "x": 1006, "y": 537},
                        ]
                    },
                    "May": {
                        "color": "#111111",
                        "stations": [
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "HOTEL_DE_VILLE", "x": 891, "y": 415},
                            {"name": "RAMBUTEAU", "x": 929, "y": 396},
                            {"name": "ARTS_ET_METIERS", "x": 968, "y": 374},
                            {"name": "REPUBLIQUE", "x": 1025, "y": 348},
                            {"name": "GONCOURT", "x": 1062, "y": 328},
                            {"name": "BELLEVILLE", "x": 1092, "y": 311},
                            {"name": "PYRENEES", "x": 1150, "y": 299},
                            {"name": "JOURDAIN", "x": 1209, "y": 300},
                            {"name": "PLACE_DES_FETES", "x": 1257, "y": 297},
                            {"name": "TELEGRAPHE", "x": 1305, "y": 301},
                            {"name": "PORTE_DES_LILAS", "x": 1340, "y": 298},
                            {"name": "MAIRIE_DES_LILAS", "x": 1382, "y": 276}
                        ]
                    },
                    "Filatiers": {
                        "color": "#111111",
                        "stations": [
                            {"name": "PORTE_DE_LA_CHAPELLE", "x": 978, "y": 178},
                            {"name": "MARX_DORMOY", "x": 977, "y": 198},
                            {"name": "MARCADET_POISSONNIERS", "x": 857, "y": 207},
                            {"name": "JULES_JOFFRIN", "x": 813, "y": 206},
                            {"name": "LAMARCK_CAULAINCOURT", "x": 773, "y": 209},
                            {"name": "ABBESSES", "x": 774, "y": 226},
                            {"name": "PIGALLE", "x": 772, "y": 241},
                            {"name": "SAINT_GEORGES", "x": 773, "y": 267},
                            {"name": "NOTRE_DAME_DE_LORETTE", "x": 757, "y": 296},
                            {"name": "TRINITE_D_ESTIENNE_D_ORVES", "x": 682, "y": 297},
                            {"name": "SAINT_LAZARE", "x": 617, "y": 312},
                            {"name": "MADELEINE", "x": 554, "y": 378},
                            {"name": "CONCORDE", "x": 546, "y": 400},
                            {"name": "ASSEMBLEE_NATIONALE", "x": 551, "y": 443},
                            {"name": "SOLFERINO", "x": 583, "y": 458},
                            {"name": "RUE_DU_BAC", "x": 620, "y": 478},
                            {"name": "SEVRES_BABYLONE", "x": 653, "y": 492},
                            {"name": "RENNES", "x": 684, "y": 510},
                            {"name": "NOTRE_DAME_DES_CHAMPS", "x": 718, "y": 531},
                            {"name": "MONTPARNASSE_BIENVENUE", "x": 696, "y": 545},
                            {"name": "FALGUIERE", "x": 644, "y": 524},
                            {"name": "PASTEUR", "x": 608, "y": 553},
                            {"name": "VOLONTAIRES", "x": 567, "y": 573},
                            {"name": "VAUGIRARD", "x": 531, "y": 592},
                            {"name": "CONVENTION", "x": 495, "y": 610},
                            {"name": "PORTE_DE_VERSAILLES", "x": 452, "y": 630},
                            {"name": "CORENTIN_CELTON", "x": 408, "y": 655},
                            {"name": "MAIRIE_D_ISSY", "x": 366, "y": 674}
                        ]
                    },
                    "Mage": {
                        "color": "#111111",
                        "stations": [
                            {"name": "SAINT_DENIS_UNIVERSITE", "x": 937, "y": 82},
                            {"name": "BASILIQUE_DE_SAINT_DENIS", "x": 918, "y": 94},
                            {"name": "SAINT_DENIS_PORTE_DE_PARIS", "x": 828, "y": 114},
                            {"name": "CARREFOUR_PLEYEL", "x": 732, "y": 121},
                            {"name": "MAIRIE_DE_SAINT_OUEN", "x": 688, "y": 139},
                            {"name": "GARIBALDI", "x": 657, "y": 156},
                            {"name": "PORTE_DE_SAINT_OUEN", "x": 624, "y": 178},
                            {"name": "GUY_MOQUET", "x": 625, "y": 200},
                            {"name": "LA_FOURCHE", "x": 623, "y": 223},
                            {"name": "PLACE_DE_CLICHY", "x": 626, "y": 241},
                            {"name": "LIEGE", "x": 625, "y": 269},
                            {"name": "SAINT_LAZARE", "x": 617, "y": 312},
                            {"name": "MIROMESNIL", "x": 566, "y": 340},
                            {"name": "CHAMPS_ELYSEES_CLEMENCEAU", "x": 519, "y": 392},
                            {"name": "INVALIDES", "x": 520, "y": 434},
                            {"name": "VARENNE", "x": 521, "y": 462},
                            {"name": "SAINT_FRANCOIS_XAVIER", "x": 554, "y": 487},
                            {"name": "DUROC", "x": 589, "y": 523},
                            {"name": "MONTPARNASSE_BIENVENUE", "x": 696, "y": 545},
                            {"name": "GAITE", "x": 696, "y": 545},
                            {"name": "PERNETY", "x": 672, "y": 595},
                            {"name": "PLAISANCE", "x": 636, "y": 612},
                            {"name": "PORTE_DE_VANVES", "x": 593, "y": 637},
                            {"name": "MALAKOFF_PLATEAU_DE_VANVES", "x": 556, "y": 652},
                            {"name": "MALAKOFF_RUE_ETIENNE_DOLET", "x": 532, "y": 665},
                            {"name": "CHATILLON_MONROUGE", "x": 484, "y": 689},
                        ]
                    },
                    "Espinasse": {
                        "color": "#111111",
                        "stations": [
                            {"name": "SAINT_LAZARE", "x": 617, "y": 312},
                            {"name": "MADELEINE", "x": 554, "y": 378},
                            {"name": "PYRAMIDES", "x": 672, "y": 383},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "GARE_DE_LYON", "x": 1069, "y": 515},
                            {"name": "BERCY", "x": 1148, "y": 562},
                            {"name": "COUR_SAINT_EMILION", "x": 1169, "y": 588},
                            {"name": "BIBLIOTHEQUE_FRANCOIS_MITTERAND", "x": 1140, "y": 601},
                            {"name": "OLYMPIADES", "x": 1065, "y": 616}
                        ]
                    },
                    "Geste": {
                        "color": "#111111",
                        "stations": [
                            {"name": "GARE_SAINT_DENIS", "x": 847, "y": 67},
                            {"name": "THEATRE_GERARD_PHILIPE", "x": 897, "y": 116},
                            {"name": "MARCHE_DE_SAINT_DENIS", "x": 935, "y": 110},
                            {"name": "CIMETIERE_DE_SAINT_DENIS", "x": 974, "y": 94},
                            {"name": "HOPITAL_DELAFONTAINE", "x": 1008, "y": 94},
                            {"name": "COSMONAUTES", "x": 1057, "y": 94},
                            {"name": "LA_COURNEUVE_SIX_ROUTES", "x": 1093, "y": 94},
                            {"name": "HOTEL_DE_VILLE_DE_LA_COURNEUVE", "x": 1132, "y": 94},
                            {"name": "STADE_GEO_ANDRE", "x": 1166, "y": 94},
                            {"name": "DANTON", "x": 1196, "y": 94},
                            {"name": "LA_COURNEUVE_8_MAI_1945", "x": 1239, "y": 94},
                            {"name": "MAURICE_LACHATRE", "x": 1276, "y": 115},
                            {"name": "DRANCY_AVENIR", "x": 1287, "y": 143},
                            {"name": "HOPITAL_AVICIENNE", "x": 1305, "y": 166},
                            {"name": "GASTON_ROULAUD", "x": 1342, "y": 182},
                            {"name": "ESCADRILLE_NORMANDIE_NIEMEN", "x": 1378, "y": 179},
                            {"name": "LA_FERME", "x": 1411, "y": 161},
                            {"name": "LIBERATION", "x": 1426, "y": 185},
                            {"name": "HOTEL_DE_VILLE_DE_BOBIGNY", "x": 1423, "y": 213},
                            {"name": "BOBIGNY_PABLO_PICASSO", "x": 1425, "y": 242},
                            {"name": "JEAN_ROSTAND", "x": 1427, "y": 264},
                            {"name": "AUGUSTE_DELAUNE", "x": 1439, "y": 281},
                            {"name": "PONT_DE_BONDY", "x": 1470, "y": 286},
                            {"name": "PETIT_NOISY", "x": 1473, "y": 312},
                            {"name": "NOISY_LE_SEC", "x": 1473, "y": 340}
                        ]
                    },
                    "Daurade": {
                        "color": "#111111",
                        "stations": [
                            {"name": "LA_DEFENSE", "x": 212, "y": 235},
                            {"name": "PUTEAUX", "x": 118, "y": 266},
                            {"name": "BELVEDERE", "x": 96, "y": 305},
                            {"name": "SURESNES_LONGCHAMP", "x": 95, "y": 357},
                            {"name": "LES_COTEAUX", "x": 95, "y": 453},
                            {"name": "LES_MILONS", "x": 95, "y": 516},
                            {"name": "PARC_DE_SAINT_CLOUD", "x": 96, "y": 586},
                            {"name": "MUSEE_DE_SEVRES", "x": 122, "y": 658},
                            {"name": "BRIMBORION", "x": 164, "y": 680},
                            {"name": "MEUDON_SUR_SEINE", "x": 204, "y": 679},
                            {"name": "LES_MOULINEAUX", "x": 265, "y": 673},
                            {"name": "JACQUES_HENRI_LARTIGUE", "x": 292, "y": 656},
                            {"name": "ISSY_VAL_DE_SEINE", "x": 326, "y": 640},
                            {"name": "BALARD", "x": 360, "y": 617},
                            {"name": "PORTE_DE_VERSAILLES", "x": 452, "y": 630}
                        ]
                    },
                    "Bédelières": {
                        "color": "#999999",
                        "stations": [
                            {"name": "PONT_DU_GARIGLIANO", "x": 328, "y": 592},
                            {"name": "BALARD", "x": 360, "y": 617},
                            {"name": "DESNOUETTES", "x": 412, "y": 616},
                            {"name": "PORTE_DE_VERSAILLES", "x": 452, "y": 630},
                            {"name": "GEORGES_BRASSENS", "x": 483, "y": 638},
                            {"name": "BRANCION", "x": 535, "y": 637},
                            {"name": "PORTE_DE_VANVES", "x": 593, "y": 637},
                            {"name": "DIDOT", "x": 653, "y": 637},
                            {"name": "JEAN_MOULIN", "x": 711, "y": 637},
                            {"name": "PORTE_D_ORLEANS", "x": 755, "y": 633},
                            {"name": "CITE_UNIVERSITAIRE", "x": 806, "y": 632},
                            {"name": "STADE_CHARLETY", "x": 850, "y": 637},
                            {"name": "POTERNE_DES_PEUPLIERS", "x": 904, "y": 637},
                            {"name": "PORTE_D_ITALIE", "x": 986, "y": 641},
                            {"name": "PORTE_DE_CHOISY", "x": 1036, "y": 641},
                            {"name": "PORTE_D_IVRY", "x": 1085, "y": 641},
                            {"name": "BIBLIOTHEQUE_FRANCOIS_MITTERAND", "x": 1140, "y": 601},
                            {"name": "PORTE_DE_CHARENTON", "x": 1230, "y": 574},
                            {"name": "PORTE_DOREE", "x": 1260, "y": 559},
                            {"name": "MON_TEMPOIVRE", "x": 1312, "y": 521},
                            {"name": "PORTE_DE_VINCENNES", "x": 1290, "y": 477}
                        ]
                    },
                    "Merlane": {
                        "color": "#999999",
                        "stations": [
                            {"name": "LA_DEFENSE", "x": 212, "y": 235},
                            {"name": "CHARLES_DE_GAULLE_ETOILE", "x": 401, "y": 333},
                            {"name": "AUBER", "x": 642, "y": 355},
                            {"name": "CHATELET", "x": 831, "y": 415},
                            {"name": "GARE_DE_LYON", "x": 1069, "y": 515},
                            {"name": "NATION", "x": 1231, "y": 468}
                        ]
                    },
                    "Vélane": {
                        "color": "#111111",
                        "stations": [
                            {"name": "GARE_DU_NORD", "x": 956, "y": 259},
                            {"name": "CHATELET_LES_HALLES", "x": 846, "y": 406},
                            {"name": "SAINT_MICHEL_NOTRE_DAME", "x": 787, "y": 465},
                            {"name": "LUXEMBOURG", "x": 807, "y": 509},
                            {"name": "PORT_ROYAL", "x": 807, "y": 546},
                            {"name": "DENFERT_ROCHEREAU", "x": 806, "y": 577},
                            {"name": "CITE_UNIVERSITAIRE", "x": 806, "y": 632}
                        ]
                    },
                    "Etroite": {
                        "color": "#524816",
                        "stations": [
                            {"name": "PORTE_DE_CLICHY", "x": 563, "y": 188},
                            {"name": "PEREIRE", "x": 437, "y": 249},
                            {"name": "PORTE_MAILLOT", "x": 335, "y": 296},
                            {"name": "AVENUE_FOCH", "x": 206, "y": 368},
                            {"name": "AVENUE_HENRI_MARTIN", "x": 204, "y": 417},
                            {"name": "LA_MUETTE", "x": 234, "y": 457},
                            {"name": "AVENUE_DU_PRESIDENT_KENNEDY", "x": 289, "y": 474},
                            {"name": "CHAMP_DE_MARS_TOUR_EIFFEL", "x": 357, "y": 475},
                            {"name": "PONT_DE_L_ALMA", "x": 440, "y": 434},
                            {"name": "INVALIDES", "x": 520, "y": 434},
                            {"name": "MUSEE_D_ORSAY", "x": 585, "y": 434},
                            {"name": "SAINT_MICHEL_NOTRE_DAME", "x": 787, "y": 465},
                            {"name": "GARE_D_AUSTERLITZ", "x": 1006, "y": 537},
                            {"name": "BIBLIOTHEQUE_FRANCOIS_MITTERAND", "x": 1140, "y": 601}
                        ]
                    },
                    "Tourneurs": {
                        "color": "#524816",
                        "stations": [
                            {"name": "GARE_DU_NORD", "x": 956, "y": 259},
                            {"name": "CHATELET_LES_HALLES", "x": 846, "y": 406},
                            {"name": "GARE_DE_LYON", "x": 1069, "y": 515}
                        ]
                    },
                    "Trinité": {
                        "color": "#111111",
                        "stations": [
                            {"name": "SAINT_LAZARE", "x": 617, "y": 312},
                            {"name": "GARE_DU_NORD", "x": 956, "y": 259}
                        ]
                    },
                }
            };


        // var backgroundImage = new Image();
        // backgroundImage.src = "Plan-Metro.1669996027.webp";

        // backgroundImage.onload = function() {
        //     drawMap();
        // };

        function updateAllData() {
            fetch('get_all_data.php')
                .then(response => response.json())
                .then(newData => {
                    stopsData = newData.stops;     // Remplacer les données des arrêts
                    streetsData = newData.streets; // Remplacer les données des rues
                    bicyclesData = newData.bicycles; // Remplacer les données des vélos
                    drawMap();  // Redessiner la carte avec les nouvelles données
                })
                .catch(error => console.error('Erreur lors de la mise à jour des données:', error));
        }

        function getStationByName(stationName) {
            for (const line of Object.values(data.lines)) {
                for (const station of line.stations) {
                    if (station.name === stationName) {
                        return station;
                    }
                }
            }
            return null;
        }

        function getLineByStation(station) {
            for (const [lineName, line] of Object.entries(data.lines)) {
                if (line.stations.some(s => s.name === station.name)) {
                    return lineName;
                }
            }
            return null;
        }

        // Appeler la fonction toutes les 30 secondes
        setInterval(updateAllData, 30000);
        updateAllData();

        function getBicycleInfo(bicycle) {
            return `Bicycle Info: ID ${bicycle.id}, Autonomy: ${bicycle.autonomy}, load: ${bicycle.load}, itineraire: ${bicycle.path_id}`;
        }

        function getBicycleAt(x, y) {
            for (const bike of bicyclesData) {
                const stopNameFormatted = bike.name;

                // Trouver la station associée à l'arrêt du vélo
                let stationFound = null;
                Object.entries(data.lines).forEach(([lineName, line]) => {
                    const station = line.stations.find(s => s.name === stopNameFormatted);
                    if (station) {
                        stationFound = station;
                    }
                });

                // Si la station est trouvée, vérifier si la souris est proche du vélo
                if (stationFound) {
                    const dx = x - stationFound.x;
                    const dy = y - stationFound.y;
                    if (Math.sqrt(dx * dx + dy * dy) < 10) {
                        return bike;  // Retourner les informations du vélo si la souris est proche
                    }
                }
            }
            return null;
        }

        function drawStation(x, y, text, lineName, empty) {
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI, false);

            if (text === currentStation) {
                ctx.fillStyle = 'orange';
            }
            ctx.fillStyle = currentStreet ? (lineName === currentStreet ? 'blue' : 'white') : 'white';
            if (empty == 1) {
                ctx.fillStyle = 'green'; // Déchets ramassés
            } else {
                ctx.fillStyle = 'red'; // Déchets non ramassés
            }
            ctx.fill();

            ctx.lineWidth = 2;
            ctx.strokeStyle = 'black';
            ctx.stroke();
            if (zoomLevel >= 2) {
                ctx.fillStyle = 'black';
                ctx.font = '9px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(text, x, y + 20);
            }
        }

        function drawLine(x1, y1, x2, y2, color, lineName, empty) {
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.strokeStyle = currentStreet ? (lineName === currentStreet ? 'blue' : 'gray') : color;
            if (empty == 1) {
                ctx.strokeStyle = 'green'; // Déchets ramassés
            }
            ctx.lineWidth = 4;
            ctx.stroke();
        }

        function drawBicycle(x, y) {
            ctx.font = '50px Arial'; // Définissez la taille de l'emoji
            ctx.textAlign = 'center'; // Centrez l'emoji sur le point
            ctx.textBaseline = 'middle'; // Centrez également verticalement
            ctx.fillText('🚲', x, y); // Dessinez l'emoji vélo à l'emplacement donné
        }

        function drawMap() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.scale(zoomLevel, zoomLevel);
            ctx.translate(offsetX / zoomLevel, offsetY / zoomLevel);

            // if (showBackground) {
            //     ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            // }

            Object.entries(data.lines).forEach(([lineName, line]) => {
                var lineNameDB = lineName.toUpperCase().replace(/[- ]/g, '_');
                if (lineName !== currentStreet) {
                    for (let i = 0; i < line.stations.length - 1; i++) {
                        var station1 = line.stations[i];
                        var station2 = line.stations[i + 1];
                        var stop = stopsData.find(s => s.name === station1.name);
                        var emptyStopStatus = stop ? stop.empty : 0;
                        var street = streetsData.find(s => s.name === lineNameDB);
                        var emptyStreetStatus = street ? street.empty : 0;
                        drawLine(station1.x, station1.y, station2.x, station2.y, line.color, lineName, emptyStreetStatus);
                        drawStation(station1.x, station1.y, station1.name, lineName, emptyStopStatus);
                    }
                    var lastStation = line.stations[line.stations.length - 1];
                    var stop = stopsData.find(s => s.name === lastStation.name);
                    var emptyStopStatus = stop ? stop.empty : 0;
                    drawStation(lastStation.x, lastStation.y, lastStation.name, lineName, emptyStopStatus);
                }
            });

            bicyclesData.forEach(bike => {
                let stationFound = null;
                Object.entries(data.lines).forEach(([lineName, line]) => {
                    const station = line.stations.find(s => s.name.toUpperCase().replace(/[- ]/g, '_') === bike.name);
                    if (station) {
                        stationFound = station;
                    }
                });

                if (stationFound) {
                    drawBicycle(stationFound.x, stationFound.y);
                } else {
                    console.error('Station non trouvée pour le vélo : ', bike.name);
                }
            });

            ctx.restore();


            if (currentStreet) {
                var currentLine = data.lines[currentStreet];
                for (let i = 0; i < currentLine.stations.length - 1; i++) {
                    var station1 = currentLine.stations[i];
                    var station2 = currentLine.stations[i + 1];
                    var stop = stopsData.find(s => s.name === station1.name);
                    var emptyStopStatus = stop ? stop.empty : 0;
                    if (currentStreet.name) {
                        var currentStreetNameBD = currentStreet.name.toUpperCase().replace(/[- ]/g, '_');
                        var street = streetsData.find(s => s.name === currentStreetNameBD);
                    } else {
                        var street = streetsData.find(s => s.name === currentStreet.name);
                    }
                    var emptyStreetStatus = street ? street.empty : 0;
                    drawLine(station1.x, station1.y, station2.x, station2.y, currentLine.color, currentStreet, emptyStreetStatus);
                    drawStation(station1.x, station1.y, station1.name, currentStreet, emptyStopStatus);
                }
                var lastStation = currentLine.stations[currentLine.stations.length - 1];
                var stop = stopsData.find(s => s.name === lastStation.name);
                var emptyStatus = stop ? stop.empty : 0;
                drawStation(lastStation.x, lastStation.y, lastStation.name, currentStreet, emptyStopStatus);
            }

            ctx.restore();
        }

        function zoomIn() {
            if (zoomLevel < maxZoom) {
                zoomLevel += zoomIncrement;
                drawMap();
                zoomLevelDiv.textContent = 'Zoom Level: ' + zoomLevel.toFixed(1);
            }
        }

        function zoomOut() {
            if (zoomLevel > minZoom) {
                zoomLevel -= zoomIncrement;
                drawMap();
                zoomLevelDiv.textContent = 'Zoom Level: ' + zoomLevel.toFixed(1);
            }
        }

        function toggleBackground() {                   //********fonction de contruction de la map******
            showBackground = !showBackground;
            drawMap();
        }

        function getStationInfo(stationName, lineName) {
            const line = data.lines[lineName];
            const index = line.stations.findIndex(station => station.name === stationName);
            const previousStation = index > 0 ? line.stations[index - 1].name : "None";
            const nextStation = index < line.stations.length - 1 ? line.stations[index + 1].name : "None";
            const stop = stopsData.find(s => s.name === stationName);
            const emptyStatus = stop ? (stop.empty == 1 ? "Déchets ramassés" : "Déchets non ramassés") : "Information indisponible";
            return `Station: ${stationName}\nRue: ${lineName}\nPrécédent: ${previousStation}\nSuivant: ${nextStation}\nÉtat: ${emptyStatus}`;
        }

        function getStationAt(x, y) {
            for (const [lineName, line] of Object.entries(data.lines)) {
                for (const station of line.stations) {
                    const dx = x - station.x;
                    const dy = y - station.y;
                    if (Math.sqrt(dx * dx + dy * dy) < 5) {
                        return { station, lineName };
                    }
                }
            }
            return null;
        }

        canvas.addEventListener('mousemove', function(event) {      //********fonction de contruction de la map******
            var rect = canvas.getBoundingClientRect();
            var x = (event.clientX - rect.left) / zoomLevel;
            var y = (event.clientY - rect.top) / zoomLevel;
            //mousePositionDiv.innerHTML = 'Mouse Position: x= ' + Math.round(x) + ' y= ' + Math.round(y);
            mousePositionDiv.innerHTML = '"x": ' + Math.round(x) + ', "y": ' + Math.round(y);
            var stationInfo = getStationAt(x - offsetX / zoomLevel, y - offsetY / zoomLevel);
            var bicycleInfo = getBicycleAt(x - offsetX / zoomLevel, y - offsetY / zoomLevel);

            let infoText = '';
            if (stationInfo) {
                infoText += getStationInfo(stationInfo.station.name, stationInfo.lineName);  // Affiche les informations de la station
            }
            
            if (bicycleInfo) {
                infoText += '\n\n' + getBicycleInfo(bicycleInfo);  // Ajoute les informations du vélo à la suite
            }

            if (!stationInfo && !bicycleInfo) {
                stationInfoDiv.innerText = 'Station Info:';
            } else {
                stationInfoDiv.innerText = infoText;
            }
        });

        canvas.addEventListener('mousedown', function(event) {
            var rect = canvas.getBoundingClientRect();
            startX = event.clientX - rect.left;
            startY = event.clientY - rect.top;
            isPanning = true;
        });

        canvas.addEventListener('mouseup', function() {
            isPanning = false;
        });

        canvas.addEventListener('mousemove', function(event) {
            if (isPanning) {
                var rect = canvas.getBoundingClientRect();
                var x = event.clientX - rect.left;
                var y = event.clientY - rect.top;
                offsetX += (x - startX) / zoomLevel;
                offsetY += (y - startY) / zoomLevel;
                startX = x;
                startY = y;
                drawMap();
            }
        });

        canvas.addEventListener('dblclick', function() {
            zoomIn();
        });

        function copyToClipboard(text) {                                //********fonction de contruction de la map******
            const textarea = document.createElement("textarea");
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand("copy");
            document.body.removeChild(textarea);
        }


document.addEventListener('keydown', function(event) {                 //********fonction de contruction de la map******
    if (event.key === "c") {
        const textToCopy = mousePositionDiv.innerText;
        copyToClipboard(textToCopy);
    }
});

        drawMap();
    </script>
</body>
</html>
