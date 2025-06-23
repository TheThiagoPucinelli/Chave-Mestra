<?php include __DIR__ . '/../PHP/verifica_login.php'; ?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="CM.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/index.css">
      

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/IMG/2.png" type="image/x-icon">
    <link rel="icon" type="image/png" href="../IMG/cmpage.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Chave Mestra</title>
</head>
<body>

      <main>
        <header class="relative top-0 left-0 w-full bg-white shadow z-50">
          <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <!-- Logo -->
            <a href="../pages/index.html" class="flex items-center">
              <img src="../IMG/CM (5).png" alt="Logo" class="h-20 w-auto" />
            </a>
      
            <!-- Menu Toggle (mobile) -->
            <input type="checkbox" id="menu-toggle" class="hidden peer" />
            <label for="menu-toggle" class="cursor-pointer md:hidden block">
              <div class="space-y-1.5">
                <span class="block w-6 h-0.5 bg-gray-800"></span>
                <span class="block w-6 h-0.5 bg-gray-800"></span>
                <span class="block w-6 h-0.5 bg-gray-800"></span>
              </div>
            </label>
      
            <!-- Menu feito com Tailwind -->
            <nav class="absolute top-full left-0 w-full bg-white shadow-md 
                        flex-col space-y-2 px-6 py-4
                        hidden peer-checked:flex 
                        md:static md:w-auto md:bg-transparent md:shadow-none 
                        md:flex md:flex-row md:space-x-6 md:space-y-0 md:items-center md:px-0 md:py-0">
              <a href="../Pages/contato.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Contato</a>
              <a href="../PHP/login.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">login</a>
              <a href="../Pages/chaves.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Chaves</a>
              <a href="../Pages/registro.php" class="block text-gray-700 hover:bg-blue-500 hover:text-white px-4 py-2 rounded transition">Registro</a>
            </nav>
          </div>
        </header>
      </main>




 <!-- DESKTOP VERSION -->
<div class="container-tab table-desktop">
  <h2>Gerenciamento de Chaves. <br><em>(Somente Funcionários)</em></h2>
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Chave</th>
          <th>Categoria</th>
          <th>Retirada</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select id="chave">
              <option>N°23 ACAD</option>
              <option>N°47 Lab.1</option>
              <option>N°35 Professores(as)Humanas</option>
              <option>N°62 Lab.Matematica</option>
              <option>N°55 Lab.Fisica</option>
            </select>
          </td>
          <td>
            <select id="categoria">
              <option value="aluno">Aluno</option>
              <option value="funcionario">Funcionário</option>
            </select>
          </td>
          <td>
            <input type="text" id="nome" placeholder="Digite seu nome">
            <button>Confirmar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="container-tab table-desktop">
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Devolução</th>
          <th>Confirmação</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select id="devolucao">
              <option></option>
              <option>Roger</option>
              <option>João</option>
              <option>Brod</option>
              <option>Thiago</option>
              <option>Victor</option>
            </select>
          </td>
          <td>
            <button>Confirmar</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MOBILE VERSION -->
<div class="container-tab form-mobile">
  <h2>Retirada de Chaves</h2>
  <div class="form-group">
    <label for="chave-mobile">Chave</label>
    <select id="chave-mobile">
      <option>N°23 ACAD</option>
      <option>N°47 Lab.1</option>
      <option>N°35 Professores(as)Humanas</option>
      <option>N°62 Lab.Matematica</option>
      <option>N°55 Lab.Fisica</option>
    </select>
  </div>
  <div class="form-group">
    <label for="categoria-mobile">Categoria</label>
    <select id="categoria-mobile">
      <option value="aluno">Aluno</option>
      <option value="funcionario">Funcionário</option>
    </select>
  </div>
  <div class="form-group">
    <label for="nome-mobile">Nome</label>
    <input type="text" id="nome-mobile" placeholder="Digite seu nome">
  </div>
  <button>Confirmar</button>
</div>

<div class="container-tab form-mobile">
  <h2>Devolução de Chaves</h2>
  <div class="form-group">
    <label for="devolucao-mobile">Devolução</label>
    <select id="devolucao-mobile">
      <option></option>
      <option>Roger</option>
      <option>João</option>
      <option>Brod</option>
      <option>Thiago</option>
      <option>Victor</option>
    </select>
  </div>
  <button>Confirmar</button>
</div>


      
<br><br><br>
<br><br><br><br>


<footer class="footer">
  <div class="footer-container">
    <p>&copy; 2025 Chave Mestra | 
      <a href="../Pages/contato.html" class="footer-link">Contato</a>
    </p>
  </div>
</footer>

<script>
  setTimeout(() => {
    alert('Sua sessão expirou! Faça Login Novamente!');
    window.location.href = '../PHP/login.php'; // redireciona para login
  }, <?= $tempoRestante ?>);
</script>


    </body>
    </html>