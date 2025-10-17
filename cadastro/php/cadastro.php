<?php 
ini_set('max_execution_time', 300);
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('mysqli.connect_timeout', 300);
ini_set('default_socket_timeout', 300);

session_start();

// ⭐⭐ SEMPRE GERA DADOS ALEATÓRIOS QUANDO A PÁGINA ABRE ⭐⭐
function gerarCPF() {
    $n = [];
    for ($i = 0; $i < 9; $i++) {
        $n[$i] = rand(0,9);
    }
    $d1 = 0;
    for ($i=0; $i<9; $i++) $d1 += $n[$i] * (10 - $i);
    $d1 = 11 - ($d1 % 11);
    if ($d1 >= 10) $d1 = 0;

    $d2 = 0;
    for ($i=0; $i<9; $i++) $d2 += $n[$i] * (11 - $i);
    $d2 += $d1 * 2;
    $d2 = 11 - ($d2 % 11);
    if ($d2 >= 10) $d2 = 0;

    return sprintf("%d%d%d.%d%d%d.%d%d%d-%d%d",
        $n[0],$n[1],$n[2],
        $n[3],$n[4],$n[5],
        $n[6],$n[7],$n[8],
        $d1,$d2
    );
}

function gerarTelefone() {
    return sprintf("(%02d) 9%04d-%04d", rand(11,99), rand(1000,9999), rand(1000,9999));
}

$nomes = ["Leticia","João","Ana","Pedro","Mariana","Lucas","Carla","Gabriel","Beatriz","Mateus"];
$sobrenomes = ["Silva","Souza","Pereira","Oliveira","Costa","Santos","Ferreira","Mendes","Lima","Ribeiro"];
$graus = ["Leve","Moderado","Severo"];
$dificuldadesPossiveis = ["Comunicação","Socialização","Concentração","Controle Emocional","Autonomia","Organização","Habilidades Motoras","Leitura","Escrita","Ansiedade"];

// Gera dados aleatórios
$nome = $nomes[array_rand($nomes)];
$sobrenome = $sobrenomes[array_rand($sobrenomes)];
$idade = rand(6,18);
$cpf = gerarCPF();
$grau_autismo = $graus[array_rand($graus)];
$email = strtolower($nome).".".strtolower($sobrenome).rand(1,99)."@gmail.com";
$senha = rand(1000,9999);
$responsavel = $sobrenomes[array_rand($sobrenomes)] . " " . $sobrenome;
$telefoner = gerarTelefone();
$cpfr = gerarCPF();
$emailR = strtolower(str_replace(" ", "", $responsavel)).rand(1,99)."@gmail.com";

// Escolhe de 2 a 4 dificuldades
shuffle($dificuldadesPossiveis);
$dificuldades = array_slice($dificuldadesPossiveis, 0, rand(2,4));

$dados_predefinidos = [
    'nome' => $nome,
    'sobrenome' => $sobrenome,
    'idade' => $idade,
    'cpf' => $cpf,
    'grau_autismo' => $grau_autismo,
    'email' => $email,
    'senha' => $senha,
    'responsavel' => $responsavel,
    'telefoner' => $telefoner,
    'cpfr' => $cpfr,
    'emailR' => $emailR,
    'dificuldades' => $dificuldades
];

