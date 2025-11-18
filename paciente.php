
<?php
// paciente.php - list patients and their reports (requires DB configured in config.php)
$config = [];
if (file_exists('config.php')) { $cfg = include 'config.php'; if (is_array($cfg)) $config = $cfg; }
$has_db = !empty($config['DB_HOST']) && !empty($config['DB_USER']) && !empty($config['DB_NAME']);
$patients = [];
if ($has_db) {
    $mysqli = @new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
    if (!$mysqli->connect_errno) {
        $res = $mysqli->query("SELECT id,name,age,gender,imc,imc_desc,created_at FROM patients ORDER BY created_at DESC");
        while ($row = $res->fetch_assoc()) $patients[] = $row;
        $mysqli->close();
    }
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pacientes - UPAMED</title>
<link rel="icon" href="assets/favicon.ico"><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div class="brand"><img src="assets/logo.png" class="logo-small"><div><h1>UPAMED</h1><small class="muted">Pacientes</small></div></div></header>
<main class="container" style="max-width:980px;margin:20px auto">
  <section class="card">
    <h2>Pacientes cadastrados</h2>
    <?php if (!$has_db): ?>
      <p>Banco de dados não configurado. Edite <code>config.php</code> com as credenciais MySQL para ativar o armazenamento.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead><tr><th>Nome</th><th>Idade</th><th>Gênero</th><th>IMC</th><th>Data</th><th>Relatórios</th></tr></thead>
        <tbody>
        <?php foreach($patients as $p): ?>
          <tr style="border-top:1px solid #eee">
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo (int)$p['age']; ?></td>
            <td><?php echo htmlspecialchars($p['gender']); ?></td>
            <td><?php echo htmlspecialchars($p['imc'].' '.$p['imc_desc']); ?></td>
            <td><?php echo htmlspecialchars($p['created_at']); ?></td>
            <td><a href="patient_reports.php?id=<?php echo $p['id']; ?>" class="btn">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</main>
<script src="script.js"></script>
</body></html>
