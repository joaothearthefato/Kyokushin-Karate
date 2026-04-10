<?php
/**
 * ATUALIZAR_TREINO.PHP
 * Handler para atualizar treinos registrados
 */

session_start();
require '../php/config.php';

// RNF04 – Validação de Sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$treino_id = intval($_POST['treino_id'] ?? 0);
$data_treino = trim($_POST['data_treino'] ?? '');
$duracao_min = intval($_POST['duracao_min'] ?? 0);
$observacoes = trim($_POST['observacoes'] ?? '');

// Validar ID do treino
if ($treino_id <= 0) {
    header("Location: treinos.php?erro=treino_invalido");
    exit();
}

// Verificar se o treino pertence ao usuário
$sql_check = "SELECT id FROM treinos WHERE id = '$treino_id' AND usuario_id = '$usuario_id'";
$result_check = mysqli_query($conn, $sql_check);

if (!$result_check || mysqli_num_rows($result_check) === 0) {
    header("Location: treinos.php?erro=acesso_negado");
    exit();
}

// Validações de data e duração
$data_atual = date('Y-m-d');
if ($data_treino > $data_atual) {
    header("Location: editar_treino.php?id=$treino_id&erro=data_futura");
    exit();
}

if (empty($data_treino) || $duracao_min < 5 || empty($observacoes)) {
    header("Location: editar_treino.php?id=$treino_id&erro=campos_obrigatorios");
    exit();
}

// Sanitizar dados
$data_treino = mysqli_real_escape_string($conn, $data_treino);
$observacoes = mysqli_real_escape_string($conn, $observacoes);

// Atualizar treino
$sql_update = "UPDATE treinos 
               SET data_treino = '$data_treino', 
                   duracao_min = '$duracao_min', 
                   observacoes = '$observacoes'
               WHERE id = '$treino_id'";

if (!mysqli_query($conn, $sql_update)) {
    error_log("Erro ao atualizar treino: " . mysqli_error($conn));
    header("Location: editar_treino.php?id=$treino_id&erro=banco_dados");
    exit();
}

// Deletar exercícios antigos
$sql_delete_exercicios = "DELETE FROM treino_exercicios WHERE treino_id = '$treino_id'";
mysqli_query($conn, $sql_delete_exercicios);

$total_exercicios = 0;

// Inserir novos exercícios
if (isset($_POST['exercicios']) && is_array($_POST['exercicios'])) {
    foreach ($_POST['exercicios'] as $exercicio) {
        $descricao = trim($exercicio['descricao'] ?? '');
        $series = intval($exercicio['series'] ?? 0);
        $repeticoes = intval($exercicio['repeticoes'] ?? 0);

        if (!empty($descricao)) {
            $descricao = mysqli_real_escape_string($conn, $descricao);

            $sql_exercicio = "INSERT INTO treino_exercicios (treino_id, descricao, series, repeticoes)
                              VALUES ('$treino_id', '$descricao', '$series', '$repeticoes')";

            if (mysqli_query($conn, $sql_exercicio)) {
                $total_exercicios++;
            } else {
                error_log("Erro ao inserir exercício: " . mysqli_error($conn));
            }
        }
    }
}

// Validação: deve ter pelo menos 1 exercício
if ($total_exercicios === 0) {
    // Deletar o treino para manter consistência
    $sql_delete = "DELETE FROM treinos WHERE id = '$treino_id'";
    mysqli_query($conn, $sql_delete);
    header("Location: treinos.php?erro=sem_exercicios");
    exit();
}

mysqli_close($conn);

// Mensagem de confirmação
header("Location: treinos.php?sucesso=treino_atualizado");
exit();
?>
