<?php
require '../../pdo.php';
require '../../vendor/autoload.php'; // Inclure PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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

        // Envoi de l'email de confirmation avec PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Paramètres SMTP
            $mail->isSMTP();
            $mail->SMTPDebug = 2;
            $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'lucas.gardes@limayrac.fr'; // Votre adresse email SMTP
            $mail->Password = 'eojg qizt vtkd uoqh'; // Votre mot de passe ou clé d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 465;

            // Paramètres de l'e-mail
            $mail->setFrom('lucas.gardes@limayrac.fr', 'Green City');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Confirmation de votre compte";
            $mail->Body = "Veuillez cliquer sur ce lien pour confirmer votre compte : 
            <a href='http://localhost/projetBSI/projetESi/frontend/PHP/verify.php?email=$email'>Activer mon compte</a>";

            $mail->send();
            $_SESSION['message'] = "Inscription réussie ! Veuillez vérifier votre e-mail pour activer votre compte.";
            // header("Location: login.php");
            // exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
        }
        
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
