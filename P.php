<?php
session_start();
$siteName = "Blue Red Automobile";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Configuration pour l'envoi d'email
    $to = "contact@bluered-auto.fr";
    $subject = "Nouveau message de " . $nom;
    $headers = "From: " . $email . "\r\n";

    // Envoi de l'email
    mail($to, $subject, $message, $headers);

    // Redirection avec message de succès
    header("Location: index.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blue Red Automobile - Garage à Viriat</title>
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Blue Red Automobile</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#vehicules">Véhicules</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="container">
            <h1>Bienvenue chez Blue Red Automobile</h1>
            <p>Votre spécialiste automobile à Viriat</p>
        </div>
    </div>

    <div class="container my-5" id="services">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Réparation</h5>
                        <p class="card-text">Service de réparation toutes marques</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Entretien</h5>
                        <p class="card-text">Entretien régulier de votre véhicule</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Vente</h5>
                        <p class="card-text">Véhicules d'occasion sélectionnés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5" id="vehicules">
        <h2>Nos Véhicules</h2>
        <!-- Ajoutez ici la liste des véhicules -->
    </div>

    <div class="container my-5" id="contact">
        <h2>Contactez-nous</h2>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Votre message a été envoyé avec succès !</div>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Blue Red Automobile</h5>
                    <p>123 Rue Principale<br>01440 Viriat</p>
                </div>
                <div class="col-md-4">
                    <h5>Horaires</h5>
                    <p>Lundi - Vendredi : 8h30 - 18h30<br>
                    Samedi : 9h00 - 17h00</p>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <p>Tél : 04.XX.XX.XX.XX<br>
                    Email : contact@bluered-auto.fr</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
