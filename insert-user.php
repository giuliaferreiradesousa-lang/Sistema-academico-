<?php
// A sessão também é usada para levar a mensagem de sucesso até a tela de login.
session_start();

// Quem já entrou vai direto para o dashboard.
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erros = [];
$nome = '';
$email = '';

// Recebe os dados enviados pelo formulário.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');
    $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

    // Confere nome, e-mail, senha e confirmação antes de cadastrar.
    if ($nome === '') {
        $erros['nome'] = 'Informe seu nome.';
    } elseif (mb_strlen($nome) < 3) {
        $erros['nome'] = 'O nome deve ter pelo menos 3 caracteres.';
    }

    if ($email === '') {
        $erros['email'] = 'Informe seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Digite um e-mail válido.';
    }

    if ($senha === '') {
        $erros['senha'] = 'Crie uma senha.';
    } elseif (strlen($senha) < 6) {
        $erros['senha'] = 'A senha deve ter pelo menos 6 caracteres.';
    }

    if ($confirmarSenha === '') {
        $erros['confirmar_senha'] = 'Confirme sua senha.';
    } elseif ($senha !== $confirmarSenha) {
        $erros['confirmar_senha'] = 'As senhas não coincidem.';
    }

    // Só tenta salvar quando não há erros nos campos.
    if ($erros === []) {
        require 'conexao.php';

        if ($erroConexao !== null) {
            $erros['geral'] = $erroConexao;
        } else {
            try {
                // password_hash() cria um hash seguro. A senha original nunca é salva no banco.
                $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

                // A consulta preparada mantém os dados do formulário separados do SQL.
                $sql = 'INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)';
                $resultado = $conexao->prepare($sql);
                $resultado->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':senha' => $senhaCriptografada,
                ]);

                // Usa redirecionamento após o cadastro para evitar novo envio ao atualizar a página.
                $_SESSION['flash_sucesso'] = 'Conta criada com sucesso. Agora você já pode entrar.';
                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                error_log('Erro ao cadastrar usuário: ' . $e->getMessage());
                $erros['geral'] = $e->getCode() === '23000'
                    ? 'Este e-mail já possui uma conta.'
                    : 'Não foi possível criar a conta no momento.';
            }
        }
    }
}

// Exibe o texto sem interpretá-lo como HTML.
function escaparCadastro(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Criação de conta no Sistema Acadêmico.">
    <title>Criar conta | Sistema Acadêmico</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/auth.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-card" aria-labelledby="titulo-cadastro">
            <a class="brand" href="login.php" aria-label="Sistema Acadêmico, página de login">
                <span class="brand-mark" aria-hidden="true">S</span>
                <span>Sistema Acadêmico</span>
            </a>

            <div class="auth-heading">
                <p class="eyebrow">Nova conta</p>
                <h1 id="titulo-cadastro">Crie sua conta</h1>
                <p>Preencha os dados para acessar sua área restrita.</p>
            </div>

            <?php if (isset($erros['geral'])): ?>
                <div class="feedback feedback-error" role="alert">
                    <?= escaparCadastro($erros['geral']) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post">
                <div class="field-group">
                    <label for="nome">Nome</label>
                    <input id="nome" name="nome" type="text" value="<?= escaparCadastro($nome) ?>" placeholder="Como deseja ser chamado?" autocomplete="name" maxlength="100" required aria-invalid="<?= isset($erros['nome']) ? 'true' : 'false' ?>" aria-describedby="nome-feedback">
                    <p id="nome-feedback" class="field-feedback <?= isset($erros['nome']) ? 'is-visible' : '' ?>"><?= isset($erros['nome']) ? escaparCadastro($erros['nome']) : 'Use pelo menos 3 caracteres.' ?></p>
                </div>

                <div class="field-group">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" value="<?= escaparCadastro($email) ?>" placeholder="voce@exemplo.com" autocomplete="email" maxlength="100" required aria-invalid="<?= isset($erros['email']) ? 'true' : 'false' ?>" aria-describedby="email-feedback">
                    <p id="email-feedback" class="field-feedback <?= isset($erros['email']) ? 'is-visible' : '' ?>"><?= isset($erros['email']) ? escaparCadastro($erros['email']) : 'Use o e-mail que será usado para entrar.' ?></p>
                </div>

                <div class="field-group">
                    <label for="senha">Senha</label>
                    <div class="password-wrapper">
                        <input id="senha" name="senha" type="password" placeholder="Crie uma senha" autocomplete="new-password" minlength="6" required aria-invalid="<?= isset($erros['senha']) ? 'true' : 'false' ?>" aria-describedby="senha-feedback">
                        <button class="password-toggle" type="button" data-password-toggle="senha" aria-label="Mostrar senha" aria-pressed="false">Mostrar</button>
                    </div>
                    <p id="senha-feedback" class="field-feedback <?= isset($erros['senha']) ? 'is-visible' : '' ?>"><?= isset($erros['senha']) ? escaparCadastro($erros['senha']) : 'Use pelo menos 6 caracteres.' ?></p>
                </div>

                <div class="field-group">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <div class="password-wrapper">
                        <input id="confirmar_senha" name="confirmar_senha" type="password" placeholder="Digite a senha novamente" autocomplete="new-password" minlength="6" required aria-invalid="<?= isset($erros['confirmar_senha']) ? 'true' : 'false' ?>" aria-describedby="confirmar-senha-feedback">
                        <button class="password-toggle" type="button" data-password-toggle="confirmar_senha" aria-label="Mostrar senha" aria-pressed="false">Mostrar</button>
                    </div>
                    <p id="confirmar-senha-feedback" class="field-feedback <?= isset($erros['confirmar_senha']) ? 'is-visible' : '' ?>"><?= isset($erros['confirmar_senha']) ? escaparCadastro($erros['confirmar_senha']) : 'Repita a senha para evitar erros de digitação.' ?></p>
                </div>

                <button class="button button-primary" type="submit">Criar conta</button>
            </form>

            <p class="auth-footer">Já possui uma conta? <a href="login.php">Entrar</a></p>
        </section>
    </main>
</body>
</html>
