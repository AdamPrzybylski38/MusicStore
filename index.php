<?php
session_start();

//sprawdzanie czy użytkownik jest już zalogowany
if (isset($_SESSION['id_user'])) {
    header('Location: store.php');
    exit();
}

require_once "connect.php"; // połączenie z bazą danych

//zmienne do przechowywania błędów
$login_error = '';
$register_error = '';
$show_register_form = false;

//sprawdzanie czy formularz logowania został wysłany poprawnie
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if (isset($_SESSION['register_error'])) {
    $register_error = $_SESSION['register_error'];
    $show_register_form = true;
    unset($_SESSION['register_error']);
}
?>

<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Music Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="ms_favicon.png" type="image/png">
</head>

<body class="bg-light">
    <header>
        <div class="container mt-3">
            <div class="text-center mb-3">
                <div class="d-inline-flex align-items-center">
                    <img src="ms_logo.svg" alt="Music Store Logo"
                        style="width: 3rem; height: 3rem; margin-right: 0.5rem;">
                    <h1 class="mb-0 fs-2 text-primary">
                        Sklep Muzyczny <span class="header-badge">v0.1</span>
                    </h1>
                </div>
            </div>
    </header>

    <main class="container">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div id="loginForm" class="auth-box" style="display: <?= $show_register_form ? 'none' : 'block' ?>;">
                <h3 class="mb-4 text-center">Logowanie</h3>

                <!-- wyświetlanie błędów logowania -->
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($login_error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="twój@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Hasło</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button id="registerBtn" type="button" class="btn btn-secondary">Zarejestruj się</button>
                        <button type="submit" class="btn btn-primary">Zaloguj</button>
                    </div>
                </form>
            </div>

            <div id="registrationForm" class="auth-box" style="display: <?= $show_register_form ? 'block' : 'none' ?>;">
                <h3 class="mb-4 text-center">Rejestracja</h3>

                <!-- wyświetlanie błędów rejestracji -->
                <?php if (!empty($register_error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($register_error) ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label for="email-reg" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email-reg" name="email" placeholder="twój@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Nazwa użytkownika</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Imię" required>
                    </div>
                    <div class="mb-3">
                        <label for="password-reg" class="form-label">Hasło</label>
                        <input type="password" class="form-control" id="password-reg" name="password" placeholder="********" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Powtórz hasło</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="********" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button id="loginBtn" type="button" class="btn btn-secondary">Wróć do logowania</button>
                        <button type="submit" class="btn btn-success">Zarejestruj się</button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script>
        //przełączanie między formularzami logowania i rejestracji
        $(document).ready(function () {
            $('#registerBtn').click(function () {
                $('#loginForm').hide();
                $('#registrationForm').show();
            });

            $('#loginBtn').click(function () {
                $('#registrationForm').hide();
                $('#loginForm').show();
            });
        });
    </script>

</body>

</html>