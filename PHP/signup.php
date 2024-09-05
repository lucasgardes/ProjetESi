<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "348216";
    $password_bdd = "mdp4B2D2Pr0j€t";
    $dbname = "mysql-projet-poubelle";
    $conn = new mysqli($servername, $username, $password_bdd, $dbname);

    if ($conn->connect_error) {
        die("La connexion a échoué : " . $conn->connect_error);
    }


    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insertion des données
    $sql = "SET NAMES UTF8";
    $result = $conn->query($sql);
    $sql = "INSERT INTO users (email, password, verified) VALUES (" . $email . ", " . $password . ", 0)";
    $result = $conn->query($sql);

    // Envoi de l'email de confirmation
    $to = $email;
    $subject = "Confirmation de votre compte";
    $message = "Veuillez cliquer sur ce lien pour confirmer votre compte : http://localhost/projetBSI/projetESi/PHP/verify.php?email=$email";
    $headers = "From: no-reply@votredomaine.com\r\n";
    $headers .= "Reply-To: no-reply@votredomaine.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    mail($to, $subject, $message, $headers);

    echo "Inscription réussie ! Veuillez vérifier votre e-mail pour activer votre compte.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Green City Waste Collection</title>
    <link rel="stylesheet" href="../CSS/signup.css">
</head>
<body>

<div class="signup-container">
    <form action="signup.php" method="post" class="signup-form">
        <h2>Signup</h2>
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="input-group">
            <label for="confirm-password">Confirm password</label>
            <input type="password" id="confirm-password" name="confirm-password" required>
        </div>
        <button type="submit">Signup</button>
    </form>
</div>

</body>
</html>
