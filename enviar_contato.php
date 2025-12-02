<?php
require_once 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $telefone = mysqli_real_escape_string($conexao, $_POST['telefone']);
    $mensagem = mysqli_real_escape_string($conexao, $_POST['mensagem']);
    
    // Buscar chave do reCAPTCHA
    $sql = "SELECT valor FROM config WHERE chave = 'recaptcha_secret'";
    $result = mysqli_query($conexao, $sql);
    $recaptcha_secret = mysqli_fetch_assoc($result)['valor'];
    
    // Verificar reCAPTCHA se configurado
    if ($recaptcha_secret && isset($_POST['g-recaptcha-response'])) {
        $recaptcha_response = $_POST['g-recaptcha-response'];
        
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
        $captcha_success = json_decode($verify);
        
        if (!$captcha_success->success) {
            echo json_encode(['success' => false, 'message' => 'Por favor, valide o reCAPTCHA.']);
            exit();
        }
    }
    
    // Salvar no banco de dados (ou enviar por email)
    $sql = "INSERT INTO contatos (nome, email, telefone, mensagem, data) 
            VALUES ('$nome', '$email', '$telefone', '$mensagem', NOW())";
    
    if (mysqli_query($conexao, $sql)) {
        // Aqui você pode adicionar o envio por email
        echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar mensagem.']);
    }
}
?>