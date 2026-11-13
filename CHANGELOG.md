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
