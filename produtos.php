<?php 

// Inicia a sessão.
// Mesmo a página sendo pública, precisamos acessar a sessão para saber
// se a pessoa que está vendo os produtos já fez login.
session_start(); 


// Verifica se as três informações do usuário existem na sessão.
// isset() retorna true se todas existirem.
// Assim sabemos se o usuário está autenticado.
$usuarioAutenticado = isset(
    $_SESSION['usuario_id'],
    $_SESSION['usuario_nome'],
    $_SESSION['usuario_email']
); 


// Cria um array vazio que depois receberá os produtos vindos do banco.
$produtos = []; 


// Começa sem nenhuma mensagem de erro.
// Caso aconteça algum problema na consulta, essa variável receberá a mensagem.
$erroConsulta = null; 


// Importa o arquivo responsável pela conexão com o banco de dados.
// Depois disso podemos utilizar a variável $conexao.
require 'conexao.php'; 


// Verifica se aconteceu algum erro ao conectar com o banco.
if ($erroConexao !== null) { 

    // Se houve erro de conexão, guardamos a mensagem para mostrar depois.
    $erroConsulta = $erroConexao; 

} else { 

    // Se a conexão funcionou, tentamos buscar os produtos.
    try { 

        // Comando SQL responsável por buscar os produtos.
        // SELECT = selecionar/buscar dados.
        // id, nome, preco e estoque = colunas que queremos buscar.
        // FROM produto = tabela onde os dados estão.
        // ORDER BY id DESC = produtos com IDs maiores aparecem primeiro.
        $sql = 'SELECT id, nome, preco, estoque FROM produto ORDER BY id DESC'; 


        // Prepara o comando SQL antes de executá-lo.
        // Estamos utilizando PDO.
        $resultado = $conexao->prepare($sql); 


        // Executa o SELECT no banco de dados.
        $resultado->execute(); 


        // fetchAll() pega TODOS os produtos encontrados.
        // O resultado é colocado dentro do array $produtos.
        $produtos = $resultado->fetchAll(); 


    // Se ocorrer algum erro relacionado ao banco, entra aqui.
    } catch (PDOException $e) { 

        // Registra os detalhes técnicos do erro no log do servidor.
        // Isso ajuda o programador a descobrir o problema,
        // sem mostrar informações internas do banco para o usuário.
        error_log('Erro ao consultar produtos: ' . $e->getMessage()); 


        // Mensagem simples que poderá aparecer na página.
        $erroConsulta = 'Não foi possível carregar os produtos no momento.'; 
    } 
} 


// Função criada para exibir textos vindos do banco com segurança.
function escaparProdutos(string $texto): string 
{ 
    // htmlspecialchars() transforma caracteres especiais em códigos HTML.
    // Isso evita que um texto vindo do banco seja interpretado como HTML.
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); 
} 

?> 


<!DOCTYPE html> 
<html lang="pt-BR"> 

<head> 

    <!-- Define a codificação da página.
         UTF-8 permite usar corretamente acentos, ç etc. -->
    <meta charset="UTF-8"> 


    <!-- Faz a página se adaptar ao tamanho da tela,
         principalmente em celulares. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 


    <!-- Pequena descrição da página. -->
    <meta
        name="description"
        content="Consulta pública de produtos do Sistema Acadêmico."
    > 


    <!-- Nome que aparece na aba do navegador. -->
    <title>Produtos | Sistema Acadêmico</title> 


    <!-- CSS com os estilos gerais utilizados pelo sistema. -->
    <link rel="stylesheet" href="assets/css/global.css"> 


    <!-- CSS específico da página de produtos. -->
    <link rel="stylesheet" href="assets/css/produtos.css"> 

</head> 


