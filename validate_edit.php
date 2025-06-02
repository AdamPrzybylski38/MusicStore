<?php
session_start();
require_once "connect.php";

if (!isset($_SESSION['id_user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_products.php');
    exit();
}

$id_user = $_SESSION['id_user'];

// Sprawdzenie uprawnień
$stmt = $connect->prepare("SELECT 1 FROM (SELECT id_user FROM admins UNION SELECT id_user FROM mods) AS r WHERE id_user = :id");
$stmt->execute(['id' => $id_user]);
if ($stmt->rowCount() === 0) {
    header('Location: index.php');
    exit();
}

$id_album = $_POST['id_album'] ?? '';
$id_artist = $_POST['id_artist'] ?? '';
$title = trim($_POST['title'] ?? '');
$release_date = $_POST['release_date'] ?? '';
$price = $_POST['price'] ?? '';
$cover_path = trim($_POST['cover_path'] ?? '');

// WALIDACJE

if (!is_numeric($id_artist) || $id_artist <= 0) {
    $_SESSION['album_message'] = "Nieprawidłowe ID artysty (musi być dodatnią liczbą).";
    header("Location: edit_products.php?id_album=" . $id_album);
    exit();
}

// Sprawdzenie, czy ID artysty istnieje w bazie
$stmt = $connect->prepare("SELECT 1 FROM artists WHERE id_artist = :id");
$stmt->execute(['id' => $id_artist]);
if ($stmt->rowCount() === 0) {
    $_SESSION['album_message'] = "Taki artysta nie istnieje w bazie danych.";
    header("Location: edit_products.php?id_album=" . $id_album);
    exit();
}

if ($title === '') {
    $_SESSION['album_message'] = "Tytuł nie może być pusty.";
    header("Location: edit_products.php?id_album=" . $id_album);
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $release_date)) {
    $_SESSION['album_message'] = "Nieprawidłowa data wydania.";
    header("Location: edit_products.php?id_album=" . $id_album);
    exit();
}

if (!is_numeric($price) || $price < 0) {
    $_SESSION['album_message'] = "Cena musi być liczbą nieujemną.";
    header("Location: edit_products.php?id_album=" . $id_album);
    exit();
}



// ZAKTUALIZUJ DANE
$stmt = $connect->prepare("UPDATE albums SET id_artist = :id_artist, title = :title, release_date = :release_date, price = :price, cover_path = :cover_path WHERE id_album = :id");
$stmt->execute([
    'id' => $id_album,
    'id_artist' => $id_artist,
    'title' => $title,
    'release_date' => $release_date,
    'price' => $price,
    'cover_path' => $cover_path
]);

$_SESSION['album_message'] = "Album został zaktualizowany.";
header("Location: manage_products.php");
exit();
?>