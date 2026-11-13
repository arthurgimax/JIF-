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
- Risco identificado na referência remota `origin/main`: um commit antigo ainda rastreia `jif_plus.sqlite` com contas ativas e matrículas. O arquivo foi removido da versão local atual, mas a sincronização e eventual limpeza do histórico remoto permanecem pendentes.

## [1.0.3-tests] — 13/11/2026 — Correção identificada pela integração contínua

- **Falha detectada pelo GitHub Actions:** a suíte de aceite CA13 reutilizava o mesmo placeholder nomeado `:id` duas vezes na consulta PDO, incompatível com prepared statements nativos do MySQL.
- **Correção:** substituídos por `:equipe_a_id` e `:equipe_b_id`, ambos vinculados ao identificador da equipe testada.
- **Validação:** a correção foi enviada ao repositório; a nova execução do workflow ainda precisa concluir para confirmar a suíte completa.
