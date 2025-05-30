<?php
try {
    $connect = new PDO("pgsql:host=localhost;dbname=MusicStore", "postgres", "postgres");
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>
