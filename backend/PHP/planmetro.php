<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plan de Rammassage des Poubelle</title>
    <link rel="stylesheet" href="../CSS/planmetro.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <?php include 'header.php';?>
</head>
<body>
    <?php
        require '../../pdo.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        $stmt = $pdo->prepare("SELECT b.id FROM bicycles b WHERE b.client_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $bicycle_id = $stmt->fetch(PDO::FETCH_ASSOC);
        $bicycle_id = $bicycle_id['id'];

        $stmt = $pdo->prepare("SELECT s.name AS StationName
        FROM bicycles b 
        LEFT JOIN stops s ON s.id = b.stop_id
        LEFT JOIN streets_stops ss ON ss.stop_id = s.id
        LEFT JOIN streets ON streets.id = ss.street_id
        WHERE b.client_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentStation = $current['StationName'];

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

        // Mode "Block Street"
        $stmt = $pdo->prepare("SELECT name FROM blocked_stop WHERE etat = 'blocked'");
        $stmt->execute();
        $blockedStations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT name FROM blocked_stop WHERE etat = 'deleted'");
        $stmt->execute();
        $deletedStations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $blockedStationsJSON = json_encode($blockedStations);
        $deletedStationsJSON = json_encode($deletedStations);

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateWinter'])) {
            $winterStatus = isset($_POST['winter']) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE bicycles SET winter = ?");
            $success = $stmt->execute([$winterStatus]);
        }
        $stmt = $pdo->prepare("SELECT winter FROM bicycles WHERE id = 1");
        $stmt->execute();
        $currentWinterStatus = $stmt->fetchColumn();
    ?>
    <div class="container">
        <div>
            <div class="canvasTop">
                <form method="post" class="winterForm">
                    <label for="winterToggle">Hiver:</label>
                    <input type="checkbox" id="winterToggle" name="winter" value="1" <?= $currentWinterStatus ? 'checked' : '' ?>>
                    <button type="submit" name="updateWinter">Valider</button>
                </form>
                <div class="mapInformations">
                    <div id="mousePosition">Position de la souris: (x, y)</div>
                    <div id="zoomLevel">Niveau de zoom: 1</div>
                </div>
            </div>
        </div>
        <div id="stationInfo">Station Info: </div>
        <div class="canvasContainer">
            <canvas id="metroMap" width="1600" height="800"></canvas>
        </div>
        <div id="controls">
            <button onclick="zoomIn()"><i class="fas fa-search-plus"></i></button>
            <button onclick="zoomOut()"><i class="fas fa-search-minus"></i></button>
            <button onclick="toggleBlockStreetMode()">Bloquer des arret</button>
            <button onclick="toggleDeleteStreetMode()">Supprimer des arret</button>
            <button onclick="validateStreetUpdate()">Valider</button>
            <button onclick="toggleUnblockStreetMode()">Débloquer un arret</button>
        </div>
    </div>
    <script id="stopsData" type="application/json"><?= json_encode($stops); ?></script>
    <script id="streetsData" type="application/json"><?= json_encode($streets); ?></script>
    <script id="bicyclesData" type="application/json"><?= json_encode($bicycles); ?></script>
    <script id="blockedStations" type="application/json"><?= $blockedStationsJSON; ?></script>
    <script id="deletedStations" type="application/json"><?= $deletedStationsJSON; ?></script>
    <script id="bicycle_id" type="application/json"><?= json_encode($bicycle_id); ?></script>
    <script id="currentStation" type="application/json"><?= json_encode($currentStation); ?></script>
    <script src="../JS/planmetro.js"></script>
</body>
</html>
