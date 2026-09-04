<?php
// session_start() é necessário para acessar a sessão atual antes de encerrá-la.
session_start();

// Remove os dados guardados durante o login.
$_SESSION = [];

// Remove também o cookie técnico da sessão no navegador, quando ele estiver sendo utilizado.
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
}

// Destrói a sessão para impedir o acesso ao dashboard sem um novo login.
session_destroy();

// O cookie "email" é mantido: ele guarda apenas o e-mail escolhido em "Lembrar de mim".
header('Location: login.php');
exit;
