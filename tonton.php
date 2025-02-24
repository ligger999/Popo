<?php
session_start();
require_once 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

// Configuration de la base de données
$dbConfig = [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/db/database.sqlite',
];

// Connexion à la base de données
try {
    $pdo = new PDO($dbConfig['driver'] . ':' . $dbConfig['database']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Fonction pour afficher les notifications
function showNotification($message, $type = 'success') {
    echo "<div class='alert alert-$type'>$message</div>";
}

// Fonction pour rediriger
function redirect($path) {
    header("Location: $path");
    exit();
}

// Modèle User
class User {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        return $stmt->execute($data);
    }
}

// Modèle Vehicle
class Vehicle {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM vehicles");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO vehicles (name, description, price, image) VALUES (:name, :description, :price, :image)");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE vehicles SET name = :name, description = :description, price = :price, image = :image WHERE id = :id");
        return $stmt->execute(array_merge($data, ['id' => $id]));
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM vehicles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

// Modèle Contact
class Contact {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM contacts");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
        return $stmt->execute($data);
    }
}

// Contrôleur Auth
class AuthController {
    protected $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function login(Request $request) {
        if ($request->isMethod('POST')) {
            $email = $request->get('email');
            $password = $request->get('password');

            $user = $this->user->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                showNotification('Connexion réussie !');
                redirect('/');
            } else {
                showNotification('Email ou mot de passe incorrect.', 'danger');
            }
        }

        return $this->render('auth/login');
    }

    public function register(Request $request) {
        if ($request->isMethod('POST')) {
            $name = $request->get('name');
            $email = $request->get('email');
            $password = password_hash($request->get('password'), PASSWORD_DEFAULT);

            $this->user->create(compact('name', 'email', 'password'));
            showNotification('Inscription réussie !');
            redirect('/login');
        }

        return $this->render('auth/register');
    }

    public function logout() {
        session_destroy();
        redirect('/');
    }

    protected function render($view, $params = []) {
        ob_start();
        extract($params);
        include __DIR__ . "/views/$view.php";
        return ob_get_clean();
    }
}

// Contrôleur Vehicle
class VehicleController {
    protected $vehicle;

    public function __construct(Vehicle $vehicle) {
        $this->vehicle = $vehicle;
    }

    public function index() {
        $vehicles = $this->vehicle->all();
        return $this->render('vehicles/index', compact('vehicles'));
    }

    public function show($id) {
        $vehicle = $this->vehicle->find($id);
        return $this->render('vehicles/show', compact('vehicle'));
    }

    public function create(Request $request) {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->vehicle->create($data);
            showNotification('Véhicule créé avec succès !');
            redirect('/vehicles');
        }

        return $this->render('vehicles/create');
    }

    public function edit(Request $request, $id) {
        $vehicle = $this->vehicle->find($id);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->vehicle->update($id, $data);
            showNotification('Véhicule mis à jour avec succès !');
            redirect('/vehicles');
        }

        return $this->render('vehicles/edit', compact('vehicle'));
    }

    public function delete($id) {
        $this->vehicle->delete($id);
        showNotification('Véhicule supprimé avec succès !');
        redirect('/vehicles');
    }

    protected function render($view, $params = []) {
        ob_start();
        extract($params);
        include __DIR__ . "/views/$view.php";
        return ob_get_clean();
    }
}

// Contrôleur Contact
class ContactController {
    protected $contact;

    public function __construct(Contact $contact) {
        $this->contact = $contact;
    }

    public function index() {
        $contacts = $this->contact->all();
        return $this->render('contacts/index', compact('contacts'));
    }

    public function create(Request $request) {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->contact->create($data);
            showNotification('Message envoyé avec succès !');
            redirect('/contacts');
        }

        return $this->render('contacts/create');
    }

    protected function render($view, $params = []) {
        ob_start();
        extract($params);
        include __DIR__ . "/views/$view.php";
        return ob_get_clean();
    }
}

// Routes
$routes = new RouteCollection();
$routes->add('home', new Route('/', ['App\Controllers\VehicleController', 'index']));
$routes->add('login', new Route('/login', ['App\Controllers\AuthController', 'login']));
$routes->add('register', new Route('/register', ['App\Controllers\AuthController', 'register']));
$routes->add('logout', new Route('/logout', ['App\Controllers\AuthController', 'logout']));
$routes->add('vehicles', new Route('/vehicles', ['App\Controllers\VehicleController', 'index']));
$routes->add('vehicle_show', new Route('/vehicles/{id}', ['App\Controllers\VehicleController', 'show']));
$routes->add('vehicle_create', new Route('/vehicles/create', ['App\Controllers\VehicleController', 'create']));
$routes->add('vehicle_edit', new Route('/vehicles/{id}/edit', ['App\Controllers\VehicleController', 'edit']));
$routes->add('vehicle_delete', new Route('/vehicles/{id}/delete', ['App\Controllers\VehicleController', 'delete']));
$routes->add('contacts', new Route('/contacts', ['App\Controllers\ContactController', 'index']));
$routes->add('contact_create', new Route('/contacts/create', ['App\Controllers\ContactController', 'create']));

