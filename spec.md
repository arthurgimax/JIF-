# JIF+ — Plataforma de Gestão e Inteligência Esportiva para os Jogos dos Institutos Federais
## Especificação de Requisitos e Arquitetura do Sistema (Versão 1.0.4 — Publicação e homologação manual)

---

### Identificação do Projeto

- **Nome do Projeto**: JIF+ (Plataforma de Gestão e Inteligência Esportiva para os Jogos dos Institutos Federais)
- **Instituição de Ensino**: Instituto Federal de Educação, Ciência e Tecnologia de Rondônia (IFRO) — Campus Porto Velho Calama
- **Curso**: Técnico em Informática Integrado ao Ensino Médio (3º Ano)
- **Disciplina**: Desenvolvimento de Sistemas (4º Bimestre)
- **Docente Responsável**: Prof. Rafael Pitwak
- **Estudante Autor**: Arthur Henrique Gimax Oliveira
- **Repositório Oficial**: [github.com/arthurgimax/JIF-](https://github.com/arthurgimax/JIF-)
- **Versão do Documento**: 1.0.4 (publicação e homologação manual)
- **Data da Revisão**: 13 de novembro de 2026

---

## 1. Introdução e Contextualização

Os Jogos dos Institutos Federais (JIF) constituem o maior evento poliesportivo da Rede Federal de Educação Profissional, Científica e Tecnológica do Brasil. Reunindo milhares de estudantes-atletas em etapas locais (como os Jogos Intercampi do IFRO — JIFRO), regionais (JIF Norte) e na etapa nacional consolidada, essas competições fomentam a integração comunitária, a cidadania, o espírito esportivo e o desenvolvimento socioemocional dos jovens.

Apesar de sua relevância institucional e desportiva, os processos de gestão, registro e comunicação dos eventos esportivos historicamente carecem de centralização tecnológica. Na rotina dos jogos, as informações de delegações, equipes, atletas, tabelas de confrontos e súmulas são frequentemente fragmentadas em planilhas locais, arquivos de texto avulsos, impressões em papel e boletins informativos distribuídos em formato PDF através de aplicativos de mensagens instantâneas.

O **JIF+** nasce no âmbito acadêmico do IFRO — Campus Porto Velho Calama como uma solução web robusta, segura e moderna para unificar todo o ciclo de vida informacional dos jogos em uma única plataforma digital. O sistema conecta coordenadores de eventos, docentes de Educação Física, técnicos desportivos, estudantes-atletas e a comunidade acadêmica em geral, garantindo transparência, integridade referencial dos dados e agilidade nas tomadas de decisão esportiva.

---

## 2. Problema Identificado

Durante a auditoria de processos operacionais dos jogos escolares e intercampi, identificaram-se os seguintes gargalos estruturais:

1. **Fragmentação e Dispersão de Dados**: Publicação de boletins em arquivos PDF desarticulados, o que dificulta buscas históricas e a consulta instantânea por parte de estudantes e familiares.
2. **Lentidão e Falhas no Cálculo Manual**: A apuração manual de tabelas de classificação, saldo de gols, critérios de desempate e estatísticas individuais (artilharia e disciplina) demanda tempo excessivo e gera margem para divergências entre comissões técnicas.
3. **Perda da Memória Esportiva e Inexistência de BID**: A ausência de um Boletim Informativo Diário (BID) e de um repositório centralizado impede a preservação da trajetória e do histórico esportivo dos estudantes-atletas ao longo dos anos de permanência no Instituto Federal.
4. **Falta de Transparência e Acompanhamento em Tempo Real**: Estudantes, servidores e familiares que acompanham os jogos à distância não dispõem de um ambiente centralizado para consultar confrontos agendados, placares homologados e classificações atualizadas.
5. **Vulnerabilidade e Insegurança em Ambientes de Controle**: Ausência de controle de permissões por nível de usuário (RBAC) em processos manuais, permitindo inconsistências de inscrições e alterações indevidas em súmulas.

---

## 3. Objetivo Geral e Objetivos Específicos

### 3.1. Objetivo Geral
Desenvolver e implantar a plataforma web **JIF+**, centralizando a gestão de instituições, competições, modalidades, equipes, atletas, partidas, súmulas e classificações, além de disponibilizar módulos de inteligência e estatística esportiva orientados à Rede Federal de Educação Profissional e Tecnológica.

### 3.2. Objetivos Específicos
- **Modelar e estruturar** um banco de dados relacional íntegro, composto por 10 entidades mapeadas, assegurando integridade referencial estrita (`FOREIGN KEY`) e suporte a transações ACID.
- **Implementar controle de acesso baseado em papéis (RBAC)**, distinguindo os privilégios operacionais de Administradores, Organizadores, Treinadores, Atletas e Visitantes.
- **Construir súmula eletrônica interativa** capaz de homologar placares e vincular eventos individuais (gols, pontos, faltas e cartões) diretamente aos atletas participantes.
- **Automatizar a computação de tabelas de classificação**, aplicando pontuação oficial (3 pontos por vitória, 1 por empate) e critérios desportivos de desempate (saldo de gols, gols pró e confrontos).
- **Consolidar o Boletim Informativo do Atleta (BID)**, fornecendo perfil individual detalhado com foto, dorsal, posição, histórico de jogos disputados e métricas acumuladas.
- **Disponibilizar painéis analíticos de inteligência esportiva**, incluindo ranking de artilharia (Chuteira de Ouro), eficiência ofensiva por campus e Troféu Fair Play (indicador disciplinar).
- **Garantir estrita conformidade com as boas práticas de segurança** preconizadas na apostila acadêmica *Programar com IA de forma responsável*, protegendo senhas via algoritmo bcrypt e blindando o sistema contra injeções de SQL por meio de prepared statements com PDO.

---

## 4. Escopo do Sistema

### 4.1. Módulos Implementados no Escopo Oficial (Versão 1.0)
- **Módulo de Acesso e Identidade**: Registro público de novos usuários, login com validação segura de credenciais, encerramento de sessão (logout) e barramento RBAC para proteção de rotas restritas.
- **Módulo Institucional e Organizacional**: Cadastro e manutenção dos campi dos Institutos Federais, gestão de torneios oficiais (JIFRO, JIF Regional) e parametrização de esportes por categoria (masculino, feminino, misto) e formato (coletivo ou individual).
- **Módulo de Delegações e Elencos**: Inscrição de equipes representativas por campus/modalidade, associação de técnicos responsáveis e vinculação de estudantes-atletas com dorsal e posição.
- **Módulo de Competições e Tabela de Jogos**: Programação de partidas com data, horário, local/quadra e fase, impedindo confrontos entre equipes de modalidades distintas ou da mesma instituição em chaveamento irregular.
- **Módulo de Súmula e Homologação de Resultados**: Lançamento de placares, encerramento oficial de partidas e registro minucioso de estatísticas por atleta (gols, advertências e expulsões).
- **Módulo de Classificação e Inteligência**: Exibição pública de tabelas calculadas automaticamente e painéis gráficos com destaques individuais e coletivos dos Institutos Federais.
- **Módulo Administrativo Integrado (`/admin`)**: Painel de governança restrito para auditagem, controle de permissões de usuários e ativação/inativação de cadastros.

---

## 5. Público-Alvo e Perfis de Usuários (RBAC)

O sistema implementa o modelo de Controle de Acesso Baseado em Papéis (*Role-Based Access Control* — RBAC), discriminando cinco níveis operacionais estritos definidos no arquivo [`config/auth.php`](config/auth.php):

| Perfil | Identificador Técnico | Escopo de Permissões e Ações Permitidas |
| :--- | :--- | :--- |
| **Administrador Geral** | `admin` | Acesso irrestrito a todas as áreas do sistema. Responsável por cadastrar instituições/campi, gerenciar usuários, alterar perfis de acesso, auditar registros e intervir globalmente nas competições. |
| **Coordenador / Organizador** | `organizador` | Responsável por criar competições, definir modalidades esportivas oficiais, homologar inscrições, agendar partidas, preencher a súmula eletrônica com placares e eventos, e publicar classificações. |
| **Treinador / Professor** | `treinador` | Responsável por inscrever equipes representativas do seu campus nas competições ativas, gerenciar o elenco de estudantes-atletas sob sua tutela e acompanhar o rendimento tático de sua equipe. |
| **Estudante-Atleta** | `atleta` | Acesso ao seu Boletim Informativo Diário (BID) individual, consulta de suas estatísticas pessoais acumuladas (gols, cartões e jogos), histórico de equipes que integrou e acompanhamento do calendário. |
| **Visitante / Público Geral** | `visitante` *(não autenticado)* | Consulta pública em modo somente-leitura aos placares em tempo real, tabelas de classificação, páginas de equipes, perfis BID e painéis de inteligência, sem permissão para criar ou modificar dados. |

---

## 6. Casos de Uso

A dinâmica operacional do JIF+ é norteada por 12 casos de uso principais, cujos fluxos estão implementados no código e precisam ser reexecutados na homologação final:

- **UC01 — Cadastrar-se e autenticar-se no sistema**:
  - *Ator*: Visitante / Usuário.
  - *Fluxo Principal*: O visitante acessa a tela de cadastro, preenche nome, e-mail e senha. O sistema valida os campos, criptografa a senha com hash bcrypt e grava o registro. Na tela de login, informa credenciais; o sistema autentica contra o banco relacional, regenera o ID de sessão e direciona o usuário ao painel correspondente ao seu perfil.
- **UC02 — Gerenciar usuários e permissões de acesso**:
  - *Ator*: Administrador Geral.
  - *Fluxo Principal*: O administrador acessa `admin/usuarios.php`, visualiza a listagem de contas registradas, altera o perfil funcional (`admin`, `organizador`, `treinador`, `atleta`) e ativa ou suspende contas.
- **UC03 — Gerenciar instituições e campi**:
  - *Ator*: Administrador Geral.
  - *Fluxo Principal*: Em `admin/instituicoes.php`, cadastra novos campi dos Institutos Federais informando sigla, nome, campus e unidade federativa (UF).
- **UC04 — Gerenciar competições e modalidades esportivas**:
  - *Ator*: Coordenador / Organizador.
  - *Fluxo Principal*: Cadastra eventos (ex.: JIFRO 2026) com período de realização, local-sede e status operacional. Vincula as modalidades oficiais (Futsal, Vôlei, Basquete, Xadrez, Atletismo) definindo categoria e formato.
- **UC05 — Gerenciar equipes representativas**:
  - *Ator*: Treinador ou Coordenador.
  - *Fluxo Principal*: Registra a equipe vinculando-a obrigatoriamente a uma instituição, uma competição e uma modalidade ativa, indicando o professor responsável.
- **UC06 — Gerenciar atletas e inscrição em equipes**:
  - *Ator*: Treinador ou Coordenador.
  - *Fluxo Principal*: Cadastra os dados do estudante-atleta (nome, matrícula institucional e data de nascimento) e o inscreve na equipe designando o número da camisa (dorsal) e sua posição esportiva.
- **UC07 — Agendar e programar partidas**:
  - *Ator*: Coordenador / Organizador.
  - *Fluxo Principal*: Cria o confronto selecionando a competição, a modalidade, as duas equipes adversárias (mandante e visitante distintas), fase/rodada, data, horário e quadra/local de disputa.
- **UC08 — Registrar súmula eletrônica e estatísticas**:
  - *Ator*: Coordenador / Organizador.
  - *Fluxo Principal*: Na tela `admin/sumula.php`, o coordenador atualiza os placares parciais/finais, registra eventos individuais (gols marcados, cartões amarelos, cartões vermelhos e faltas) vinculados aos atletas e encerra oficialmente o jogo.
- **UC09 — Consultar página da equipe e histórico**:
  - *Ator*: Todos os perfis / Visitante.
  - *Fluxo Principal*: O usuário acessa a tela de equipes, filtra por campus ou esporte e visualiza o elenco completo, comissão técnica, calendário de partidas e resultados anteriores.
- **UC10 — Pesquisar e filtrar informações esportivas**:
  - *Ator*: Todos os perfis / Visitante.
  - *Fluxo Principal*: Aplicação de buscas textuais e filtros combinados por campus, modalidade, atleta ou competição nas páginas públicas da plataforma.
- **UC11 — Visualizar painéis de inteligência e desempenho**:
  - *Ator*: Todos os perfis / Visitante.
  - *Fluxo Principal*: Consulta visual aos rankings de artilharia (Chuteira de Ouro), eficiência ofensiva por campus participante e índice de disciplina (Fair Play) calculados a partir dos dados consolidados das súmulas.
- **UC12 — Validar regras de negócio e garantir persistência**:
  - *Ator*: Sistema / Backend PHP.
  - *Fluxo Principal*: Intercepta toda requisição de entrada, sanitiza strings, valida restrições no servidor e persiste os dados em tabelas InnoDB utilizando transações e queries parametrizadas.

---

## 7. Requisitos Funcionais

A tabela a seguir consolida os 17 Requisitos Funcionais (RF01 a RF17), indicando o status verificado em código-fonte e os arquivos de implementação:

| Código | Descrição do Requisito Funcional | Status Real | Arquivos de Implementação no Sistema |
| :--- | :--- | :---: | :--- |
| **RF01** | Autocadastro de usuários com validação de formato de e-mail, verificação de duplicidade e obrigatoriedade de senha com mínimo de 6 caracteres. | **Implementado** | [`cadastro.php`](./cadastro.php), [`config/auth.php`](config/auth.php) |
| **RF02** | Autenticação de credenciais via e-mail e senha, controle de sessão ativa, proteção contra Session Fixation e encerramento seguro (logout). | **Implementado** | [`login.php`](./login.php), [`logout.php`](./logout.php), [`config/auth.php`](config/auth.php) |
| **RF03** | Controle estrito de acesso e permissões (RBAC) bloqueando o acesso de usuários não autorizados a rotas administrativas (`HTTP 403`). | **Implementado** | [`config/auth.php`](config/auth.php), [`admin/usuarios.php`](./admin/usuarios.php) |
| **RF04** | Gestão de instituições e campi participantes (cadastro, listagem e vinculação geográfica por UF). | **Implementado** | [`admin/instituicoes.php`](./admin/instituicoes.php) |
| **RF05** | Gestão de competições (cadastro de edição, ano, períodos de início/término, local-sede e controle de status: planejada, em andamento, encerrada). | **Implementado** | [`admin/competicoes.php`](./admin/competicoes.php), [`competicoes.php`](./competicoes.php) |
| **RF06** | Gestão de modalidades esportivas com vínculo direto à competição, definindo categoria (masculino/feminino/misto) e formato (coletiva/individual). | **Implementado** | [`admin/modalidades.php`](./admin/modalidades.php), [`modalidades.php`](./modalidades.php) |
| **RF07** | Gestão de equipes esportivas associadas a campus, competição, modalidade e técnico responsável, prevenindo duplicidades. | **Implementado** | [`admin/equipes.php`](./admin/equipes.php), [`equipes.php`](./equipes.php) |
| **RF08** | Cadastro e inscrição de atletas em equipes, registrando matrícula institucional, dorsal (número da camisa) e posição em campo. | **Implementado** | [`admin/atletas.php`](./admin/atletas.php) |
| **RF09** | Perfil esportivo individual do atleta (Boletim Informativo do Atleta — BID) com histórico de partidas, equipes integradas e gols/pontos acumulados. | **Implementado** | [`atletas.php`](./atletas.php) |
| **RF10** | Agendamento e programação de partidas oficiais definindo fase, data, horário, local/quadra e duas equipes distintas da mesma modalidade. | **Implementado** | [`admin/partidas.php`](./admin/partidas.php), [`partidas.php`](./partidas.php) |
| **RF11** | Registro de placares parciais e homologação de resultado final com transição de situação da partida para "encerrada". | **Implementado** | [`admin/sumula.php`](./admin/sumula.php), [`partidas.php`](./partidas.php) |
| **RF12** | Súmula eletrônica com registro de eventos individuais de atletas (gols, advertências por cartão amarelo, expulsões e faltas coletivas). | **Implementado** | [`admin/sumula.php`](./admin/sumula.php) |
| **RF13** | Visualização pública da página da equipe contendo listagem do elenco completo, comissão técnica, próximos jogos e retrospecto de resultados. | **Implementado** | [`equipes.php`](./equipes.php) |
| **RF14** | Mecanismo de busca textual e filtros dinâmicos combinados por campus, modalidade, atleta, equipe e status de confronto. | **Implementado** | [`index.php`](./index.php), [`atletas.php`](./atletas.php), [`partidas.php`](./partidas.php), [`assets/js/app.js`](./assets/js/app.js) |
| **RF15** | Painéis analíticos e relatórios de inteligência com cálculo de classificação por pontos, Chuteira de Ouro e Troféu Fair Play. | **Implementado** | [`classificacao.php`](./classificacao.php), [`inteligencia.php`](./inteligencia.php), [`index.php`](./index.php) |
| **RF16** | Validação no backend dos campos e regras de negócio principais antes da persistência; revisão exaustiva de todos os campos ainda pendente. | **Parcial** | Todos os scripts em `admin/*.php`, [`cadastro.php`](./cadastro.php) e `config/` |
| **RF17** | Persistência relacional integral em banco de dados relacional com restrições de chaves estrangeiras e transações estruturadas. | **Implementado** | [`database/schema.sql`](./database/schema.sql), [`config/database.php`](config/database.php) |

---

## 8. Requisitos Não Funcionais

Os Requisitos Não Funcionais (RNF01 a RNF11) atendem às diretrizes de engenharia de software e ao Checklist de Segurança do Anexo B da apostila:

- **RNF01 — Criptografia e Armazenamento Seguro de Senhas**: As credenciais dos usuários nunca são gravadas em texto puro. O sistema emprega a função nativa `password_hash()` com algoritmo `bcrypt` e fator de custo de processamento ajustado (`cost => 10`). A autenticação utiliza `password_verify()`.
- **RNF02 — Validação Obrigatória no Servidor**: O cadastro público e os principais fluxos administrativos possuem validações no backend. A revisão identificou que ainda falta conferir de forma exaustiva limites, enumerações e consistência relacional em todos os formulários; por isso, o item é tratado como parcial no checklist de segurança.
- **RNF03 — Proteção contra Injeção de SQL (SQL Injection)**: As consultas que recebem valores de formulários, filtros ou identificadores variáveis usam **PDO com prepared statements** e parâmetros vinculados. Consultas estáticas sem entrada externa usam SQL fixo. A diretiva `PDO::ATTR_EMULATE_PREPARES` está explicitamente desativada (`false`).
- **RNF04 — Isolamento de Segredos e Variáveis de Ambiente**: Credenciais de banco de dados (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) residem exclusivamente no arquivo `.env`. Esse arquivo está registrado no `.gitignore` para impedir sua submissão ao versionamento Git no GitHub. Um arquivo modelo [`/.env.example`](./.env.example) é fornecido para configuração do ambiente.
- **RNF05 — Controle de Acesso às Rotas e Segurança de Sessão**: Cada requisição a áreas restritas (`/admin/*`) executa a função `exigirAutenticacao()`, que verifica a existência de sessão ativa e confronta o perfil do usuário logado com os privilégios requeridos. Sessões utilizam cookies com flags `HttpOnly` (prevenção contra roubo via script XSS) e `SameSite=Lax`.
- **RNF06 — Privacidade de Dados de Estudantes-Atletas**: O perfil público exibe nome esportivo, campus, número de camisa, estatísticas e histórico esportivo. Matrícula institucional, data de nascimento e e-mail de responsáveis não são exibidos em páginas públicas; esses dados permanecem restritos às telas administrativas.
- **RNF07 — Tratamento Seguro de Erros e Exceções**: Mensagens técnicas nativas do banco de dados, nomes internos de colunas e stack traces do PHP não são exibidos aos usuários em tela. Erros são capturados em blocos `try/catch`, gravados no log interno do servidor via `error_log()` e substituídos por avisos visuais amigáveis.
- **RNF08 — Protocolo de Comunicação**: o código suporta cookies seguros quando HTTPS é detectado; HTTPS efetivo permanece pendente de configuração e teste no provedor de produção.
- **RNF09 — Desempenho e Tempo de Resposta**: o esquema contém índices em colunas consultadas com frequência; a meta de resposta abaixo de 2 segundos ainda não foi medida por teste de carga.
- **RNF10 — Responsividade e Acessibilidade**: A interface visual foi desenvolvida em CSS3 responsivo utilizando layouts adaptáveis com Flexbox e CSS Grid. O sistema oferece usabilidade homogênea em navegadores desktop, tablets e smartphones com larguras a partir de 360px.
- **RNF11 — Integridade Relacional e Suporte a Transações**: O esquema físico do banco adota o mecanismo de armazenamento **InnoDB** no MySQL, impondo chaves estrangeiras (`FOREIGN KEY`) com regras de integridade (`ON DELETE RESTRICT` e `ON DELETE CASCADE`) para garantir a consistência das entidades esportivas.

---

## 9. Regras de Negócio

As operações do JIF+ seguem as seguintes regras de negócio mandatórias:

- **RN01 — Unicidade de Cadastro por E-mail**: Não é permitido o cadastro de dois usuários com o mesmo endereço de e-mail no sistema.
- **RN02 — Vínculo Obrigatório de Equipe**: Toda equipe deve estar estritamente vinculada a uma instituição (campus), uma competição existente e uma modalidade esportiva cadastrada nessa competição.
- **RN03 — Validação de Confrontos**: Uma partida só pode ser agendada entre equipes pertencentes à mesma competição e modalidade. As equipes mandante e visitante devem ser distintas (`equipe_a_id != equipe_b_id`).
- **RN04 — Sistema de Pontuação Oficial**: O cálculo de pontuação na classificação segue o padrão desportivo internacional:
  - Vitória: 3 pontos
  - Empate: 1 ponto
  - Derrota: 0 pontos
- **RN05 — Critérios Oficiais de Desempate**: Em caso de empate em pontos na tabela de classificação, o ordenamento segue a hierarquia:
  1. Maior número de vitórias;
  2. Maior Saldo de Gols (SG) / Saldo de Pontos;
  3. Maior número de Gols Pró (GP);
  4. Menor número de Gols Sofridos (GC).
- **RN06 — Homologação de Partidas**: Placares só produzem efeitos estatísticos na classificação geral e nos perfis individuais após a situação da partida ser alterada para `encerrada`. Partidas com status `agendada` ou `cancelada` não computam pontos.
- **RN07 — Proteção contra Exclusão de Histórico (Integridade Referencial)**: Equipes e instituições que possuam vínculos ativos em partidas ou súmulas não podem ser excluídas fisicamente do banco de dados (`ON DELETE RESTRICT`), prevenindo inconsistências em registros históricos.
- **RN08 — Concessão Restrita de Privilégios**: Novos usuários cadastrados publicamente pelo formulário web recebem exclusivamente o perfil `atleta`. A concessão dos perfis `treinador`, `organizador` e `admin` é feita por um Administrador Geral através da tela de gerenciamento de usuários.

---

## 10. Arquitetura e Tecnologias Utilizadas

A arquitetura do sistema adota uma abordagem estruturada em camadas, priorizando simplicidade, manutenibilidade, conformidade pedagógica e segurança nativa:

```text
┌───────────────────────────────────────────────────────────────┐
│                    CAMADA DE APRESENTAÇÃO                     │
│   Navegador Web: HTML5 Semântico, CSS3 Moderno, ES6+ JS      │
│   (Design Responsivo, Identidade Visual IFRO, Interatividade) │
└───────────────────────────────┬───────────────────────────────┘
                                │ Requisições HTTP / POST / GET
                                ▼
┌───────────────────────────────────────────────────────────────┐
│                    CAMADA DE APLICAÇÃO (PHP)                  │
│   Controladores e Páginas: index.php, atletas.php, admin/*    │
│   Módulos Centrais:                                           │
│   ├── config/auth.php     (Autenticação, Sessões, RBAC)       │
│   └── includes/           (Header, Footer, Alertas Visuais)   │
└───────────────────────────────┬───────────────────────────────┘
                                │ Camada de Acesso a Dados
                                ▼
┌───────────────────────────────────────────────────────────────┐
│               CAMADA DE PERSISTÊNCIA (PDO SINGLETON)          │
│   config/database.php (Prepared Statements, ATTR_EMULATE=false│
│   Gerenciamento de Transações e Conexões Seguras              │
└───────────────────────────────┬───────────────────────────────┘
                                │
        ┌───────────────────────┴───────────────────────┐
        ▼                                               ▼
┌───────────────────────────────┐       ┌───────────────────────────────┐
│  MOTOR PRINCIPAL (PRODUÇÃO)   │       │ MOTOR DE CONTINGÊNCIA (DEV)   │
│  MySQL 8.0 / MariaDB (InnoDB) │       │ SQLite (jif_plus.sqlite)      │
│  database/schema.sql          │       │ Suporte local transparente    │
└───────────────────────────────┘       └───────────────────────────────┘
```

### 10.1. Justificativa da Stack Selecionada
1. **PHP 8.2+ Puro Estruturado**: Atende integralmente à formação técnica do estudante Arthur Henrique Gimax Oliveira no IFRO, permitindo exercitar fundamentos essenciais da disciplina (segurança, controle de sessão, manipulação de estados e arquitetura web) sem a complexidade de abstração de frameworks pesados.
2. **Camada de Abstração PDO**: Garante portabilidade e blindagem técnica mandatória contra SQL Injection com suporte nativo a consultas preparadas.
3. **Mecanismo Duplo de Banco de Dados**:
   - **MySQL 8.0 / MariaDB**: Motor de banco principal configurado com mecanismo **InnoDB**, suportando transações atômicas, chaves estrangeiras e integridade referencial.
   - **SQLite Local de Contingência**: Configurado em [`config/database.php`](config/database.php) para garantir alta disponibilidade no ambiente de desenvolvimento local, ativando automaticamente o arquivo `database/jif_plus.sqlite` caso as credenciais locais do MySQL sofram interrupções momentâneas.

---

## 11. Modelo de Dados e Diagrama Entidade-Relacionamento (DER)

O banco de dados relacional é composto por 10 tabelas normalizadas, definidas em [`database/schema.sql`](./database/schema.sql):

### 11.1. Dicionário de Dados

1. **`usuarios`**: Contas e autenticação.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador único.
   - `nome` (VARCHAR(150), NOT NULL): Nome completo do usuário.
   - `email` (VARCHAR(150), NOT NULL, UNIQUE): E-mail de acesso.
   - `senha_hash` (VARCHAR(255), NOT NULL): Hash bcrypt da senha.
   - `perfil` (ENUM('admin', 'organizador', 'treinador', 'atleta'), NOT NULL, DEFAULT 'atleta'): Perfil RBAC.
   - `ativo` (TINYINT(1), NOT NULL, DEFAULT 1): Status da conta.
   - `criado_em` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP).
