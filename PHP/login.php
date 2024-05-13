<?php
    $post = $_POST;
    $logged = false;
    if (isset($post) && !empty($post)) {
    $firstname = $post['firstname'];
    $lastname = $post['lastname'];
    $email = $post['email'];
    $password = $post['password'];

    $servername = "localhost";
    $username = "348216";
    $password_bdd = "mdp4B2D2Pr0j€t";
    $dbname = "mysql-projet-poubelle";

    $conn = new mysqli($servername, $username, $password_bdd, $dbname);

    if (!$conn->set_charset("utf8")) {
        echo "Erreur lors du chargement du jeu de caractères utf8 : " . $conn->error;
    } else {
        echo "Jeu de caractères actuel : " . $conn->character_set_name();
    }

    if ($conn->connect_error) {
        die("La connexion a échoué : " . $conn->connect_error);
    }

    $sql = "SELECT id
            FROM client
            WHERE email = " . $email . "
            AND `password` = " . $password;
    $result = $conn->query($sql);

    if (!empty($result)) {
        $logged = true;
    } else {
        $logged = false;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion - Green City Waste Collection</title>
        <link rel="stylesheet" href="../CSS/login.css">
    </head>
    <body>

    <div class="login-container">
        <form action="../PHP/index.php" method="post" class="login-form">
            <h2>Connexion</h2>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
    </div>

    </body>
</html>