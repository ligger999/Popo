<?php
session_start();
require_once 'db/database.php';
require_once 'lang/fr.php';

// Fonction pour afficher les notifications
function showNotification($message, $type = 'success') {
    echo "<div class='alert alert-$type'>$message</div>";
}

// Connexion à la base de données
$db = new SQLite3('db/database.sqlite');

// Vérification de l'authentification
if (isset($_SESSION['user_id'])) {
    $user = $db->querySingle("SELECT * FROM users WHERE id = {$_SESSION['user_id']}", true);
}

// Traitement du formulaire de contact
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $stmt = $db->prepare("INSERT INTO contacts (nom, email, message) VALUES (:nom, :email, :message)");
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':message', $message, SQLITE3_TEXT);
    $stmt->execute();

    showNotification('Votre message a été envoyé avec succès !');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo $lang['site_name']; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php"><?php echo $lang['home']; ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services"><?php echo $lang['services']; ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="vehicles.php"><?php echo $lang['vehicles']; ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact"><?php echo $lang['contact']; ?></a></li>
                    <?php if (isset($user)): ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php"><?php echo $lang['logout']; ?></a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><?php echo $lang['login']; ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php"><?php echo $lang['register']; ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="container">
            <h1><?php echo $lang['welcome']; ?></h1>
            <p><?php echo $lang['tagline']; ?></p>
        </div>
    </div>

    <div class="container my-5" id="services">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang['repair']; ?></h5>
                        <p class="card-text"><?php echo $lang['repair_desc']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang['maintenance']; ?></h5>
                        <p class="card-text"><?php echo $lang['maintenance_desc']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang['sales']; ?></h5>
                        <p class="card-text"><?php echo $lang['sales_desc']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5" id="contact">
        <h2><?php echo $lang['contact_us']; ?></h2>
        <form action="index.php" method="POST">
            <input type="hidden" name="contact" value="1">
            <div class="mb-3">
                <label for="nom" class="form-label"><?php echo $lang['name']; ?></label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label"><?php echo $lang['message']; ?></label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $lang['send']; ?></button>
        </form>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo $lang['site_name']; ?></h5>
                    <p><?php echo $lang['address']; ?></p>
                </div>
                <div class="col-md-4">
                    <h5><?php echo $lang['hours']; ?></h5>
                    <p><?php echo $lang['hours_desc']; ?></p>
                </div>
                <div class="col-md-4">
                    <h5><?php echo $lang['contact']; ?></h5>
                    <p><?php echo $lang['contact_info']; ?></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
