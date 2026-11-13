# JIF+ — Plataforma de Gestão e Inteligência Esportiva para os Jogos dos Institutos Federais

O **JIF+** é uma plataforma web completa desenvolvida para centralizar, organizar e acompanhar as competições esportivas dos Institutos Federais, unificando em um único ambiente digital informações sobre instituições, campi, competições, modalidades, equipes, atletas, partidas, súmulas eletrônicas, resultados e painéis de inteligência esportiva.

O projeto foi construído em estrito cumprimento à especificação oficial ([`spec.md`](./spec.md)) e às diretrizes metodológicas da apostila *"Programar com IA de forma responsável"* do Professor Rafael Pitwak, no âmbito da disciplina de Desenvolvimento de Sistemas do **IFRO — Campus Porto Velho Calama**.

---

## 🏆 Funcionalidades Principais

1. **Autenticação & Controle de Acesso (RBAC)**:
   - Login seguro e autocadastro com senhas protegidas por **hash bcrypt**.
   - 5 Perfis de usuários com restrição de rotas no servidor: *Administrador Geral*, *Coordenador/Organizador*, *Treinador/Professor*, *Estudante-Atleta* e *Visitante/Público*.
2. **Gestão de Instituições e Campi**:
   - Cadastro e vinculação dos campi participantes (Porto Velho Calama, Ariquemes, Ji-Paraná, Cacoal, Vilhena, Guajará-Mirim).
3. **Gestão de Competições & Modalidades**:
   - Organização de torneios oficiais (JIFRO, JIF Regional Norte), fases de campeonato, modalidades coletivas e individuais nas categorias Masculino, Feminino e Misto.
4. **Gerenciamento de Equipes & Elencos**:
   - Inscrição de equipes por campus e modalidade, comissão técnica e prevenção de inscrições duplicadas.
5. **Boletim Informativo do Atleta (BID)**:
   - Perfil esportivo individual de cada estudante-atleta contendo histórico de partidas, número da camisa, posição, fotos e totalizadores de gols, pontos, cartões e faltas.
6. **Programação & Calendário de Partidas**:
   - Agendamento de confrontos por data, hora, quadra/local e fase do torneio.
7. **Súmula Eletrônica & Homologação**:
   - Lançamento oficial de placares finais, registro de gols por atleta, faltas, cartões amarelos e vermelhos, e encerramento homologado.
8. **Classificação Automática em Tempo Real**:
   - Tabela oficial gerada automaticamente com critérios de desempate: Pontos (3 p/ vitória, 1 p/ empate), Jogos, Vitórias, Empates, Derrotas, Gols Pró, Gols Contra, Saldo e Aproveitamento percentual.
9. **Painéis de Inteligência Esportiva**:
   - Ranking de Artilharia (Chuteira de Ouro), Eficiência Ofensiva por Campus e Troféu Fair Play (índice disciplinar de cartões).

---

## 🛠️ Stack Tecnológica

- **Backend**: **PHP 8.2+** estruturado e modular.
- **Banco de Dados**: **MySQL 8.0 / MariaDB (InnoDB)** com integridade referencial estrita e transações ACID.
- **Camada de Persistência**: **PDO (PHP Data Objects)** com prepared statements para consultas que recebem valores externos; consultas estáticas usam SQL fixo.
- **Frontend**: **HTML5 semântico, CSS3 moderno** (design esportivo responsivo para computadores, tablets e smartphones) e **JavaScript nativo (ES6+)**.
- **Segurança**: Senhas com `password_hash()` bcrypt, cookies `HttpOnly`, sanitização contra XSS e isolamento de segredos em variáveis de ambiente (`.env`).
- **Versionamento**: **Git** e **GitHub** com commits semânticos por funcionalidade.

---

## 📁 Estrutura de Arquivos e Pastas

