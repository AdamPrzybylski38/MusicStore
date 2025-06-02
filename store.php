<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once "connect.php";
?>

<!DOCTYPE html>
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

            <div class="bg-light text-dark rounded py-3 px-3">

                <div class="row align-items-center text-center text-md-start mb-2">
                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                        <div class="fs-4 fw-semibold">Witaj, <?= htmlspecialchars($_SESSION["username"]) ?>!</div>
                    </div>
                    <div class="col-12 col-md-6 text-center text-md-end">
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <!--DODANIE PRZYCISKU PANELU ADMINA -->
                            <a href="admin.php" class="btn btn-primary w-sm-100 w-md-auto">Panel administratora</a>
                            <!--DODANIE PRZYCISKU PANELU ADMINA -->
                        <?php endif; ?> <!--DODANIE PRZYCISKU PANELU ADMINA -->
                        <?php if (isset($_SESSION['is_mod']) && $_SESSION['is_mod']): ?>
                            <!--DODANIE PRZYCISKU PANELU MODERATORA  -->
                            <a href="manage_orders.php" class="btn btn-outline-primary w-sm-100 w-md-auto">Panel moderatora</a>
                            <!--DODANIE PRZYCISKU PANELU Moderatora -->
                        <?php endif; ?>
                        <button id="toggleMenuBtn" class="btn btn-outline-secondary">☰ Menu</button>
                    </div>
                </div>

                <div id="menu"
                    class="d-none d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-md-end mt-3">
                    <a href="chat.php" class="btn btn-info w-sm-100 w-md-auto">Asystent AI</a>
                    <a href="cart.php" class="btn btn-outline-primary w-sm-100 w-md-auto">Koszyk</a>
                    <a href="orders.php" class="btn btn-outline-success w-sm-100 w-md-auto">Zamówienia</a>
                    <a href="logout.php" class="btn btn-danger w-sm-100 w-md-auto">Wyloguj się</a>
                </div>

            </div>
        </div>
    </header>

    <main>
        <div class="main-box container">

            <?php
            $allowedSorts = ['title', 'price'];
            $sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'title';
            $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc';
            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
            ?>

            <div class="d-flex justify-content-center mb-4 mt-3">
                <div class="btn-group" role="group" aria-label="Sortowanie">
                    <a href="store.php?sort=title&order=<?= ($sort === 'title' ? $nextOrder : 'asc') ?>"
                        class="btn btn-outline-secondary<?= ($sort === 'title') ? ' active' : '' ?>">
                        Sortuj po nazwie <?= ($sort === 'title' ? ($order === 'asc' ? '▲' : '▼') : '') ?>
                    </a>
                    <a href="store.php?sort=price&order=<?= ($sort === 'price' ? $nextOrder : 'asc') ?>"
                        class="btn btn-outline-secondary<?= ($sort === 'price') ? ' active' : '' ?>">
                        Sortuj po cenie <?= ($sort === 'price' ? ($order === 'asc' ? '▲' : '▼') : '') ?>
                    </a>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php
                try {
                    $stmt = $connect->query("SELECT * FROM get_available_albums()");
                    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    usort($albums, function ($a, $b) use ($sort, $order) {
                        if ($sort === 'price') {
                            return ($order === 'asc') ? ($a['price'] <=> $b['price']) : ($b['price'] <=> $a['price']);
                        } else {
                            return ($order === 'asc')
                                ? strcasecmp($a['title'], $b['title'])
                                : strcasecmp($b['title'], $a['title']);
                        }
                    });

                    if ($albums && count($albums) > 0) {
                        foreach ($albums as $row) {
                            $id_album = (int) $row['id_album'];
                            $title = htmlspecialchars($row['title']);
                            $artist = htmlspecialchars($row['artist_name']);
                            $copies = (int) $row['available_copies'];
                            $cover = htmlspecialchars($row['cover_path']);
                            $price = number_format((float) $row['price'], 2, ',', '');

                            $message = '';
                            if (isset($_SESSION['message'][$id_album])) {
                                $msg_text = htmlspecialchars($_SESSION['message'][$id_album]);
                                $message = "<div class='alert alert-info py-1 px-2 mb-2'>$msg_text</div>";
                            }

                            echo <<<HTML
                                <div class="col">
                                    <div class="card h-100 shadow-sm">
                                        <img src="$cover" class="card-img-top" alt="Okładka albumu">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">$title</h5>
                                            <p class="card-text text-muted mb-1">$artist</p>
                                            <p class="card-text fw-bold">Cena: $price zł</p>
                                            <p class="card-text fw-bold">Dostępne sztuki: $copies</p>
                                            $message
                                            <a href="add_to_cart.php?id_album=$id_album&sort=$sort&order=$order" class="btn btn-outline-primary mt-auto">Dodaj do koszyka</a>
                                        </div>
                                    </div>
                                </div>
                            HTML;
                            unset($_SESSION['message'][$id_album]);
                        }
                    } else {
                        echo '<div class="col-12 text-center"><p class="text-muted">Brak dostępnych albumów.</p></div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="col-12 text-danger">Błąd zapytania: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function () {
            $('#toggleMenuBtn').click(function () {
                $('#menu').toggleClass('d-none');
            });
        });
    </script>

</body>

</html>