2. **`instituicoes`**: Campi da Rede Federal.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador da instituição/campus.
   - `nome` (VARCHAR(150), NOT NULL): Nome institucional (ex.: Instituto Federal de Rondônia).
   - `sigla` (VARCHAR(30), NOT NULL): Sigla (ex.: IFRO).
   - `campus` (VARCHAR(100), NOT NULL): Campus (ex.: Porto Velho Calama).
   - `estado` (CHAR(2), NOT NULL): UF (ex.: RO).
3. **`competicoes`**: Eventos esportivos.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador da competição.
   - `instituicao_organizadora_id` (INT, NOT NULL, FK `instituicoes.id`): Campus anfitrião.
   - `nome` (VARCHAR(150), NOT NULL): Título oficial (ex.: JIFRO 2026).
   - `edicao` (VARCHAR(50), NOT NULL): Edição do torneio.
   - `ano` (INT, NOT NULL): Ano de realização.
   - `data_inicio` (DATE, NOT NULL): Início das disputas.
   - `data_fim` (DATE, NOT NULL): Encerramento das disputas.
   - `local_sede` (VARCHAR(150), NOT NULL): Cidade-sede.
   - `status` (ENUM('planejada', 'em_andamento', 'encerrada'), NOT NULL, DEFAULT 'planejada').