// Request Context
$context = new RequestContext();
$context->fromRequest(Request::createFromGlobals());

// Url Matcher
$matcher = new UrlMatcher($routes, $context);

// Controller Resolver
$controllerResolver = new ControllerResolver();

// Argument Resolver
$argumentResolver = new ArgumentResolver();

// Http Kernel
$kernel = new HttpKernel($matcher, $controllerResolver, Request::createFromGlobals(), $argumentResolver);

// Handle Request
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

// Terminate
$kernel->terminate($request, $response);

// Vues
function renderView($view, $params = []) {
    ob_start();
    extract($params);
    include __DIR__ . "/views/$view.php";
    return ob_get_clean();
}

// Layout
function layout($content) {
    return renderView('layout', compact('content'));
}

// Vues

// Layout
function layout($content) {
    return renderView('layout', compact('content'));
}

// Auth Login
function authLoginView() {
    return layout(renderView('auth/login'));
}

// Auth Register
function authRegisterView() {
    return layout(renderView('auth/register'));
}

// Vehicles Index
function vehiclesIndexView($vehicles) {
    return layout(renderView('vehicles/index', compact('vehicles')));
}

// Vehicles Create
function vehiclesCreateView() {
    return layout(renderView('vehicles/create'));
}

// Vehicles Edit
function vehiclesEditView($vehicle) {
    return layout(renderView('vehicles/edit', compact('vehicle')));
}

// Contacts Create
function contactsCreateView() {
    return layout(renderView('contacts/create'));
}

// Vues

// Layout
function layout($content) {
    return renderView('layout', compact('content'));
}

// Auth Login
function authLoginView() {
    return layout(renderView('auth/login'));
}

// Auth Register
function authRegisterView() {
    return layout(renderView('auth/register'));
}

// Vehicles Index
function vehiclesIndexView($vehicles) {
    return layout(renderView('vehicles/index', compact('vehicles')));
}

// Vehicles Create
function vehiclesCreateView() {
    return layout(renderView('vehicles/create'));
}

// Vehicles Edit
function vehiclesEditView($vehicle) {
    return layout(renderView('vehicles/edit', compact('vehicle')));
}

// Contacts Create
function contactsCreateView() {
    return layout(renderView('contacts/create'));
}

// Vues HTML

// Layout
echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Title</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Site Name</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="/vehicles">Vehicles</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contacts">Contact</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <form action="/logout" method="POST" style="display:inline;">
                                <button type="submit" class="nav-link btn btn-link">Logout</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        {$content}
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Site Name</h5>
                    <p>Address</p>
                </div>
                <div class="col-md-4">
                    <h5>Hours</h5>
                    <p>Hours Description</p>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <p>Contact Info</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/script.js"></script>
</body>
</html>
HTML;

// Auth Login
echo <<<HTML
<h2>Login</h2>
<form action="/login" method="POST">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>
HTML;

// Auth Register
echo <<<HTML
<h2>Register</h2>
<form action="/register" method="POST">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
HTML;

// Vehicles Index
echo <<<HTML
<h2>Our Vehicles</h2>
<a href="/vehicles/create" class="btn btn-primary mb-3">Add Vehicle</a>
<div class="row">
    {foreach $vehicles as $vehicle}
        <div class="col-md-4">
            <div class="card">
                <img src="{$vehicle['image']}" class="card-img-top" alt="{$vehicle['name']}">
                <div class="card-body">
                    <h5 class="card-title">{$vehicle['name']}</h5>
                    <p class="card-text">{$vehicle['description']}</p>
                    <p class="card-text">Price: {$vehicle['price']} €</p>
                    <a href="/vehicles/{$vehicle['id']}" class="btn btn-primary">View</a>
                    <a href="/vehicles/{$vehicle['id']}/edit" class="btn btn-secondary">Edit</a>
                    <a href="/vehicles/{$vehicle['id']}/delete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    {/foreach}
</div>
HTML;

// Vehicles Create
echo <<<HTML
<h2>Add Vehicle</h2>
<form action="/vehicles/create" method="POST">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price</label>
        <input type="number" class="form-control" id="price" name="price" required>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="text" class="form-control" id="image" name="image" required>
    </div>
    <button type="submit" class="btn btn-primary">Add Vehicle</button>
</form>
HTML;

// Vehicles Edit
echo <<<HTML
<h2>Edit Vehicle</h2>
<form action="/vehicles/{$vehicle['id']}/edit" method="POST">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{$vehicle['name']}" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="5" required>{$vehicle['description']}</textarea>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price</label>
        <input type="number" class="form-control" id="price" name="price" value="{$vehicle['price']}" required>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="text" class="form-control" id="image" name="image" value="{$vehicle['image']}" required>
    </div>
    <button type="submit" class="btn btn-primary">Edit Vehicle</button>
</form>
HTML;

// Contacts Create
echo <<<HTML
<h2>Contact Us</h2>
<form action="/contacts/create" method="POST">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>
HTML;
