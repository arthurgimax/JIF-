<?php
/**
 * JIF+ — Encerramento Seguro de Sessão (Logout)
 */
require_once __DIR__ . '/config/auth.php';

encerrarSessao();
iniciarSessaoSegura();
$_SESSION['mensagem_info'] = 'Sua sessão foi encerrada com segurança.';
header('Location: index.php');
exit;