<?php
session_start();

// sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once "connect.php";

// tworzenie nowej sesji czatu
if (!isset($_SESSION['id_chat']) || isset($_GET['new_chat'])) {
    $stmt = $connect->prepare("INSERT INTO chats (id_user) VALUES (:id_user) RETURNING id_chat");
    $stmt->execute(['id_user' => $_SESSION['id_user']]);
    $_SESSION['id_chat'] = $stmt->fetchColumn();

    if (isset($_GET['new_chat'])) {
        header("Location: chat.php");
        exit();
    }
}

// obsługa zapytania
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_query = $_POST['query'];

    // pobieranie historii czatu
    $stmt = $connect->prepare("SELECT prompt, completion FROM chat_history WHERE id_chat = :id_chat ORDER BY created_at ASC");
    $stmt->execute(['id_chat' => $_SESSION['id_chat']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // konwersja historii czatu do formatu JSON
    $history_json = escapeshellarg(json_encode($history, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $escaped_query = escapeshellarg($user_query);

    // uruchomienie skryptu Python
    //MacOS & Linux
    $command = "source msenv/bin/activate && python3 connect.py $escaped_query $history_json 2>&1";

    //Windows
    //$command = "python connect.py $escaped_query $history_json 2>&1";

    // wykonanie komendy
    $output = shell_exec($command);

    // sprawdzenie błędów
    if ($output === null) {
        die("Błąd wykonania skryptu Python.");
    }

    // przetwarzenie odpowiedzi
    $response = nl2br(htmlspecialchars($output));

    // dodanie zapytania i odpowiedzi do historii czatu
    $stmt = $connect->prepare("INSERT INTO chat_history (id_chat, prompt, completion) VALUES (:id_chat, :prompt, :completion)");
    $stmt->execute([
        'id_chat' => $_SESSION['id_chat'],
        'prompt' => $user_query,
        'completion' => $response
    ]);

    echo json_encode(['response' => $response]);
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
    </header>

            <div class="bg-light text-dark rounded py-2 px-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                    <div class="mb-0 fs-4 fw-semibold">
                        Witaj, <?= htmlspecialchars($_SESSION["username"]) ?>!
                    </div>

                    <div class="d-flex gap-2">
                        <a href="store.php" class="btn btn-outline-primary">Powrót do sklepu</a>
                        <a href="logout.php" class="btn btn-danger">Wyloguj się</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <br>

    <main>
        <div class="chat-container">
            <div id="response-box" class="mb-3"></div>
            <div id="suggestions" class="mb-3 d-flex flex-wrap justify-content-center gap-2 px-2 px-sm-0"></div>
            <div class="input-group">
                <a href="chat.php?new_chat=1" class="btn btn-secondary">+</a>
                <input type="text" name="query" id="query" class="form-control" placeholder="Wpisz zapytanie..."
                    required>
                <button id="send-btn" class="btn btn-primary">Wyślij</button>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function () {
            // automatyczne przewijanie chatu do dołu
            function scrollToBottom() {
                const box = $('#response-box');
                box.scrollTop(box[0].scrollHeight);
            }

            // funkcja do wysyłania zapytania
            function sendQuery() {
                var query = $("#query").val();
                if (!query) return;
                $("#query").val(""); // czyszczenie pola tekstowego

                // dodanie wiadomości użytkownika do chatu
                var userMessage = $('<div>', {
                    class: 'message-user',
                    text: query
                });
                $("#response-box").append(userMessage);

                // animacja ładowania
                var loadingDots = $('<div class="loading-dots"><span></span><span></span><span></span></div>');
                $("#response-box").append(loadingDots);
                loadingDots.show();
                scrollToBottom();

                // wysyłanie zapytania do chat.php
                $.post("chat.php", { query: query }, function (data) {
                    try {
                        var response = JSON.parse(data).response;
                        loadingDots.remove();

                        // dodanie odpowiedzi AI do chatu
                        var aiMessage = $('<div>', {
                            class: 'message-ai',
                            html: response
                        });
                        $("#response-box").append(aiMessage);

                        scrollToBottom();
                    } catch (e) {
                        loadingDots.remove();
                        $("#response-box").html("Błąd w przetwarzaniu odpowiedzi");
                    }
                });
            }

            // obsługa pola tekstowego i przycisku
            $("#send-btn").click(sendQuery);
            $("#query").keypress(function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendQuery();
                }
            });

            scrollToBottom();
        });

        // pobieranie sugestii z pliku suggestions.php
        $.getJSON("suggestions.php", function (suggestions) {
            if (suggestions.length > 0) {
                suggestions.forEach(function (prompt) {
                    var btn = $('<button>', {
                        class: 'btn btn-outline-secondary btn-sm',
                        text: prompt,
                        click: function () {
                            $("#query").val(prompt);
                            $("#send-btn").click();
                        }
                    });
                    $("#suggestions").append(btn);
                });
            }
        });

    </script>
</body>

</html>