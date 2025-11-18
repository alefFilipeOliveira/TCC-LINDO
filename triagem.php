<?php session_start(); ?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>UPAMED - Triagem</title>
<link rel="icon" href="assets/favicon.ico"><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar">
  <div class="brand"><img src="assets/logo.png" class="logo-small" alt="logo"><div><h1>UPAMED</h1><small class="muted">Unidade de Pronto Atendimento Médica</small></div></div>
  <div class="top-actions"><button id="openSupport" class="btn outline">Suporte IA</button><a href="logout.php" class="btn">Sair</a></div>
</header>
<main class="container">
  <section class="left">
    <div class="card">
      <h2>Nova Triagem</h2>
      <form id="triageForm" method="post" action="processo.php">
        <div class="form-row"><label>Nome completo</label><input name="nome" required></div>
        <div class="form-row small"><label>Idade</label><input name="idade" type="number" min="0"></div>
        <div class="form-row small"><label>Peso (kg)</label><input id="peso" name="peso" type="number" step="0.1"></div>
        <div class="form-row small"><label>Altura (m)</label><input id="altura" name="altura" type="number" step="0.01" placeholder="1.75"></div>
        <div class="form-row"><label>Gênero</label><select name="genero"><option>Masculino</option><option>Feminino</option><option>Outro</option></select></div>
        <hr>
        <label>Selecione sintomas na lista à direita (clique em "Adicionar"):</label>
        <div id="selectedList" class="selected-list" aria-live="polite"></div>
        <input type="hidden" name="selected_symptoms" id="selected_symptoms">
        <div class="form-actions">
          <button type="submit" class="btn primary" id="generateBtn">Gerar Relatório</button>
          <button type="button" class="btn outline" id="clearSelected">Limpar</button>
        </div>
      </form>
    </div>
  </section>
  <aside class="right">
    <div class="card">

      <h3>Catálogo de Sintomas (única lista colorida)</h3>

      <!-- 🔍 BARRA DE PESQUISA ADICIONADA AQUI -->
      <input 
        type="text" 
        id="searchSymptoms" 
        placeholder="🔍 Buscar sintomas..." 
        class="search-bar"
        style="
          width:100%;
          padding:12px;
          margin-bottom:12px;
          border-radius:8px;
          border:1px solid #ccc;
          font-size:16px;
          outline:none;
          box-shadow:0 0 4px rgba(0,0,0,0.08);
        "
      >

      <div id="catalog" class="catalog"></div>
    </div>

    <div class="card quick-info">
      <h4>Resumo</h4>
      <div id="summary">Selecione sintomas para ver classificação e orientações.</div>
    </div>
  </aside>
</main>

<div id="supportPopup" class="support-popup" aria-hidden="true">
  <div class="support-inner">
    <div class="support-head"><strong>UPAMED - Suporte</strong><button id="closeSupport" title="Fechar">×</button></div>
    <div id="supportChat" class="support-chat"></div>
    <div class="support-input"><input id="supportText" placeholder="Descreva o problema..."><button id="sendSupport" class="btn primary">Enviar</button></div>
    <div class="support-note">Se a IA não resolver, envie para alefimena@gmail.com ou WhatsApp 71 99335-2751</div>
  </div>
</div>

<button id="openSupportFloating" class="open-support" title="Suporte IA">💬</button>
<script src="script.js"></script>

<!-- 🔍 SCRIPT PARA FILTRAR A LISTA -->
<script>
document.getElementById("searchSymptoms").addEventListener("input", function () {
    const query = this.value.toLowerCase();
    const items = document.querySelectorAll("#catalog .item");

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? "" : "none";
    });
});
</script>

</body></html>
