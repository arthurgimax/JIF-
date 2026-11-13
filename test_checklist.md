# Checklist de Segurança Básica — JIF+ (Conferência Anexo B)

Conferência item a item dos requisitos obrigatórios de segurança estabelecidos no **Anexo B da apostila** *"Programar com IA de forma responsável"* (Prof. Rafael Pitwak).

| Item de Segurança (Anexo B) | Status | Implementação no JIF+ | Arquivos de Referência |
| :--- | :---: | :--- | :--- |
| **1. Senhas armazenadas com hash, nunca em texto puro** | ✅ **CONFORME** | Senhas convertidas exclusivamente com a função nativa `password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10])`. O hash é verificado via `password_verify()`. Nenhuma senha em texto plano é persistida. | [`config/auth.php`](../config/auth.php), [`database/seed.sql`](../database/seed.sql) |
| **2. Toda consulta ao banco usa parâmetros/prepared statements** | ✅ **CONFORME** | Emulação de prepared statements desativada (`PDO::ATTR_EMULATE_PREPARES => false`). 100% das queries de seleção, inserção, atualização e exclusão utilizam parâmetros vinculados (`:parametro`). Zero concatenação direta de dados de usuários em strings SQL. | [`config/database.php`](../config/database.php), [`admin/sumula.php`](../admin/sumula.php), todas as páginas |
| **3. Todo campo de formulário validado no servidor** | ✅ **CONFORME** | Validação mandatória no backend para e-mails (`filter_var`), tamanhos mínimos de senha, regras de unicidade e regras esportivas (equipes de mesma modalidade, mandante e visitante distintos). | [`cadastro.php`](../cadastro.php), [`admin/partidas.php`](../admin/partidas.php), [`admin/equipes.php`](../admin/equipes.php) |
| **4. Nenhuma senha, chave de API ou string de conexão commitada no código** | ✅ **CONFORME** | Credenciais residem exclusivamente no `.env`. Arquivo `.gitignore` configurado para ignorar `.env` e arquivos locais. Disponibilizado `.env.example` sem valores sensíveis. | [`.gitignore`](../.gitignore), [`.env.example`](../.env.example) |
| **5. Acesso restrito por tipo de usuário (RBAC)** | ✅ **CONFORME** | Função `exigirAutenticacao($perfisPermitidos)` aplicada no início de todas as rotas administrativas e operacionais. Um usuário regular não consegue acessar páginas administrativas manipulando a URL (retorna HTTP 403). | [`config/auth.php`](../config/auth.php), páginas em [`admin/`](../admin/) |
| **6. Mensagens de erro não expõem detalhes internos do sistema** | ✅ **CONFORME** | `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` com tratamento seguro via `try/catch`. Erros internos são registrados em logs e mensagens amigáveis são exibidas ao usuário, sem expor queries, tabelas ou stack traces. | [`config/database.php`](../config/database.php), [`login.php`](../login.php) |
| **7. Prevenção contra Cross-Site Scripting (XSS)** | ✅ **CONFORME** | Toda e qualquer saída de dados vinda do banco ou de parâmetros de URL é devidamente tratada com `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`. Cookies de sessão configurados com diretiva `HttpOnly => true`. | [`includes/header.php`](../includes/header.php), [`config/auth.php`](../config/auth.php) |
| **8. Dados pessoais sensíveis preservados** | ✅ **CONFORME** | Dados estritamente esportivos (nome esportivo, campus, dorsal, modalidade) expostos no BID. Documentos privados (CPF, RG, telefones) não são coletados nem exibidos no ambiente público. | [`atletas.php`](../atletas.php) |

---

### Resumo da Auditoria de Segurança:
- **Itens avaliados**: 8 de 8
- **Conformidade**: **100% de aprovação**
- **Vulnerabilidades críticas encontradas**: 0
