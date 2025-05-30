<?php
session_start();
require_once 'connect.php';

//wywołanie funkcji logout_user jeśli użytkownik jest zalogowany
if (isset($_SESSION['id_activity'])) {
    $stmt = $connect->prepare("SELECT logout_user(:id)");
    $stmt->execute(['id' => $_SESSION['id_activity']]);
}

//usunięcie danych użytkownika i zamknięcie sesji
session_unset();
session_destroy();

//przekierowanie do strony głównej
header('Location: index.php');
exit();
?>