<?php //wszystko do zmiany liczby kopii w panelu admina 
require_once "connect.php";
header('Content-Type: application/json');

if (!isset($_POST['id_album'], $_POST['change'])) {
    echo json_encode(['success' => false, 'message' => 'Brak danych wejściowych']);
    exit();
}

$id_album = (int)$_POST['id_album'];
$change = (int)$_POST['change'];

try {
    if ($change === 1) {
        // Dodaj nową kopię
        $stmt = $connect->prepare("INSERT INTO copies (id_album) VALUES (:id_album)");
        $stmt->execute(['id_album' => $id_album]);
    } elseif ($change === -1) {
        // Usuń jedną z kopii, jeśli istnieje
        $stmt = $connect->prepare("SELECT id_copy FROM copies WHERE id_album = :id_album LIMIT 1");
        $stmt->execute(['id_album' => $id_album]);
        $copy = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($copy) {
            $deleteStmt = $connect->prepare("DELETE FROM copies WHERE id_copy = :id_copy");
            $deleteStmt->execute(['id_copy' => $copy['id_copy']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Brak kopii do usunięcia']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nieprawidłowa wartość zmiany']);
        exit();
    }

    // Policzenie nowej liczby kopii
    $countStmt = $connect->prepare("SELECT COUNT(*) AS count FROM copies WHERE id_album = :id_album");
    $countStmt->execute(['id_album' => $id_album]);
    $newCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode(['success' => true, 'newCount' => $newCount]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd bazy: ' . $e->getMessage()]);
}
?>