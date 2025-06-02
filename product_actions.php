<?php
session_start();
require_once "connect.php";

// Tylko admin/mod może działać
if (!isset($_SESSION['id_user']))
    exit("Brak sesji.");
$id_user = $_SESSION['id_user'];

// Sprawdzenie czy to admin lub mod
$stmt = $connect->prepare("SELECT 1 FROM (SELECT id_user FROM admins UNION SELECT id_user FROM mods) AS all_roles WHERE id_user = :id");
$stmt->execute(['id' => $id_user]);
if ($stmt->rowCount() === 0)
    exit("Brak dostępu.");

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        // Walidacja ceny
        $price = floatval($_POST['price']);
        if ($price < 0) {
            $_SESSION['album_message'] = "❌ Cena nie może być ujemna.";
            header("Location: manage_products.php");
            exit();
        }

        // Obsługa nowego artysty lub wybranego istniejącego
        if (!empty($_POST['new_artist'])) {
            $stmt = $connect->prepare("INSERT INTO artists (artist_name) VALUES (:name) RETURNING id_artist");
            $stmt->execute(['name' => $_POST['new_artist']]);
            $id_artist = $stmt->fetchColumn();
        } elseif (!empty($_POST['id_artist'])) {
            $id_artist = (int) $_POST['id_artist'];
        } else {
            $_SESSION['album_message'] = "Musisz wybrać istniejącego artystę lub podać nowego.";
            header("Location: manage_products.php");
            exit();
        }

        // Dodanie albumu
        $stmt = $connect->prepare("INSERT INTO albums (id_artist, title, release_date, price, cover_path) 
                               VALUES (:id_artist, :title, :release_date, :price, :cover_path)");
        $stmt->execute([
            'id_artist' => $id_artist,
            'title' => $_POST['title'],
            'release_date' => $_POST['release_date'],
            'price' => $price,
            'cover_path' => $_POST['cover_path'] ?? 'albums/default_cover.png'
        ]);

        $_SESSION['album_message'] = "Dodano album.";
        header("Location: manage_products.php");
        exit();

    case 'delete':
        $stmt = $connect->prepare("DELETE FROM albums WHERE id_album = :id");
        $stmt->execute(['id' => $_POST['id_album']]);
        echo "Usunięto album.";
        break;

    case 'edit':
        // Walidacja ceny
        $price = floatval($_POST['price']);
        if ($price < 0) {
            $_SESSION['album_message'] = "❌ Cena nie może być ujemna.";
            header("Location: manage_products.php");
            exit();
        }

        $stmt = $connect->prepare("UPDATE albums SET id_artist = :id_artist, title = :title, release_date = :release_date, price = :price, cover_path = :cover_path WHERE id_album = :id");
        $stmt->execute([
            'id' => $_POST['id_album'],
            'id_artist' => $_POST['id_artist'],
            'title' => $_POST['title'],
            'release_date' => $_POST['release_date'],
            'price' => $price,
            'cover_path' => $_POST['cover_path']
        ]);
        $_SESSION['album_message'] = "✅ Zaktualizowano album.";
        header('Location: manage_products.php');
        break;

    default:
        echo "Nieznana akcja.";
}
?>