4. **`modalidades`**: Esportes disputados na competição.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador da modalidade.
   - `competicao_id` (INT, NOT NULL, FK `competicoes.id`): Torneio ao qual pertence.
   - `nome` (VARCHAR(100), NOT NULL): Esporte (ex.: Futsal, Vôlei, Basquete, Xadrez).
   - `categoria` (ENUM('masculino', 'feminino', 'misto'), NOT NULL): Gênero/categoria.
   - `tipo` (ENUM('coletiva', 'individual'), NOT NULL): Formato da modalidade.
   - `status` (ENUM('ativa', 'inativa'), NOT NULL, DEFAULT 'ativa').
5. **`equipes`**: Equipes representativas dos campi.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador da equipe.
   - `instituicao_id` (INT, NOT NULL, FK `instituicoes.id`): Campus de origem.
   - `competicao_id` (INT, NOT NULL, FK `competicoes.id`): Torneio disputado.
   - `modalidade_id` (INT, NOT NULL, FK `modalidades.id`): Esporte disputado.
   - `responsavel_id` (INT, NOT NULL, FK `usuarios.id`): Professor/técnico responsável.
   - `nome` (VARCHAR(150), NOT NULL): Nome oficial de exibição da equipe.
6. **`atletas`**: Cadastro de estudantes-atletas.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador do atleta.
   - `usuario_id` (INT, NULL, FK `usuarios.id`): Vínculo opcional com conta de login.
   - `instituicao_id` (INT, NOT NULL, FK `instituicoes.id`): Campus de matrícula.
   - `nome` (VARCHAR(150), NOT NULL): Nome completo do estudante.
   - `matricula_estudantil` (VARCHAR(50), NOT NULL): Matrícula institucional no SUAP.
   - `data_nascimento` (DATE, NULL): Data de nascimento.
   - `foto_url` (VARCHAR(255), NULL): Caminho/URL da foto oficial do BID.
   - `criado_em` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP).
