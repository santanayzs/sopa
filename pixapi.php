<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Pedidos - Restaurante</title>
    <style>
        body { font-family: sans-serif; display: flex; gap: 20px; padding: 20px; background: #f4f4f4; }
        .cardapio, .carrinho { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .cardapio { flex: 2; }
        .carrinho { flex: 1; min-width: 300px; }
        .item { display: flex; justify-content: space-between;  align-items:center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .acoes { display: flex; gap: 8px;/* espaço entre os botões */}
        button { cursor: pointer; background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px;}
        .btn-pagar { width: 100%; padding: 15px; font-weight: bold; margin-top: 10px; }
        #qrcode-area { text-align: center; margin-top: 20px; display: none; border: 2px dashed #009ee3; padding: 10px; }
    </style>
</head>
<body>

<div class="cardapio">
    <h2>🍕 Pizzas</h2>
    <div class="item"><span>Calabresa - R$ 45,00</span> <div class="acoes"> <button onclick="subtract(1, 'Pizza Calabresa', 45.00)">-</button> <button onclick="add(1, 'Pizza Calabresa', 45.00)">+ </button></div></div>
    <div class="item"><span>Marguerita - R$ 40,00</span> <div class="acoes"> <button onclick="subtract(2, 'Pizza Marguerita', 40.00)">-</button> <button onclick="add(2, 'Pizza Marguerita', 40.00)">+</button></div></div>
    <div class="item"><span>Portuguesa - R$ 50,00</span> <div class="acoes"> <button onclick="subtract(3, 'Pizza Portuguesa', 50.00)">-</button> <button onclick="add(3, 'Pizza Portuguesa', 50.00)">+</button></div></div>
    <div class="item"><span>Frango c/ Catupiry - R$ 48,00</span> <div class="acoes"> <button onclick="subtract(4, 'Pizza Frango', 48.00)">-</button> <button onclick="add(4, 'Pizza Frango', 48.00)">+</button></div></div>

    <h2>🥤 Bebidas</h2>
    <div class="item"><span>Coca-Cola - R$ 8,00</span> <div class="acoes"> <button onclick="subtract(1, 'Pizza Calabresa', 45.00)">- <button onclick="add(5, 'Coca-Cola', 8.00)">+</button></div></div>
    <div class="item"><span>Suco Natural - R$ 10,00</span> <div class="acoes"> <button onclick="subtract(1, 'Pizza Calabresa', 45.00)">- <button onclick="add(6, 'Suco Natural', 10.00)">+</button></div></div>
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
        <!-- Simulando um QR Code gerado pela API -->
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SimulandoPixMercadoPago" id="qr-img">
        <p><strong>Valor: R$ <span id="valor-pix"></span></strong></p>
    </div>
</div>

<script>
    let carrinho = [];
    let total = 0;

    function add(id, nome, preco) {
        const itemExistente = carrinho.find(i => i.id === id);
        if (itemExistente) {
            itemExistente.qtd++;
        } else {
            carrinho.push({id, nome, preco, qtd: 1});
        }
        render();
    }
    function subtract(id, nome, preco) {
    const itemExistente = carrinho.find(i => i.id === id);
    if (itemExistente) {
        if (itemExistente.qtd > 1) {
            itemExistente.qtd--;
        } else {
            // Remove o item do carrinho quando chegar em 0
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
            
            // Aqui entraria a chamada fetch() para o seu PHP que usa Mercado Pago
            alert("Solicitando QR Code Dinâmico ao Mercado Pago...");
        } else {
            areaQR.style.display = 'none';
            alert("Pedido fechado! Forma: " + metodo);
        }
    const totalPedido = total; // Valor acumulado no carrinho
    
    // Envia o valor para o arquivo PHP que criamos
    fetch('pagamento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'total=' + totalPedido
    })
    .then(res => res.json())
    .then(dados => {
        // A API do Mercado Pago retorna o código do PIX dentro de 'point_of_interaction'
        const codigoPix = dados.point_of_interaction.transaction_data.qr_code;
        
        // Agora geramos a imagem do QR Code com esse código
        const qrImg = document.getElementById('qr-img');
        qrImg.src = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + codigoPix;
        
        document.getElementById('qrcode-area').style.display = 'block';
    });
}
</script>

</body>
</html>