<?php
// A consulta de usuários é restrita porque mostra e-mails de contas cadastradas.
session_start();

if (!isset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_email'])) {
    header('Location: login.php');
    exit;
}

$usuarios = [];
$erroConsulta = null;

require 'conexao.php';

if ($erroConexao !== null) {
    $erroConsulta = $erroConexao;
} else {
    try {
        // A consulta preparada recupera apenas os dados permitidos para a listagem.
        // A senha e seu hash nunca são exibidos nesta página.
        $sql = 'SELECT id, nome, email FROM usuario ORDER BY id DESC';
        $resultado = $conexao->prepare($sql);
        $resultado->execute();
        $usuarios = $resultado->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao consultar usuários: ' . $e->getMessage());
        $erroConsulta = 'Não foi possível carregar a lista de usuários no momento.';
    }
}

function escaparUsuarios(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Consulta de usuários do Sistema Acadêmico.">
    <title>Usuários | Sistema Acadêmico</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/usuarios.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar" aria-label="Navegação principal">
            <a class="brand brand-sidebar" href="dashboard.php">
                <span class="brand-mark" aria-hidden="true">S</span>
                <span>Sistema Acadêmico</span>
            </a>

            <nav class="sidebar-nav">
                <a class="nav-link" href="dashboard.php">Início</a>
                <a class="nav-link" href="produtos.php">Produtos</a>
                <a class="nav-link is-active" href="usuarios.php" aria-current="page">Usuários</a>
            </nav>

            <a class="logout-link" href="logout.php">Sair</a>
        </aside>

        <main class="dashboard-content">
            <header class="page-header">
                <div>
                    <p class="eyebrow">Consulta</p>
                    <h1>Usuários cadastrados</h1>
                    <p>Lista de contas registradas no sistema.</p>
                </div>
            </header>

            <section class="table-card" aria-labelledby="titulo-tabela-usuarios">
                <div class="table-card-heading">
                    <h2 id="titulo-tabela-usuarios">Dados dos usuários</h2>
                    <span class="table-count"><?= count($usuarios) ?> usuário(s)</span>
                </div>

                <?php if ($erroConsulta !== null): ?>
                    <p class="consulta-feedback consulta-feedback-error" role="alert"><?= escaparUsuarios($erroConsulta) ?></p>
                <?php elseif ($usuarios === []): ?>
                    <p class="consulta-empty">Nenhum usuário foi encontrado.</p>
                <?php else: ?>
                    <div class="table-wrapper" tabindex="0" aria-label="Tabela com a lista de usuários">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">E-mail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= (int) $usuario['id'] ?></td>
                                        <td><?= escaparUsuarios($usuario['nome']) ?></td>
                                        <td><?= escaparUsuarios($usuario['email']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
