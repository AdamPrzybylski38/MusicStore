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
                                Zarządzanie zamówieniami
                            </div>

                            <div
                                class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto justify-content-center justify-content-md-end">
                                <?php if ($isAdmin): ?>
                                    <a href="admin.php" class="btn btn-outline-dark">Panel administracyjny</a>
                                <?php endif; ?>
                                <a href="manage_products.php"
                                    class="btn btn-outline-success w-sm-100 w-md-auto">Zarządzanie produktami</a>
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
        <div class="main-box">
            <div class="" container my-4">
                <div id="orderTable"></div>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function () {
            function loadOrders() {
                $('#orderTable').load('order_table.php');
            }

            loadOrders();

            $(document).on('click', '.cancel-order', function () {
                if (confirm('Na pewno anulować zamówienie?')) {
                    $.post('order_actions.php', {
                        action: 'cancel',
                        id_order: $(this).data('id')
                    }, function (response) {
                        alert(response);
                        loadOrders();
                    });
                }
            });
        });
    </script>

</body>

</html>