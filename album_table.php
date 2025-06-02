<?php
require_once "connect.php";

try {
    $stmt = $connect->query("
    SELECT 
    a.id_album, 
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

    echo '<table class="table table-striped table-bordered align-middle">';
    echo '<thead class="table-dark"><tr>
            <th>ID</th>
            <th>Tytuł</th>
            <th>Artysta</th>
            <th>Data wydania</th>
            <th>Cena</th>
            <th>Okładka</th>
            <th>Kopie</th>
            <th>Akcje</th>
          </tr></thead><tbody>';

    foreach ($albums as $album) {
        /*NA DOLE DODANE ZARZADZANIE KOPIAMI W ZARZADZANIU PRODUKATMI W PANELU ADMIN */
        echo "<tr>
                <td>{$album['id_album']}</td>
                <td>{$album['title']}</td>
                <td>{$album['artist']}</td>
                <td>{$album['release_date']}</td>
                <td>{$album['price']} zł</td>
                <td><img src='{$album['cover_path']}' alt='okładka' style='height: 50px;'></td>
                
                <td> 
                 <div class='d-flex align-items-center gap-2'>
                        <button class='btn btn-sm btn-outline-secondary change-copy' data-id='{$album['id_album']}' data-change='-1'>–</button>  
                        <span class='copy-count' id='copy-count-{$album['id_album']}'>{$album['num_copies']}</span>
                        <button class='btn btn-sm btn-outline-primary change-copy' data-id='{$album['id_album']}' data-change='1'>+</button>  
                </div>
                </td>
                <td>
    <button class='btn btn-sm btn-danger delete-album' data-id='{$album['id_album']}'>
        <i class='bi bi-trash'></i>
    </button>
</td>
              </tr>";
    }

    echo '</tbody></table>';

} catch (PDOException $e) {
    echo "Błąd bazy: " . $e->getMessage();
}
