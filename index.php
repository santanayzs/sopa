<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Pedidos - Restaurante</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="cardapio">
    <h2>🍕 Pizzas</h2>
    <div class="item"><span>Calabresa - R$ 45,00</span> <div class="acoes"> <button onclick="subtract(1, 'Pizza Calabresa', 45.00)">-</button> <button onclick="add(1, 'Pizza Calabresa', 45.00)">+</button></div></div>
    <div class="item"><span>Marguerita - R$ 40,00</span> <div class="acoes"> <button onclick="subtract(2, 'Pizza Marguerita', 40.00)">-</button> <button onclick="add(2, 'Pizza Marguerita', 40.00)">+</button></div></div>
    <div class="item"><span>Portuguesa - R$ 50,00</span> <div class="acoes"> <button onclick="subtract(3, 'Pizza Portuguesa', 50.00)">-</button> <button onclick="add(3, 'Pizza Portuguesa', 50.00)">+</button></div></div>
    <div class="item"><span>Frango c/ Catupiry - R$ 48,00</span> <div class="acoes"> <button onclick="subtract(4, 'Pizza Frango', 48.00)">-</button> <button onclick="add(4, 'Pizza Frango', 48.00)">+</button></div></div>

    <h2>🥤 Bebidas</h2>
    <div class="item"><span>Coca-Cola - R$ 8,00</span> <div class="acoes"> <button onclick="subtract(5, 'Coca-Cola', 8.00)">-</button> <button onclick="add(5, 'Coca-Cola', 8.00)">+</button></div></div>
    <div class="item"><span>Suco Natural - R$ 10,00</span> <div class="acoes"> <button onclick="subtract(6, 'Suco Natural', 10.00)">-</button> <button onclick="add(6, 'Suco Natural', 10.00)">+</button></div></div>
</div>

<div class="carrinho">
    <h2>🛒 Pedido</h2>
    <div id="itens-carrinho"></div>
    <hr>
    <h3>Total: R$ <span id="total">0,00</span></h3>

    <label>Forma de Pagamento:</label>
    <select id="metodo-pagamento" style="width: 100%; margin: 10px 0; padding: 5px;">
        <option value="dinheiro">Dinheiro</option>
        <option value="cartao">Cartão (Maquininha)</option>
        <option value="pix">PIX (Gerar QR Code)</option>
    </select>

    <button class="btn-pagar" onclick="fecharConta()">FECHAR CONTA</button>

    <div id="qrcode-area">
        <p>Aguardando pagamento...</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SimulandoPixMercadoPago" id="qr-img">
        <p><strong>Valor: R$ <span id="valor-pix"></span></strong></p>
    </div>
</div>

<div class="titulocep">
    <h2>📍 Consulta CEP</h2>

    <div class="formcep">
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="cep">Digite seu CEP</label>
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
        if (isset($_POST['verificar']) && !empty($_POST['cep'])) {
            $cep = preg_replace('/[^0-9]/', '', $_POST['cep']);
            $url = "https://viacep.com.br/ws/{$cep}/json/";

            $context = stream_context_create(["http" => ["ignore_errors" => true]]);
            $response = @file_get_contents($url, false, $context);
            $dados = json_decode($response);

            if ($dados && !isset($dados->erro)) {
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

<script>
    // ── Carrinho ──────────────────────────────────────────
    let carrinho = [];
    let total = 0;

    function add(id, nome, preco) {
        const itemExistente = carrinho.find(i => i.id === id);
        if (itemExistente) {
            itemExistente.qtd++;
        } else {
            carrinho.push({ id, nome, preco, qtd: 1 });
        }
        render();
    }

    function subtract(id, nome, preco) {
        const itemExistente = carrinho.find(i => i.id === id);
        if (itemExistente) {
            if (itemExistente.qtd > 1) {
                itemExistente.qtd--;
            } else {
                carrinho = carrinho.filter(i => i.id !== id);
            }
        }
        render();
    }

    function render() {
        const lista = document.getElementById('itens-carrinho');
        lista.innerHTML = '';
        total = 0;

        carrinho.forEach(item => {
            total += item.preco * item.qtd;
            lista.innerHTML += `<div class="item">${item.nome} x${item.qtd} - R$ ${(item.preco * item.qtd).toFixed(2)}</div>`;
        });

        document.getElementById('total').innerText = total.toFixed(2);
    }

    function fecharConta() {
        const metodo = document.getElementById('metodo-pagamento').value;
        const areaQR = document.getElementById('qrcode-area');

        if (total === 0) return alert("Adicione itens ao pedido!");

        if (metodo === 'pix') {
            document.getElementById('valor-pix').innerText = total.toFixed(2);
            areaQR.style.display = 'block';
            alert("Solicitando QR Code Dinâmico ao Mercado Pago...");

            fetch('pagamento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'total=' + total
            })
            .then(res => res.json())
            .then(dados => {
                const codigoPix = dados.point_of_interaction.transaction_data.qr_code;
                const qrImg = document.getElementById('qr-img');
                qrImg.src = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + codigoPix;
                areaQR.style.display = 'block';
            });
        } else {
            areaQR.style.display = 'none';
            alert("Pedido fechado! Forma: " + metodo);
        }
    }

    // ── CEP ───────────────────────────────────────────────
    function mascaraCEP(input) {
        let valor = input.value.replace(/\D/g, '');
        valor = valor.replace(/^(\d{5})(\d)/, "$1-$2");
        input.value = valor;
    }

    function limpar() {
        document.getElementById("cep").value = "";
        const resultado = document.querySelector(".resultado");
        if (resultado) resultado.style.display = "none";
    }
</script>

</body>
</html>