7. **`equipe_atletas`**: Tabela associativa de escalação e elenco.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador da inscrição.
   - `equipe_id` (INT, NOT NULL, FK `equipes.id`): Equipe da inscrição.
   - `atleta_id` (INT, NOT NULL, FK `atletas.id`): Atleta inscrito.
   - `numero_camisa` (INT, NULL): Dorsal do atleta.
   - `posicao` (VARCHAR(50), NULL): Posição tática em campo.
   - `status_inscricao` (ENUM('regular', 'suspenso', 'inativo'), NOT NULL, DEFAULT 'regular').
   - `UNIQUE KEY uk_equipe_atleta (equipe_id, atleta_id)`: Previne inscrição duplicada.
8. **`partidas`**: Calendário de confrontos e resultados.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador do confronto.
   - `competicao_id` (INT, NOT NULL, FK `competicoes.id`): Competição.
   - `modalidade_id` (INT, NOT NULL, FK `modalidades.id`): Esporte disputado.
   - `equipe_a_id` (INT, NOT NULL, FK `equipes.id`): Equipe mandante.
   - `equipe_b_id` (INT, NOT NULL, FK `equipes.id`): Equipe visitante.
   - `fase_rodada` (VARCHAR(50), NOT NULL): Fase (Grupos, Semifinal, Final).
   - `data_hora` (DATETIME, NOT NULL): Horário e data agendados.
   - `local_quadra` (VARCHAR(150), NOT NULL): Ginásio/quadra da disputa.
   - `placar_a` (INT, NULL, DEFAULT NULL): Placar final da equipe mandante.
   - `placar_b` (INT, NULL, DEFAULT NULL): Placar final da equipe visitante.
   - `situacao` (ENUM('agendada', 'em_andamento', 'encerrada', 'cancelada'), NOT NULL, DEFAULT 'agendada').
