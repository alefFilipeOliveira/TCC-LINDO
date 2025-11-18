
<?php
session_start();
$config = [];
if (file_exists('config.php')) { $cfg = include 'config.php'; if (is_array($cfg)) $config = $cfg; }
$nome = trim(htmlspecialchars($_POST['nome'] ?? '---'));
$idade = intval($_POST['idade'] ?? 0);
$peso_raw = trim($_POST['peso'] ?? '');
$altura_raw = trim($_POST['altura'] ?? '');
$peso = floatval(str_replace(',', '.', $peso_raw));
$altura = floatval(str_replace(',', '.', $altura_raw));
$genero = htmlspecialchars($_POST['genero'] ?? '');
$selected = $_POST['selected_symptoms'] ?? '';
$symptoms = array_filter(array_map('trim', explode(',', $selected)));
$severity_map = json_decode(file_get_contents('severity_map.json'), true);
$sum = 0; $max = 0; $list = [];
foreach ($symptoms as $s) {
    $sev = isset($severity_map[$s]) ? intval($severity_map[$s]) : 1;
    $list[] = ['name'=>$s, 'sev'=>$sev];
    $sum += $sev; if ($sev > $max) $max = $sev;
}
$level = 'VERDE';
if ($max >= 3 || $sum >= 12) $level = 'VERMELHO';
elseif ($max >= 2 || $sum >=6) $level = 'AMARELO';
$color = ($level==='VERMELHO') ? '#e74c3c' : (($level==='AMARELO') ? '#f1c40f' : '#2ecc71');

// BMI (IMC) and validation
$imc = 0;
$imc_desc = 'Dados insuficientes';
if ($altura > 0 && $peso > 0) {
    $imc = $peso / ($altura * $altura);
    $imc = round($imc,2);
    if ($imc < 18.5) $imc_desc = 'Abaixo do peso';
    elseif ($imc < 25) $imc_desc = 'Peso normal';
    elseif ($imc < 30) $imc_desc = 'Sobrepeso';
    else $imc_desc = 'Obesidade';
}

// Build extensive report text
$now = date('Y-m-d H:i:s');
$report_lines = [];
$report_lines[] = '*** Relatório de Triagem - UPAMED ***';
$report_lines[] = 'Data/Hora: ' . $now;
$report_lines[] = 'Paciente: ' . $nome;
$report_lines[] = 'Idade: ' . $idade . ' anos';
$report_lines[] = 'Gênero: ' . $genero;
$report_lines[] = 'Peso: ' . ($peso > 0 ? $peso . ' kg' : $peso_raw);
$report_lines[] = 'Altura: ' . ($altura > 0 ? $altura . ' m' : $altura_raw);
$report_lines[] = 'IMC: ' . ($imc > 0 ? $imc . ' (' . $imc_desc . ')' : 'Não calculado - dados incompletos');
$report_lines[] = '';
$report_lines[] = '--- Sintomas selecionados (' . count($list) . ') ---';
if (empty($list)) {
    $report_lines[] = '- Nenhum sintoma selecionado.';
} else {
    foreach ($list as $it) $report_lines[] = '- ' . $it['name'] . ' (gravidade=' . $it['sev'] . ')';
}
$report_lines[] = '';
$report_lines[] = '--- Classificação de risco ---';
$report_lines[] = 'Nível: ' . $level;
$report_lines[] = '';
$report_lines[] = '--- Análise estendida (simulada) ---';
if ($level==='VERMELHO') {
    $report_lines[] = 'Paciente com alto risco. Recomenda-se atendimento imediato, avaliação de via aérea, respiração e circulação (ABC), administração de oxigênio, acesso venoso e avaliação para internação.';
    $report_lines[] = 'Exames urgentes recomendados: hemograma, gasometria arterial, RX de tórax, ECG, marcadores cardíacos.';
} elseif ($level==='AMARELO') {
    $report_lines[] = 'Paciente de risco moderado. Avaliação médica prioritária, monitorização e exames conforme suspeita clínica.';
    $report_lines[] = 'Exames recomendados: hemograma, função renal, eletrólitos, RX conforme necessidade.';
} else {
    $report_lines[] = 'Paciente de baixo risco. Orientações de suporte, analgesia se necessário, hidratação e retorno em caso de piora.';
    $report_lines[] = 'Cuidados domiciliares: repouso, hidratação, controle de temperatura e observação.';
}
$report_lines[] = '';
$report_lines[] = '--- Pré-indicações ---';
$report_lines[] = '- Medir sinais vitais: PA, FC, FR, SpO2.';
$report_lines[] = '- Controle de dor e náuseas conforme protocolo.';
$report_lines[] = '- Registrar o paciente no sistema e notificar o médico responsável.';
$report_lines[] = '';
$report_lines[] = 'Observação: Este relatório foi encaminhado automaticamente ao médico responsável (simulado).';
// Doctor digital stamp
$report_lines[] = '';
$report_lines[] = '--- Assinatura Digital ---';
$report_lines[] = 'Dr(a): Alef Filipe';
$report_lines[] = 'Cargo: Enfermeiro Responsável';
$report_lines[] = 'ID: ALEF001';
$report_text = implode("\n", $report_lines);

