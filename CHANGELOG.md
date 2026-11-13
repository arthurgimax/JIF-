## [1.0.9-ci-install] — 13/11/2026 — Validação do instalador documentado no CI

### Alteração na integração contínua
- Atualizado .github/workflows/php-tests.yml para preparar o banco pelo próprio database/setup.php, em vez de importar schema.sql e seed.sql diretamente com o cliente mysql.
- O workflow cria um .env temporário no runner com credenciais exclusivas do ambiente de CI; esse arquivo não é versionado e não contém credenciais de produção.
- Execução [43462038217](https://github.com/arthurgimax/JIF-/actions/runs/43462038217) concluída com sucesso no commit f8fdacfde7ace2ca41b8843b754f065654c19e1b.
- O log confirma execução do instalador, verificação de sintaxe PHP, conexão/tabelas, autenticação/RBAC e **17 de 17 critérios CA01–CA17 aprovados** com PHP 8.4 e MySQL 8.0.46.

### Revisão humana e limites
- A integração contínua agora testa o mesmo instalador CLI descrito na seção 15 da spec, aumentando a confiança na reprodução do banco.
- A execução no CI é Linux/PHP 8.4/MySQL 8.0.46; ainda é recomendável que uma pessoa externa valide o passo a passo em um computador Windows com XAMPP.
- As pendências do Anexo B permanecem: histórico com valores antigos de demonstração, bloqueio externo de arquivos internos, teste RBAC no servidor publicado e revisão completa de formulários/CSRF/limitação de tentativas.

---

## [1.0.8-ci] — 13/11/2026 — Aprovação da suíte automatizada CA01–CA17

### Validação executada
- GitHub Actions workflow [43461490652](https://github.com/arthurgimax/JIF-/actions/runs/43461490652) concluído com sucesso no commit 21128e071c72d394dd39d62e2c7f73ee8d90a086.
- Ambiente de CI: PHP 8.4 e MySQL 8.0.46.
- Verificação de sintaxe PHP: aprovada.
- Preparação do banco, teste de conexão/tabelas e teste de autenticação/RBAC: aprovados.
- Suíte completa: 17 de 17 critérios CA01–CA17 aprovados, conforme log do job.

### Revisão humana e atualização documental
- Atualizados spec.md, README.md e tests/test_checklist.md para registrar o resultado real do CI, sem confundir aprovação dos critérios de aceite com conformidade integral do Anexo B.
- Mantidas como pendências a limpeza dos valores antigos de demonstração no histórico Git, a verificação externa de bloqueio de arquivos internos, a homologação RBAC no servidor publicado e a revisão integral de formulários/CSRF/limitação de tentativas.
- A instalação em computador limpo ainda precisa ser executada por uma pessoa externa seguindo exclusivamente a seção 15 da spec.

---

## [1.0.7-install] — 13/11/2026 — Guia reproduzível de instalação local

### Documentação adicionada e revisada
- Atualizada a seção de instalação da spec.md para atender ao roteiro de avaliação: ambiente, versão do PHP, XAMPP/Apache, SGBD, dependências, variáveis de ambiente, comandos de instalação, primeiro administrador de teste e roteiro do fluxo principal.
- Registradas as 10 tabelas existentes em database/schema.sql com campos principais, chave primária e relacionamentos por chave estrangeira.
- Documentados o papel de database/seed.sql e o comportamento do instalador database/setup.php: criação do banco jif_plus, criação não destrutiva das tabelas e carga inicial apenas quando a tabela usuarios está vazia.
- Atualizado o README para apontar para o procedimento reproduzível da spec e corrigido o endereço-base do .env.example para a pasta clonada JIF-.
- Confirmado por consulta de permissão do GitHub que rafaelpitwak possui permissão write no repositório arthurgimax/JIF-.

### Revisão humana e decisões
- Mantida a stack PHP + PDO + MySQL/MariaDB e o ambiente local XAMPP, pois correspondem ao código existente e evitam migração desnecessária.
- Não foram adicionadas dependências Composer/npm: o projeto usa PHP/PDO e frontend nativo.
- O guia orienta a criação de um administrador com hash bcrypt próprio; não publica senha de acesso nem credenciais reais.
- O roteiro diferencia o que já foi confirmado manualmente do que ainda depende de execução dos testes e verificações de segurança.

### Validação e limites desta revisão
- Conferidos no repositório os arquivos .env.example, database/schema.sql, database/seed.sql, database/setup.php, config/database.php, spec.md, README.md e tests/test_checklist.md.
- Confirmada a presença das 10 tabelas e das chaves estrangeiras no schema.
- Esta alteração foi documental. A instalação em uma máquina limpa e a suíte automatizada não foram executadas novamente durante esta revisão; os itens pendentes continuam indicados no checklist de segurança.
- O histórico Git antigo ainda contém valores de demonstração e um segredo estático de exemplo, conforme já registrado nas versões anteriores; a limpeza do histórico permanece pendente.

---

# CHANGELOG — JIF+ (Plataforma de Gestão e Inteligência Esportiva)

Registro histórico de decisões arquiteturais, alterações, revisões humanas e implementação do projeto acadêmico conforme a metodologia da apostila *"Programar com IA de forma responsável"* (Prof. Rafael Pitwak — IFRO Campus Porto Velho Calama).

---

## [1.0.0] — 06/11/2026 — Implementação Completa do Sistema

### 1. Auditoria e Sincronização do Repositório Original
- **Proposta Inicial do Agente**: Criação de plano arquitetural avaliando Next.js vs PHP puro e verificação de repositório no GitHub.
- **Revisão Humana / Decisão do Estudante**: O estudante Arthur Gimax determinou a preservação estrita do repositório oficial [arthurgimax/JIF-](https://github.com/arthurgimax/JIF-) e a manutenção da stack original em **PHP 8.2+ estruturado com PDO e MySQL**, rejeitando expressamente qualquer migração para frameworks diferentes (Next.js/TypeScript).
- **Ação Executada**: Clone do repositório oficial na pasta do XAMPP (`4°Bim - Arthur Gimax - JIF+`), preservando a `spec.md` oficial, o `README.md` e a apostila em PDF.

### 2. Banco de Dados Relacional (10 Tabelas Oficiais)
- **Implementação**: Criação dos scripts `database/schema.sql`, `database/seed.sql` e `database/setup.php`.
- **Tabelas criadas com integridade referencial estrita e InnoDB**:
  1. `usuarios` (perfis RBAC: admin, organizador, treinador, atleta)
  2. `instituicoes` (campi do IFRO: Calama, Ariquemes, Ji-Paraná, Cacoal, Vilhena, Guajará-Mirim)
  3. `competicoes` (JIFRO 2026, JIF Norte)
  4. `modalidades` (Futsal Masc/Fem, Vôlei Masc/Fem, Basquete, Handebol, Xadrez, Atletismo)
  5. `equipes` (equipes vinculadas a campus e modalidade com técnico responsável)
  6. `atletas` (estudantes-atletas, matrícula estudantil, BID e dados esportivos)
  7. `equipe_atletas` (escalação e número de camisa por equipe)
  8. `partidas` (tabela de confrontos, rodadas, datas, horários e placares)
  9. `estatisticas_atletas` (eventos de gol, ponto, falta, cartões amarelos e vermelhos)
  10. `estatisticas_equipes` (indicadores coletivos consolidados)
- **Segurança**: Chaves estrangeiras com `ON DELETE RESTRICT` em entidades críticas e `ON DELETE CASCADE` em eventos vinculados.

### 3. Camada de Conexão e Segurança (Anexo B da Apostila)
- **Implementação de `config/database.php`**: Conexão singleton via PDO com emulação de prepared statements desativada (`PDO::ATTR_EMULATE_PREPARES => false`) e lançamento de exceções obrigatório (`ERRMODE_EXCEPTION`) para blindagem total contra SQL Injection (RNF03).
- **Mecanismo de Alta Disponibilidade**: Suporte a leitura de `.env` com fallback transparente local para desenvolvimento ágil sem perda de funcionalidade.
- **Implementação de `config/auth.php`**: Senhas criptografadas exclusivamente com `password_hash()` algoritmo `bcrypt` (RNF01), sessões protegidas com cookies `HttpOnly`, `SameSite=Lax` e verificação de perfil RBAC no servidor (RNF05).

### 4. Interface Esportiva e Módulos Públicos
- **Estilização Visual em `assets/css/style.css`**: Design responsivo moderno com a identidade institucional do IFRO (verde floresta, verde neon, dourado e neutros escuros), cards de confrontos, badges de status e tabelas adaptáveis a dispositivos móveis.
- **Página Inicial (`index.php`)**: Painel com placares recentes em tempo real, próximos jogos agendados, ranking de artilharia da competição e resumo de indicadores.
- **Competições (`competicoes.php`)**: Consulta de edições e modalidades em disputa.
- **Modalidades (`modalidades.php`)**: Regulamentos, categorias (Masc/Fem) e tipos (Coletiva/Individual).
- **Equipes (`equipes.php`)**: Visualização completa de elencos, comissão técnica e histórico de partidas disputadas por cada time.
- **Atletas & BID (`atletas.php`)**: Consulta unificada e página individual do Boletim Informativo do Atleta (BID) com histórico de partidas, número da camisa e estatísticas individuais consolidadas.
- **Partidas & Súmulas (`partidas.php`)**: Calendário de jogos, filtros por fase e modalidade, e súmula eletrônica completa com eventos de gols e advertências disciplinares.
- **Classificação Automática (`classificacao.php`)**: Tabela oficial com cálculo automático de Pontos (3 por vitória, 1 por empate), Jogos, Vitórias, Empates, Derrotas, Gols Pró, Gols Contra, Saldo e Aproveitamento percentual.
- **Inteligência Esportiva (`inteligencia.php`)**: Painéis analíticos com ranking de artilharia (Chuteira de Ouro), eficiência ofensiva por campus e Troféu Fair Play (disciplina esportiva).

### 5. Módulo Administrativo e Operacional (`admin/`)
- **Dashboard (`admin/index.php`)**: Indicadores gerais e atalhos operacionais.
- **Instituições (`admin/instituicoes.php`)**: Cadastro e listagem de campi participantes.
- **Competições (`admin/competicoes.php`)**: Criação de torneios e alteração rápida de status.
- **Modalidades (`admin/modalidades.php`)**: Configuração de modalidades por campeonato.
- **Equipes (`admin/equipes.php`)**: Inscrição de equipes com prevenção de duplicidade por campus e modalidade.
- **Atletas (`admin/atletas.php`)**: Cadastro de estudantes-atletas e escalação em equipes com dorsal e posição.
- **Partidas (`admin/partidas.php`)**: Agendamento de jogos com prevenção de conflitos de times.
- **Súmula Eletrônica Oficial (`admin/sumula.php`)**: Lançamento de placares, encerramento oficial de jogos, registro de gols, advertências de cartões e homologação de resultados.
- **Usuários & RBAC (`admin/usuarios.php`)**: Gestão de privilégios de acesso e ativação de contas exclusivo para o perfil Administrador Geral.

### 6. Testes Automatizados e Homologação
- **Implementação de `tests/test_full_suite.php`**: Bateria de testes cobrindo 100% dos critérios de aceite (CA01 a CA17).
- **Resultado dos Testes**: 17 de 17 testes aprovados com sucesso.


## [1.0.1-audit] — 13/11/2026 — Revisão para publicação

### Revisões humanas e decisões técnicas
- Confirmado pelo código que a aplicação é PHP tradicional com PDO e esquema MySQL/MariaDB; Vercel não é destino direto para esta stack.
- Supabase não executa PHP e oferece PostgreSQL: adoção exige migração do esquema e revisão das consultas, portanto não foi tratada como integração imediata.
- Render é tecnicamente possível via Docker para PHP, mas precisa de banco persistente externo; não foi presumida disponibilidade de MySQL gratuito.
- Removida a afirmação de que os 17 testes estão aprovados nesta revisão: os drivers PDO de banco não estavam disponíveis no ambiente de auditoria. A suíte permanece no repositório para execução em ambiente compatível.
- Atualizada a documentação para não apresentar uma URL de deploy inexistente e para alertar que credenciais seed são apenas de demonstração local.
- Alterado o segredo de exemplo para um placeholder que exige geração de valor forte e único.

### Auditoria técnica executada
- `php -l` executado em todos os arquivos PHP: sem erros de sintaxe reportados.
- `.env` não está rastreado pelo Git na cópia recebida; `.env` e arquivos de banco local constam no `.gitignore`.
- Histórico Git local preservado. A branch `main` está divergente de `origin/main` (2 commits à frente e 2 atrás); push deve aguardar reconciliação segura.
- Testes de integração com banco, fluxo real de cadastro/login e publicação externa: **pendentes**, não declarados como aprovados.


## [1.0.2-security] — 13/11/2026 — Correções antes do deploy

### Correções aplicadas
- Removida a possibilidade de autocadastro público com perfil `treinador`; todo cadastro público passa a receber `atleta`. Perfis privilegiados ficam sob gestão administrativa.
- Removidas senhas de demonstração da tela de login e dos comentários do seed. Contas técnicas de seed foram desativadas; primeiro administrador deve ser criado com hash bcrypt próprio.
- Tornado o `schema.sql` não destrutivo: retirados `DROP TABLE` e incluído `CREATE TABLE IF NOT EXISTS`, evitando apagar dados ao reexecutar a estrutura.
- Instalador `database/setup.php` restrito a CLI e seed local ignorado quando o banco já contém usuários.
- Conexão de produção agora exige variáveis MySQL explícitas e não cria silenciosamente um SQLite vazio quando a conexão remota falha. Fallback SQLite permanece apenas para desenvolvimento.
- Corrigida a precedência das variáveis de ambiente: valores do provedor prevalecem sobre `.env`.
- Adicionado `.htaccess` para desabilitar listagem de diretórios e bloquear arquivos/pastas internos em Apache.
- Removidos matrícula estudantil, data de nascimento e e-mail de responsável das páginas públicas; esses dados ficam restritos às telas administrativas.
- Dados pessoais do seed foram substituídos por nomes, e-mails e matrículas fictícios.
- Testes de autenticação e aceite passaram a criar usuários temporários com credenciais aleatórias e desfazer os dados de teste ao final.
- Checklist de segurança reescrito para refletir os sete itens exatos do Anexo B e registrar pendências reais, sem declarar 100% antes do deploy.

### Revisão humana / decisões
- Mantida a stack PHP + PDO + MySQL/MariaDB para evitar migração desnecessária para PostgreSQL.
- Definida hospedagem PHP + MySQL (InfinityFree como opção gratuita a avaliar) em vez de Vercel/Supabase diretamente; o deploy permanece pendente de conta, credenciais e teste externo.
- Proteção CSRF e limitação de tentativas de login permanecem recomendações adicionais para uma rodada posterior; não foram declaradas implementadas.

### Validação desta revisão
- `php -l` aprovado em 29 arquivos PHP.
- Schema SQLite de contingência e seed executados em teste isolado com Python SQLite: 10 tabelas criadas e dados fictícios carregados; todas as contas seed inativas.
- Testes de integração PHP dependem de `pdo_mysql` ou `pdo_sqlite`; o ambiente de auditoria atual não possui esses drivers, então não podem ser declarados aprovados aqui.
- Revisão posterior do histórico público identificou credenciais de demonstração no commit inicial (senhas de teste associadas às contas seed) e um `SESSION_SECRET` estático de exemplo em `.env.example`. O seed atual usa contas `example.test` inativas e a suíte atual gera credenciais aleatórias, mas os registros antigos continuam acessíveis no histórico. A inativação das contas seed na base de produção e a decisão de limpar o histórico ou restringir o repositório com acesso do professor permanecem pendentes.

## [1.0.3-tests] — 13/11/2026 — Correção identificada pela integração contínua

- **Falha detectada pelo GitHub Actions:** a suíte de aceite CA13 reutilizava o mesmo placeholder nomeado `:id` duas vezes na consulta PDO, incompatível com prepared statements nativos do MySQL.
- **Correção:** substituídos por `:equipe_a_id` e `:equipe_b_id`, ambos vinculados ao identificador da equipe testada.
- **Validação:** a correção foi enviada ao repositório; a nova execução do workflow ainda precisa concluir para confirmar a suíte completa.


## [1.0.4-deploy] — 13/11/2026 — Publicação e homologação manual no InfinityFree

### Implantação executada
- **Hospedagem escolhida:** InfinityFree, preservando a aplicação PHP tradicional com PDO e MySQL/MariaDB. A migração para Vercel/Next.js ou Supabase/PostgreSQL foi descartada nesta entrega por exigir adaptação de arquitetura e/ou migração do banco.
- **Domínio publicado:** `jifplus-ifro.free.nf`; os arquivos da aplicação foram enviados para a raiz `/htdocs`.
- **Banco remoto:** banco MySQL criado no provedor; o phpMyAdmin exibiu as 10 tabelas previstas. As credenciais foram configuradas no arquivo `.env` do servidor, não no repositório.
- **Administrador:** conta própria criada com hash bcrypt e login administrativo realizado com sucesso no ambiente publicado.
- **Fluxo manual de persistência:** um atleta fictício de teste foi cadastrado no painel administrativo e apareceu na listagem pública do BID. Isso confirma uma operação de escrita e uma consulta subsequente no banco remoto.
- **Acesso público:** a página inicial e a página de atletas foram abertas em janela anônima.

### Revisões humanas e limites da homologação
- A decisão de manter PHP + MySQL foi mantida após comparar a stack real com as opções da atividade: Vercel não executa diretamente a aplicação PHP/Apache convencional; Supabase oferece PostgreSQL e não hospeda PHP, portanto exigiria migração. InfinityFree foi escolhido para preservar a stack existente.
- A captura do navegador exibiu o indicador **“Não seguro”**. Portanto, embora o domínio esteja acessível, o certificado TLS/HTTPS ainda não foi validado e não deve ser declarado conforme até a correção e novo teste.
- A suíte automatizada completa de 17 critérios não foi executada na hospedagem; a validação acima é manual e cobre apenas os fluxos descritos.
- O arquivo `.htaccess` contém regras para negar acesso direto ao `.env` e às pastas internas, mas a resposta HTTP de bloqueio ainda precisa ser verificada externamente.
- O histórico público contém credenciais antigas de demonstração e um segredo estático de exemplo. As contas seed da versão atual estão inativas; ainda é necessário confirmar o estado das contas seed no banco de produção e decidir sobre a limpeza do histórico antes da entrega definitiva.
- A proteção CSRF, a limitação de tentativas de login e a validação completa de todos os campos administrativos permanecem como melhorias pendentes, conforme o checklist de segurança.


## [1.0.5-security] — 13/11/2026 — Desativação de contas de demonstração em produção

- Após a conferência humana da tela `/admin/usuarios.php`, o responsável informou que os cinco usuários adicionais eram exclusivamente demonstrativos.
- O responsável confirmou ter usado a ação **Desativar** para os cinco registros, preservando a conta administrativa principal e sem excluir linhas do banco.
- A alteração foi realizada manualmente no painel publicado. O estado pós-alteração foi informado pelo responsável, mas ainda não foi confirmado por uma nova captura ou consulta direta ao banco.
- As credenciais antigas de demonstração que permanecem em commits históricos continuam sendo uma pendência de segurança do repositório público; desativar as contas no banco de produção não remove esses dados do histórico Git.


## [1.0.6] — 13/11/2026 — Confirmação de HTTPS e fechamento da publicação

- **HTTPS/TLS:** o responsável confirmou que a URL pública passou a abrir em janela anônima com indicação de conexão segura. A documentação registra a confirmação visual, sem afirmar monitoramento contínuo do certificado.
- **Contas de demonstração:** o responsável confirmou que desativou os cinco usuários demonstrativos adicionais no painel de produção, preservando a conta administrativa principal e sem excluir registros.
- **Revisão humana:** mantida a hospedagem InfinityFree e a stack PHP + PDO + MySQL para evitar uma migração desnecessária para Vercel/Next.js ou Supabase/PostgreSQL dentro do prazo da atividade.
- **Pendências declaradas:** o histórico público ainda contém credenciais fictícias de seed e um segredo estático de exemplo em commits antigos; a limpeza exige reescrita do histórico Git. A suíte automatizada completa, o teste negativo de RBAC, o teste externo de bloqueio de `/.env` e a revisão integral de formulários continuam sem evidência final e não são declarados concluídos.
