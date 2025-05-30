<?php
session_start();

// sprawdzenie, czy użytkownik jest zalogowany
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
            <h2 class="mb-4">Twoje zamówienia</h2>
            <div class="row">
                <?php
                try {
                    $stmt = $connect->prepare("SELECT * FROM get_user_orders(:id_user)");
                    $stmt->execute([':id_user' => $_SESSION['id_user']]);
                    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($orders && count($orders) > 0) {
                        foreach ($orders as $order) {
                            $title = htmlspecialchars($order['title']);
                            $artist = htmlspecialchars($order['artist_name']);
                            $price = number_format($order['price'], 2, ',', '');
                            $status = htmlspecialchars($order['status']);
                            $date = date('d.m.Y H:i', strtotime($order['order_date']));

                            echo <<<HTML
                        <div class="col-12 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-1">$title</h5>
                                    <p class="card-text text-muted mb-1">$artist</p>
                                    <p class="card-text mb-1">Cena: $price zł</p>
                                    <p class="card-text mb-1">Zamówiono: $date</p>
                                    <p class="card-text"><span class="badge bg-secondary">$status</span></p>
                                </div>
                            </div>
                        </div>
                        HTML;
                        }
                    } else {
                        echo '<p class="text-muted">Nie masz jeszcze żadnych zamówień.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="text-danger">Błąd: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
    </main>

</body>

</html>