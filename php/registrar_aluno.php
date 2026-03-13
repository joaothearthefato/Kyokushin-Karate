<?php
include("config.php");

if(isset($_POST["nome"])){

$nome = $_POST["nome"];
$email = $_POST["email"];
$senha = $_POST["senha"];

$sql = "INSERT INTO usuarios_oyama (nome,email,senha,tipo)
VALUES ('$nome','$email','$senha','aluno')";

$res = mysqli_query($conn,$sql);

if($res){
echo "<script>alert('Usuário cadastrado!'); location.href='../index.html';</script>";
}else{
echo "<script>alert('Erro ao cadastrar');</script>";
}

}
?>