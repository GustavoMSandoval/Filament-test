<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Impressão</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4a6cf7;
        }
        
        h1 {
            color: #4a6cf7;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 20px;
            background: #f8f9ff;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .print-button {
            display: block;
            width: 200px;
            margin: 0 auto;
            padding: 12px 20px;
            background: #4a6cf7;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .print-button:hover {
            background: #3a5ae0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Estilos específicos para impressão */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .print-container, .print-container * {
                visibility: visible;
            }
            
            .print-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                box-shadow: none;
            }
            
            .print-button, .print-button * {
                display: none;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema de Impressão</h1>
            <p class="no-print">Esta página demonstra a funcionalidade de impressão</p>
        </header>
        
        <div class="content">
            <p>Este é um exemplo de conteúdo que será impresso quando você clicar no botão.</p>
            <p>Observe que durante a impressão:</p>
            <ul>
                <li>O botão de impressão será ocultado</li>
                <li>Somente esta área será impressa</li>
                <li>Elementos de navegação não aparecerão</li>
            </ul>
        </div>
        
        <button id="printBtn" class="print-button">Imprimir Conteúdo</button>
    </div>

    <script>
        document.getElementById('printBtn').addEventListener('click', function() {
            // Clona o conteúdo que queremos imprimir
            const printContent = document.querySelector('.content').cloneNode(true);
            
            // Cria um container temporário para impressão
            const printContainer = document.createElement('div');
            printContainer.className = 'print-container';
            printContainer.appendChild(printContent);
            
            // Adiciona ao corpo
            document.body.appendChild(printContainer);
            
            // Dispara a impressão
            window.print();
            
            // Remove o container temporário após a impressão
            document.body.removeChild(printContainer);
        });
    </script>
</body>
</html>