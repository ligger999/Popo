<?php
session_start();
require_once 'db/database.php';
require_once 'lang/fr.php';

// Connexion à la base de données SQLite
$db = new SQLite3('db/database.sqlite');

// Fonction pour afficher les notifications
function showNotification($message, $type = 'success') {
    echo "<div class='alert alert-$type'>$message</div>";
}

// Fonction pour vérifier l'authentification de l'utilisateur
function checkAuth() {
    global $db;
    if (isset($_SESSION['user_id'])) {
        return $db->querySingle("SELECT * FROM users WHERE id = {$_SESSION['user_id']}", true);
    }
    return null;
}

// Fonction pour traiter le formulaire de contact
function handleContactForm() {
    global $db, $lang;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $email = htmlspecialchars($_POST['email']);
        $message = htmlspecialchars($_POST['message']);

        $stmt = $db->prepare("INSERT INTO contacts (nom, email, message) VALUES (:nom, :email, :message)");
        $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        $stmt->execute();

        showNotification($lang['contact_success']);
    }
}

// Fonction pour traiter le formulaire de connexion
function handleLoginForm() {
    global $db, $lang;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($user = $result->fetchArray(SQLITE3_ASSOC)) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            showNotification($lang['login_error'], 'danger');
        }
    }
}

// Fonction pour traiter le formulaire d'inscription
function handleRegisterForm() {
    global $db, $lang;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);

        $stmt = $db->prepare("INSERT INTO users (nom, email, password) VALUES (:nom, :email, :password)");
        $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->execute();

        header("Location: index.php?login=1");
        exit();
    }
}

// Fonction pour gérer la déconnexion
function handleLogout() {
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Fonction pour récupérer les véhicules
function getVehicles() {
    global $db;
    return $db->query("SELECT * FROM vehicles");
}

// Vérification de l'authentification
$user = checkAuth();

// Traitement des formulaires
handleContactForm();
handleLoginForm();
handleRegisterForm();
handleLogout();

// Récupération des véhicules
$vehicles = getVehicles();
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
    <style>
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/garage-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="index.php#vehicles"><?php echo $lang['vehicles']; ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact"><?php echo $lang['contact']; ?></a></li>
                    <?php if ($user): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?logout=1"><?php echo $lang['logout']; ?></a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="index.php#login"><?php echo $lang['login']; ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#register"><?php echo $lang['register']; ?></a></li>
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

    <div class="container my-5" id="vehicles">
        <h2><?php echo $lang['our_vehicles']; ?></h2>
        <div class="row">
            <?php while ($vehicle = $vehicles->fetchArray(SQLITE3_ASSOC)): ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo $vehicle['image']; ?>" class="card-img-top" alt="<?php echo $vehicle['nom']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $vehicle['nom']; ?></h5>
                            <p class="card-text"><?php echo $vehicle['description']; ?></p>
                            <p class="card-text"><?php echo $lang['price']; ?>: <?php echo $vehicle['prix']; ?> €</p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
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

    <div class="container my-5" id="login">
        <h2><?php echo $lang['login']; ?></h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <input type="hidden" name="login" value="1">
            <div class="mb-3">
                <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><?php echo $lang['password']; ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $lang['login']; ?></button>
        </form>
    </div>

    <div class="container my-5" id="register">
        <h2><?php echo $lang['register']; ?></h2>
        <form action="index.php" method="POST">
            <input type="hidden" name="register" value="1">
            <div class="mb-3">
                <label for="nom" class="form-label"><?php echo $lang['name']; ?></label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><?php echo $lang['password']; ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $lang['register']; ?></button>
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
</body>
</html>
