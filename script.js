
document.addEventListener('DOMContentLoaded', function(){
  fetch('severity_map.json?v=' + Date.now()).then(r=>r.json()).then(map=>{ window.SEVERITY_MAP = map; renderCatalog(map); }).catch(e=>{ console.error(e); renderCatalog({}); });

  const selectedList = document.getElementById('selectedList');
  const hiddenField = document.getElementById('selected_symptoms');
  const clearSelected = document.getElementById('clearSelected');
  const triageForm = document.getElementById('triageForm');
  const openSupport = document.getElementById('openSupport');
  const supportPopup = document.getElementById('supportPopup');
  const closeSupport = document.getElementById('closeSupport');
  const sendSupport = document.getElementById('sendSupport');
  const supportText = document.getElementById('supportText');
  const openSupportFloating = document.getElementById('openSupportFloating');
  let selected = [];

  function renderCatalog(map){
    const catalog = document.getElementById('catalog');
    catalog.innerHTML='';
    const items = Object.keys(map || {}).sort();
    items.forEach(name=>{
      const sev = map[name] || 1;
      const div = document.createElement('div');
      div.className = 'item ' + (sev===3? 'red' : (sev===2? 'yellow' : 'green'));
      div.innerHTML = '<span>'+name+'</span><button class="add-btn">Adicionar</button>';
      div.querySelector('.add-btn').addEventListener('click', ()=>{ addSymptom(name); });
      catalog.appendChild(div);
    });
  }

  function addSymptom(name){
    if (selected.indexOf(name)!==-1) return;
    selected.push(name);
    renderSelected();
  }
  function renderSelected(){
    selectedList.innerHTML='';
    selected.forEach(s=>{
      const sev = window.SEVERITY_MAP[s] || 1;
      const pill = document.createElement('div');
      pill.className = 'pill ' + (sev===3? 'red' : (sev===2? 'yellow':'green'));
      pill.innerHTML = '<span>'+s+'</span><button class="rm">×</button>';
      pill.querySelector('.rm').addEventListener('click', ()=>{ selected = selected.filter(x=>x!==s); renderSelected(); });
      selectedList.appendChild(pill);
    });
    hiddenField.value = selected.join(',');
    updateSummary();
  }

  clearSelected.addEventListener('click', ()=>{ selected=[]; renderSelected(); });

  triageForm.addEventListener('submit', function(e){
    e.preventDefault();
    // show 3s overlay
    const overlay = document.createElement('div');
    overlay.style.position='fixed'; overlay.style.left=0; overlay.style.top=0; overlay.style.width='100%'; overlay.style.height='100%';
    overlay.style.background='rgba(2,6,23,0.6)'; overlay.style.display='flex'; overlay.style.alignItems='center'; overlay.style.justifyContent='center'; overlay.style.zIndex=99999;
    overlay.innerHTML = '<div style="text-align:center;color:white"><div class="spinner"></div><p style="margin-top:12px">Processando relatório... Aguarde</p></div>';
    document.body.appendChild(overlay);
    setTimeout(()=>{ document.body.removeChild(overlay); triageForm.submit(); }, 3000);
  });

  // support controls
  openSupport.addEventListener('click', ()=>{ supportPopup.style.display='block'; });
  closeSupport.addEventListener('click', ()=>{ supportPopup.style.display='none'; });
  openSupportFloating.addEventListener('click', ()=>{ supportPopup.style.display='block'; });
  sendSupport.addEventListener('click', async ()=>{
    const text = supportText.value.trim(); if (!text) return alert('Descreva o problema.');
    addSupportMsg('Você', text); supportText.value=''; addSupportMsg('UPAMED', 'Analisando...'); await new Promise(r=>setTimeout(r,2000));
    addSupportMsg('UPAMED', 'Resposta simulada: seu problema foi registrado. Caso não resolvido, encaminhe para alefimena@gmail.com.');
  });
  function addSupportMsg(sender,msg){ const p=document.createElement('p'); p.innerHTML='<strong>'+sender+':</strong> '+msg; document.getElementById('supportChat').appendChild(p); }

  function updateSummary(){ const sum=document.getElementById('summary'); const count=selected.length; const sevCounts={1:0,2:0,3:0}; selected.forEach(s=>{ const sv=window.SEVERITY_MAP[s]||1; sevCounts[sv]++; }); sum.innerHTML = '<strong>'+count+'</strong> sintomas selecionados<br>🔴 '+(sevCounts[3]||0)+' 🔸 '+(sevCounts[2]||0)+' 🟢 '+(sevCounts[1]||0); }

});
