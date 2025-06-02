<?php
session_start();

// sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once "connect.php"; // tworzy zmienną $connect

try {
    // sprawdzenie, czy użytkownik jest administratorem
    $stmt = $connect->prepare("SELECT 1 FROM admins WHERE id_user = :id_user");
    $stmt->execute(['id_user' => $_SESSION['id_user']]);

    if ($stmt->rowCount() === 0) {
        // użytkownik nie jest administratorem
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
                            Panel administracyjny
                        </div>

                        <div
                            class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto justify-content-center justify-content-md-end">
                            <a href="manage_orders.php" class="btn btn-outline-primary w-sm-100 w-md-auto">Zarządzanie zamówieniami</a>
                            <a href="manage_products.php" class="btn btn-outline-success w-sm-100 w-md-auto">Zarządzanie produktami</a>
                            <a href="logout.php" class="btn btn-danger w-sm-100 w-md-auto">Wyloguj się</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main>
        <div class="main-box">

        </div>
    </main>

</body>

</html>