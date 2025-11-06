<?php
session_start();
$_SESSION['usuario_id'] = 1; // use o ID real de um beneficiário no banco
$_SESSION['usuario_perfil'] = 'beneficiario';
$_SESSION['usuario_nome'] = 'João Beneficiário';
header("Location: dashboard-beneficiario.php");
exit;
?>
