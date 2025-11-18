
<?php
// patient_reports.php?id=ID
$config = [];
if (file_exists('config.php')) { $cfg = include 'config.php'; if (is_array($cfg)) $config = $cfg; }
$has_db = !empty($config['DB_HOST']) && !empty($config['DB_USER']) && !empty($config['DB_NAME']);
$reports = [];
$patient = null;
$id = intval($_GET['id'] ?? 0);
if ($has_db && $id>0) {
    $mysqli = @new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
    if (!$mysqli->connect_errno) {
        $res = $mysqli->query("SELECT id,name FROM patients WHERE id={$id} LIMIT 1");
        $patient = $res->fetch_assoc();
        $res = $mysqli->query("SELECT id,filename,risk_level,created_at FROM reports WHERE patient_id={$id} ORDER BY created_at DESC");
        while ($row = $res->fetch_assoc()) $reports[] = $row;
        $mysqli->close();
    }
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Relatórios do Paciente - UPAMED</title>
<link rel="icon" href="assets/favicon.ico"><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div class="brand"><img src="assets/logo.png" class="logo-small"><div><h1>UPAMED</h1><small class="muted">Relatórios</small></div></div></header>
<main class="container" style="max-width:980px;margin:20px auto">
  <section class="card">
    <h2>Relatórios do paciente</h2>
    <?php if (!$has_db): ?>
      <p>Banco de dados não configurado.</p>
    <?php elseif (!$patient): ?>
      <p>Paciente não encontrado.</p>
    <?php else: ?>
      <h3><?php echo htmlspecialchars($patient['name']); ?></h3>
      <ul>
      <?php foreach($reports as $r): ?>
        <li><?php echo htmlspecialchars($r['created_at'].' - '.$r['risk_level']); ?> - <a href="reports/<?php echo rawurlencode($r['filename']); ?>" download>Baixar</a></li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</main>
<script src="script.js"></script>
</body></html>
