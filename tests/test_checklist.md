# Checklist de Segurança — Anexo B da apostila

A conferência abaixo separa revisão de código, testes automatizados executados no GitHub Actions, testes manuais na hospedagem e itens que continuam pendentes. A aprovação dos testes de aceite não significa que todos os controles de segurança foram integralmente homologados.

| Item do Anexo B | Status nesta revisão | Evidência / observação |
|---|---|---|
| Senhas armazenadas com hash, nunca em texto puro | **Aprovado nos testes automatizados e no fluxo manual** | O código usa password_hash() e password_verify(). O teste CA01 foi aprovado no CI; a conta administrativa de produção foi criada com hash bcrypt e o login foi realizado com sucesso. |
| Toda consulta ao banco usa parâmetros/prepared statements | **Testes automatizados aprovados; auditoria integral de código ainda recomendada** | O workflow CI passou pela suíte CA01–CA17 e a conexão PDO desativa emulação de prepared statements. A aprovação dos cenários automatizados não substitui revisão manual de todas as consultas e módulos. |
| Todo campo de formulário validado no servidor | **Parcial** | O teste CA16 de rejeição de dados inválidos passou no CI. Ainda falta revisar todos os campos administrativos, limites, enumerações e consistência relacional de cada ação. |
| Nenhuma senha, chave de API ou string de conexão commitada no código-fonte | **Pendente — histórico público contém dados de demonstração antigos** | O estado atual do repositório ignora .env e bancos locais e o .env.example atual contém apenas valores de exemplo. Entretanto, commits antigos do histórico público incluem credenciais de demonstração do seed/testes e um SESSION_SECRET estático de exemplo. O seed atual usa contas example.test inativas; o responsável confirmou que desativou os cinco usuários demonstrativos adicionais na produção. A limpeza do histórico Git continua pendente. |
| Acesso restrito por tipo de usuário | **Teste automatizado aprovado; verificação adicional em produção pendente** | O teste CA03 de permissões RBAC passou no CI. O login administrativo e o acesso ao painel também foram confirmados na hospedagem. Ainda falta testar o bloqueio real usando uma conta sem privilégio no ambiente publicado. |
| Mensagens de erro não expõem detalhes internos | **Conforme na revisão de código; teste remoto pendente** | A conexão registra detalhes em log e apresenta mensagens genéricas. Ainda falta provocar erros controlados na hospedagem para verificar a resposta exibida ao visitante. |
| HTTPS ativo | **Conforme por confirmação visual do responsável** | O responsável confirmou que, em janela anônima, o navegador passou a exibir conexão segura (HTTPS) em 13/11/2026. |

## Testes automatizados executados

O workflow do GitHub Actions [43462038217](https://github.com/arthurgimax/JIF-/actions/runs/43462038217) foi concluído com sucesso em 13/11/2026, no commit f8fdacfde7ace2ca41b8843b754f065654c19e1b.

- Ambiente: PHP 8.4 e MySQL 8.0.46.
- Verificação de sintaxe PHP: aprovada.
- Execução do instalador database/setup.php, criação/verificação do banco e aplicação do schema/seed: aprovadas.
- Teste de conexão e leitura das tabelas: aprovado.
- Teste de autenticação e RBAC: aprovado.
- Suíte CA01–CA17: **17 de 17 critérios aprovados**.

## Testes manuais já realizados na hospedagem

- Página inicial aberta em janela anônima.
- Login com a conta de administrador concluído.
- Cadastro de um atleta fictício pelo painel administrativo.
- O atleta de teste apareceu na listagem pública do BID, confirmando gravação e leitura do registro no MySQL remoto.
- O phpMyAdmin exibiu as 10 tabelas previstas.
- HTTPS confirmado visualmente pelo responsável em janela anônima.

## Verificações pendentes antes da entrega

- Limpar o histórico Git para remover credenciais fictícias de demonstração e o SESSION_SECRET de exemplo dos commits antigos; a versão atual não deve voltar a incluir esses valores.
- Confirmar que uma requisição direta a /.env e a /config/database.php é bloqueada e não revela conteúdo.
- Testar o bloqueio de uma rota administrativa no ambiente publicado usando usuário sem privilégios. O cenário RBAC automatizado passou no CI.
- Executar o roteiro de instalação da seção 15 da spec em um computador limpo, sem reaproveitar o banco ou a pasta local do autor.
- Revisar integralmente a validação de todos os campos administrativos.

## Melhorias adicionais recomendadas

- Implementar tokens CSRF para formulários e ações autenticadas.
- Trocar operações que alteram dados via GET por POST.
- Adicionar limitação de tentativas de login.
- Concluir validações de domínio em todos os formulários administrativos.
