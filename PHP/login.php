<?php
$post = $_POST;
var_dump($post);
die();
if (isset($post) && !empty($post)) {
    $firstname = $post['firstname'];
    $lastname = $post['lastname'];
    $email = $post['email'];
    $password = $post['password'];

    $servername = "localhost";
    $username = "348216";
    $password = "mdp4B2D2Pr0j€t";
    $dbname = "mysql-projet-poubelle";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("La connexion a échoué : " . $conn->connect_error);
    }

    $sql = "SELECT id
            FROM client
            WHERE email = :email
            AND `password` = :`password`";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);

    $result = $conn->query($sql);

    if (!empty($result)) {

    } else {
        
    }

    $conn->close();
}



?>