<?php
// A sessão identifica quem fez login e protege esta página contra acessos diretos.
session_start();

if (!isset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_email'])) {
    header('Location: login.php');
    exit;
}

// O e-mail guardado na sessão identifica qual registro deve ser consultado.
$emailUsuario = $_SESSION['usuario_email'];
$dadosUsuario = null;
$erroDashboard = null;

require 'conexao.php';

if ($erroConexao !== null) {
    $erroDashboard = $erroConexao;
} else {
    try {
        // Esta consulta demonstra o uso de uma sessão junto com o banco de dados.
        // Mesmo com dados na sessão, o dashboard busca nome e e-mail novamente no banco.
        $sql = 'SELECT nome, email FROM usuario WHERE email = :email LIMIT 1';
        $resultado = $conexao->prepare($sql);
        $resultado->execute([':email' => $emailUsuario]);
        $dadosUsuario = $resultado->fetch();

        if (!$dadosUsuario) {
            // Se o usuário não existir mais no banco, a sessão não deve continuar válida.
            $_SESSION = [];
            session_destroy();
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Erro ao consultar dados no dashboard: ' . $e->getMessage());
        $erroDashboard = 'Não foi possível consultar seus dados no momento.';
    }
}

function escaparDashboard(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Área restrita do Sistema Acadêmico.">
    <title>Início | Sistema Acadêmico</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar" aria-label="Navegação principal">
            <a class="brand brand-sidebar" href="dashboard.php">
                <span class="brand-mark" aria-hidden="true">S</span>
                <span>Sistema Acadêmico</span>
            </a>

            <nav class="sidebar-nav">
                <a class="nav-link is-active" href="dashboard.php" aria-current="page">Início</a>
                <a class="nav-link" href="produtos.php">Produtos</a>
                <a class="nav-link" href="usuarios.php">Usuários</a>
            </nav>

            <a class="logout-link" href="logout.php">Sair</a>
        </aside>

        <main class="dashboard-content">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Visão geral</p>
                    <h1>Olá, <?= escaparDashboard($_SESSION['usuario_nome']) ?> <span aria-hidden="true">👋</span></h1>
                    <p>Bem-vindo à sua área restrita.</p>
                </div>
                <div class="user-avatar" aria-label="Avatar de <?= escaparDashboard($_SESSION['usuario_nome']) ?>">
                    <?= escaparDashboard(mb_strtoupper(mb_substr($_SESSION['usuario_nome'], 0, 1))) ?>
                </div>
            </header>

            <section class="dashboard-grid" aria-label="Resumo da conta">
                <article id="perfil" class="dashboard-card">
                    <span class="card-icon" aria-hidden="true">◉</span>
                    <p class="card-label">Perfil</p>
                    <h2><?= escaparDashboard($_SESSION['usuario_nome']) ?></h2>
                    <p>Seus dados de identificação estão disponíveis nesta área.</p>
                </article>

                <article id="minha-conta" class="dashboard-card">
                    <span class="card-icon" aria-hidden="true">⌁</span>
                    <p class="card-label">Seus dados</p>
                    <h2>Informações da conta</h2>
                    <?php if ($erroDashboard !== null): ?>
                        <p class="dashboard-alert" role="alert"><?= escaparDashboard($erroDashboard) ?></p>
                    <?php else: ?>
                        <dl class="user-data-list">
                            <div>
                                <dt>Nome</dt>
                                <dd><?= escaparDashboard($dadosUsuario['nome']) ?></dd>
                            </div>
                            <div>
                                <dt>E-mail</dt>
                                <dd><?= escaparDashboard($dadosUsuario['email']) ?></dd>
                            </div>
                        </dl>
                    <?php endif; ?>
                </article>

                <article class="dashboard-card">
                    <span class="card-icon" aria-hidden="true">✓</span>
                    <p class="card-label">Sessão ativa</p>
                    <h2>Acesso protegido</h2>
                    <p>Você está navegando com uma sessão autenticada.</p>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
