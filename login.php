<?php
// session_start() permite ler e criar dados que permanecem enquanto o usuário navega no sistema.
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erros = [];
$mensagemSucesso = $_SESSION['flash_sucesso'] ?? '';
unset($_SESSION['flash_sucesso']);

// O cookie guarda somente o e-mail para preencher este campo em uma visita futura.
// A senha nunca é salva em cookie.
$emailLembrado = $_COOKIE['email'] ?? '';
$email = $emailLembrado;
$lembrarMarcado = $emailLembrado !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');
    $lembrarMarcado = isset($_POST['lembrar']);

    if ($email === '') {
        $erros['email'] = 'Informe seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Digite um e-mail válido.';
    }

    if ($senha === '') {
        $erros['senha'] = 'Informe sua senha.';
    }

    if ($erros === []) {
        require 'conexao.php';

        if ($erroConexao !== null) {
            $erros['geral'] = $erroConexao;
        } else {
            try {
                // Busca o usuário apenas pelo e-mail.
                // A senha será validada depois usando password_verify(),
                // pois no banco ela está armazenada como hash.
                $sql = 'SELECT id, nome, email, senha FROM usuario WHERE email = :email LIMIT 1';
                $resultado = $conexao->prepare($sql);
                $resultado->execute([':email' => $email]);
                $usuario = $resultado->fetch();

                if ($usuario && password_verify($senha, $usuario['senha'])) {
                    // Regenera o ID da sessão depois do login.
                    // Isso ajuda a evitar que um ID de sessão antigo seja reutilizado.
                    session_regenerate_id(true);

                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];

                    // Define um caminho válido tanto no localhost quanto quando o projeto
                    // estiver dentro de outra pasta do servidor.
                    $caminhoCookie = rtrim(dirname($_SERVER['PHP_SELF']), '/');
                    $caminhoCookie = $caminhoCookie === '' ? '/' : $caminhoCookie;
                    $usaHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

                    if ($lembrarMarcado) {
                        // Salva somente o e-mail por 30 dias para preencher o próximo login.
                        // HttpOnly impede acesso por JavaScript; SameSite=Lax reduz envios indevidos.
                        setcookie('email', $usuario['email'], [
                            'expires' => time() + (60 * 60 * 24 * 30),
                            'path' => $caminhoCookie,
                            'secure' => $usaHttps,
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                    } else {
                        // Se a opção não foi marcada, remove o e-mail lembrado anteriormente.
                        setcookie('email', '', [
                            'expires' => time() - 3600,
                            'path' => $caminhoCookie,
                            'secure' => $usaHttps,
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                    }

                    header('Location: dashboard.php');
                    exit;
                }

                $erros['geral'] = 'E-mail ou senha incorretos.';
            } catch (PDOException $e) {
                error_log('Erro ao realizar login: ' . $e->getMessage());
                $erros['geral'] = 'Não foi possível realizar o login no momento.';
            }
        }
    }
}

function escaparLogin(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acesso à área restrita do Sistema Acadêmico.">
    <title>Entrar | Sistema Acadêmico</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/auth.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="titulo-login">
            <a class="brand" href="login.php" aria-label="Sistema Acadêmico, página de login">
                <span class="brand-mark" aria-hidden="true">S</span>
                <span>Sistema Acadêmico</span>
            </a>

            <div class="auth-heading">
                <p class="eyebrow">Área restrita</p>
                <h1 id="titulo-login">Bem-vindo de volta</h1>
                <p>Entre na sua conta para continuar.</p>
            </div>

            <?php if ($mensagemSucesso !== ''): ?>
                <div class="feedback feedback-success" role="status">
                    <?= escaparLogin($mensagemSucesso) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($erros['geral'])): ?>
                <div class="feedback feedback-error" role="alert">
                    <?= escaparLogin($erros['geral']) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post">
                <div class="field-group">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" value="<?= escaparLogin($email) ?>" placeholder="voce@exemplo.com" autocomplete="email" required aria-invalid="<?= isset($erros['email']) ? 'true' : 'false' ?>" aria-describedby="email-feedback">
                    <p id="email-feedback" class="field-feedback <?= isset($erros['email']) ? 'is-visible' : '' ?>">
                        <?= isset($erros['email']) ? escaparLogin($erros['email']) : 'Use o e-mail cadastrado na sua conta.' ?>
                    </p>
                </div>

                <div class="field-group">
                    <label for="senha">Senha</label>
                    <div class="password-wrapper">
                        <input id="senha" name="senha" type="password" placeholder="Digite sua senha" autocomplete="current-password" required aria-invalid="<?= isset($erros['senha']) ? 'true' : 'false' ?>" aria-describedby="senha-feedback">
                        <button class="password-toggle" type="button" data-password-toggle="senha" aria-label="Mostrar senha" aria-pressed="false">Mostrar</button>
                    </div>
                    <p id="senha-feedback" class="field-feedback <?= isset($erros['senha']) ? 'is-visible' : '' ?>">
                        <?= isset($erros['senha']) ? escaparLogin($erros['senha']) : 'A senha é verificada com segurança no servidor.' ?>
                    </p>
                </div>

                <label class="checkbox-field" for="lembrar">
                    <input id="lembrar" name="lembrar" type="checkbox" <?= $lembrarMarcado ? 'checked' : '' ?>>
                    <span>Lembrar meu e-mail neste dispositivo</span>
                </label>

                <button class="button button-primary" type="submit">Entrar</button>
            </form>

            <p class="auth-footer">Ainda não possui uma conta? <a href="insert-user.php">Cadastre-se</a></p>
        </section>
    </main>
</body>
</html>