$_SESSION['dados_pre_carregados'] = $dados_predefinidos;

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include "C:/xampp/htdocs/tentativa-1/conexao.php";

    // Dados do formulário
    $nome          = trim($_POST['nome'] ?? '');
    $sobrenome     = trim($_POST['sobrenome'] ?? '');
    $idade         = intval($_POST['idade'] ?? 0);
    $responsavel   = trim($_POST['responsavel'] ?? '');
    $grau_autismo  = trim($_POST['grau_autismo'] ?? '');
    $plano         = trim($_POST['plano'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $senha         = trim($_POST['senha'] ?? '');
    $telefoner     = trim($_POST['telefoner'] ?? '');
    $cpfr          = trim($_POST['cpfr'] ?? '');
    $emailR        = trim($_POST['emailR'] ?? '');
    $cpf           = trim($_POST['cpf'] ?? '');

    // Uploads (mantém como está)
    $foto_perfil = (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) 
                     ? file_get_contents($_FILES['foto_perfil']['tmp_name']) : null;
    $ftresponsavel = (isset($_FILES['ftresponsavel']) && $_FILES['ftresponsavel']['error'] == 0) 
                     ? file_get_contents($_FILES['ftresponsavel']['tmp_name']) : null;
    $laudo_blob = (isset($_FILES['laudo']) && $_FILES['laudo']['error'] == 0) 
                     ? file_get_contents($_FILES['laudo']['tmp_name']) : null;

    $dificuldades = isset($_POST['dificuldades']) ? implode(", ", (array)$_POST['dificuldades']) : '';
    date_default_timezone_set("America/Sao_Paulo");
    $data_cadastro = date('Y-m-d H:i:s');

    try {
        $sql_temp = "INSERT INTO tb_user_temp 
                     (nome, sobrenome, idade, cpf, plano, email, senha, responsavel, cpfr, telefoner, emailR, grau_autismo, dificuldades, foto_perfil, ftresponsavel, laudo, data_cadastro) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $cone->prepare($sql_temp);
        
        if ($stmt === false) {
             throw new Exception("Erro ao preparar a declaração: " . $cone->error);
        }

        $stmt->bind_param("ssissssssssssssss",
            $nome, $sobrenome, $idade, $cpf, $plano, $email, $senha,
            $responsavel, $cpfr, $telefoner, $emailR, $grau_autismo,
            $dificuldades, $foto_perfil, $ftresponsavel, $laudo_blob, $data_cadastro
        );

        $stmt->execute();
        $id_temp = $cone->insert_id;
        $stmt->close();

        // Limpa os dados pré-carregados após uso bem-sucedido
        unset($_SESSION['dados_pre_carregados']);

        // Redireciona para a tela de pagamento
        if ($plano === "Prata") {
            header("Location: /tentativa-1/cadastro/php/pagamento.php?id_temp={$id_temp}&valor=49.99");
        } elseif ($plano === "Ouro") {
            header("Location: /tentativa-1/cadastro/php/pagamento.php?id_temp={$id_temp}&valor=64.99");
        } else {
            header("Location: /tentativa-1/cadastro/php/pagamento.php?id_temp={$id_temp}");
        }
        exit();

    } catch (Exception $e) {
        echo "<script>alert('Erro no pré-cadastro: " . addslashes($e->getMessage()) . "'); window.location.href='cadastro.html';</script>";
        exit();
    } finally {
        if (isset($cone) && $cone instanceof mysqli) {
            $cone->close();
        }
    }
}

// Função para marcar checkboxes como checked
function isChecked($value) {
    if (isset($_SESSION['dados_pre_carregados']['dificuldades']) && 
        is_array($_SESSION['dados_pre_carregados']['dificuldades'])) {
        return in_array($value, $_SESSION['dados_pre_carregados']['dificuldades']) ? 'checked' : '';
    }
    return '';
}

// Função para selecionar option
function isSelected($field, $value) {
    if (isset($_SESSION['dados_pre_carregados'][$field])) {
        return $_SESSION['dados_pre_carregados'][$field] == $value ? 'selected' : '';
    }
    return '';
}

// Função para preencher valores dos campos
function getValue($field) {
    return isset($_SESSION['dados_pre_carregados'][$field]) 
        ? htmlspecialchars($_SESSION['dados_pre_carregados'][$field]) 
        : '';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Paciente - Altus</title>
  <link rel="stylesheet" href="../css/cadastro.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
</head>
<body>
  <div class="form-wrapper">
    <div class="form-header">
      <div class="logo-container">
        <img src="icons\Logo.png" alt="Altus Logo" class="logo">
        <h1>Altus</h1>
      </div>
      <h2>Olá! Pronto para começar uma jornada de autoconhecimento?</h2>
      <p>Preencha os dados abaixo para criar sua conta</p>
      <!-- Botões para gerenciar dados pré-carregados -->
   
    </div>

    <div class="form-container">
      <form action="cadastro.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
        
        <div class="form-section">
          <h3><i class="fas fa-user"></i> Dados Pessoais</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="nome">Nome</label>
              <div class="input-with-icon">
                <i class="fas fa-user"></i>
                <input type="text" name="nome" id="nome" placeholder="Digite o nome" value="<?php echo getValue('nome'); ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="sobrenome">Sobrenome</label>
              <div class="input-with-icon">
                <i class="fas fa-user"></i>
                <input type="text" name="sobrenome" id="sobrenome" placeholder="Digite o sobrenome" value="<?php echo getValue('sobrenome'); ?>" required>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="idade">Idade</label>
              <div class="input-with-icon">
                <i class="fas fa-birthday-cake"></i>
                <input type="number" name="idade" id="idade" placeholder="Idade" min="0" max="120" value="<?php echo getValue('idade'); ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="cpf">CPF do paciente</label>
              <div class="input-with-icon">
                <i class="fas fa-id-card"></i>
                <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" value="<?php echo getValue('cpf'); ?>" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="grau_autismo">Nivel de suporte</label>
            <div class="select-with-icon">
              <i class="fas fa-heart"></i>
              <select name="grau_autismo" id="grau_autismo" required>
                <option value="">Selecione o nível </option>
                <option value="Leve" <?php echo isSelected('grau_autismo', 'Leve'); ?>>Leve</option>
                <option value="Moderado" <?php echo isSelected('grau_autismo', 'Moderado'); ?>>Moderado</option>
                <option value="Severo" <?php echo isSelected('grau_autismo', 'Severo'); ?>>Severo</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-camera"></i> Foto de Perfil</h3>
          <div class="form-group">
            <div class="file-upload-container">
              <label for="foto_perfil" class="file-upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Selecionar Foto de Perfil</span>
              </label>
              <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" required>
              <small>Formatos: JPG, PNG | Máximo: 1MB</small>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-lock"></i> Dados de Acesso</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-with-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="seu@email.com" value="<?php echo getValue('email'); ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="senha">Senha</label>
              <div class="input-with-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="senha" id="senha" placeholder="Crie uma senha segura" value="<?php echo getValue('senha'); ?>" required>
              </div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-user-shield"></i> Dados do Responsável</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="responsavel">Nome do Responsável</label>
              <div class="input-with-icon">
                <i class="fas fa-user-tie"></i>
                <input type="text" name="responsavel" id="responsavel" placeholder="Nome completo" value="<?php echo getValue('responsavel'); ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="telefoner">Telefone</label>
              <div class="input-with-icon">
                <i class="fas fa-phone"></i>
                <input type="text" name="telefoner" id="telefoner" placeholder="(00) 00000-0000" value="<?php echo getValue('telefoner'); ?>" required>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="cpfr">CPF do Responsável</label>
              <div class="input-with-icon">
                <i class="fas fa-id-card"></i>
                <input type="text" name="cpfr" id="cpfr" placeholder="000.000.000-00" value="<?php echo getValue('cpfr'); ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="emailR">Email do Responsável</label>
              <div class="input-with-icon">
                <i class="fas fa-envelope"></i>
                <input type="email" name="emailR" id="emailR" placeholder="responsavel@email.com" value="<?php echo getValue('emailR'); ?>" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="file-upload-container">
              <label for="ftresponsavel" class="file-upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Foto do Responsável</span>
              </label>
              <input type="file" name="ftresponsavel" id="ftresponsavel" accept="image/*" required>
              <small>Formatos: JPG, PNG | Máximo: 1MB</small>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-file-medical"></i> Documentos</h3>
          <div class="form-group">
            <div class="file-upload-container">
              <label for="laudo" class="file-upload-label">
                <i class="fas fa-file-medical-alt"></i>
                <span>Laudo Médico</span>
              </label>
              <input type="file" name="laudo" id="laudo" accept="image/*" required>
              <small>Formatos: JPG, PNG, PDF | Máximo: 2MB</small>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-tasks"></i> Dificuldades</h3>
          <div class="checkbox-grid">
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Comunicação" <?php echo isChecked('Comunicação'); ?>>
              <span class="checkmark"></span>
              Comunicação
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Socialização" <?php echo isChecked('Socialização'); ?>>
              <span class="checkmark"></span>
              Socialização
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Concentração" <?php echo isChecked('Concentração'); ?>>
              <span class="checkmark"></span>
              Concentração
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Controle Emocional" <?php echo isChecked('Controle Emocional'); ?>>
              <span class="checkmark"></span>
              Controle Emocional
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Autonomia" <?php echo isChecked('Autonomia'); ?>>
              <span class="checkmark"></span>
              Autonomia
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Organização" <?php echo isChecked('Organização'); ?>>
              <span class="checkmark"></span>
              Organização
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Habilidades Motoras" <?php echo isChecked('Habilidades Motoras'); ?>>
              <span class="checkmark"></span>
              Habilidades Motoras
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Leitura" <?php echo isChecked('Leitura'); ?>>
              <span class="checkmark"></span>
              Leitura
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Escrita" <?php echo isChecked('Escrita'); ?>>
              <span class="checkmark"></span>
              Escrita
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="dificuldades[]" value="Ansiedade" <?php echo isChecked('Ansiedade'); ?>>
              <span class="checkmark"></span>
              Ansiedade
            </label>
          </div>
        </div>

        <div class="form-section">
          <h3><i class="fas fa-crown"></i> Plano</h3>
          <div class="form-group">
            <div class="select-with-icon">
              <i class="fas fa-gem"></i>
              <select name="plano" id="plano" required>
                <option value="">-- Selecione o plano --</option>
                <option value="Prata" <?php echo isSelected('plano', 'Prata'); ?>>Prata - R$ 49,99/mês</option>
                <option value="Ouro" <?php echo isSelected('plano', 'Ouro'); ?>>Ouro - R$ 64,99/mês</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-submit">
          <button type="submit" class="btn-submit">
            <i class="fas fa-user-plus"></i>
            Criar Conta
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function validaCPF(cpf) {
      cpf = cpf.replace(/[^\d]+/g,''); 
      
      if(cpf.length !== 11) return false;
      if(/(\d)\1{10}/.test(cpf)) return false;

      let soma = 0, resto;

      for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i-1, i)) * (11 - i);
      resto = (soma * 10) % 11;
      if ((resto === 10) || (resto === 11)) resto = 0;
      if (resto !== parseInt(cpf.substring(9, 10))) return false;

      soma = 0;
      for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i-1, i)) * (12 - i);
      resto = (soma * 10) % 11;
      if ((resto === 10) || (resto === 11)) resto = 0;
      if (resto !== parseInt(cpf.substring(10, 11))) return false;

      return true;
  }

  function validateForm() {
      let cpf = document.getElementById('cpf').value;
      let cpfr = document.getElementById('cpfr').value;
      let email = document.getElementById('email').value;
      let emailR = document.getElementById('emailR').value;
      let senha = document.getElementById('senha').value;

      if (!validaCPF(cpf)) {
          alert('CPF do paciente inválido.');
          return false;
      }
      if (!validaCPF(cpfr)) {
          alert('CPF do responsável inválido.');
          return false;
      }

      let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
          alert('Email do paciente inválido.');
          return false;
      }
      if (!emailPattern.test(emailR)) {
          alert('Email do responsável inválido.');
          return false;
      }

      return true;
  }

  function atualizarDadosPrecarregados() {
      if (confirm('Deseja atualizar os dados pré-carregados com os valores do código?')) {
          window.location.href = 'cadastro.php?atualizar=1';
      }
  }

  function limparDadosPrecarregados() {
      if (confirm('Deseja limpar todos os dados pré-carregados?')) {
          window.location.href = 'cadastro.php?limpar=1';
      }
  }

  $(document).ready(function(){
      $("#cpf, #cpfr").inputmask("999.999.999-99");
      $("#telefoner").inputmask("(99) 99999-9999");
      
      // Verifica se deve limpar os dados
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('limpar') === '1') {
          alert('Dados pré-carregados removidos!');
      }
      if (urlParams.get('atualizar') === '1') {
          alert('Dados pré-carregados atualizados!');
      }
  });
  </script>
</body>
</html>