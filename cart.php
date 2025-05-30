<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once "connect.php";

// Obsługa przycisków formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usuń cały koszyk
    if (isset($_POST['remove'])) {
        $_SESSION['cart'] = [];
    }

    // Złóż zamówienie
    if (isset($_POST['order_all']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id_album => $quantity) {
            for ($i = 0; $i < $quantity; $i++) {
                $stmt = $connect->prepare("SELECT make_order(:id_user, :id_album)");
                $stmt->execute([
                    ':id_user' => $_SESSION['id_user'],
                    ':id_album' => $id_album
                ]);
            }
        }
        $_SESSION['cart'] = []; // Wyczyść koszyk po złożeniu zamówienia
        header("Location: orders.php");
        exit();
    }
}
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
        </div>
        <div>
            <div class="bg-light text-dark rounded py-2 px-3">
                <div class="container">
                    <div
                        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 flex-wrap">

                        <div class="fs-4 fw-semibold text-center text-md-start w-100 w-md-auto">
                            Witaj, <?= htmlspecialchars($_SESSION["username"]) ?>!
                        </div>

                        <div
                            class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto justify-content-center justify-content-md-end">
                            <a href="store.php" class="btn btn-secondary w-sm-100 w-md-auto">Powrót do sklepu</a>
                            <a href="logout.php" class="btn btn-danger w-sm-100 w-md-auto">Wyloguj się</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="main-box">
            <h2 class="mb-3">Twój koszyk</h2>

            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="post">
                    <div class="list-group">
                        <?php
                        $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
                        $stmt = $connect->prepare("SELECT id_album, title, price FROM albums WHERE id_album IN ($placeholders)");
                        $stmt->execute(array_keys($_SESSION['cart']));
                        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $total_price = 0;

                        foreach ($albums as $album):
                            $title = htmlspecialchars($album['title']);
                            $price_raw = (float) $album['price'];
                            $price = number_format($price_raw, 2, ',', '');
                            $id = (int) $album['id_album'];
                            $quantity = $_SESSION['cart'][$id];

                            $subtotal = $price_raw * $quantity;
                            $total_price += $subtotal;

                            $subtotal_formatted = number_format($subtotal, 2, ',', '');
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?= $title ?> <span class="badge bg-secondary"><?= $quantity ?> szt.</span>
                                </div>
                                <span class="badge bg-primary"><?= $subtotal_formatted ?> zł</span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3 text-end">
                        <h5 class="fw-semibold">Suma: <?= number_format($total_price, 2, ',', '') ?> zł</h5>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" name="order_all" class="btn btn-success">Złóż zamówienie</button>
                        <button type="submit" name="remove" class="btn btn-outline-danger">Wyczyść koszyk</button>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-muted">Koszyk jest pusty.</p>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>