9. **`estatisticas_atletas`**: Eventos individuais da súmula.
   - `id` (INT, PK, AUTO_INCREMENT): Identificador do registro.
   - `partida_id` (INT, NOT NULL, FK `partidas.id`): Partida onde o evento ocorreu.
   - `atleta_id` (INT, NOT NULL, FK `atletas.id`): Atleta autor do evento.
   - `equipe_id` (INT, NOT NULL, FK `equipes.id`): Equipe do atleta.
   - `tipo_estatistica` (VARCHAR(50), NOT NULL): Evento ('gol', 'ponto', 'falta', 'cartao_amarelo', 'cartao_vermelho').
   - `valor` (INT, NOT NULL, DEFAULT 1): Quantidade computada.
10. **`estatisticas_equipes`**: Indicadores consolidados por time.
    - `id` (INT, PK, AUTO_INCREMENT): Identificador do registro coletivo.
    - `partida_id` (INT, NOT NULL, FK `partidas.id`): Partida associada.
    - `equipe_id` (INT, NOT NULL, FK `equipes.id`): Equipe associada.
    - `tipo_estatistica` (VARCHAR(50), NOT NULL): Métrica ('pontos_total', 'faltas_coletivas', 'sets_vencidos').
    - `valor` (INT, NOT NULL): Total registrado.

