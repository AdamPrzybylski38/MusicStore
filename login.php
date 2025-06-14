<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        //pobieranie danych z formularza
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);

        //wywołanie funkcji login_user z bazy danych
        $stmt = $connect->prepare("SELECT * FROM login_user(:email, :password)");
        $stmt->execute(['email' => $email, 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            //zapisanie danych użytkownika do sesji
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['id_activity'] = $user['id_activity'];

            // Sprawdzenie, czy użytkownik jest administratorem
            // Sprawdzenie, czy użytkownik jest administratorem
            $stmt = $connect->prepare("SELECT 1 FROM admins WHERE id_user = :id_user");
            $stmt->execute(['id_user' => $user['id_user']]);
            $isAdmin = $stmt->rowCount() > 0;

            // Sprawdzenie, czy użytkownik jest moderatorem
            $stmt = $connect->prepare("SELECT 1 FROM mods WHERE id_user = :id_user");
            $stmt->execute(['id_user' => $user['id_user']]);
            $isMod = $stmt->rowCount() > 0;

            // Ustawienie sesji z uprawnieniami
            $_SESSION['is_admin'] = $isAdmin;
            $_SESSION['is_mod'] = !$isAdmin && $isMod;  // moderator tylko jeśli NIE jest adminem


            //przekierowanie do strony chatu
            header('Location: store.php');
            exit();
        }

    } catch (PDOException $e) {
        //obsługa błędów logowania
        $error = $e->getMessage();
        if (str_contains($error, 'EMAIL_NOT_FOUND')) {
            $_SESSION['login_error'] = 'Użytkownik o podanym adresie email nie istnieje.';
        } elseif (str_contains($error, 'INVALID_PASSWORD')) {
            $_SESSION['login_error'] = 'Nieprawidłowe hasło.';
        } else {
            $_SESSION['login_error'] = 'Błąd logowania.';
        }

        header('Location: index.php');
        exit();
    }
}
?>