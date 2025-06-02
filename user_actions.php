<?php
session_start();
require_once "connect.php";

// Tylko administrator może zarządzać użytkownikami
if (!isset($_SESSION['id_user']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

// Obsługa akcji typu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id_user'])) {
    $id_user = (int)$_POST['id_user'];
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'add_admin':
                $connect->prepare("CALL add_admin(:id_user)")->execute(['id_user' => $id_user]);
                break;
            case 'remove_admin':
                $connect->prepare("CALL remove_admin(:id_user)")->execute(['id_user' => $id_user]);
                break;
            case 'add_mod':
                $connect->prepare("CALL add_mod(:id_user)")->execute(['id_user' => $id_user]);
                break;
            case 'remove_mod':
                $connect->prepare("CALL remove_mod(:id_user)")->execute(['id_user' => $id_user]);
                break;
            case 'delete_user':
                $connect->prepare("CALL delete_user(:id_user)")->execute(['id_user' => $id_user]);
                break;
            default:
                echo "Nieznana akcja.";
                exit();
        }

        // Po akcji przekieruj z powrotem do listy
        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        echo "Błąd: " . $e->getMessage();
        exit();
    }
}
?>
