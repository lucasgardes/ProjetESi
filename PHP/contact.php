<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$servername = "mysql-projet-poubelle.alwaysdata.net";
$username = "348216";
$password = "mdp4B2D2Pr0j€t";
$dbname = "projet-poubelle_bdd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_message'])) {
        $name = htmlspecialchars($_POST['Name']);
        $email = htmlspecialchars($_POST['Email']);
        $message = htmlspecialchars($_POST['Comment']);

        $sql = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            echo "Votre message a été envoyé avec succès!";
        } else {
            echo "Erreur: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['delete_message'])) {
        $message_id = $_POST['message_id'];
        
        $sql = "DELETE FROM contact_messages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $message_id);

        if ($stmt->execute()) {
            echo "Message supprimé avec succès!";
        } else {
            echo "Erreur: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['respond_message'])) {
        $message_id = $_POST['message_id'];
        $response = htmlspecialchars($_POST['response']);
        $sql = "UPDATE contact_messages SET response = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $response, $message_id);
        if ($stmt->execute()) {
            echo "Réponse ajoutée avec succès!";
        } else {
            echo "Erreur: " . $stmt->error;
        }

        $stmt->close();
    }
}

$sql = "SELECT id, name, email, message, response, created_at FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);

$user_id = $_SESSION['user_id'];
$sql_admin = "SELECT admin FROM client WHERE id = ?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("i", $user_id);
$stmt_admin->execute();
$stmt_admin->bind_result($is_admin);
$stmt_admin->fetch();
$stmt_admin->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Green City Waste Collection - Contact</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="contact.css">
</head>
<body>

<div class="w3-top">
    <div class="w3-bar w3-green w3-card w3-large">
        <a href="#" class="w3-bar-item w3-button w3-padding-large w3-white">Home</a>
        <a href="#contact" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Contact</a>
        <?php if (!$_SESSION['loggedin']) : ?>
        <div class="w3-right w3-hide-small user-menu">
            <a href="login.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Connexion</a>
            <a href="signup.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Inscription</a>
        </div>
        <?php else : ?>
        <div class="w3-right w3-hide-small user-menu dropdown">
            <button class="dropbtn"><i class="fa fa-user"></i></button>
            <div class="dropdown-content">
                <a href="#profile">Profil</a>
                <a href="#settings">Paramètres</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<header class="w3-container w3-green w3-center" style="padding:128px 16px">
    <h1 class="w3-margin w3-jumbo">Green City Waste Collection</h1>
    <p class="w3-xlarge">Efficient and Eco-Friendly Waste Collection</p>
    <button class="w3-button w3-black w3-padding-large w3-large w3-margin-top">Learn More</button>
</header>

<div class="w3-row-padding w3-container">
    <div class="w3-content">
        <h2>Leave a Comment</h2>
        <form action="" method="post">
            <p><input class="w3-input w3-border w3-quarter" type="text" placeholder="Name" required name="Name"></p><br><br>
            <p><input class="w3-input w3-border w3-quarter" type="email" placeholder="Email" required name="Email"></p><br><br>
            <p><textarea class="w3-input w3-border" placeholder="Comment" required name="Comment"></textarea></p>
            <p><button class="w3-button w3-green w3-padding-large" type="submit" name="submit_message">Submit</button></p>
        </form>

        <div class="w3-margin-top">
            <h2>Previous Comments</h2>
            <table class="w3-table w3-striped w3-bordered">
                <thead>
                    <tr>
                        <th class="message-name">Name</th>
                        <th class="message-mail">Email</th>
                        <th class="message-column">Message</th>
                        <th class="response-column">Response</th>
                        <?php if ($is_admin) : ?>
                        <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars($row['response']); ?></td>
                            <?php if ($is_admin) : ?>
                            <td>
                                <form action="" method="post" style="display:inline-block;">
                                    <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <input type="text" name="response" placeholder="Enter response">
                                    <button class="w3-button w3-green" type="submit" name="respond_message">Respond</button>
                                </form>
                                <form action="" method="post" style="display:inline-block;">
                                    <input type="hidden" name="message_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button class="w3-button w3-red" type="submit" name="delete_message">Delete</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="<?php echo $is_admin ? 5 : 4; ?>">No comments yet. Be the first to comment!</td>
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
