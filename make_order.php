<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION['id_user']) || !isset($_GET['id_album'])) {
    header("Location: store.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_album = (int) $_GET['id_album'];

if (!isset($_SESSION['cart'][$id_album])) {
    header("Location: cart.php");
    exit();
}

$quantity = $_SESSION['cart'][$id_album];

try {
    $stmt = $connect->prepare("SELECT make_order(:id_user, :id_album)");
    for ($i = 0; $i < $quantity; $i++) {
        $stmt->execute([
            ':id_user' => $id_user,
            ':id_album' => $id_album
        ]);
    }

    // Usuwamy zamówiony album z koszyka
    unset($_SESSION['cart'][$id_album]);

    header("Location: orders.php");
    exit();
} catch (PDOException $e) {
    echo "Błąd zamówienia: " . htmlspecialchars($e->getMessage());
}
