# Checklist de Segurança — Anexo B da apostila

A apostila contém sete itens. O status abaixo distingue o que foi verificado no código do que só pode ser confirmado após a publicação. Não se declara conformidade total antes dos testes no ambiente real.

| Item do Anexo B | Status nesta revisão | Evidência / observação |
|---|---|---|
| Senhas armazenadas com hash, nunca em texto puro | **Conforme no código** | Cadastro usa `password_hash()` e login usa `password_verify()`. Contas de seed estão inativas e não têm senha de demonstração divulgada. |
| Toda consulta ao banco usa parâmetros/prepared statements | **Conforme na aplicação auditada** | Consultas que recebem dados de formulários ou filtros usam `PDO::prepare()` e parâmetros. Consultas sem entrada variável usam SQL fixo. |
| Todo campo de formulário validado no servidor | **Parcial** | Cadastro público valida nome, e-mail e senha. Módulos administrativos têm validações principais, mas ainda falta uma revisão completa de limites, enumerações e consistência relacional em todas as ações. |
| Nenhuma senha, chave de API ou string de conexão commitada | **Pendente de limpeza do remoto** | A cópia local atual ignora `.env`, `config.local.php` e SQLite. Porém, a referência remota `origin/main` ainda contém `jif_plus.sqlite` em um commit antigo, com contas ativas e matrículas; também há credenciais de demonstração em commits antigos. A versão atual remove esses itens do estado mais recente, mas o remoto precisa ser atualizado e a limpeza do histórico avaliada antes de tornar o repositório público. |
| Acesso restrito por tipo de usuário | **Conforme no código revisado** | Rotas administrativas chamam `exigirAutenticacao()`. O cadastro público agora sempre cria perfil `atleta`; perfis elevados são atribuídos por administrador. |
| Mensagens de erro não expõem detalhes internos | **Conforme na aplicação web** | Falhas de conexão são registradas em log e a interface recebe mensagem genérica. O instalador de banco agora é restrito ao CLI. |
| HTTPS ativo | **Pendente de publicação** | Só poderá ser confirmado depois que o domínio de produção estiver configurado com certificado SSL e testado em `https://`. |

## Revisões adicionais recomendadas

- Implementar proteção CSRF para ações autenticadas e trocar operações que alteram dados via GET por POST.
- Adicionar limitação de tentativas de login.
- Concluir validações de domínio em todos os formulários administrativos.
- Testar cadastro, login, permissões e persistência no provedor de produção.