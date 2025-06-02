<?php
session_start();

$isAdmin = false;

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once "connect.php"; // zapewnia $connect jako instancję PDO

try {
    $id_user = $_SESSION['id_user'];

    // Sprawdzenie, czy użytkownik jest administratorem
    $stmtAdmin = $connect->prepare("SELECT 1 FROM admins WHERE id_user = :id_user");
    $stmtAdmin->execute(['id_user' => $id_user]);
    $isAdmin = $stmtAdmin->rowCount() > 0;

    // Sprawdzenie, czy użytkownik jest adminem lub moderatorem
    $stmtAccess = $connect->prepare("
        SELECT 1 FROM (
            SELECT id_user FROM admins
            UNION
            SELECT id_user FROM mods
        ) AS combined
        WHERE id_user = :id_user
    ");
    $stmtAccess->execute(['id_user' => $id_user]);

    if ($stmtAccess->rowCount() === 0) {
        // Brak dostępu – nie admin i nie moderator
        header('Location: index.php');
        exit();
    }

} catch (PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage();
    exit();
}
?>



<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Music Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
            <div>
                <div class="bg-light text-dark rounded py-2 px-3">
                    <div class="container">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 flex-wrap">

                            <div class="fs-4 fw-semibold text-center text-md-start w-100 w-md-auto">
                                Zarządzanie produktami
                            </div>

                            <div
                                class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto justify-content-center justify-content-md-end">
                                <?php if ($isAdmin): ?>
                                    <a href="admin.php" class="btn btn-outline-dark">Panel administracyjny</a>
                                <?php endif; ?>
                                <a href="manage_orders.php"
                                    class="btn btn-outline-primary w-sm-100 w-md-auto">Zarządzanie zamówieniami</a>
                                <a href="store.php" class="btn btn-outline-secondary w-sm-100 w-md-auto">Powrót do
                                    sklepu</a>
                                <a href="logout.php" class="btn btn-danger w-sm-100 w-md-auto">Wyloguj się</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
    </header>

    <main>
        <div class="main-box"> <!-- DODANE DO ZARZADZANIE W ADMIN -->
            <div class="container mt-4">
                <?php if (isset($_SESSION['album_message'])): ?>
                    <div class="alert alert-info"><?= $_SESSION['album_message'] ?></div>
                    <?php unset($_SESSION['album_message']); ?>
                <?php endif; ?>


                <h3>Produkty</h3>

                <!-- Formularz dodawania nowego albumu -->
                <form method="post" action="product_actions.php" class="row g-3 mb-4" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4">
                        <input type="text" name="title" class="form-control" placeholder="Tytuł" required>
                    </div>
                    <div class="col-md-3">
                        <select name="id_artist" class="form-select">
                            <option value="">-- Wybierz istniejącego artystę --</option>
                            <?php
                            try {
                                $stmt = $connect->query("SELECT id_artist, artist_name FROM artists ORDER BY artist_name");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value=\"{$row['id_artist']}\">{$row['artist_name']} (ID: {$row['id_artist']})</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option disabled>Błąd bazy</option>";
                            }
                            ?>
                        </select>
                        <small class="text-muted">Lub wpisz nowego artystę poniżej</small>
                        <input type="text" name="new_artist" class="form-control mt-1" placeholder="Nowy artysta">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="release_date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="Cena" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100">Dodaj</button>
                    </div>
                </form>

                <!-- Tabela z albumami -->
                <div id="productsTable">
                    <!-- AJAX-em wypełnimy tutaj dane -->
                </div>
            </div>

        </div>
    </main>

</body>

<script>
    $(document).ready(function () {

        // Dodawanie albumu
        $('#addAlbumForm').on('submit', function (e) {
            e.preventDefault();
            $.post('product_actions.php', $(this).serialize() + '&action=add', function (response) {
                alert(response);
                loadAlbums(); // funkcja do przeładowania tabeli
            });
        });

        // Usuwanie albumu
        $(document).on('click', '.delete-album', function () {
            if (confirm('Na pewno usunąć album?')) {
                $.post('product_actions.php', { id_album: $(this).data('id'), action: 'delete' }, function (response) {
                    alert(response);
                    loadAlbums();
                });
            }
        });

        // Edycja albumu – opcjonalnie
        $('#editAlbumForm').on('submit', function (e) {
            e.preventDefault();
            $.post('product_actions.php', $(this).serialize() + '&action=edit', function (response) {
                alert(response);
                loadAlbums();
            });
        });

        function loadAlbums() {
            $('#productsTable').load('album_table.php');
        }

        loadAlbums(); // inicjalne załadowanie
    });
    // Zmiana liczby kopii
    $(document).on('click', '.change-copy', function () {
        const albumId = $(this).data('id');
        const change = $(this).data('change');

        $.post('update_copies.php', { id_album: albumId, change: change }, function (response) {
            if (response.success) {
                $('#copy-count-' + albumId).text(response.newCount);
            } else {
                alert(response.message);
            }
        }, 'json');
    });
</script>

</html>