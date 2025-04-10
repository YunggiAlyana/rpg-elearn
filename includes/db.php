<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=rpg_elearn', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
