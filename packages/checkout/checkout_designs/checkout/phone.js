/* ════════════════════════════════════════════════════════════════════
   CHECKOUT · international phone input
   Fused country-dial selector + masked, country-aware national number.
   Classic (non-module) script — shares the global lexical scope.
   Load order: last — after interactions.js (uses updateCompletion()).
   ════════════════════════════════════════════════════════════════════ */
(function(){

  /* Per-country config:
     dial   — E.164 country calling code
     groups — how the national significant number is chunked for display
     len    — [min, max] number of national digits considered valid
     ex     — placeholder example (national, grouped)
     trunk  — leading trunk digit stripped when pasted (UK/EU '0', US '1') */
  const COUNTRIES = [
    { iso:'GB', name:'United Kingdom', dial:'+44',  groups:[4,6],       len:[10,10], ex:'7400 123456',   trunk:'0' },
    { iso:'IE', name:'Ireland',        dial:'+353', groups:[2,3,4],     len:[9,9],   ex:'85 123 4567',   trunk:'0' },
    { iso:'FR', name:'France',         dial:'+33',  groups:[1,2,2,2,2], len:[9,9],   ex:'6 12 34 56 78', trunk:'0' },
    { iso:'DE', name:'Germany',        dial:'+49',  groups:[3,8],       len:[10,11], ex:'151 23456789',  trunk:'0' },
    { iso:'US', name:'United States',  dial:'+1',   groups:[3,3,4],     len:[10,10], ex:'201 555 0123',  trunk:'1' },
  ];
  const byIso = iso => COUNTRIES.find(c=>c.iso===iso) || COUNTRIES[0];

  /* group a digit string per the country's chunk pattern */
  function group(digits, c){
    const out=[]; let i=0;
    for(const g of c.groups){ if(i>=digits.length) break; out.push(digits.slice(i,i+g)); i+=g; }
    if(i<digits.length) out.push(digits.slice(i));
    return out.join(' ');
  }

  /* normalise raw input → national digits for the given country */
  function digitsFor(raw, c){
    let d = (raw||'').replace(/\D/g,'');
    if(c.iso==='US'){ if(d.length===11 && d[0]==='1') d=d.slice(1); }
    else if(c.trunk && d[0]===c.trunk){ d=d.replace(/^0+/, m=>m.slice(1)).replace(/^0/,''); } // drop a single trunk 0
    return d.slice(0, c.len[1]);
  }

  /* registry: field-id → { country } */
  const reg = {};

  /* ── public API consumed by validation.js ─────────────────────────── */
  window.PhoneField = {
    countryOf(id){ return reg[id] ? reg[id].country : 'GB'; },
    /* inline blur error — empty is allowed (the field may be optional);
       a present-but-incomplete number returns a blameless message */
    error(input){
      const c = byIso(reg[input.id] ? reg[input.id].country : 'GB');
      const d = input.value.replace(/\D/g,'');
      if(d.length===0) return true;
      if(d.length < c.len[0]) return 'Your phone number looks incomplete.';
      if(d.length > c.len[1]) return 'That phone number has too many digits.';
      return true;
    },
    /* strict completion check — empty counts as NOT valid */
    valid(id){
      const c = byIso(reg[id] ? reg[id].country : 'GB');
      const el = document.getElementById(id); if(!el) return false;
      const d = el.value.replace(/\D/g,'');
      return d.length >= c.len[0] && d.length <= c.len[1];
    },
    /* E.164-ish value for submission, e.g. +447400123456 */
    e164(id){
      const c = byIso(reg[id] ? reg[id].country : 'GB');
      const el = document.getElementById(id); if(!el) return '';
      const d = el.value.replace(/\D/g,''); if(!d) return '';
      return c.dial + d;
    },
    /* re-apply mask after a programmatic value set (e.g. saved-address autofill) */
    refresh(id){ const g=document.querySelector(`.phone-group[data-phone="${id}"]`); if(g) g.__reformat(); },
  };

  /* ── wire each phone group ────────────────────────────────────────── */
  document.querySelectorAll('.phone-group[data-phone]').forEach(groupEl=>{
    const id    = groupEl.dataset.phone;
    const input = document.getElementById(id);
    const ccBtn = groupEl.querySelector('.phone-cc');
    const dialEl= groupEl.querySelector('.phone-dial');
    const menu  = groupEl.querySelector('.phone-menu');
    if(!input || !ccBtn || !menu) return;

    reg[id] = { country: groupEl.dataset.country || 'GB' };

    /* build the country list once */
    menu.innerHTML = COUNTRIES.map(c=>`
      <button type="button" class="ac-item" role="option" data-iso="${c.iso}" aria-selected="${c.iso===reg[id].country}">
        <span class="tick ico"><i data-lucide="check"></i></span>
        <span class="nm">${c.name}</span>
        <span class="dl">${c.dial}</span>
      </button>`).join('');

    function syncDial(){ dialEl.textContent = byIso(reg[id].country).dial; }
    function syncPlaceholder(){ input.placeholder = byIso(reg[id].country).ex; }

    /* reformat the current value for the active country */
    function reformat(){
      const c = byIso(reg[id].country);
      const d = digitsFor(input.value, c);
      input.value = group(d, c);
    }
    groupEl.__reformat = reformat;

    function setCountry(iso){
      reg[id].country = iso;
      menu.querySelectorAll('.ac-item').forEach(it=> it.setAttribute('aria-selected', String(it.dataset.iso===iso)));
      syncDial(); syncPlaceholder(); reformat();
      if(input.getAttribute('aria-invalid')==='true' && typeof validateField==='function') validateField(input);
      if(typeof updateCompletion==='function') updateCompletion();
    }

    /* menu open/close */
    function openMenu(){
      menu.hidden=false; ccBtn.setAttribute('aria-expanded','true');
      const sel = menu.querySelector('[aria-selected="true"]') || menu.querySelector('.ac-item');
      sel && sel.focus();
    }
    function closeMenu(focusBtn){
      menu.hidden=true; ccBtn.setAttribute('aria-expanded','false');
      if(focusBtn) ccBtn.focus();
    }
    ccBtn.addEventListener('click', ()=>{ menu.hidden ? openMenu() : closeMenu(); });

    menu.addEventListener('click', e=>{
      const it=e.target.closest('.ac-item'); if(!it) return;
      setCountry(it.dataset.iso); closeMenu(true); input.focus();
    });
    /* keyboard nav inside the menu */
    menu.addEventListener('keydown', e=>{
      const items=[...menu.querySelectorAll('.ac-item')];
      const i=items.indexOf(document.activeElement);
      if(e.key==='ArrowDown'){ e.preventDefault(); (items[i+1]||items[0]).focus(); }
      else if(e.key==='ArrowUp'){ e.preventDefault(); (items[i-1]||items[items.length-1]).focus(); }
      else if(e.key==='Escape'){ e.preventDefault(); closeMenu(true); }
      else if(e.key==='Enter'||e.key===' '){ e.preventDefault(); document.activeElement.click(); }
    });
    /* close on outside click */
    document.addEventListener('click', e=>{ if(!groupEl.contains(e.target)) closeMenu(false); });

    /* masking + intl-paste detection */
    input.addEventListener('input', ()=>{
      const raw = input.value.trim();
      /* pasted with a + dial code → switch country, keep the rest */
      if(raw[0]==='+'){
        const match = COUNTRIES
          .filter(c=> raw.replace(/[^\d+]/g,'').startsWith(c.dial))
          .sort((a,b)=> b.dial.length-a.dial.length)[0];
        if(match){
          reg[id].country = match.iso;
          menu.querySelectorAll('.ac-item').forEach(it=> it.setAttribute('aria-selected', String(it.dataset.iso===match.iso)));
          syncDial(); syncPlaceholder();
          const rest = raw.replace(/[^\d+]/g,'').slice(match.dial.length);
          const c=byIso(match.iso); input.value = group(c.iso==='US'?rest.replace(/\D/g,'').slice(0,c.len[1]):rest.replace(/\D/g,'').slice(0,c.len[1]), c);
          return;
        }
      }
      reformat();
    });

    /* group focus ring (covers both the button and the input) */
    [ccBtn,input].forEach(el=>{
      el.addEventListener('focus', ()=> groupEl.classList.add('is-focus'));
      el.addEventListener('blur',  ()=> setTimeout(()=>{ if(!groupEl.contains(document.activeElement)) groupEl.classList.remove('is-focus'); },0));
    });

    syncDial(); syncPlaceholder();
  });

  if(window.lucide) lucide.createIcons();
})();
