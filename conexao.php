<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "login";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);
if ($conexao->connect_error) {
    echo "Falha na conexão:( " . $conexao->connect_error . ")" . $conexao->connect_error;
}
?>