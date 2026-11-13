<?php
/**
 * JIF+ — Rodapé Institucional
 */
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
?>
</main>

<footer class="footer">
    <div class="footer-container">
        <div>
            <h4 style="color:#ffffff;margin-bottom:12px;font-size:1.1rem;display:flex;align-items:center;gap:8px;">
                🏆 JIF+ Gestão Esportiva
            </h4>
            <p style="margin-bottom:12px;">
                Plataforma oficial de gerenciamento e inteligência esportiva para os Jogos dos Institutos Federais, unificando competições, equipes, atletas, súmulas e estatísticas.
            </p>
            <p style="font-size:0.8rem;color:#64748b;">
                Desenvolvimento de Sistemas — IFRO Campus Porto Velho Calama.
            </p>
        </div>

        <div>
            <h4 style="color:#ffffff;margin-bottom:12px;font-size:1rem;">Navegação Rápida</h4>
            <ul style="list-style:none;line-height:2;">
                <li><a href="<?= $basePath ?>index.php" style="color:#cbd5e1;">Página Inicial</a></li>
                <li><a href="<?= $basePath ?>partidas.php" style="color:#cbd5e1;">Calendário de Jogos</a></li>
                <li><a href="<?= $basePath ?>classificacao.php" style="color:#cbd5e1;">Tabela de Classificação</a></li>
                <li><a href="<?= $basePath ?>atletas.php" style="color:#cbd5e1;">Boletim Informativo do Atleta (BID)</a></li>
                <li><a href="<?= $basePath ?>inteligencia.php" style="color:#cbd5e1;">Painel de Inteligência Esportiva</a></li>
            </ul>
        </div>

        <div>
            <h4 style="color:#ffffff;margin-bottom:12px;font-size:1rem;">Segurança & Auditoria</h4>
            <p style="font-size:0.85rem;margin-bottom:10px;">
                Desenvolvido sob o protocolo de desenvolvimento com IA responsável (Prof. Rafael Pitwak): prepared statements, hash bcrypt de senhas e auditoria de súmula.
            </p>
            <div style="background:#1e293b;padding:8px 12px;border-radius:6px;font-size:0.8rem;border:1px solid #334155;">
                <strong>Autor:</strong> Arthur Gimax (3ºA Informática)<br>
                <strong>Instituição:</strong> IFRO Porto Velho Calama
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> JIF+ — Mais organização, transparência e inteligência para o esporte nos Institutos Federais.</p>
    </div>
</footer>

<script src="<?= $basePath ?>assets/js/app.js"></script>
</body>
</html>
