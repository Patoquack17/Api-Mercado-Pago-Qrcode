<!DOCTYPE html>
<html>
<head>
	<title>Gerador de QR Code PIX</title>
	<meta charset="UTF-8">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            font-size: 16px;
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-top: 50px;
        }
        
        form {
            width: 30%;
            margin: 0 auto;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        input[type="submit"] {
            background-color: #2c3e50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #1d2d3e;
        }
        
        input:required {
            box-shadow:none;
        }
    </style>
</head>
<body>
	<h1>Gerador PIX</h1>
	<form method="POST" action="gerar-pix.php" id="gerar-pix">
		<label for="valor">Valor do PIX:</label>
		<input type="number" step="0.01" min="0.01" name="valor" id="valor" required>
		
		<label for="description">Descrição:</label>
		<input type="text" name="description" id="description" required>
		
		<label for="cpf">CPF:</label>
		<input type="text" name="cpf" id="cpf" required>
		
		<input type="submit" value="Gerar Pix">
	</form>
	
	
</body>
</html>

<?php


// Verifica se a requisição HTTP recebida é do tipo POST


// Verifica se a requisição HTTP recebida é do tipo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe os dados enviados pelo formulário HTML
    $valor = intval($_POST['valor']); // converte para inteiro
    $description = $_POST['description']; // recebe a descrição do HTML
    $cpf = $_POST['cpf']; // recebe o CPF do HTML
    
    // Cria um array com os dados da transação a serem enviados para a API do Mercado Pago
    $dados = array(
        "transaction_amount" => $valor,
        "description" => $description, // usa a descrição recebida
        "external_reference" => "2",
        "payment_method_id" => "pix",
        "payer" => array(
            "email" => "EMAIL",
            "first_name" => "NOME",
            "last_name" => "SOBRENOME",
            "identification" => array(
                "type" => "CPF",
                "number" => $cpf, // usa o CPF recebido
            ),
            "address" => array(
                "zip_code" => "37273970",
                "street_name" => "Praça Nossa Senhora Aparecida 119",
                "street_number" => "3003",
                "neighborhood" => "Aguanil",
                "city" => "Centro",
                "federal_unit" => "MG",
            ),
        ),
    );
    
    // Inicializa uma sessão cURL
    $curl = curl_init();
    
    // Configura as opções da sessão cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dados),
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'content-type: application/json',
            'Authorization: Bearer SUA TOKEN',
        ),
    ));
    
    // Executa a sessão cURL e armazena a resposta
    $response = curl_exec($curl);
    
    // Converte a resposta JSON em um objeto PHP
    $resultado = json_decode($response);
    
    // Encerra a sessão cURL
    curl_close($curl);

    // Verifica o status do pagamento retornado pela API
    if ($resultado->status == 'approved') {
        // pagamento aprovado
        echo 'O pagamento foi aprovado.';
    } elseif ($resultado->status == 'pending') {
        // pagamento pendente
        echo 'O pagamento está pendente.';
    } else {
        // pagamento rejeitado ou cancelado
        echo 'O pagamento foi rejeitado ou cancelado.';
    }
	
}

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerador de QR Code PIX</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #qr-window {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background-color: white;
            border: 1px solid black;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        #qr-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #qr-code {
            display: block;
            width: 300px;
            height: 300px;
        }
        #qr-text {
            margin-top: 10px;
            padding: 5px;
            border: 1px solid black;
            width: 300px;
            text-align: center;
        }
        #qr-copy-button {
            margin-top: 10px;
            padding: 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        #qr-close-button {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="resultado-pagamento">
    <!-- Aqui será exibido o resultado do pagamento -->
</div>

<?php if(isset($resultado)) { ?>
    <div id="qr-window">
        <button id="qr-close-button">&times;</button>
        <div id="qr-content">
            <img id="qr-code" src="data:image/jpeg;base64,<?php echo $resultado->point_of_interaction->transaction_data->qr_code_base64;?>">
            <input type="text" id="qr-text" value="<?php echo $resultado->point_of_interaction->transaction_data->qr_code;?>" readonly>
            <button id="qr-copy-button">Copiar código</button>
        </div>
    </div>
<?php } ?>

<script>
    // Copiar o texto do campo de input quando o botão de copiar for clicado
    $('#qr-copy-button').on('click', function() {
        var copyText = document.getElementById("qr-text");
        copyText.select();
        document.execCommand("copy");
        alert("Código copiado para a área de transferência!");
    });

    // Fechar a janela quando o botão de fechar for clicado
    $('#qr-close-button').on('click', function() {
        $('#qr-window').remove();
    });
</script>
</body>
</html>






