
<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']===true) { header('Location: triagem.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if ($u==='EnfermeiroUpa2' && $p==='Upa2') {
        $_SESSION['loggedin']=true; header('Location: triagem.php'); exit;
    } else { $error='Usuário ou senha incorretos.'; }
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>UPAMED - Sistema de Triagem</title>
<link rel="icon" href="assets/favicon.ico"><link rel="stylesheet" href="style.css"></head><body>
<div class="page-center">
  <div class="card login-card">
    <div class="login-grid">
      <div class="login-visual" style="background-image:url('assets/fundo.png')">
        <img src="assets/logo.png" class="login-logo" alt="UPAMED">
      </div>
      <div class="login-form-area">
        <h1>UPAMED</h1>
        <p class="muted">Unidade de Pronto Atendimento Médica — UPAMED</p>
        <?php if($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" class="login-form">
          <input name="username" placeholder="Usuário" value="EnfermeiroUpa2" required>
          <input name="password" placeholder="Senha" type="password" value="Upa2" required>
          <div class="row-between">
            <button class="btn primary" type="submit">Entrar</button>
            
          </div>
        </form>
        <small class="credits">Desenvolvido por Alef Filipe, Tais Santana, Matheus Santana e Vitória Yasodhara</small>
      </div>
    </div>
  </div>
</div>
<script src="script.js"></script>
</body></html>