<!-- Classe usada pelo CSS para estilizar a página do catálogo. -->
<body class="catalog-page"> 


    <!-- Cabeçalho da página. -->
    <header class="public-header"> 


        <!-- Nome/logo do sistema.
             Ao clicar, o usuário volta para produtos.php. -->
        <a class="brand" href="produtos.php"> 

            <!-- Pequeno símbolo visual da marca.
                 aria-hidden informa ao leitor de tela que ele é apenas decorativo. -->
            <span class="brand-mark" aria-hidden="true">S</span> 

            <span>Sistema Acadêmico</span> 

        </a> 


        <!-- PHP verifica se o usuário está autenticado. -->
        <?php if ($usuarioAutenticado): ?> 


            <!-- Se estiver logado, mostramos o menu completo. -->
            <nav class="public-nav" aria-label="Navegação principal"> 


                <!-- Vai para o Dashboard. -->
                <a
                    class="public-nav-link"
                    href="dashboard.php"
                >
                    Início
                </a> 


                <!-- Link da página atual.
                     is-active permite destacar Produtos no CSS.
                     aria-current informa que essa é a página atual. -->
                <a
                    class="public-nav-link is-active"
                    href="produtos.php"
                    aria-current="page"
                >
                    Produtos
                </a> 


                <!-- Vai para a consulta de usuários. -->
                <a
                    class="public-nav-link"
                    href="usuarios.php"
                >
                    Usuários
                </a> 


                <!-- Chama logout.php para encerrar a sessão. -->
                <a
                    class="public-nav-link public-logout-link"
                    href="logout.php"
                >
                    Sair
                </a> 

            </nav> 


        <!-- Caso NÃO esteja autenticado... -->
        <?php else: ?> 


            <!-- Mostramos somente o botão/link para entrar. -->
            <a class="public-login-link" href="login.php">
                Entrar
            </a> 


        <!-- Final da condição do PHP. -->
        <?php endif; ?> 

    </header> 


    <!-- Conteúdo principal da página. -->
    <main class="catalog-content"> 


        <!-- Cabeçalho do conteúdo da página. -->
        <header class="page-header"> 

            <p class="eyebrow">Catálogo</p> 

            <h1>Nossos produtos</h1> 

            <p>
                Consulte os itens disponíveis e a quantidade em estoque.
            </p> 

        </header> 


        <!-- PRIMEIRA SITUAÇÃO:
             verifica se ocorreu algum erro ao consultar o banco. -->
        <?php if ($erroConsulta !== null): ?> 


            <!-- Se aconteceu erro, mostra a mensagem.
                 role="alert" ajuda recursos de acessibilidade. -->
            <p
                class="consulta-feedback consulta-feedback-error"
                role="alert"
            >
                <?= escaparProdutos($erroConsulta) ?>
            </p> 


        <!-- SEGUNDA SITUAÇÃO:
             não aconteceu erro, mas nenhum produto foi encontrado. -->
        <?php elseif ($produtos === []): ?> 


            <p class="consulta-empty">
                Nenhum produto foi encontrado.
            </p> 


        <!-- TERCEIRA SITUAÇÃO:
             existem produtos para mostrar. -->
        <?php else: ?> 


            <!-- Área que contém todos os cards dos produtos. -->
            <section
                class="product-grid"
                aria-label="Lista de produtos"
            > 


                <!-- foreach percorre todos os produtos encontrados.
                     A cada repetição, $produto representa um produto. -->
                <?php foreach ($produtos as $produto): ?> 


                    <!-- Card individual do produto. -->
                    <article class="product-card"> 


                        <!-- Mostra o ID do produto.
                             (int) garante que o valor seja tratado como número inteiro. -->
                        <p class="product-label">
                            Produto #<?= (int) $produto['id'] ?>
                        </p> 


                        <!-- Mostra o nome do produto.
                             escaparProdutos() evita que HTML malicioso seja executado. -->
                        <h2>
                            <?= escaparProdutos($produto['nome']) ?>
                        </h2> 


                        <!-- Mostra o preço.

                             (float) transforma o valor em número decimal.

                             number_format:
                             2 = duas casas decimais
                             ',' = separador decimal
                             '.' = separador de milhar

                             Exemplo:
                             3890.00 → R$ 3.890,00
                        -->
                        <p class="product-price">
                            R$ <?= number_format(
                                (float) $produto['preco'],
                                2,
                                ',',
                                '.'
                            ) ?>
                        </p> 


                        <!-- Mostra o estoque.

                             Aqui também existe uma condição:

                             Se estoque > 0:
                             adiciona a classe "is-available"

                             Caso contrário:
                             adiciona "is-empty"

                             O CSS pode usar essas classes para mudar a aparência.
                        -->
                        <p class="product-stock <?= 
                            (int) $produto['estoque'] > 0
                                ? 'is-available'
                                : 'is-empty'
                        ?>"> 

                            Estoque:
                            <?= (int) $produto['estoque'] ?>
                            unidade(s) 

                        </p> 


                        <!-- Botão demonstrativo de compra.

                             Se o estoque for 0 ou menor:
                             PHP adiciona o atributo disabled.

                             Portanto, o botão fica desativado.
                        -->
                        <button
                            class="product-action"
                            type="button"
                            <?= (int) $produto['estoque'] <= 0
                                ? 'disabled'
                                : ''
                            ?>
                        >
                            Comprar
                        </button> 

                        <!-- Envia o ID do produto pela URL para realizar a exclusão. -->
                        <a class="public-nav-link public-logout-link" href="delete.php?id=<?= (int) $produto['id'] ?>">
                            Excluir
                        </a>

                    </article> 


                <!-- Final do foreach. -->
                <?php endforeach; ?> 

            </section> 


        <!-- Final das condições de erro/produtos. -->
        <?php endif; ?> 

    </main> 

</body> 
</html>
