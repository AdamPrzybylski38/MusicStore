<?php //ADMIN ZAMOWIENIA 
session_start();
require_once "connect.php";

if (!isset($_SESSION['id_user']))
    exit("Brak sesji.");
$id_user = $_SESSION['id_user'];

$stmt = $connect->prepare("SELECT 1 FROM (SELECT id_user FROM admins UNION SELECT id_user FROM mods) AS all_roles WHERE id_user = :id");
$stmt->execute(['id' => $id_user]);
if ($stmt->rowCount() === 0)
    exit("Brak dostępu.");

$action = $_POST['action'] ?? '';

if ($action === 'cancel') {
    $stmt = $connect->prepare("UPDATE orders SET status = 'anulowane' WHERE id_order = :id");
    $stmt->execute(['id' => $_POST['id_order']]);
    echo "Zamówienie anulowane.";
} else {
    echo "Nieznana akcja.";
}
?>