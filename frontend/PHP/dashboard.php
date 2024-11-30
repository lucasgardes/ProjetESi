<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Utilisateur</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
</head>
<body>
    <?php include 'header.php';?>
    <div class="dashboard">
        <h1>Tableau de Bord de l'Utilisateur</h1>
        <div class="activities">
            <h2>Activités Récentes</h2>
                <h3>Itinéraires en Cours</h3>
            <?php
                include '../../pdo.php';
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                if (!isset($_SESSION['user_id'])) {
                    header("Location: login.php");
                    exit;
                }
                $stmt = $pdo->prepare("SELECT ph.`started_at`, ph.`ended_at`, s1.name AS stop1Name, s2.name AS stop2Name, s3.name AS stop3Name, s4.name AS stop4Name, p.distance
                FROM path_history ph
                LEFT JOIN `paths` p ON p.id = ph.path_id
                LEFt JOIN stops s1 ON s1.id = p.start_stop_id
                LEFt JOIN stops s2 ON s2.id = p.stop2_id
                LEFt JOIN stops s3 ON s3.id = p.stop3_id
                LEFt JOIN stops s4 ON s4.id = p.stop4_id
                WHERE ph.client_id = ?
                ORDER BY started_at DESC LIMIT 10");
                $stmt->execute([$_SESSION['user_id']]);
                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $finishedActivities = [];
                $ongoingActivities = [];
                foreach ($activities as $activity) {
                    if (!is_null($activity['ended_at'])) {
                        $finishedActivities[] = $activity;
                    } else {
                        $ongoingActivities[] = $activity;
                    }
                }
                echo "<ul>";
                foreach ($ongoingActivities as $activity) {
                    echo "<li>" 
                        . htmlspecialchars($activity['stop1Name']) . " - "
                        . htmlspecialchars($activity['stop2Name']) . " - "
                        . htmlspecialchars($activity['stop3Name']) . " - "
                        . htmlspecialchars($activity['stop4Name']);
                        if (!is_null($activity['started_at'])) {
                            echo " commencé le " . htmlspecialchars($activity['started_at']);
                        } 
                    echo " (en cours...)</li>";
                }
                echo "</ul>";
            ?>

            <h3>Itinéraires Finis</h3>

            <?php
                echo "<ul>";
                foreach ($finishedActivities as $activity) {
                    echo "<li>" 
                        . htmlspecialchars($activity['stop1Name']) . " - "
                        . htmlspecialchars($activity['stop2Name']) . " - "
                        . htmlspecialchars($activity['stop3Name']) . " - "
                        . htmlspecialchars($activity['stop4Name']) . " commencé le "
                        . htmlspecialchars($activity['started_at']) . " et fini le "
                        . htmlspecialchars($activity['ended_at']) . "</li>";
                }
                echo "</ul>";
            ?>
        </div>
    </div>
</body>
</html>
