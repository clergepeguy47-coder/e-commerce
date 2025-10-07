<?php
session_start();
define('SITE_ACCESS', true);

require_once __DIR__ . '/../config.php';
//require_once __DIR__ . '/../includes/database.php';
//require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../functions/user-functions.php';
// Initialisation des variables
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validation basique
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "<p class='error'>Veuillez remplir tous les champs.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<p class='error'>Adresse e-mail invalide.</p>";
    } elseif ($password !== $confirm_password) {
        $message = "<p class='error'>Les mots de passe ne correspondent pas.</p>";
    } else {
        // Vérifier si l'email existe déjà
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "<p class='error'>Cet e-mail est déjà enregistré.</p>";
        } else {
            // Hacher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertion dans la base de données
            $stmt = $conn->prepare("INSERT INTO users (nom, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nom, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = "<p class='success'>Inscription réussie ! Vous pouvez maintenant <a href='login.php'>vous connecter</a>.</p>";
            } else {
                $message = "<p class='error'>Une erreur est survenue. Veuillez réessayer.</p>";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<div class="container">
    <h1>Créer un compte</h1>
    <?= $message ?>

    <form method="POST" action="">
        <label for="nom">Nom complet :</label>
        <input type="text" name="nom" id="nom" required>

        <label for="email">Adresse e-mail :</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirmer le mot de passe :</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">S'inscrire</button>
    </form>
</div>

<?php require_once("../includes/footer.php"); ?>

<style>
.container {
    width: 400px;
    margin: 50px auto;
    padding: 20px;
    background: #1bb4cfff;
    border-radius: 20px;
    border: 1px solid #2b97898e;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

form label {
    display: block;
    margin-top: 10px;
}

form input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

button {
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    background-color: #020911ff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #0056b3;
}

.error {
    color: red;
    text-align: center;
}

.success {
    color: green;
    text-align: center;
}
</style>