# AedesZero

Sistema web para denúncia de focos do mosquito *Aedes aegypti* (dengue, chikungunya e zika). O público envia denúncias com foto opcional e a equipe acompanha tudo em um painel administrativo protegido por login.

Feito em **PHP procedural** (apenas funções, sem POO), **PDO** e **MySQL/MariaDB**.

## Funcionalidades

**Área pública**
- Formulário de denúncia com validação no servidor (bairro, endereço, tipo de foco e descrição).
- Upload opcional de imagem JPG, PNG ou WEBP, até 2 MB, com verificação do tipo real do arquivo e nome aleatório.

**Área administrativa**
- Login com `password_verify`, regeneração de sessão e proteção das páginas internas.
- Painel de denúncias: consulta dos registros e alteração de status (pendente, em andamento, resolvida).
- Gerenciamento de administradores: criar novas contas (usuário e senha) e apagar contas existentes.
- Token CSRF em todos os formulários do painel.

**Segurança**
- Consultas preparadas (PDO) contra SQL Injection.
- Saídas escapadas com `htmlspecialchars` contra XSS.
- Senhas armazenadas apenas como hash (`password_hash`).
- Não é possível apagar a própria conta nem o único administrador existente.

## Requisitos

- PHP 8.1 ou superior, com as extensões `pdo_mysql`, `fileinfo` e `mbstring`
- MySQL ou MariaDB
- Apache (por exemplo, XAMPP)

## Estrutura do projeto

```
AedesZero/
├── index.php                 # redireciona para public/index.php (hospedagens que abrem a raiz)
├── config/
│   ├── config.php            # dados de conexão com o banco
│   └── database.php          # função conectar_banco() (PDO)
├── database/
│   ├── aedesZero.sql         # estrutura do banco
│   └── criar_admin.php       # cria um administrador pelo terminal
├── includes/
│   ├── auth.php              # login, sessão e logout
│   ├── administradores.php   # listar, criar e apagar administradores
│   ├── denuncias.php         # acesso aos dados das denúncias
│   └── validacao.php         # validação e upload das denúncias
└── public/
    ├── index.php             # página inicial
    ├── form.php              # formulário de denúncia
    ├── css/                  # index.css e form.css
    ├── uploads/              # imagens enviadas (precisa de permissão de escrita)
    └── admin/
        ├── login.php
        ├── logout.php
        ├── painel.php             # denúncias e status
        └── administradores.php    # criar e apagar administradores
```

## Instalação

### 1. Copiar o projeto
Copie a pasta `AedesZero` para a pasta pública do servidor, por exemplo `/opt/lampp/htdocs/AedesZero` ou `/var/www/html/AedesZero`.

### 2. Criar o banco
No terminal, dentro da pasta do projeto:

```bash
mysql -u root -p < database/aedesZero.sql
```

Se o `root` do seu MySQL não tem senha, use `mysql -u root < database/aedesZero.sql`. Também é possível importar o arquivo `database/aedesZero.sql` pelo phpMyAdmin.

### 3. Configurar a conexão
Edite `config/config.php` com host, porta, nome do banco, usuário e senha do seu MySQL. O padrão é `root` com senha vazia, comum em instalações locais. Se a sua senha for `1234`, por exemplo, altere `$password` para `'1234'`.

### 4. Permissões
O servidor precisa de permissão de escrita em `public/uploads/` para guardar as imagens.

### 5. Acessar
Abra `http://localhost/AedesZero/` no navegador. O `index.php` da raiz redireciona para `public/`, o que também permite publicar em hospedagens que abrem direto a raiz (como o `htdocs` do InfinityFree).

## Acesso administrativo

Entre em `public/admin/login.php` (ou pelo botão "Área administrativa" na página inicial).

O banco de exemplo já traz uma conta inicial:

| Usuário | Senha        |
|---------|--------------|
| `root`  | `1234567890` |

> **Importante:** essa conta serve apenas para o primeiro acesso em ambiente local. Antes de publicar na internet, crie um administrador com senha forte e apague a conta `root`.

### Criar e apagar administradores

Depois de entrar, clique em **Administradores** no topo do painel:

- **Criar:** informe o nome de usuário (3 a 80 caracteres) e a senha (mínimo de 10 caracteres) e clique em *Criar administrador*.
- **Apagar:** clique em *Apagar* na linha da conta e digite a senha dessa conta para confirmar. Sua própria conta e o último administrador restante não podem ser apagados.

Também é possível criar um administrador pelo terminal:

```bash
php database/criar_admin.php USUARIO SENHA
```

## Publicação na internet

- Use um usuário MySQL próprio, com permissões limitadas ao banco do projeto.
- Ative HTTPS.
- Troque a senha do administrador inicial (ou apague a conta `root`).
- Mantenha uma rotina de backup do banco e da pasta `public/uploads/`.
