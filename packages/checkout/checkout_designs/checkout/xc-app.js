/* ════════════════════════════════════════════════════════════════════
   EXPRESS CHECKOUT · CONFIRM & COMPLETE — page controller
   Classic (non-module) script. Shares the global lexical scope with
   data.js + render.js (reuses: state, fmt, calc, summaryHTML, etaLabel,
   shippingMethods, allStores, stockPill).
   ════════════════════════════════════════════════════════════════════ */
(function(){
  "use strict";
  const $  = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => [...r.querySelectorAll(s)];

  /* ── A clean, believable express basket (overrides data.js sample) ───── */
  state.items = [
    { id:'knit',  title:'Merino crew knit',   variant:'Charcoal · M', sku:'AH-2241-CHM', qty:1, price:12800, icon:'shirt' },
    { id:'belt',  title:'Bridle leather belt', variant:'Black · 34',  sku:'AH-0488-BLK', qty:1, price:7900,  icon:'minus' },
    { id:'scarf', title:'Lambswool scarf',     variant:'Forest',      sku:'AH-0712-FOR', qty:1, price:6500,  icon:'shirt' },
  ];
  state.fulfilment = 'delivery';
  state.shippingId = 'standard';
  state.store      = null;

  /* ════════════════════════════════════════════════════════════════════
     WALLET — what the express sheet handed back. Parameterised over the
     three providers; the card/account line differs per wallet.
     ════════════════════════════════════════════════════════════════════ */
  const WALLETS = {
    apple:  { name:'Apple\u00A0Pay',  instrument:{ kind:'card', brand:'Visa',       last4:'4242' } },
    google: { name:'Google\u00A0Pay', instrument:{ kind:'card', brand:'Mastercard', last4:'1881' } },
    paypal: { name:'PayPal',          instrument:{ kind:'account', email:'jordan.avery@icloud.com' } },
  };
  let provider = 'apple';

  /* ── Authorisation hold ───────────────────────────────────────────────
     The wallet authorised a hold for ONE specific amount. We baseline it
     here and re-baseline whenever the wallet (re-)opens. If the live order
     total later drifts from this — e.g. the buyer changes delivery method —
     the hold no longer covers the order and must be re-authorised before we
     can capture. */
  let authorizedTotal = null;
  let justReauthed = false;
  function reauthNeeded(){ return authorizedTotal != null && calc().total !== authorizedTotal; }

  /* Brand-coloured wallet lockup (placeholders — source official art in prod) */
  function walletBadgeHTML(p){
    if(p==='apple') return '<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M17.05 12.04c-.03-2.6 2.12-3.85 2.22-3.91-1.21-1.77-3.09-2.01-3.76-2.04-1.6-.16-3.12.94-3.93.94-.81 0-2.06-.92-3.39-.89-1.74.03-3.35 1.01-4.25 2.57-1.81 3.14-.46 7.79 1.3 10.34.86 1.25 1.88 2.65 3.22 2.6 1.29-.05 1.78-.83 3.34-.83 1.56 0 2 .83 3.37.81 1.39-.03 2.27-1.27 3.12-2.53.98-1.45 1.39-2.85 1.41-2.92-.03-.01-2.71-1.04-2.74-4.13M14.6 4.59c.71-.86 1.19-2.06 1.06-3.25-1.02.04-2.26.68-2.99 1.54-.66.76-1.23 1.98-1.08 3.15 1.14.09 2.3-.58 3.01-1.44"/></svg><span class="wb-apple">Pay</span>';
    if(p==='google') return '<svg viewBox="0 0 48 48" width="14" height="14" aria-hidden="true"><path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z"/><path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"/><path fill="#FBBC05" d="M11.69 28.18C11.25 26.86 11 25.45 11 24s.25-2.86.69-4.18v-5.7H4.34C2.85 17.09 2 20.45 2 24s.85 6.91 2.34 9.88l7.35-5.7z"/><path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z"/></svg><span style="color:#3c4043">Pay</span>';
    return '<span class="wb-pp1">Pay</span><span class="wb-pp2">Pal</span>';
  }

  /* ── Editable model — the source of truth for the review rows ────────── */
  const model = {
    email:'jordan.avery@icloud.com',
    line1:'12 Marchmont Street', line2:'', city:'London', postcode:'WC1N 1AB',
    phone:'+44 7700 900123',
    name:'',            // the required blank — empty until filled
    notes:'', ref:'', news:false,
  };

  /* ════════════════════════════════════════════════════════════════════
     REVIEW-ROW VALUES
     ════════════════════════════════════════════════════════════════════ */
  function paymentValueHTML(){
    const w = WALLETS[provider]; const inst = w.instrument;
    const detail = inst.kind==='card'
      ? `${inst.brand} <span class="mono">••••&nbsp;${inst.last4}</span>`
      : inst.email;
    return `<span class="pay-mark"><span class="wallet-badge">${walletBadgeHTML(provider)}</span></span>
      <span class="ln" style="margin-top:5px">${detail} · <span class="muted">authorised</span></span>`;
  }

  function renderRowValues(){
    /* contact */
    $('#rv-contact').textContent = model.email;

    /* deliver to — name (once given) sits above the wallet address */
    const nameLn = model.name ? `<span class="ln" style="font-weight:var(--weight-semibold)">${model.name}</span>` : '';
    const l2 = model.line2 ? `${model.line2}, ` : '';
    $('#rv-address').innerHTML =
      `${nameLn}<span class="ln">${model.line1}</span><span class="ln">${l2}${model.city} ${model.postcode}</span>` +
      `<span class="ln muted" style="margin-top:4px"><span class="ico" style="font-size:13px;vertical-align:-2px"><i data-lucide="phone"></i></span> ${model.phone}</span>`;

    /* collect from */
    if(state.store){
      const s = state.store;
      $('#rv-store').innerHTML =
        `<span class="ln" style="font-weight:var(--weight-semibold)">${s.name}</span>` +
        `<span class="ln">${s.addr}</span>` +
        `<span class="ln muted" style="margin-top:4px">${s.dist} mi · ready ${s.lead}</span>`;
    }

    /* shipping */
    const m = shippingMethods.find(x=>x.id===state.shippingId) || shippingMethods[0];
    const price = m.price===0 ? 'Free' : fmt(m.price);
    $('#rv-shipping').innerHTML =
      `<span class="ln" style="font-weight:var(--weight-semibold)">${m.name} · ${price}</span>` +
      `<span class="ln muted" style="margin-top:2px">Arrives ${etaLabel(m)}</span>`;

    /* payment */
    $('#rv-payment').innerHTML = paymentValueHTML();

    if(window.lucide) lucide.createIcons();
  }

  /* ════════════════════════════════════════════════════════════════════
     EDIT-PANEL LISTS (shipping methods / stores) — staged until Save
     ════════════════════════════════════════════════════════════════════ */
  let pendingShip = state.shippingId;
  let pendingStore = null;

  function renderShipEdit(){
    $('#ed-shipping-list').innerHTML = shippingMethods.map(m=>{
      const on = m.id===pendingShip;
      const price = m.price===0
        ? '<span class="pill free"><span class="dot" style="background:var(--fg-tertiary)"></span>Free</span>'
        : `<span class="pprice">${fmt(m.price)}</span>`;
      return `<div class="pick" role="radio" tabindex="${on?0:-1}" aria-checked="${on}" data-ship="${m.id}">
        <span class="radio"></span>
        <div class="pbody">
          <div class="ptop"><span class="pname">${m.name}</span>${price}</div>
          <div class="pmeta">Arrives ${etaLabel(m)}</div>
          <div class="pmuted">${m.sub}</div>
        </div>
      </div>`;
    }).join('');
    if(window.lucide) lucide.createIcons();
  }
  function renderStoreEdit(){
    const sel = pendingStore || state.store;
    $('#ed-store-list').innerHTML = allStores.map(s=>{
      const out = s.stock==='out';
      const on = sel && sel.id===s.id;
      return `<div class="pick" role="radio" tabindex="${on?0:-1}" aria-checked="${on}" aria-disabled="${out}" data-store="${s.id}">
        <span class="radio"></span>
        <div class="pbody">
          <div class="ptop"><span class="pname">${s.name}</span><span class="pmuted" style="margin-top:0">${s.dist} mi</span></div>
          <div class="pmeta">${s.addr}</div>
          <div class="pmuted">${s.hours}</div>
          <div style="display:flex;gap:10px;align-items:center;margin-top:8px">
            ${stockPill(s.stock)}
            ${out?'':`<span class="lead-time"><span class="ico"><i data-lucide="clock"></i></span>Ready ${s.lead}</span>`}
          </div>
        </div>
      </div>`;
    }).join('');
    if(window.lucide) lucide.createIcons();
  }

  /* ════════════════════════════════════════════════════════════════════
     EDIT OPEN / SAVE / CANCEL
     ════════════════════════════════════════════════════════════════════ */
  function rowEl(key){ return $(`.xc-row[data-row="${key}"]`); }
  function closeAllEdits(){ $$('.xc-row.editing').forEach(r=> r.classList.remove('editing')); }

  function openEdit(key){
    closeAllEdits();
    const row = rowEl(key); if(!row) return;
    if(key==='shipping'){ pendingShip = state.shippingId; renderShipEdit(); }
    if(key==='store'){ pendingStore = state.store; renderStoreEdit(); }
    if(key==='contact'){ $('#ed-email').value = model.email; }
    if(key==='address'){
      $('#ed-line1').value = model.line1; $('#ed-line2').value = model.line2;
      $('#ed-city').value = model.city;   $('#ed-postcode').value = model.postcode;
      $('#ed-phone').value = model.phone;
    }
    row.classList.add('editing');
    if(window.lucide) lucide.createIcons();
    const first = row.querySelector('.xc-row-edit input, .xc-row-edit .pick');
    if(first && first.focus) first.focus({preventScroll:true});
  }

  function saveEdit(key){
    if(key==='contact'){ const v=$('#ed-email').value.trim(); if(v) model.email=v; }
    if(key==='address'){
      model.line1 = $('#ed-line1').value.trim() || model.line1;
      model.line2 = $('#ed-line2').value.trim();
      model.city  = $('#ed-city').value.trim() || model.city;
      model.postcode = $('#ed-postcode').value.trim().toUpperCase() || model.postcode;
      model.phone = $('#ed-phone').value.trim() || model.phone;
    }
    if(key==='shipping'){ state.shippingId = pendingShip; }
    if(key==='store'){ if(pendingStore) state.store = pendingStore; }
    rowEl(key)?.classList.remove('editing');
    renderAll();
    announce(reauthNeeded()
      ? `Delivery updated — your total is now ${fmt(calc().total)}. Re-authorise to confirm.`
      : 'Updated');
  }
  function cancelEdit(key){ rowEl(key)?.classList.remove('editing'); }

  /* delegated clicks for the edit controls */
  document.addEventListener('click', (e)=>{
    const open = e.target.closest('[data-edit]');
    if(open){ openEdit(open.getAttribute('data-edit')); return; }
    const save = e.target.closest('[data-edit-save]');
    if(save){ saveEdit(save.getAttribute('data-edit-save')); return; }
    const cancel = e.target.closest('[data-edit-cancel]');
    if(cancel){ cancelEdit(cancel.getAttribute('data-edit-cancel')); return; }

    const ship = e.target.closest('[data-ship]');
    if(ship){ pendingShip = ship.getAttribute('data-ship'); renderShipEdit(); return; }
    const store = e.target.closest('[data-store]');
    if(store){ if(store.getAttribute('aria-disabled')==='true') return;
      pendingStore = allStores.find(s=>s.id===store.getAttribute('data-store')); renderStoreEdit(); return; }
  });

  /* ════════════════════════════════════════════════════════════════════
     GAP FIELDS + CTA GATING
     ════════════════════════════════════════════════════════════════════ */
  function nameValid(){ return $('#gap-name').value.trim().length >= 2; }
  function clearNameError(){
    $('#gap-name-fl')?.classList.remove('has-error');
    $('#gap-name-err')?.classList.remove('show');
    $('#gap-name-field')?.classList.remove('is-err');
  }
  function showNameError(){
    $('#gap-name-fl')?.classList.add('has-error');
    $('#gap-name-err')?.classList.add('show');
    $('#gap-name-field')?.classList.add('is-err');
  }
  /* scroll the required field into view + focus it (no scrollIntoView) */
  function focusName(){
    const inp = $('#gap-name');
    const card = $('.xc-gaps') || inp;
    const r = card.getBoundingClientRect();
    window.scrollTo({ top: Math.max(0, window.scrollY + r.top - 100), behavior:'smooth' });
    setTimeout(()=>{ try{ inp.focus({preventScroll:true}); }catch(_){ inp.focus(); } }, 340);
  }
  function updateGate(){
    const ok = nameValid();
    const dh = $('#d-pay-hint'); if(dh) dh.hidden = ok || reauthNeeded();
    const rc = $('#gap-req-count'); if(rc) rc.hidden = ok;
    if(ok) clearNameError();
  }
  $('#gap-name').addEventListener('input', (e)=>{ model.name = e.target.value.trim(); clearNameError(); renderRowValues(); updateGate(); });
  $('#gap-notes').addEventListener('input', (e)=> model.notes = e.target.value);
  $('#gap-ref').addEventListener('input',  (e)=> model.ref = e.target.value);
  $('#gap-news').addEventListener('change',(e)=> model.news = e.target.checked);

  /* ════════════════════════════════════════════════════════════════════
     SUMMARY + TOTALS
     ════════════════════════════════════════════════════════════════════ */
  function renderSummary(){
    const html = summaryHTML();
    $('#summary-card').innerHTML = html;
    $('#m-summary-panel').innerHTML = html;
    const c = calc();
    $$('[data-total]').forEach(el=> el.textContent = fmt(c.total));
    $('#m-total').textContent = fmt(c.total);
    const n = state.items.reduce((s,i)=>s+i.qty,0);
    $('#m-count').textContent = n + (n!==1?' items':' item');
    if(window.lucide) lucide.createIcons();
  }

  function renderAll(){ renderSummary(); renderRowValues(); renderAuthBand(); renderPayBar(); updateGate(); }

  /* ════════════════════════════════════════════════════════════════════
     STATUS BAND — three states: authorised (default) · re-authorise needed
     (total drifted from the hold) · just re-authorised (transient confirm)
     ════════════════════════════════════════════════════════════════════ */
  function renderAuthBand(){
    const band = $('#xc-auth'); if(!band) return;
    const wname = WALLETS[provider].name;
    const cur = calc().total;
    if(justReauthed){
      band.classList.remove('is-reauth');
      band.innerHTML =
        `<div class="xc-auth-main">
          <p class="xc-reauth-flash"><span class="ico"><i data-lucide="check-circle-2"></i></span> Re-authorised — new hold for <span class="hold">${fmt(authorizedTotal)}</span></p>
          <p class="xc-auth-sub">You're all set. Tap <strong>Confirm&nbsp;&amp;&nbsp;pay</strong> to charge the new total.</p>
        </div>`;
    } else if(reauthNeeded()){
      band.classList.add('is-reauth');
      band.innerHTML =
        `<div class="xc-auth-ico"><span class="ico"><i data-lucide="refresh-cw"></i></span></div>
        <div class="xc-auth-main">
          <p class="xc-auth-title">Re-authorise to confirm your new total</p>
          <p class="xc-auth-sub">Your total changed to <span class="hold">${fmt(cur)}</span>, so the hold <span class="wallet-name">${wname}</span> placed no longer matches your order. Re-open it to authorise the new amount — your earlier hold of <span class="hold">${fmt(authorizedTotal)}</span> is released automatically, so you're never charged twice.</p>
          <div class="xc-auth-delta"><span class="from">${fmt(authorizedTotal)}</span><span class="arr ico"><i data-lucide="arrow-right"></i></span><span class="to">${fmt(cur)}</span></div>
        </div>`;
    } else {
      band.classList.remove('is-reauth');
      band.innerHTML =
        `<div class="xc-auth-main">
          <p class="xc-auth-title">Your order isn't placed yet</p>
          <p class="xc-auth-sub">Your card's authorised with <span class="wallet-name">${wname}</span> — a hold for <span class="hold">${fmt(authorizedTotal ?? cur)}</span>, charged only when you tap <strong>Confirm&nbsp;&amp;&nbsp;pay</strong>.</p>
        </div>`;
    }
    if(window.lucide) lucide.createIcons();
  }

  /* ════════════════════════════════════════════════════════════════════
     PAY BAR — desktop note + both CTAs flip to a re-authorise affordance
     when the hold is stale (skipped mid-flight so the spinner survives)
     ════════════════════════════════════════════════════════════════════ */
  function renderPayBar(){
    const need = reauthNeeded();
    const cur = calc().total;
    const wname = WALLETS[provider].name;
    const note = $('.d-pay-note');
    if(note){
      note.innerHTML = need
        ? `<span class="ico"><i data-lucide="alert-triangle"></i></span> Total changed to <strong>${fmt(cur)}</strong> — re-authorise with ${wname}`
        : `<span class="ico"><i data-lucide="shield-check"></i></span> Card authorised — <strong>not charged</strong> until you confirm`;
      note.classList.toggle('is-warn', need);
    }
    if(!processing){
      $$('.xc-pay-btn').forEach(b=>{
        b.innerHTML = need
          ? `<span class="ico"><i data-lucide="wallet"></i></span><span class="cta-label">Re-authorise <span class="mono">${fmt(cur)}</span></span>`
          : `<span class="ico"><i data-lucide="lock"></i></span><span class="cta-label">Confirm &amp; pay <span class="mono">${fmt(cur)}</span></span>`;
      });
    }
    if(window.lucide) lucide.createIcons();
  }

  function announce(msg){ const l=$('#live'); if(l){ l.textContent=''; setTimeout(()=>{ l.textContent=msg; },30); } }

  /* ════════════════════════════════════════════════════════════════════
     SUBMIT → capture the held authorisation
     ════════════════════════════════════════════════════════════════════ */
  function submit(e){
    if(e) e.preventDefault();
    if(!nameValid()){
      showNameError();
      focusName();
      announce('Enter your name to confirm the order.');
      return;
    }
    $$('.xc-pay-btn').forEach(b=>{ b.disabled=true; b.dataset.label = b.querySelector('.cta-label').innerHTML;
      b.querySelector('.cta-label').innerHTML = ''; const ic=b.querySelector('.ico'); if(ic) ic.outerHTML = '<span class="spinner"></span>'; });
    setTimeout(()=>{
      const s = $('#success');
      $('#success-msg').textContent = `Thanks, ${model.name.split(' ')[0] || 'there'} — a receipt is on its way to ${model.email}.`;
      s.hidden = false;
      if(window.lucide) lucide.createIcons();
    }, 1100);
  }
  /* Re-open the wallet to authorise the new total. Simulated: the buttons
     show a wallet-opening spinner, then the hold is re-baselined to the live
     total and the page returns to the normal "confirm" state. */
  function reauthorize(){
    if(processing) return;
    processing = true;
    const wname = WALLETS[provider].name;
    $$('.xc-pay-btn').forEach(b=>{
      b.disabled = true;
      b.innerHTML = `<span class="spinner"></span><span class="cta-label">Re-opening ${wname}…</span>`;
    });
    announce(`Re-opening ${wname} to authorise ${fmt(calc().total)}.`);
    setTimeout(()=>{
      authorizedTotal = calc().total;   // fresh hold for the new amount
      processing = false;
      justReauthed = true;
      $$('.xc-pay-btn').forEach(b=> b.disabled=false);
      renderAll();
      announce(`Re-authorised — new hold for ${fmt(authorizedTotal)}.`);
      setTimeout(()=>{ justReauthed = false; renderAuthBand(); }, 2800);
    }, 1200);
  }

  /* the single pay affordance: re-authorise if the hold is stale, else capture */
  function onPay(e){
    if(e) e.preventDefault();
    if(processing) return;
    if(reauthNeeded()){ reauthorize(); return; }
    submit();
  }
  $('#confirm-form').addEventListener('submit', onPay);
  $('#m-confirm-btn').addEventListener('click', onPay);
  $('#d-confirm-btn').addEventListener('click', onPay);
  $('#d-pay-hint')?.addEventListener('click', focusName);
  $('#success-close').addEventListener('click', ()=> location.reload());

  /* ════════════════════════════════════════════════════════════════════
     MOBILE summary toggle
     ════════════════════════════════════════════════════════════════════ */
  const mTog = $('#m-summary-toggle');
  if(mTog) mTog.addEventListener('click', ()=>{
    const open = mTog.getAttribute('aria-expanded')==='true';
    mTog.setAttribute('aria-expanded', String(!open));
    $('#m-summary-panel').hidden = open;
  });

  /* ════════════════════════════════════════════════════════════════════
     FLOW / WALLET / REASSURANCE — driven by Tweaks
     ════════════════════════════════════════════════════════════════════ */
  function applyWalletText(){
    const name = WALLETS[provider].name;
    $$('[data-wallet-name]').forEach(el=> el.textContent = name);
    $$('[data-wallet-badge]').forEach(el=> el.innerHTML = walletBadgeHTML(provider));
  }
  window.xcSetWallet = function(p){ if(!WALLETS[p]) return; provider=p; authorizedTotal = calc().total; applyWalletText(); renderAll(); if(window.lucide) lucide.createIcons(); };

  window.xcSetFulfilment = function(f){
    state.fulfilment = f;
    if(f==='collect'){
      state.store = state.store || allStores[0];
      $$('[data-flow="delivery"]').forEach(el=> el.hidden = true);
      $$('[data-flow="collect"]').forEach(el=> el.hidden = false);
      $('#gap-name-label').textContent = "Collector's name";
      $('[data-name-help]').textContent = 'Who\u2019s collecting — bring photo ID that matches.';
    } else {
      state.store = null; state.shippingId = state.shippingId==='pickup' ? 'standard' : state.shippingId;
      $$('[data-flow="collect"]').forEach(el=> el.hidden = true);
      $$('[data-flow="delivery"]').forEach(el=> el.hidden = false);
      $('#gap-name-label').textContent = 'Full name';
      $('[data-name-help]').textContent = 'For your delivery label.';
    }
    closeAllEdits();
    authorizedTotal = calc().total;   // a fulfilment switch is a fresh scenario → freshly authorised
    renderAll();
  };

  window.xcSetReassurance = function(mode){
    $('#xc-auth').classList.toggle('is-subtle', mode==='subtle');
  };

  /* demo helper — pre-fill or clear the required name gap */
  window.xcSetNameProvided = function(provided){
    const inp = $('#gap-name');
    inp.value = provided ? 'Jordan Avery' : '';
    model.name = inp.value;
    inp.closest('.fl').classList.toggle('is-filled', !!inp.value);
    renderRowValues(); updateGate();
  };

  /* ════════════════════════════════════════════════════════════════════
     INIT
     ════════════════════════════════════════════════════════════════════ */
  applyWalletText();
  authorizedTotal = calc().total;
  renderAll();
  if(window.lucide) lucide.createIcons();
})();