### 11.2. Diagrama Entidade-Relacionamento (DER em Mermaid)

```mermaid
erDiagram
    INSTITUICOES ||--o{ COMPETICOES : "organiza"
    INSTITUICOES ||--o{ EQUIPES : "representa"
    INSTITUICOES ||--o{ ATLETAS : "matricula"
    COMPETICOES ||--o{ MODALIDADES : "possui"
    COMPETICOES ||--o{ EQUIPES : "disputa"
    COMPETICOES ||--o{ PARTIDAS : "agrupa"
    MODALIDADES ||--o{ EQUIPES : "classifica"
    MODALIDADES ||--o{ PARTIDAS : "rege"
    USUARIOS ||--o{ EQUIPES : "lidera"
    USUARIOS ||--o| ATLETAS : "vincula_login"
    EQUIPES ||--o{ EQUIPE_ATLETAS : "escala"
    ATLETAS ||--o{ EQUIPE_ATLETAS : "integra"
    EQUIPES ||--o{ PARTIDAS : "mandante_ou_visitante"
    PARTIDAS ||--o{ ESTATISTICAS_ATLETAS : "gera_eventos"
    ATLETAS ||--o{ ESTATISTICAS_ATLETAS : "acumula_estatisticas"
    PARTIDAS ||--o{ ESTATISTICAS_EQUIPES : "totaliza"
    EQUIPES ||--o{ ESTATISTICAS_EQUIPES : "consolida_metricas"
```

---

## 12. Segurança da Aplicação (Conformidade com o Anexo B)

Foi realizada uma revisão estática inicial conforme o Checklist de Segurança do Anexo B. Os resultados e pendências estão discriminados em `tests/test_checklist.md`; não se declara conformidade total antes da execução no ambiente publicado:

1. **Armazenamento de Senhas (RNF01)**:
   - Funções utilizadas: `password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10])` e `password_verify($senha, $hash)`.
   - Nenhuma senha em texto puro é registrada em logs ou armazenada no banco.
2. **Proteção contra SQL Injection (RNF03)**:
   - Conexão configurada em `config/database.php` com `PDO::ATTR_EMULATE_PREPARES => false`.
   - Consultas que recebem dados externos usam prepared statements com vinculação de parâmetros; consultas sem parâmetros são SQL estático.
3. **Controle de Acesso e RBAC (RNF05)**:
   - Verificação em nível de servidor antes do processamento de qualquer rota administrativa através da função `exigirAutenticacao(['admin', 'organizador'])`.
   - Resposta HTTP 403 com layout visual amigável e bloqueio de execução caso o usuário não possua privilégios suficientes.
4. **Sessões e Cookies Seguros**:
   - Inicialização padronizada com `session_set_cookie_params()` impondo diretivas `httponly => true` (impossibilita leitura de sessão via JavaScript) e `samesite => 'Lax'`.
   - Regeneração de ID de sessão (`session_regenerate_id(true)`) no momento do login para mitigar ataques de fixação de sessão.
5. **Proteção contra Cross-Site Scripting (XSS)**:
   - As saídas dinâmicas das páginas públicas e administrativas são tratadas com `htmlspecialchars()` nos pontos revisados; a verificação de todos os templates deve continuar no checklist de testes.
   - Entradas de formulário são sanitizadas previamente via `strip_tags()` e filtros nativos do PHP (`FILTER_SANITIZE_EMAIL`).
6. **Proteção contra Exposição de Dados Sensíveis (RNF04 e RNF07)**:
   - Arquivo `.env` isolado no `.gitignore`.
   - Modos de exibição de erros desativados em produção e mensagens de exceção de banco de dados capturadas sem revelação de estrutura de tabelas ou credenciais.

---

## 13. Interface e Navegação

A interface visual do JIF+ adota a identidade institucional dos Institutos Federais, com paleta centrada no verde floresta (`#006633`), verde esportivo neon (`#00cc66`), dourado troféu (`#e6a100`) e tons neutros de alto contraste.

### Mapa de Rotas e Navegação

```text
Portal JIF+
├── Área Pública (Acesso Livre)
│   ├── /index.php                  (Home: Placares ao vivo, próximos jogos e artilharia)
│   ├── /competicoes.php            (Edições dos jogos e fases)
│   ├── /modalidades.php            (Esportes, categorias e regulamentos)
│   ├── /equipes.php                (Times participantes e elencos)
│   ├── /atletas.php                (Boletim Informativo do Atleta - BID e histórico)
│   ├── /partidas.php               (Calendário oficial e visualização de súmulas)
│   ├── /classificacao.php          (Tabela oficial de pontuação com desempate)
│   └── /inteligencia.php           (Painéis de inteligência esportiva e Fair Play)
│
├── Área de Acesso e Identidade
│   ├── /login.php                  (Autenticação segura com validação de perfil)
│   ├── /cadastro.php               (Autocadastro de estudantes-atletas e técnicos)
│   └── /logout.php                 (Encerramento seguro de sessão)
│
└── Área Administrativa Restrita (/admin)
    ├── /admin/index.php            (Painel de controle geral e atalhos operacionais)
    ├── /admin/instituicoes.php     (Gestão de campi do Instituto Federal)
    ├── /admin/competicoes.php      (Gestão e homologação de competições)
    ├── /admin/modalidades.php      (Parametrização de esportes oficiais)
    ├── /admin/equipes.php          (Inscrição de equipes e vínculos de técnicos)
    ├── /admin/atletas.php          (Inscrição e vinculação de atletas às equipes)
    ├── /admin/partidas.php         (Agendamento de jogos e rodadas)
    ├── /admin/sumula.php           (Súmula eletrônica, placares, gols e cartões)
    └── /admin/usuarios.php         (Gestão de usuários e permissões RBAC - Exclusivo Admin)
```

---

## 14. Critérios de Aceitação e Testes

A conformidade do sistema foi validada por uma bateria automatizada de testes implementada em [`tests/test_full_suite.php`](tests/test_full_suite.php), correspondendo aos 17 Critérios de Aceitação (CA01 a CA17):

