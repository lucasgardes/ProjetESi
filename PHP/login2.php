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