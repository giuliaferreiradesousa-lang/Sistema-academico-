<?php
// Dados de conexão usados pelo projeto no XAMPP local.
// A porta 3306 está funcionando neste ambiente; ela pode ser diferente em outros computadores.
$servername = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = '';
$dbname = 'sistema';

$conexao = null;
$erroConexao = null;

try {
    // O charset utf8mb4 permite salvar corretamente acentos e outros caracteres.
    $conexao = new PDO(
        "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // O detalhe técnico fica no log do PHP, sem ser exposto para quem usa o sistema.
    error_log('Falha na conexão com o banco: ' . $e->getMessage());
    $erroConexao = 'Não foi possível conectar ao banco de dados no momento.';
}