| Critério | Requisito | Cenário de Teste Previsto | Status de Execução |
| :---: | :---: | :--- | :---: |
| **CA01** | RF01 | Cadastro de novo usuário persistido com hash bcrypt e validação de e-mail único. | **Implementado; reexecução pendente** |
| **CA02** | RF02 | Autenticação com credenciais corretas e rejeição de senhas inválidas sem vazamento de existência do e-mail. | **Parcialmente verificado em produção: login administrativo válido; teste de senha inválida pendente** |
| **CA03** | RF03 | Bloqueio de acesso a páginas administrativas por usuários sem privilégio (RBAC) e liberação para Administrador. | **Implementado; reexecução pendente** |
| **CA04** | RF04 | Cadastro e persistência de campus/instituição participante no banco de dados. | **Implementado; reexecução pendente** |
| **CA05** | RF05 | Cadastro de competição com datas de início/fim e gravação do status inicial "planejada". | **Implementado; reexecução pendente** |
| **CA06** | RF06 | Associação de modalidade esportiva vinculada a uma competição com categoria e formato válidos. | **Implementado; reexecução pendente** |
| **CA07** | RF07 | Inscrição de equipes com chaves estrangeiras válidas de instituição, competição e modalidade. | **Implementado; reexecução pendente** |
| **CA08** | RF08 | Inscrição de estudante-atleta com matrícula institucional, dorsal (número de camisa) e posição tática. | **Implementado; reexecução pendente** |
| **CA09** | RF09 | Consulta e renderização completa de perfil individual (BID) com histórico de partidas e gols. | **Implementado; reexecução pendente** |
| **CA10** | RF10 | Agendamento de confronto entre equipes distintas pertencentes à mesma competição e modalidade. | **Implementado; reexecução pendente** |
| **CA11** | RF11 | Lançamento de placares parciais/finais e transição de status da partida para "encerrada". | **Implementado; reexecução pendente** |
| **CA12** | RF12 | Registro de eventos individuais na súmula (gols e faltas) com reflexo imediato no perfil do atleta. | **Implementado; reexecução pendente** |
| **CA13** | RF13 | Visualização pública da equipe com elenco escalado, comissão técnica e histórico de resultados. | **Implementado; reexecução pendente** |
| **CA14** | RF14 | Mecanismo de busca textual parametrizada por nome de atleta e filtros esportivos combinados. | **Implementado; reexecução pendente** |
| **CA15** | RF15 | Cálculo automático da tabela de classificação (pontos 3/1/0, saldo de gols e gols pró). | **Implementado; reexecução pendente** |
| **CA16** | RF16 | Rejeição no servidor de entradas inválidas ou malformadas, com mensagens de erro claras. | **Parcial; reexecução pendente** |
| **CA17** | RF17 | Integridade referencial do banco de dados relacional e persistência permanente após transações. | **Verificação manual parcial em produção: cadastro de atleta persistido e exibido na consulta pública; suíte automatizada pendente** |

**Resultado Geral dos Testes nesta revisão**: a suíte automatizada de 17 critérios está disponível, mas não foi executada integralmente nesta homologação. Foram realizados testes manuais no ambiente publicado: abertura da página inicial em janela anônima, login administrativo e cadastro de um atleta fictício seguido de consulta na página pública do BID. Esses testes comprovam parte do fluxo de autenticação e persistência, mas não substituem a execução de `php tests/test_full_suite.php` nem validam todos os critérios CA01 a CA17.

---

## 15. Implantação e Execução Local

### 15.1. Execução no Ambiente Local (XAMPP)
O sistema está plenamente funcional e configurado para execução local no servidor XAMPP (Apache e MySQL):

1. **Pré-requisitos**:
   - XAMPP instalado com PHP 8.2 ou superior e MySQL 8.0/MariaDB ativo.
   - Navegador web moderno (Google Chrome, Mozilla Firefox, Microsoft Edge).
2. **Localização dos Arquivos**:
   - Diretório de trabalho: `C:\xampp\htdocs\4°Bim - Arthur Gimax - JIF+\`
3. **Inicialização dos Serviços**:
   - Abrir o **XAMPP Control Panel** e iniciar os módulos **Apache** e **MySQL**.
4. **Instalação do Banco de Dados**:
   - Executar no terminal: `C:\xampp\php\php.exe database/setup.php` (o instalador é bloqueado via navegador).
   - O script cria o banco `jif_plus`, aplica o schema não destrutivo das 10 tabelas e insere dados de demonstração somente se o banco ainda estiver vazio.
5. **Acesso à Aplicação**:
   - URL Principal: `http://localhost/4°Bim - Arthur Gimax - JIF+/`
   - URL Administrativa: `http://localhost/4°Bim - Arthur Gimax - JIF+/admin/`

### 15.2. Publicação Externa e Homologação (Fase 6 da Metodologia)

- **Provedor escolhido:** InfinityFree, para preservar a stack PHP + PDO + MySQL/MariaDB sem reescrever o backend.
- **Domínio publicado:** `jifplus-ifro.free.nf`.
- **Diretório remoto:** `/htdocs`; os arquivos PHP, CSS, JavaScript e pastas da aplicação foram enviados diretamente para a raiz pública.
- **Banco remoto:** MySQL criado no painel do InfinityFree; o phpMyAdmin exibiu as 10 tabelas previstas. As credenciais de conexão foram armazenadas no arquivo `.env` da hospedagem, fora do repositório.
- **Conta administrativa:** conta própria criada com hash bcrypt; login no painel administrativo foi realizado com sucesso.
- **Teste de persistência:** um atleta fictício foi cadastrado pelo painel administrativo e apareceu na listagem pública do BID, confirmando escrita e leitura no banco remoto.
- **Teste de acesso público:** página inicial e listagem de atletas abertas em janela anônima.
- **HTTPS/TLS:** pendente de homologação. A captura do navegador exibiu o indicador “Não seguro”; portanto, o requisito RNF08 ainda não pode ser considerado atendido.
- **Testes automatizados:** a suíte CA01–CA17 permanece pendente de execução integral no ambiente com PDO MySQL. Os testes manuais realizados não autorizam declarar aprovação total.

#### Decisão sobre Vercel e Supabase
- **Vercel:** não é destino direto para a aplicação PHP/Apache convencional; exigiria adaptar o backend para funções compatíveis ou reestruturar a aplicação.
- **Supabase:** oferece PostgreSQL e serviços de backend, mas não executa PHP. A adoção exigiria migrar o esquema MySQL e revisar as consultas PDO.
- **Decisão desta entrega:** InfinityFree foi escolhido para manter a stack original e cumprir a publicação sem migração de tecnologia. Render com Docker é alternativa possível, mas requer configuração adicional e banco persistente externo.

#### Segurança da publicação
- O repositório atual contém `.gitignore` para excluir `.env` e bancos locais. O `.htaccess` configura bloqueio de acesso direto a arquivos de ambiente e pastas internas; a resposta de bloqueio deve ser testada externamente antes da entrega.
- A versão atual do seed cria contas de demonstração inativas. Contudo, o histórico público antigo contém credenciais de demonstração e um `SESSION_SECRET` estático de exemplo. É necessário confirmar que as contas de demonstração estão inativas no banco de produção e decidir a limpeza do histórico ou a restrição do repositório com acesso liberado ao professor.
- Permanecem pendentes: validar certificado HTTPS, confirmar bloqueio externo do `.env`, testar bloqueio RBAC com usuário sem privilégio, executar a suíte automatizada e revisar integralmente validações administrativas, CSRF e limitação de tentativas de login.
---

