<?php
require_once(dirname(__DIR__) . '/config.php');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    die();
}
?>