/**
 * JIF+ — Scripts de Interatividade e Validações Dinâmicas (ES6+)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Confirmação para ações sensíveis de administração/homologação
    const dangerousActions = document.querySelectorAll('[data-confirm]');
    dangerousActions.forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.getAttribute('data-confirm') || 'Tem certeza que deseja executar esta ação?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 2. Filtro dinâmico de pesquisa rápida em tabelas esportivas
    const searchInputs = document.querySelectorAll('[data-table-filter]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', () => {
            const targetTableSelector = input.getAttribute('data-table-filter');
            const targetTable = document.querySelector(targetTableSelector);
            if (!targetTable) return;

            const term = input.value.toLowerCase().trim();
            const rows = targetTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // 3. Auto-ocultar alertas de sessão após 6 segundos
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 6000);
    });
});