```text
4°Bim - Arthur Gimax - JIF+/
├── .env.example              <- Modelo de variáveis de ambiente sem credenciais reais
├── .gitignore                <- Exclusão de arquivos sensíveis (.env) e temporários
├── README.md                 <- Documentação completa do sistema
├── CHANGELOG.md              <- Histórico de alterações e revisões humanas
├── spec.md                   <- Especificação oficial dos requisitos do JIF+
├── database/
│   ├── schema.sql            <- DDL oficial com a criação das 10 tabelas relacionais
│   ├── seed.sql              <- Carga de dados realistas de demonstração (IFRO e JIFRO)
│   └── setup.php             <- Script de inicialização automática do banco de dados
├── config/
│   ├── database.php          <- Conexão segura PDO singleton com suporte a .env e fallback
│   └── auth.php              <- Autenticação segura, hash bcrypt e controle RBAC de perfis
├── includes/
│   ├── header.php            <- Cabeçalho padrão, navbar esportiva com controle de perfil
│   ├── footer.php            <- Rodapé institucional com links e créditos
│   └── alerts.php            <- Sistema visual de mensagens de feedback (sucesso/erro/aviso)
├── assets/
│   ├── css/
│   │   └── style.css         <- Sistema de design esportivo responsivo (identidade visual IFRO)
│   └── js/
│       └── app.js            <- Filtros dinâmicos e confirmações de ações sensíveis
├── index.php                 <- Home pública: placares ao vivo, próximos jogos e artilharia
├── login.php                 <- Tela de autenticação com validação no backend
├── cadastro.php              <- Cadastro público de usuários com perfil atleta/visitante
├── logout.php                <- Encerramento seguro de sessão
├── competicoes.php           <- Listagem e detalhes das competições esportivas
├── modalidades.php           <- Consulta de esportes oficiais e categorias
├── equipes.php               <- Visualização de equipes, comissão técnica e elencos
├── atletas.php               <- Boletim Informativo do Atleta (BID) e histórico esportivo
├── partidas.php              <- Calendário oficial de jogos e visualização de súmulas
├── classificacao.php         <- Tabelas de classificação com cálculo automático
├── inteligencia.php          <- Painel de inteligência esportiva e Troféu Fair Play
├── admin/                    <- Módulo administrativo restrito (RBAC)
│   ├── index.php             <- Dashboard geral com contadores e atalhos operacionais
│   ├── instituicoes.php      <- Gestão e cadastro de campi dos Institutos Federais
│   ├── competicoes.php       <- Criação de torneios e alteração rápida de status
│   ├── modalidades.php       <- Configuração de modalidades por campeonato
│   ├── equipes.php           <- Inscrição e gerenciamento de equipes
│   ├── atletas.php           <- Inscrição de atletas em equipes com número e posição
│   ├── partidas.php          <- Agendamento de confrontos oficiais
│   ├── sumula.php            <- Lançamento de placares, gols, cartões e homologação
│   └── usuarios.php          <- Gestão de usuários e permissões (exclusivo Administrador)
└── tests/
    ├── test_db_connection.php<- Teste de conexão e leitura das 10 tabelas
    ├── test_auth.php         <- Teste de autenticação, senhas com hash e RBAC
    ├── test_full_suite.php   <- Suíte completa validando critérios CA01 a CA17
    └── test_checklist.md     <- Status e pendências do checklist do Anexo B
```

---

## 🚀 Como Executar o Projeto Localmente

### Pré-requisitos:
- PHP 8.2 ou superior instalado (disponível no XAMPP em `C:\xampp\php\php.exe`).
- Servidor web Apache (XAMPP) ou o servidor embutido do PHP.
- MySQL ou MariaDB para a instalação principal no XAMPP; SQLite é uma contingência local quando o driver PDO_SQLite estiver disponível.

### Passo a Passo:

1. **Configurar as Variáveis de Ambiente**:
   Copie o arquivo `.env.example` para `.env`:
   ```bash
   cp .env.example .env
   ```
   *(As configurações padrão já estão pré-configuradas para o ambiente local do XAMPP).*

2. **Inicializar o Banco de Dados (Schema + Seed local)**:
   Execute o script somente pelo terminal (o acesso via navegador é bloqueado por segurança):
   ```bash
   C:\xampp\php\php.exe database/setup.php
   ```
   O schema usa `CREATE TABLE IF NOT EXISTS` e não apaga tabelas/dados existentes. O seed é inserido apenas quando o banco ainda não contém usuários.

3. **Criar o primeiro administrador**: siga a seção “Primeiro administrador e contas de demonstração” abaixo. As contas de demonstração do seed são inativas.

4. **Iniciar a Aplicação**:
   - **Pelo XAMPP**: Coloque a pasta em `c:\xampp\htdocs\`, inicie o Apache no painel de controle e acesse:
     `http://localhost/4°Bim%20-%20Arthur%20Gimax%20-%20JIF+/`
   - **Pelo Servidor Embutido do PHP**:
     Execute no terminal dentro da pasta do projeto:
     ```bash
     C:\xampp\php\php.exe -S localhost:8000
     ```
     E abra no navegador: `http://localhost:8000`.

---

## 🔐 Primeiro administrador e contas de demonstração

As contas técnicas presentes no `seed.sql` ficam **inativas** e não possuem senhas de demonstração publicadas. O cadastro público cria somente usuários com perfil `atleta`; um administrador deve conceder os perfis `treinador` e `organizador` pela área administrativa.

Para criar o primeiro administrador em uma instalação nova:

1. Gere um hash bcrypt no terminal do XAMPP, digitando a senha quando solicitado:
   ```powershell
   C:\xampp\php\php.exe -r '$p = trim(fgets(STDIN)); echo password_hash($p, PASSWORD_BCRYPT), PHP_EOL;'
   ```
