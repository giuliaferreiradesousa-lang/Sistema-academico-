// Mostra ou esconde a senha, sem conferir se ela está correta.
document.querySelectorAll('[data-password-toggle]').forEach((botao) => {

    // Para cada botão encontrado, adiciona um evento de clique.
    botao.addEventListener('click', () => {

        // Pega o valor do atributo data-password-toggle do botão
        // e procura o campo de senha que possui esse mesmo id.
        const campo = document.getElementById(botao.dataset.passwordToggle);

        // Verifica se o campo de senha realmente foi encontrado.
        if (!campo) {

            // Se o campo não existir, interrompe a execução dessa função
            // para evitar erro no JavaScript.
            return;
        }

        // Verifica se o campo está atualmente no tipo "password".
        // Se estiver, significa que a senha está escondida.
        const mostrarSenha = campo.type === 'password';

        // Se a senha estiver escondida, muda o tipo para "text"
        // para mostrar a senha.
        // Se já estiver visível, volta para "password".
        campo.type = mostrarSenha ? 'text' : 'password';

        // Altera o texto do botão de acordo com o estado atual.
        // Se acabou de mostrar a senha, o botão passa a dizer "Esconder".
        // Caso contrário, volta a dizer "Mostrar".
        botao.textContent = mostrarSenha ? 'Esconder' : 'Mostrar';

        // Atualiza o aria-label do botão.
        // Isso ajuda tecnologias assistivas, como leitores de tela,
        // a entenderem qual ação o botão realiza naquele momento.
        botao.setAttribute(
            'aria-label',
            mostrarSenha ? 'Esconder senha' : 'Mostrar senha'
        );

        // Atualiza o atributo aria-pressed.
        // Ele informa se o botão está em um estado "ativo" ou "pressionado".
        // O valor precisa ser convertido para texto porque atributos HTML
        // trabalham com strings.
        botao.setAttribute('aria-pressed', String(mostrarSenha));
    });
});