## 16. Limitações Conhecidas e Melhorias Futuras

Os módulos funcionais estão implementados no código recebido, mas a conformidade final dos critérios de aceite depende da reexecução da suíte e dos testes manuais no ambiente publicado. Para versões futuras de expansão institucional, registram-se as seguintes oportunidades de melhoria planejadas:

1. **Transmissão Lance a Lance em Tempo Real**:
   - *Status*: Planejada para a versão 2.0.
   - *Descrição*: Implementação de WebSockets ou *Server-Sent Events* (SSE) para atualização instantânea dos lances da súmula na tela do visitante sem necessidade de recarregar a página.
2. **Gerador Automático de Chaveamento Eliminatório (Brackets)**:
   - *Status*: Planejada para a versão 2.0.
   - *Descrição*: Geração automatizada de árvores eliminatórias (quartas de final, semifinais e final) com base na classificação da fase de grupos.
3. **Upload de Fotos para Armazenamento em Nuvem (CDN/S3)**:
   - *Status*: Planejada para a versão 2.0.
   - *Descrição*: Substituição dos caminhos de imagens locais por envio direto para serviços de armazenamento como Amazon S3 ou Cloudinary com recorte automático de imagens de atletas.
4. **Exportação de Súmulas e Relatórios em PDF**:
   - *Status*: Planejada para a versão 2.0.
   - *Descrição*: Geração de relatórios oficiais das partidas e tabelas de classificação em formato PDF com cabeçalho institucional e assinatura digital do árbitro responsável.

---

## 17. Considerações Finais

O desenvolvimento da plataforma **JIF+** consolidou na prática a aplicação das competências da disciplina de Desenvolvimento de Sistemas no IFRO — Campus Porto Velho Calama. Ao aliar o uso responsável e crítico de ferramentas de inteligência artificial à revisão humana constante, preservou-se a integridade do código, a conformidade rigorosa aos requisitos da especificação original e o respeito integral aos padrões de segurança em desenvolvimento web.

O sistema entrega um protótipo web operacional para a gestão das competições esportivas da Rede Federal. A homologação em hospedagem real confirmou acesso público, login administrativo e um fluxo de cadastro/consulta; a conformidade de segurança e os critérios restantes continuam sujeitos às verificações pendentes registradas nesta especificação.

---

## Status de publicação e validação (revisão de 13/11/2026)

- **Stack em produção:** PHP tradicional com PDO e MySQL/MariaDB, hospedado no InfinityFree.
- **URL pública:** `jifplus-ifro.free.nf`; a página inicial e a listagem BID foram abertas em janela anônima.
- **Fluxo funcional verificado:** login de administrador concluído; atleta fictício cadastrado no painel e localizado na listagem pública. Isso comprova parcialmente autenticação e persistência de dados em produção.
- **Banco remoto:** phpMyAdmin exibiu as 10 tabelas previstas. As credenciais estão no `.env` do servidor e não devem ser copiadas para o GitHub ou para esta especificação.
- **Vercel/Supabase:** não utilizados. Vercel não executa diretamente esta aplicação PHP/Apache; Supabase não hospeda PHP e exigiria migração MySQL para PostgreSQL.
- **Testes automatizados:** suíte CA01–CA17 presente no repositório, mas ainda não executada integralmente nesta homologação. Não há declaração de aprovação total.
- **HTTPS:** pendente. O navegador exibiu “Não seguro”; o certificado TLS precisa ser configurado e validado antes de declarar RNF08 conforme.
- **Segurança do arquivo de ambiente:** `.env` é ignorado pelo Git e o `.htaccess` contém regras de bloqueio, mas o bloqueio externo deve ser confirmado com teste de acesso direto.
- **Histórico Git:** o repositório público contém commits anteriores desde outubro de 2026, mas um commit inicial ainda expõe credenciais de demonstração e um `SESSION_SECRET` estático de exemplo. O seed atual desativa as contas de demonstração; confirmar a inativação no banco de produção e decidir a limpeza do histórico ou a restrição do repositório com acesso do professor.
- **Checklist de segurança:** senhas com hash e login administrativo foram exercitados; prepared statements e controle de acesso foram revisados no código. Validação integral de formulários, teste RBAC negativo, TLS, bloqueio externo do `.env`, CSRF e limitação de tentativas continuam pendentes.
## 18. Histórico de Versões

| Versão | Data | Autor | Resumo das Modificações e Evoluções Registradas |
| :---: | :---: | :--- | :--- |
| **0.1** | Outubro de 2026 | Arthur Henrique Gimax Oliveira | Elaboração da proposta preliminar do sistema esportivo, identificação do problema e formulação dos objetivos iniciais. |
| **0.5** | Novembro de 2026 | Arthur Henrique Gimax Oliveira | Definição da stack tecnológica oficial (PHP 8.2+, MySQL InnoDB, PDO), detalhamento dos requisitos RF01 a RF17, modelagem das 10 tabelas relacionais e estruturação do DER em Mermaid. |
| **1.0** | Novembro de 2026 | Arthur Henrique Gimax Oliveira | Registro original da implementação e dos critérios CA01 a CA17. A alegação de aprovação integral registrada na versão original não foi reexecutada nesta auditoria. |
| **1.0.2** | 13/11/2026 | Arthur Henrique Gimax Oliveira | Revisão de publicação: correção de cadastro de perfis, desativação de contas seed, schema não destrutivo, instalador CLI, variáveis de ambiente e checklist do Anexo B. Deploy e testes de integração permanecem pendentes.
| **1.0.3** | 13/11/2026 | Arthur Henrique Gimax Oliveira | Correção da suíte de testes identificada pela integração contínua, com substituição de placeholders PDO duplicados. |
| **1.0.4** | 13/11/2026 | Arthur Henrique Gimax Oliveira | Publicação manual no InfinityFree; login administrativo e fluxo de cadastro/consulta de atleta confirmados. HTTPS, suíte automatizada completa e revisão do histórico Git permanecem pendentes. |
| **1.0.3** | 13/11/2026 | Arthur Henrique Gimax Oliveira | Correção da suíte de testes identificada pela integração contínua, com substituição de placeholders PDO duplicados. |
| **1.0.4** | 13/11/2026 | Arthur Henrique Gimax Oliveira | Publicação manual no InfinityFree; login administrativo e fluxo de cadastro/consulta de atleta confirmados. HTTPS, suíte automatizada completa e revisão do histórico Git permanecem pendentes. |