2. Copie o hash gerado e, no phpMyAdmin, execute o comando abaixo substituindo nome, e-mail e `HASH_BCRYPT_GERADO`:
   ```sql
   INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo)
   VALUES ('Administrador JIF+', 'seu-email@exemplo.com', 'HASH_BCRYPT_GERADO', 'admin', 1);
   ```
3. Apague o hash da área de transferência e nunca publique a senha nem o hash em arquivos do repositório.

Os testes automatizados criam contas temporárias com credenciais aleatórias e desfazem os registros ao terminar.

O arquivo `database/jif_plus.sqlite`, se existir, é um banco local de contingência e pode conter contas e registros pessoais/testes. Ele é ignorado pelo Git e não deve ser publicado. Para produção, use o banco MySQL criado no provedor; migre os dados locais somente se forem necessários e após revisar/anonimizar informações pessoais.

---



## 🧪 Execução dos Testes Automatizados

Para rodar a suíte automatizada dos 17 critérios de aceite (requer PDO MySQL ou PDO SQLite e um banco com as 10 tabelas):
```bash
C:\xampp\php\php.exe tests/test_full_suite.php
```
A suíte está disponível, mas o resultado precisa ser reexecutado no ambiente de entrega com as extensões PDO correspondentes ao banco configurado. Não considere os testes aprovados até registrar a saída real da execução.

---

## 🌐 Publicação gratuita — decisão técnica

O JIF+ é uma aplicação PHP tradicional com PDO e esquema MySQL/MariaDB. **Vercel não é o destino direto adequado para este código PHP/Apache**. O Supabase oferece PostgreSQL, mas não hospeda o PHP e exigiria migrar o esquema MySQL e revisar consultas. Para preservar o projeto e evitar reescrita, a decisão recomendada para esta atividade é uma hospedagem PHP + MySQL, como o plano gratuito do InfinityFree.

Antes de criar a conta, confirme no painel do provedor que a versão PHP disponível é 8.2 ou superior e confira os limites do plano gratuito. O serviço não deve ser tratado como hospedagem de produção crítica.

### Passo a passo resumido de publicação

1. Crie uma conta no provedor e um subdomínio gratuito. Guarde os dados do painel e FTP em local privado.
2. No painel da hospedagem, crie um banco MySQL e anote exatamente o host, nome do banco, usuário e senha apresentados pelo provedor.
3. Abra o phpMyAdmin do banco criado, selecione o banco e importe **somente `database/schema.sql`**. O schema é não destrutivo e cria as 10 tabelas.
4. Para deixar o sistema demonstrável, importe também `database/seed.sql`: ele contém apenas dados fictícios e as contas de demonstração estão inativas. Depois, crie seu próprio administrador usando um hash bcrypt gerado localmente (instruções acima).
5. Envie os arquivos do projeto para a pasta `htdocs` via FTP/File Manager. Não envie `.git/`, `.env`, `config.local.php`, o banco SQLite local, `tests/` nem a apostila PDF.
6. Configure no servidor um arquivo `.env` privado na raiz da aplicação com `APP_ENV=production`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` e `DB_CHARSET=utf8mb4`. Use os valores reais fornecidos pelo painel; não copie placeholders deste README.
7. Ative/solicite SSL no painel do provedor e confirme que a aplicação abre por `https://`.
8. Teste em janela anônima: cadastro público de atleta, login, acesso administrativo com a conta criada, cadastro de uma instituição/competição e consulta pública dos dados.
9. Depois dos testes, confira o histórico do GitHub e abra a URL publicada em outro dispositivo ou navegador sem sessão autenticada.

O banco SQLite `database/jif_plus.sqlite`, quando existente, é um arquivo de desenvolvimento local e pode conter contas e registros pessoais. Ele não deve ser enviado ao GitHub nem copiado para a hospedagem pública; a migração dos dados locais para MySQL deve ser feita separadamente e somente se esses registros forem necessários.

O arquivo `.htaccess` bloqueia acesso HTTP às pastas internas (`config`, `database`, `tests`, `includes`) e a arquivos de configuração/SQL/banco. O instalador `database/setup.php` só pode ser executado por CLI e não deve ser enviado à hospedagem pública.

**Status:** a publicação não foi executada nesta cópia. A URL pública só será informada depois que o domínio for criado, o banco configurado e o fluxo principal testado.

---

## 👤 Autor

**Arthur Henrique Gimax Oliveira**  
Estudante do 3°A do Curso Técnico em Informática Integrado ao Ensino Médio  
Instituto Federal de Rondônia — Campus Porto Velho Calama  
Componente Curricular: Desenvolvimento de Sistemas | Professor: Rafael Pitwak