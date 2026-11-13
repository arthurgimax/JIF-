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

O guia completo e oficial de instalação está na seção **15 — Instalação e execução** do arquivo [spec.md](./spec.md). Ele foi escrito para que outra pessoa consiga clonar o repositório e executar o sistema em um computador Windows com XAMPP.

Resumo dos comandos no PowerShell:

~~~powershell
Set-Location C:\xampp\htdocs
git clone https://github.com/arthurgimax/JIF-.git
Set-Location .\JIF-
Copy-Item .env.example .env
# Edite .env com os dados do MySQL local
# Inicie Apache e MySQL no XAMPP Control Panel
& "C:\xampp\php\php.exe" .\database\setup.php
~~~

Depois, abra http://localhost/JIF-/ no navegador. O instalador cria o banco jif_plus, verifica as 10 tabelas e insere dados de demonstração somente quando ainda não há usuários. Não há dependências Composer ou npm. Para criar o primeiro administrador de teste, consulte a seção seguinte e o passo a passo detalhado da spec.


## 🔐 Primeiro administrador e contas de demonstração

As contas técnicas presentes no `seed.sql` ficam **inativas** e não possuem senhas de demonstração publicadas. O cadastro público cria somente usuários com perfil `atleta`; um administrador deve conceder os perfis `treinador` e `organizador` pela área administrativa.

Para criar o primeiro administrador em uma instalação nova:

1. Gere um hash bcrypt no PowerShell do Windows. O script temporário evita conflito de aspas e não coloca a senha na linha de comando:
   ```powershell
   $script = Join-Path $env:TEMP 'jif-hash.php'
   @'
   <?php
   echo password_hash(getenv('JIF_ADMIN_PASSWORD'), PASSWORD_DEFAULT), PHP_EOL;
   '@ | Set-Content -Path $script -Encoding ASCII

   $senha = Read-Host "Digite a senha do administrador" -AsSecureString
   $bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($senha)
   try {
       $env:JIF_ADMIN_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
       & "C:\xampp\php\php.exe" $script
   }
   finally {
       Remove-Item Env:JIF_ADMIN_PASSWORD -ErrorAction SilentlyContinue
       [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
       Remove-Item $script -ErrorAction SilentlyContinue
   }
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
O GitHub Actions concluiu com sucesso em 13/11/2026: verificação de sintaxe, conexão/tabelas, autenticação/RBAC e **17 de 17 critérios CA01–CA17 aprovados**, usando PHP 8.4 e MySQL 8.0.46. [Ver execução registrada](https://github.com/arthurgimax/JIF-/actions/runs/43461490652). A instalação em um computador limpo ainda deve ser conferida seguindo a seção 15 da spec.

---

## 🔗 Links para avaliação

- **Sistema publicado:** https://jifplus-ifro.free.nf
- **Repositório e histórico de commits:** https://github.com/arthurgimax/JIF-
- **Spec final:** [`spec.md`](./spec.md)
- **Changelog:** [`CHANGELOG.md`](./CHANGELOG.md)

## 🌐 Publicação atual — InfinityFree

O JIF+ foi publicado no InfinityFree para esta atividade, mantendo a stack original **PHP + PDO + MySQL**.

- **Domínio HTTPS:** [https://jifplus-ifro.free.nf](https://jifplus-ifro.free.nf)
- **Pasta de publicação:** `/htdocs`
- **Banco:** MySQL do InfinityFree, com as 10 tabelas previstas.
- **Configuração:** credenciais guardadas no `.env` do servidor; o arquivo não deve ser enviado ao GitHub.
- **Fluxos verificados manualmente:** página inicial aberta em janela anônima; login de administrador concluído; cadastro de um atleta fictício no painel e confirmação do registro na listagem pública do BID.

> **HTTPS/TLS:** o responsável confirmou que, em janela anônima, o navegador passou a exibir conexão segura (HTTPS). O teste visual foi informado em 13/11/2026.

### Decisão sobre Vercel e Supabase

- **Vercel:** não é o destino direto desta aplicação PHP/Apache convencional; seria necessário reestruturar o backend para funções compatíveis ou migrar a aplicação.
- **Supabase:** fornece PostgreSQL e serviços de banco/autenticação, mas não hospeda o backend PHP. A troca do MySQL por PostgreSQL exigiria migração do esquema e revisão das consultas PDO.
- **Decisão desta entrega:** InfinityFree foi escolhido para preservar PHP + MySQL e evitar reescrita incompatível com o prazo da atividade. Render com Docker seria outra alternativa para PHP, mas exige configuração de servidor e banco persistente externo.

### Procedimento de publicação realizado

1. Criada a conta de hospedagem e o subdomínio no InfinityFree.
2. Criado o banco MySQL e conferidas as 10 tabelas no phpMyAdmin.
3. Criado um administrador próprio usando hash bcrypt; o login foi validado no ambiente publicado.
4. Enviados os arquivos da aplicação diretamente para `/htdocs`.
5. Criado o arquivo `.env` na raiz da hospedagem com `APP_ENV=production` e as variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` e `DB_CHARSET`. **Os valores reais não devem ser documentados nem versionados.**
6. Testados a página inicial, o painel administrativo e o fluxo de cadastro/consulta de atleta.

### Segurança e testes pendentes

- O `.gitignore` exclui `.env` e arquivos locais de banco; o `.htaccess` contém regras para negar acesso direto a arquivos de ambiente e pastas internas. Ainda é necessário confirmar externamente que essas URLs retornam bloqueio (403/404), sem exibir conteúdo.
- O responsável confirmou que desativou os cinco usuários demonstrativos adicionais no painel de produção, preservando a conta administrativa principal. O histórico público ainda contém credenciais fictícias de demonstração e um `SESSION_SECRET` de exemplo em commits antigos. Esses valores não são credenciais do banco de produção, mas continuam visíveis no histórico Git; para cumprir integralmente o requisito de não versionar credenciais, é necessária a limpeza do histórico.
- A suíte automatizada CA01–CA17 foi aprovada no GitHub Actions (17/17) em 13/11/2026; a instalação em máquina limpa e os itens pendentes do checklist de segurança ainda precisam de homologação.
- O checklist em [`tests/test_checklist.md`](./tests/test_checklist.md) registra o que foi verificado e as pendências: limpeza das credenciais fictícias no histórico Git, teste de permissões com perfil não administrador, validação completa dos formulários, proteção CSRF e limitação de tentativas de login.

---

## 👤 Autor

**Arthur Henrique Gimax Oliveira**  
Estudante do 3°A do Curso Técnico em Informática Integrado ao Ensino Médio  
Instituto Federal de Rondônia — Campus Porto Velho Calama  
Componente Curricular: Desenvolvimento de Sistemas | Professor: Rafael Pitwak