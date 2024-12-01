<?php
require "../../pdo.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['frontend_user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['frontend_user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_message'])) {
        $name = htmlspecialchars($_POST['Name']);
        $email = htmlspecialchars($_POST['Email']);
        $message = htmlspecialchars($_POST['Comment']);

        $sql = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$name, $email, $message])) {
            echo "Votre message a été envoyé avec succès!";
        } else {
            echo "Erreur: " . implode(" | ", $stmt->errorInfo());
        }
    } elseif (isset($_POST['delete_message'])) {
        $message_id = $_POST['message_id'];

        $sql = "DELETE FROM contact_messages WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$message_id])) {
            echo "Message supprimé avec succès!";
        } else {
            echo "Erreur: " . implode(" | ", $stmt->errorInfo());
        }
    } elseif (isset($_POST['respond_message'])) {
        $message_id = $_POST['message_id'];
        $response = htmlspecialchars($_POST['response']);

        $sql = "UPDATE contact_messages SET response = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$response, $message_id])) {
            echo "Réponse ajoutée avec succès!";
        } else {
            echo "Erreur: " . implode(" | ", $stmt->errorInfo());
        }
    }
}

// Récupération des messages
$sql = "SELECT id, name, email, message, response, created_at FROM contact_messages ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Green City Waste Collection - Contact</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
<?php include 'header.php';?>

<div class="w3-row-padding w3-container">
    <div class="w3-content">
        <div class="container mt-5">
            <h2 class="mb-4">Laisser un commentaire</h2>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="Name" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="Name" name="Name" placeholder="Entrez votre nom" required>
                </div>
                <div class="mb-3">
                    <label for="Email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="Email" name="Email" placeholder="Entrez votre email" required>
                </div>
                <div class="mb-3">
                    <label for="Comment" class="form-label">Commentaire</label>
                    <textarea class="form-control" id="Comment" name="Comment" rows="4" placeholder="Entrez votre commentaire" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="submit_message">Envoyer</button>
            </form>
        </div>

        <div class="w3-margin-top">
            <h2>commentaires précédents</h2>
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr>
                        <th class="message-name">Nom</th>
                        <th class="message-mail">Email</th>
                        <th class="message-column">Message</th>
                        <th class="response-column">Réponse</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($messages)) : ?>
                        <?php foreach ($messages as $row) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars($row['response']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="4">No comments yet. Be the first to comment!</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<footer class="w3-container w3-padding-64 w3-center w3-opacity">
    <div class="w3-xlarge w3-padding-32">
        <i class="fa fa-facebook-official w3-hover-opacity"></i>
        <i class="fa fa-instagram w3-hover-opacity"></i>
        <i class="fa fa-twitter w3-hover-opacity"></i>
        <i class="fa fa-linkedin w3-hover-opacity"></i>
    </div>
</footer>
</body>
</html>
