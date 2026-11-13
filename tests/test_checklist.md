# Checklist de Segurança — Anexo B da apostila

A conferência abaixo separa revisão de código, testes manuais executados na hospedagem e itens que continuam pendentes. Não se declara conformidade total sem evidência.

| Item do Anexo B | Status nesta revisão | Evidência / observação |
|---|---|---|
| Senhas armazenadas com hash, nunca em texto puro | **Conforme no fluxo exercitado; revisão final pendente** | O código usa `password_hash()` e `password_verify()`. A conta administrativa foi criada com hash bcrypt e o login foi realizado com sucesso na hospedagem. Não se registra nem se publica a senha original. |
| Toda consulta ao banco usa parâmetros/prepared statements | **Conforme na revisão de código; auditoria integral pendente** | A conexão PDO desativa emulação de prepared statements e as consultas que recebem valores externos usam parâmetros. Ainda é recomendável revisar todos os módulos e executar a suíte automatizada. |
| Todo campo de formulário validado no servidor | **Parcial** | O cadastro público valida nome, e-mail e senha; os módulos administrativos têm validações principais, mas falta revisão completa de limites, enumerações e consistência relacional em todas as ações. |
| Nenhuma senha, chave de API ou string de conexão commitada no código-fonte | **Pendente — histórico público contém dados de demonstração antigos** | O estado atual do repositório ignora `.env` e bancos locais, e não contém o arquivo de produção. Entretanto, commits antigos do histórico público incluem credenciais de demonstração do seed/testes e um `SESSION_SECRET` estático de exemplo em `.env.example`. O seed atual usa contas `example.test` inativas e a suíte atual gera credenciais aleatórias. É necessário confirmar que todas as contas seed estão inativas no banco de produção e decidir a limpeza do histórico ou a restrição do repositório com acesso liberado ao professor. |
| Acesso restrito por tipo de usuário | **Parcialmente verificado** | O login administrativo e o acesso ao painel foram confirmados na hospedagem; o código protege rotas com `exigirAutenticacao()`. Ainda falta testar o bloqueio real usando uma conta sem privilégio. |
| Mensagens de erro não expõem detalhes internos | **Conforme no código; teste remoto pendente** | A conexão registra detalhes em log e apresenta mensagens genéricas. Ainda falta provocar erros controlados na hospedagem para verificar a resposta exibida ao visitante. |
| HTTPS ativo | **Pendente** | A captura do navegador exibiu o indicador “Não seguro”. O certificado TLS/HTTPS precisa ser configurado e validado em janela anônima antes de declarar o item conforme. |

## Testes manuais já realizados na hospedagem

- Página inicial aberta em janela anônima.
- Login com a conta de administrador concluído.
- Cadastro de um atleta fictício pelo painel administrativo.
- O atleta de teste apareceu na listagem pública do BID, confirmando gravação e leitura do registro no MySQL remoto.
- O phpMyAdmin exibiu as 10 tabelas previstas.

## Verificações pendentes antes da entrega

- Confirmar que as contas de demonstração antigas estão inativas no banco de produção.
- Resolver a exposição histórica de credenciais de demonstração: limpar o histórico Git ou restringir o repositório e conceder acesso ao professor, conforme a decisão de entrega.
- Confirmar que uma requisição direta a `/.env` e a `/config/database.php` é bloqueada e não revela conteúdo.
- Configurar e validar certificado HTTPS/TLS.
- Testar o bloqueio de uma rota administrativa com usuário sem privilégios.
- Executar `php tests/test_full_suite.php` com PDO MySQL e registrar o resultado real.

## Melhorias adicionais recomendadas

- Implementar tokens CSRF para formulários e ações autenticadas.
- Trocar operações que alteram dados via GET por POST.
- Adicionar limitação de tentativas de login.
- Concluir validações de domínio em todos os formulários administrativos.
