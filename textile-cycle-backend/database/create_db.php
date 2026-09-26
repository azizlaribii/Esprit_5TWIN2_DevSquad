<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `textilecycle` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Base de donnees `textilecycle` creee avec succes ou deja existante.\n";
} catch (PDOException $e) {
    echo "Erreur lors de la connexion/creation MySQL: " . $e->getMessage() . "\n";
}
