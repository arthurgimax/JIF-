<?php
/**
 * JIF+ — Renderizador de Alertas Visuais e Mensagens de Feedback
 */
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$tiposAlerta = [
    'mensagem_sucesso' => ['classe' => 'alert-success', 'icone' => '✅'],
    'mensagem_erro' => ['classe' => 'alert-danger', 'icone' => '❌'],
    'mensagem_aviso' => ['classe' => 'alert-warning', 'icone' => '⚠️'],
    'mensagem_info' => ['classe' => 'alert-info', 'icone' => 'ℹ️']
];

foreach ($tiposAlerta as $chave => $config) {
    if (isset($_SESSION[$chave])) {
        $msg = htmlspecialchars($_SESSION[$chave], ENT_QUOTES, 'UTF-8');
        echo "<div class='alert {$config['classe']} alert-dismissible container' style='margin-top:20px;'>";
        echo "<span>{$config['icone']}</span> <div>{$msg}</div>";
        echo "</div>";
        unset($_SESSION[$chave]);
    }
}
