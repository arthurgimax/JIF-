# Publicação do JIF+ em hospedagem PHP + MySQL (guia)

> Este guia prepara a publicação sem migrar o projeto para Node.js. O provedor e a URL final ainda precisam ser criados pelo proprietário da conta.

## 1. Criar a hospedagem

1. Acesse [InfinityFree](https://www.infinityfree.com/) e crie uma conta.
2. Confirme o e-mail e entre na área do cliente.
3. Crie uma conta de hospedagem e escolha um subdomínio gratuito disponível.
4. Abra o painel da conta e anote em local privado os dados de FTP e o endereço do site.

## 2. Criar o banco MySQL

1. No painel, abra **MySQL Databases**.
2. Crie um banco de dados.
3. Anote exatamente o host MySQL, nome do banco, usuário e senha fornecidos pelo painel. Não use `localhost` se o painel informar outro host.
4. Abra o **phpMyAdmin** do banco criado.
5. Selecione o banco e use **Importar** para enviar `database/schema.sql`.
6. Confirme que as dez tabelas foram criadas.
7. Para uma apresentação com dados, importe também `database/seed.sql`: ele contém dados fictícios e as contas de seed ficam desativadas. Em seguida, crie seu próprio administrador.

## 3. Criar o primeiro administrador

No computador local, abra o PowerShell e gere um hash sem colocar a senha na linha de comando:

```powershell
C:\xampp\php\php.exe -r '$p = trim(fgets(STDIN)); echo password_hash($p, PASSWORD_BCRYPT), PHP_EOL;'
```

Digite uma senha forte e copie o hash gerado. No phpMyAdmin, na aba SQL, execute substituindo os três valores:

```sql
INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo)
VALUES ('Administrador JIF+', 'SEU_EMAIL', 'HASH_BCRYPT_GERADO', 'admin', 1);
```

## 4. Enviar os arquivos

1. Conecte ao FTP com FileZilla ou use o File Manager do provedor.
2. Abra a pasta `htdocs`.
3. Envie o código PHP, `assets/`, `admin/` e `includes/`.
4. Não envie `.git/`, `.env` local, `config.local.php`, `database/jif_plus.sqlite`, `tests/` nem a apostila PDF.
5. O arquivo `.htaccess` deve ficar na raiz de `htdocs`.

## 5. Configurar variáveis de produção

Crie um arquivo `.env` na raiz de `htdocs` com os dados reais do painel:

```dotenv
APP_ENV=production
APP_NAME="JIF+ Gestão e Inteligência Esportiva"
APP_URL=https://SEU_SUBDOMINIO
DB_HOST=HOST_MYSQL_DO_PAINEL
DB_PORT=3306
DB_NAME=NOME_DO_BANCO_DO_PAINEL
DB_USER=USUARIO_MYSQL_DO_PAINEL
DB_PASS=SENHA_MYSQL_DO_PAINEL
DB_CHARSET=utf8mb4
```

Não copie esses placeholders literalmente. O arquivo `.env` não deve ser enviado ao GitHub. O `.htaccess` bloqueia acesso HTTP a arquivos `.env`.

## 6. Ativar HTTPS e testar

1. Ative ou solicite SSL no painel do provedor, conforme a opção disponível para o subdomínio.
2. Acesse `https://SEU_SUBDOMINIO`.
3. Teste em janela anônima: cadastro de atleta, login, logout, login administrativo, cadastro de instituição, competição e consulta pública.
4. Confirme que um usuário atleta não consegue abrir as rotas administrativas.
5. Peça a outra pessoa para abrir o link sem estar logada no painel da hospedagem.

## 7. Limitações e retorno

- A hospedagem gratuita pode ter limites de uso, recursos, suporte e disponibilidade; monitore o site antes da apresentação.
- A conexão MySQL gratuita do provedor pode aceitar acesso apenas a partir do próprio servidor web; por isso, importe o schema pelo phpMyAdmin.
- A publicação só está concluída depois que os testes acima passarem e a URL HTTPS estiver acessível externamente.