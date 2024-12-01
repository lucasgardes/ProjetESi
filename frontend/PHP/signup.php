<?php
require '../../pdo.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT c.email FROM client c");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $emailList = array_column($emails, 'email');
    if (in_array($email, $emailList)) {
        $_SESSION['error'] = "Un compte existe déjà pour cette adresse mail";
    } else {
        // Insertion des données
        $insert = $pdo->prepare("INSERT INTO client (firstname, lastname, email, `password`, `verified`) VALUES (?, ?, ?, ?, 0)");
        $insert->execute([$_POST['firstname'], $_POST['lastname'], $email, $password]);

        // Envoi de l'email de confirmation
        $to = $email;
        $subject = "Confirmation de votre compte";
        $message = "Veuillez cliquer sur ce lien pour confirmer votre compte : http://localhost/projetBSI/projetESi/frontend/PHP/verify.php?email=$email";
        $headers = "From: no-reply@votredomaine.com\r\n";
        $headers .= "Reply-To: no-reply@votredomaine.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
        $_SESSION['message'] = "Inscription réussie ! Veuillez vérifier votre e-mail pour activer votre compte.";
        // header("Location: login.php");
        // exit;
    }
}
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Green City Waste Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../CSS/signup.css">
</head>
<body>

<div class="signup-container">
    <form action="signup.php" method="post" class="signup-form">
        <h2>Signup</h2>
        <?php if ($errorMessage) echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>'; ?>
        <div class="input-group">
            <label for="firstname">Prénom</label>
            <input type="text" id="firstname" name="firstname" required>
        </div>
        <div class="input-group">
            <label for="lastname">Nom</label>
            <input type="text" id="lastname" name="lastname" required>
        </div>
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="input-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" required>
            <div class="invalid-feedback">
                Veuillez entrer un mot de passe.
            </div>
        </div>
        <div class="input-group">
            <label for="confirm-password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm-password" required 
                oninput="this.setCustomValidity(this.value !== document.getElementById('password').value ? 'Les mots de passe ne correspondent pas.' : '')">
            <div class="invalid-feedback">
                Les mots de passe ne correspondent pas.
            </div>
        </div>
        <button type="submit">Signup</button>
    </form>
    <div class="login-link">
        <p>Vous avez déjà un compte ? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</div>

</body>
</html>
