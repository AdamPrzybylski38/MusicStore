<?php
session_start();
require_once "connect.php";

function redirectWithError($message)
{
    $_SESSION['album_message'] = "❌ " . $message;
    header('Location: manage_products.php');
    exit();
}

// Sprawdzenie podstawowe
if (!isset($_SESSION['id_user']) || !isset($_GET['id_album']) || !is_numeric($_GET['id_album'])) {
    redirectWithError("Nieprawidłowe ID albumu.");
}

$id_user = $_SESSION['id_user'];
$id_album = (int) $_GET['id_album'];

// Sprawdzenie dostępu (admin/mod)
$stmt = $connect->prepare("SELECT 1 FROM (SELECT id_user FROM admins UNION SELECT id_user FROM mods) AS roles WHERE id_user = :id");
$stmt->execute(['id' => $id_user]);
if ($stmt->rowCount() === 0) {
    redirectWithError("Brak dostępu.");
}

// Pobranie danych albumu
$stmt = $connect->prepare("SELECT * FROM albums WHERE id_album = :id");
$stmt->execute(['id' => $id_album]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    redirectWithError("Album o podanym ID nie istnieje.");
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Edytuj album</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h3 class="mb-4">Edytuj album (ID: <?= htmlspecialchars($album['id_album']) ?>)</h3>

        <?php if (isset($_SESSION['album_message'])): ?>
            <div class="alert alert-warning"><?= $_SESSION['album_message'] ?></div>
            <?php unset($_SESSION['album_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['edit_error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['edit_error'] ?></div>
            <?php unset($_SESSION['edit_error']); ?>
        <?php endif; ?>

        <form method="post" action="validate_edit.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_album" value="<?= $album['id_album'] ?>">

            <div class="mb-3">
                <label class="form-label">Tytuł</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($album['title']) ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">ID Artysty</label>
                <input type="number" name="id_artist" class="form-control"
                    value="<?= htmlspecialchars($album['id_artist']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Data wydania</label>
                <input type="date" name="release_date" class="form-control"
                    value="<?= htmlspecialchars($album['release_date']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cena</label>
                <input type="number" step="0.01" name="price" class="form-control"
                    value="<?= htmlspecialchars($album['price']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Link do okładki (URL)</label>
                <input type="text" name="cover_path" class="form-control"
                    value="<?= htmlspecialchars($album['cover_path']) ?>">
            </div>

            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
            <a href="manage_products.php" class="btn btn-secondary">Anuluj</a>
        </form>
    </div>
</body>

</html>