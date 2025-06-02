<?php
require_once "connect.php";

try {
    $stmt = $connect->query("
    SELECT 
    a.id_album,
    a.id_artist,
    a.title, 
    ar.artist_name AS artist, 
    a.release_date, 
    a.price, 
    a.cover_path,
    COUNT(c.id_copy) AS num_copies
    FROM albums a
    JOIN artists ar ON a.id_artist = ar.id_artist
    LEFT JOIN copies c ON a.id_album = c.id_album
    GROUP BY a.id_album, ar.artist_name, a.title, a.release_date, a.price, a.cover_path
    ORDER BY a.id_album ASC
    ");
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($albums)) {
        echo "<p class='text-center'>Brak albumów w bazie.</p>";
        return;
    }

    echo '<div class="table-responsive">';
    // dodajemy text-center by wyśrodkować zawartość tabeli, align-middle już masz by pionowo wyśrodkować wiersze
    echo '<table class="table table-striped table-bordered align-middle table-custom text-center">';
    echo '<thead><tr>
        <th>ID Albumu</th>
        <th>ID Artysty</th>
        <th>Tytuł</th>
        <th>Artysta</th>
        <th>Data wydania</th>
        <th>Cena</th>
        <th>Okładka</th>
        <th>Kopie</th>
        <th>Akcje</th>
      </tr></thead><tbody>';

    foreach ($albums as $album) {
        echo "<tr>
            <td>{$album['id_album']}</td>
            <td>{$album['id_artist']}</td>
            <td>{$album['title']}</td>
            <td>{$album['artist']}</td>
            <td>{$album['release_date']}</td>
            <td>{$album['price']} zł</td>
            <td><img src='{$album['cover_path']}' alt='okładka' style='height: 50px;'></td>
            <td>
                <div class='d-flex justify-content-center align-items-center gap-2'>
                    <button class='btn btn-sm btn-outline-secondary change-copy' data-id='{$album['id_album']}' data-change='-1'>–</button>  
                    <span class='copy-count' id='copy-count-{$album['id_album']}'>{$album['num_copies']}</span>
                    <button class='btn btn-sm btn-outline-primary change-copy' data-id='{$album['id_album']}' data-change='1'>+</button>  
                </div>
            </td>
            <td>
                <div class='d-flex justify-content-center gap-2'>
                    <button class='btn btn-sm btn-danger delete-album' data-id='{$album['id_album']}' title='Usuń'><i class='bi bi-trash'></i></button>
                     <a href='edit_products.php?id_album={$album['id_album']}' class='btn btn-sm btn-warning' title='Edytuj'><i class='bi bi-pencil'></i></a>
                </div>
            </td>
          </tr>";
    }

    echo '</tbody></table>';
    echo '</div>'; // zamknięcie .table-responsive

} catch (PDOException $e) {
    echo "Błąd bazy: " . $e->getMessage();
}
?>