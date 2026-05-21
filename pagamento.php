<?php
// pagamento.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor = (float)$_POST['total'];
    $token = "SEU_ACCESS_TOKEN_AQUI"; // Cole aqui o token que você pegou no portal

    $url = "https://api.mercadopago.com/v1/payments";

    $data = [
        "transaction_amount" => $valor,
        "description" => "Pedido Restaurante",
        "payment_method_id" => "pix",
        "payer" => [
            "email" => "test_user_123@testuser.com" // E-mail fictício para teste
        ]
    ];

    $options = [
        "http" => [
            "header" => "Authorization: Bearer " . $token . "\r\n" .
                        "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($data),
            "ignore_errors" => true 
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    // Devolve a resposta da API para o seu JavaScript
    echo $response;
}
?>