<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Pedidos - Restaurante</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="titulocep">
    <h2>📍 Consulta CEP</h2>


<div class="formcep">
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="cep">Digite seu CEP</label>
        <!-- Adicionei o pattern correto para aceitar o traço e o placeholder -->
        <input type="text" 
       id="cep" 
       name="cep" 
       placeholder="00000-000" 
       maxlength="9" 
       oninput="mascaraCEP(this)" 
       required>
        <button type="submit" name="verificar">VERIFICAR</button>
    </form>

    <?php 
    // Só entra aqui se o botão "verificar" for clicado
    if (isset($_POST['verificar']) && !empty($_POST['cep'])) {
        $cep = preg_replace('/[^0-9]/', '', $_POST['cep']);
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        
        $context = stream_context_create(["http" => ["ignore_errors" => true]]);
        $response = @file_get_contents($url, false, $context);
        $dados = json_decode($response);

        if ($dados && !isset($dados->erro)) {
            // Se o CEP for válido, mostramos os campos de resultado
            ?>
            <div class="resultado">
                <label>Rua:</label>
                <input type="text" value="<?php echo $dados->logradouro; ?>" readonly>

                <label>Bairro:</label>
                <input type="text" value="<?php echo $dados->bairro; ?>" readonly>

                <label>Cidade:</label>
                <input type="text" value="<?php echo $dados->localidade; ?>" readonly>

                <label>Estado:</label>
                <input type="text" value="<?php echo $dados->uf; ?>" readonly>

            </div>
            <?php
        } else {
            echo "<p style='color:red; margin-top:10px;'>CEP não encontrado ou inválido.</p>";
        }
    }
    ?>
</div>
    <button onclick="limpar()" class="btn-limpar">Limpar</button>
</div>
<h1>vai tomando
</h1>
<script>    
    function mascaraCEP(input) {
        // Remove tudo que não for número
        let valor = input.value.replace(/\D/g, '');

        // Aplica a máscara 00000-000
        valor = valor.replace(/^(\d{5})(\d)/, "$1-$2");

        // Atualiza o valor do campo
        input.value = valor;
    }
    function limpar() {
        document.getElementById("cep").value = "";
        document.querySelector(".resultado").style.display = "none";
        
    }
</script>
</body>
</html>