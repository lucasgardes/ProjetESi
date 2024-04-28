<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = 'localhost';
    $db = 'nom_de_votre_base_de_donnees';
    $user = 'utilisateur_de_la_base_de_donnees';
    $pass = 'mot_de_passe_de_la_base_de_donnees';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
        exit;
    }


    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insertion des données
    $sql = "INSERT INTO users (email, password, verified) VALUES (:email, :password, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    // Envoi de l'email de confirmation
    $to = $email;
    $subject = "Confirmation de votre compte";
    $message = "Veuillez cliquer sur ce lien pour confirmer votre compte : http://votredomaine.com/verify.php?email=$email";
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
