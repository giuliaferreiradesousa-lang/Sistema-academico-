# Sistema Acadêmico

Projeto acadêmico em PHP criado para praticar cadastro de usuários, login, consultas ao banco de dados, sessões, cookies e construção de uma interface web responsiva.

## Objetivo da atividade

O sistema permite criar uma conta, entrar em uma área restrita e sair dela com segurança básica adequada a uma atividade de curso técnico. O foco é entender como PHP, MySQL, PDO, sessões e cookies trabalham juntos, sem frameworks.

## Tecnologias utilizadas

- PHP 8+
- MySQL ou MariaDB
- PDO com extensão `pdo_mysql`
- HTML5
- CSS3
- JavaScript puro
- XAMPP, para execução local durante as aulas

## O que foi implementado

- Correção da autenticação: o login busca o usuário somente pelo e-mail e usa `password_verify()` para conferir a senha.
- Cadastro com `password_hash()`, validações no servidor e confirmação de senha.
- Tratamento de e-mail já cadastrado e mensagens de erro ou sucesso dentro da interface.
- Sessão com ID, nome e e-mail do usuário.
- Regeneração do ID da sessão após o login.
- Proteção do dashboard contra acesso sem login.
- Consulta de nome e e-mail no banco dentro do dashboard, usando o e-mail da sessão.
- Consulta protegida de usuários, sem exibir senhas ou hashes.
- Consulta pública de produtos com nome, preço, estoque e botão demonstrativo.
- Logout que encerra a sessão corretamente.
- Cookie “Lembrar meu e-mail neste dispositivo”, sem salvar a senha.
- Interface visual consistente para login e cadastro.
- Dashboard responsivo, com navegação lateral no desktop e navegação reorganizada no celular.
- Comentários didáticos nas partes principais de autenticação, sessão, cookie e PDO.

## Estrutura do projeto

```text
atividade0409/
├── assets/
│   ├── css/
│   │   ├── global.css
│   │   ├── auth.css
│   │   ├── dashboard.css
│   │   ├── produtos.css
│   │   └── usuarios.css
│   └── js/
│       └── auth.js
├── conexao.php
├── index.php
├── login.php
├── insert-user.php
├── dashboard.php
├── produtos.php
├── usuarios.php
├── logout.php
├── sistema.sql
└── README.md
```

## Arquivos principais

| Arquivo | Função |
|---|---|
| `conexao.php` | Cria a conexão PDO com o banco de dados. |
| `index.php` | Redireciona o endereço inicial para `login.php`. |
| `login.php` | Mostra o formulário, valida o login, cria a sessão e controla o cookie de e-mail. |
| `insert-user.php` | Mostra o cadastro, valida os campos e salva o novo usuário. |
| `dashboard.php` | Área restrita acessível apenas após login. |
| `produtos.php` | Consulta pública de produtos em cards responsivos. |
| `usuarios.php` | Lista os IDs, nomes e e-mails dos usuários autenticados. |
| `logout.php` | Encerra a sessão e volta para o login. |
| `sistema.sql` | Estrutura e dado inicial do banco. |
| `assets/css/` | Estilos globais, de autenticação, dashboard, produtos e usuários. |
| `assets/js/auth.js` | Botão de mostrar e esconder senha. |

## Como executar localmente

1. Inicie o Apache e o MySQL pelo painel do XAMPP.
2. Mantenha a pasta do projeto dentro de `htdocs`.
3. No phpMyAdmin, crie um banco de dados chamado `sistema`.
4. Selecione esse banco e importe nele o arquivo `sistema.sql`.
5. Confira os dados em `conexao.php`:
   - servidor: `127.0.0.1`
   - banco: `sistema`
   - usuário: `root`
   - senha: vazia, conforme a configuração padrão usada neste projeto
   - porta: `3306`
6. Abra no navegador o endereço da pasta do projeto, por exemplo: `http://localhost/atividade0409/`.

### Importar o banco de dados

No phpMyAdmin, crie e selecione primeiro o banco `sistema`. Depois, escolha a opção **Importar**, selecione o arquivo `sistema.sql` e confirme. O arquivo cria as tabelas `usuario` e `produto`.

A tabela `usuario` possui os campos `id`, `nome`, `email` e `senha`. A tabela `produto` possui apenas `id`, `nome`, `preco` e `estoque`, além de três produtos de exemplo para a consulta.

## Configuração esperada de MySQL e PHP

O projeto usa PDO para conectar ao MySQL/MariaDB. É necessário que a extensão `pdo_mysql` esteja ativa no PHP do XAMPP.

A porta configurada no projeto é **3306**, pois ela já foi testada neste ambiente. Em outro computador, a porta do MySQL pode variar. Se necessário, ajuste somente a variável `$port` em `conexao.php` para a porta usada naquele ambiente.

## Como funciona o cadastro

1. A pessoa informa nome, e-mail, senha e confirmação de senha.
2. O PHP verifica se os dados são válidos.
3. A senha recebe um hash com `password_hash()`.
4. A consulta preparada do PDO insere nome, e-mail e hash na tabela `usuario`.
5. Se o cadastro funcionar, a pessoa é levada para o login com uma mensagem de sucesso.

