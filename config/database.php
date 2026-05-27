<?php
// $host = 'localhost';
// $dbname = 'atendelab';
// $user = 'root';
// $password = '';

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'atendelab';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
try {
 $pdo = new PDO(
 "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
 $user,
 $password
 );
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
 die('Erro ao conectar com o banco de dados: ' . $e->getMessage());
}