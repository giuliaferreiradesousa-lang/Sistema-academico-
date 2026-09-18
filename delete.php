<?php
// Reutiliza a conexão PDO existente no projeto.
require __DIR__ . '/conexao.php';

// Recebe o ID pela URL e aceita somente números inteiros positivos.
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

// Impede a exclusão se o ID for inválido ou houver erro na conexão.
if ($id === false || $id === null) {
    $mensagem = 'ID do produto inválido.';
} elseif ($erroConexao !== null) {
    $mensagem = $erroConexao;
} else {
    try {
        // Prepara a exclusão e executa a consulta passando o ID como parâmetro.
        $sql = 'DELETE FROM produto WHERE id = :id';
        $resultado = $conexao->prepare($sql);
        $resultado->execute(['id' => $id]);

        // Só informa sucesso se um registro realmente foi removido.
        $mensagem = $resultado->rowCount() > 0
            ? 'Produto excluído com sucesso.'
            : 'Produto não encontrado ou já excluído.';
    } catch (PDOException $e) {
        // Registra o erro técnico no servidor e mostra uma mensagem simples ao usuário.
        error_log('Erro ao excluir produto: ' . $e->getMessage());
        $mensagem = 'Não foi possível excluir o produto no momento.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir produto | Sistema Acadêmico</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/produtos.css">
</head>
<body class="catalog-page">
    <main class="catalog-content">
        <h1>Excluir produto</h1>
        <!-- Exibe o resultado com segurança e permite voltar à listagem para conferir. -->
        <p role="status"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
        <a class="public-nav-link" href="produtos.php">Voltar para a lista de produtos</a>
    </main>
</body>
</html>