### `password_hash()`

`password_hash()` transforma a senha em um hash seguro antes de salvar no banco. Isso é importante porque a senha original não fica armazenada. Mesmo quem acessa a tabela não vê a senha digitada pela pessoa.

## Como funciona o login

1. A pessoa informa e-mail e senha.
2. O sistema busca o usuário usando somente o e-mail.
3. O banco devolve o hash de senha daquele usuário, se ele existir.
4. O PHP usa `password_verify()` para comparar a senha digitada com o hash.
5. Se a validação for correta, uma sessão é criada e o dashboard é liberado.

### `password_verify()`

`password_verify()` recebe a senha digitada e o hash salvo no banco. Ela informa se os dois correspondem, sem precisar salvar ou comparar a senha em texto puro no SQL.

## Fluxo de autenticação

```text
Cadastro → password_hash() → usuário salvo no banco
→ login busca usuário pelo e-mail → password_verify() confere a senha
→ sessão é criada → dashboard é liberado → logout destrói a sessão
```

## Como funcionam as sessões

Depois de um login válido, o sistema guarda estes dados em `$_SESSION`:

- `usuario_id`: identifica o registro do usuário.
- `usuario_nome`: permite personalizar o dashboard.
- `usuario_email`: mostra o e-mail cadastrado quando necessário.

O `dashboard.php` verifica se esses dados existem antes de mostrar a página. Caso não existam, redireciona para o login. Logo após a autenticação, `session_regenerate_id(true)` cria um novo identificador de sessão, reduzindo o risco de reutilização de um ID antigo. No logout, a sessão e seu cookie técnico são removidos.

## Consulta de dados no Dashboard

Além da saudação com o nome guardado na sessão, o dashboard usa `$_SESSION['usuario_email']` para executar uma consulta preparada no banco:

```sql
SELECT nome, email FROM usuario WHERE email = :email
```

O resultado aparece na seção “Seus dados”. Assim, a atividade demonstra as duas partes: a sessão identifica quem está logado e o `SELECT` recupera os dados atuais desse usuário no banco.

## Consulta de usuários

A página `usuarios.php` é acessível apenas com uma sessão válida. Ela executa uma consulta preparada para listar `id`, `nome` e `email` da tabela `usuario`. Senhas e hashes não aparecem nessa listagem.

## Consulta de produtos

A página pública `produtos.php` executa uma consulta preparada para recuperar `id`, `nome`, `preco` e `estoque` da tabela `produto`. Os produtos são apresentados em cards responsivos. O botão “Comprar” é apenas visual e não cria carrinho, pedido ou checkout.

## Cookie “Lembrar de mim”

Ao marcar “Lembrar meu e-mail neste dispositivo”, o sistema guarda apenas o e-mail em um cookie por 30 dias. Na próxima visita à tela de login, o campo de e-mail aparece preenchido.

A senha **nunca** é salva no cookie. O cookie usa `HttpOnly` e `SameSite=Lax`. A opção `Secure` é ativada apenas quando a página está em HTTPS, para que o recurso continue funcionando no `localhost` com HTTP durante as aulas.

Ao sair, o cookie de e-mail é mantido de propósito: ele não autentica ninguém e serve apenas para preencher o campo na próxima visita. Se a pessoa entrar sem marcar a opção, esse cookie é removido.

## Principais recursos de UI/UX

- Identidade visual clara, com cartões, bordas arredondadas e sombras discretas.
- Paleta com azul como cor principal, bom contraste e fundos neutros.
- Rótulos associados aos campos; placeholders não substituem os labels.
- Estados visuais de foco, erro, sucesso, hover e botão desabilitado.
- Mensagens de feedback próximas ao formulário, sem uso de `alert()`.
- Botões para mostrar e esconder senha.
- Estrutura HTML semântica e foco visível para navegação por teclado.

## Responsividade

O CSS foi pensado primeiro para telas pequenas. Login e cadastro ocupam bem a largura do celular e ganham mais espaço em telas maiores. No dashboard, o menu fica no topo em celular e se transforma em uma sidebar lateral a partir de telas de tablet/desktop.

## Boas práticas utilizadas

- Consultas preparadas com PDO e parâmetros nomeados.
- Senhas protegidas com `password_hash()` e `password_verify()`.
- Validação obrigatória no servidor, mesmo com atributos HTML nos campos.
- Escape de dados exibidos com `htmlspecialchars()`.
- Redirecionamentos acompanhados de `exit`.
- Mensagens técnicas de banco registradas no log do PHP, sem serem expostas na tela.
- Sem frameworks: somente PHP, HTML, CSS, JavaScript simples e MySQL.

## Observações importantes

Este é um projeto didático. Os cards do dashboard são demonstrativos e não incluem edição de perfil ou configurações avançadas. A autenticação importante continua no PHP; o JavaScript é usado apenas para a interação de mostrar ou esconder senhas.
