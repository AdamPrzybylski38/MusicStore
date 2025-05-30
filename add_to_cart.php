<?php
session_start();

// Sprawdzenie logowania i obecności id_album
if (!isset($_SESSION['id_user']) || !isset($_GET['id_album'])) {
    header("Location: store.php");
    exit();
}

$id_album = (int) $_GET['id_album'];

// Połączenie z bazą danych
require 'connect.php'; // używamy $connect

// Zapytanie: ile jest dostępnych kopii (czyli niezamówionych) danego albumu
$stmt = $connect->prepare("
    SELECT COUNT(*) AS total_available
    FROM copies c
    WHERE c.id_album = :id_album
      AND NOT EXISTS (
          SELECT 1 FROM orders o WHERE o.id_copy = c.id_copy
      )
");
$stmt->execute(['id_album' => $id_album]);
$available = (int) $stmt->fetchColumn();

// Sprawdzenie ile już mamy w koszyku danego albumu
$current_in_cart = isset($_SESSION['cart'][$id_album]) ? $_SESSION['cart'][$id_album] : 0;

// Jeśli dostępne kopie > te w koszyku, można dodać
if ($current_in_cart < $available) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (!isset($_SESSION['cart'][$id_album])) {
        $_SESSION['cart'][$id_album] = 1;
    } else {
        $_SESSION['cart'][$id_album]++;
    }

    $_SESSION['message'][$id_album] = "Dodano do koszyka.";
} else {
    $_SESSION['message'][$id_album] = "Brak dostępnych egzemplarzy tego albumu.";
}

header("Location: store.php");
exit();
?>