// Save report to server as .txt for download and audit trail
$reports_dir = __DIR__ . '/reports';
if (!is_dir($reports_dir)) mkdir($reports_dir, 0755, true);
$filename_safe = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower($nome ?: 'paciente')) . '_' . date('Ymd_His') . '.txt';
$file_path = $reports_dir . '/' . $filename_safe;
file_put_contents($file_path, $report_text);

// Attempt to save to MySQL if configured
if (!empty($config['DB_HOST']) && !empty($config['DB_USER']) && !empty($config['DB_NAME'])) {
    $mysqli = @new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);
    if (!$mysqli->connect_errno) {
        // Ensure tables exist (simple creation)
        $mysqli->query("CREATE TABLE IF NOT EXISTS patients (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), age INT, gender VARCHAR(50), weight FLOAT, height FLOAT, imc FLOAT, imc_desc VARCHAR(100), created_at DATETIME)"); 
        $mysqli->query("CREATE TABLE IF NOT EXISTS reports (id INT AUTO_INCREMENT PRIMARY KEY, patient_id INT, filename VARCHAR(255), risk_level VARCHAR(20), summary TEXT, created_at DATETIME, FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE)"); 
        // Insert or find patient
        $name_esc = $mysqli->real_escape_string($nome);
        $res = $mysqli->query("SELECT id FROM patients WHERE name='{$name_esc}' AND age={$idade} LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $patient_id = $row['id'];
        } else {
            $stmt = $mysqli->prepare("INSERT INTO patients (name, age, gender, weight, height, imc, imc_desc, created_at) VALUES (?,?,?,?,?,?,?,NOW())");
            $stmt->bind_param('sissdss', $nome, $idade, $genero, $peso, $altura, $imc, $imc_desc);
            $stmt->execute();
            $patient_id = $stmt->insert_id;
            $stmt->close();
        }
        // Insert report record
        $summary = $mysqli->real_escape_string(substr($report_text,0,1000));
        $file_esc = $mysqli->real_escape_string($filename_safe);
        $mysqli->query("INSERT INTO reports (patient_id, filename, risk_level, summary, created_at) VALUES ({$patient_id}, '{$file_esc}', '{$level}', '{$summary}', NOW())");
        $mysqli->close();
    }
}

// Output HTML with report and direct link to download .txt
?>
<!doctype html><html lang="pt-BR"><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Relatório - UPAMED</title>
<link rel='icon' href='assets/favicon.ico'><link rel='stylesheet' href='style.css'></head><body>
<header class='topbar'><div class='brand'><img src='assets/logo.png' class='logo-small'><div><h1>UPAMED</h1><small class='muted'>Unidade de Pronto Atendimento Médica Digital</small></div></div></header>
<main class='container result-page' style='justify-content:center'>
  <section class='card result-card' style='max-width:780px;margin:0 auto'>
    <h2 style='text-align:center'>Relatório de Triagem</h2>
    <div class='meta' style='text-align:center;margin-bottom:8px'><strong>Paciente:</strong> <?php echo $nome; ?> — <strong>Idade:</strong> <?php echo $idade; ?> anos</div>
    <div class='report-box' id='reportBox'><pre><?php echo htmlspecialchars($report_text); ?></pre></div>
    <div style='display:flex;justify-content:space-between;align-items:center;margin-top:12px;flex-wrap:wrap'>
      <div class='risk' style='color: <?php echo $color; ?>;font-weight:bold'><strong>Classificação: <?php echo $level; ?></strong></div>
      <div style='text-align:right'><a class='btn' href='triagem.php'>Nova triagem</a> <a class='btn outline' href='logout.php'>Sair</a> <a class='btn primary' href='<?php echo 'reports/' . rawurlencode($filename_safe); ?>' download>Baixar relatório (.txt)</a></div>
    </div>
    <div style='margin-top:14px;border-top:1px solid #eee;padding-top:10px;color:#555'>
      <small>Relatório gerado automaticamente por UPAMED. Assinatura digital: Alef Filipe (Enfermeiro Responsável). ID: ALEF001</small>
    </div>
  </section>
</main>
<button id='openSupportFloating' class='open-support' title='Suporte UPAMED'>💬</button>
<script src='script.js'></script>
</body></html>
