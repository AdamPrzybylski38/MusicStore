<?php
require_once "connect.php";

try {
    $stmt = $connect->query("SELECT * FROM get_all_orders() ORDER BY order_date DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo "<div class='alert alert-info'>Brak zamówień.</div>";
        exit;
    }

    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-custom text-center'>";  // <-- dodane text-center
    echo "<thead><tr>
            <th>ID</th>
            <th>ID użytkownika</th>
            <th>ID kopii</th>
            <th>Data</th>
            <th>Status</th>
            <th>Akcja</th>
          </tr></thead><tbody>";

    foreach ($orders as $row) {
        echo "<tr>";
        echo "<td>{$row['id_order']}</td>";
        echo "<td>{$row['id_user']}</td>";
        echo "<td>{$row['id_copy']}</td>";
        echo "<td>{$row['order_date']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>";
        if (strtolower($row['status']) !== 'anulowane') {
            // opakowanie przycisku w d-flex justify-content-center i odstęp gap-2
            echo "<div class='d-flex justify-content-center gap-2'>
                    <button class='btn btn-sm btn-danger cancel-order' data-id='{$row['id_order']}' title='Anuluj zamówienie'>
                        <i class='bi bi-trash'></i>
                    </button>
                  </div>";
        } else {
            echo "—";
        }
        echo "</td></tr>";
    }

    echo "</tbody></table>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Błąd: " . $e->getMessage() . "</div>";
}
?>