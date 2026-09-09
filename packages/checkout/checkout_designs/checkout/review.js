/* ════════════════════════════════════════════════════════════════════
   REVIEW MODE — design-audit aid (NOT part of the production checkout)
   Reveals every state that's normally hidden behind a user action, inline,
   so the full design can be reviewed in one pass. Toggle bottom-left.
   Load order: 5 — after interactions.js.
   ════════════════════════════════════════════════════════════════════ */
(function(){
  function $(id){ return document.getElementById(id); }
  function show(id){ const el=$(id); if(el) el.hidden=false; }
  function setVal(id,v){ const el=$(id); if(el){ el.value=v; el.closest('.fl')?.classList.add('is-filled'); } }

  let active=false;
  function enableReview(){
    if(active) return; active=true;
    try{
      /* fill enough real data that completion logic unlocks dependent sections */
      setVal('email','alex@example.com');
      setVal('first','Alex'); setVal('last','Morgan'); setVal('phone','07700 900123');
      setVal('line1','27 Hudson Yards'); setVal('line2','Flat 4'); setVal('city','London'); setVal('postcode','EC1V 9BD');
      setVal('c-first','Alex'); setVal('c-last','Morgan'); setVal('c-phone','07700 900123');

      /* 1 · CONTACT — recognised account, signed-in + guest, stacked */
      show('acct-recognised'); show('otc-intro'); show('otc-wrap'); show('signed-in');
      show('acct-guest'); show('guest-saved-note');
      if($('guest-email')) $('guest-email').textContent = 'alex@example.com';
      if($('guest-save')) $('guest-save').checked = true;

      /* 2 · ADDRESS — structured fields + found banner + country + saved-address cards */
      show('addr-fields'); show('addr-found'); show('country-field'); show('country-row'); show('saved-addr');
      if(typeof renderSavedAddresses==='function' && typeof defaultAddr==='function')
        renderSavedAddresses('saved-addr-list', defaultAddr().id);

      /* 3 · PICKUP — the chosen-point confirmation card */
      show('pickup-mode');
      if(typeof pickupPoints!=='undefined' && pickupPoints.length){
        state.pickupPoint = pickupPoints[0];
        if(typeof showPickupSelected==='function') showPickupSelected();
      }

      /* 4 · COLLECT flow — store list + collector details revealed alongside */
      document.querySelectorAll('[data-flow="collect"]').forEach(el=>{ if(!el.classList.contains('seg-btn')) el.hidden=false; });
      if(typeof renderStores==='function' && typeof storeOrder!=='undefined') renderStores(storeOrder);
      show('alt-fields'); if($('alt-collector')) $('alt-collector').checked = true;

      /* 5 · BILLING — billing form + saved billing cards */
      if($('billing-same')) $('billing-same').checked = false;
      show('billing-fields'); show('saved-bill');
      if(typeof renderSavedAddresses==='function' && typeof defaultAddr==='function')
        renderSavedAddresses('saved-bill-list', defaultAddr().id);

      /* derived UI refresh — must run BEFORE we force the shipping/payment panels open,
         because updateCompletion() inside render() can re-lock the shipping list */
      if(typeof render==='function') render();

      /* 6 · SHIPPING — nominated method, with BOTH calendar and chosen-chip + slots shown.
         Done last so the completion pass above can't re-hide it. */
      state.shippingId = 'nominated';
      if(!state.nominatedDate){
        for(let i=1;i<=45;i++){ const d=new Date(); d.setDate(d.getDate()+i);
          if(typeof isNominatedDay==='function' && isNominatedDay(d)){ state.nominatedDate = dateKey(d); break; } }
      }
      if(state.nominatedSlot==null){
        state.nominatedSlot = 0;
        if(typeof nominatedSlotUnavailable==='function' && state.nominatedDate){
          for(let i=0;i<3;i++){ if(!nominatedSlotUnavailable(state.nominatedDate,i)){ state.nominatedSlot=i; break; } }
        }
      }
      if(typeof renderShipping==='function') renderShipping();
      if($('shipping-locked')) $('shipping-locked').hidden = true;
      show('shipping-list');
      show('nominated-panel'); show('nom-picker'); show('nom-chosen'); show('nom-slots');
      if(typeof renderCalendar==='function') renderCalendar();
      if(typeof renderNomChosen==='function') renderNomChosen();
      if(typeof renderNominatedSlots==='function') renderNominatedSlots();

      /* 7 · PAYMENT — reveal all four method panels stacked */
      document.querySelectorAll('.pm-panel').forEach(p=> p.hidden=false);

      tagSections();
      if(window.lucide) lucide.createIcons();
    }catch(e){ console.warn('[review] reveal error:', e); }
  }

  function tag(el, text){
    if(!el || (el.previousElementSibling && el.previousElementSibling.classList && el.previousElementSibling.classList.contains('rv-tag'))) return;
    const t=document.createElement('div'); t.className='rv-tag'; t.textContent=text;
    el.parentNode.insertBefore(t, el);
  }
  function tagSections(){
    tag($('acct-recognised'), 'Contact · recognised account (phone code) + signed-in');
    tag($('acct-guest'), 'Contact · guest — save details');
    tag($('saved-addr'), 'Address · saved addresses (signed-in)');
    tag($('pickup-mode'), 'Address · pickup-point chosen');
    tag($('nominated-panel'), 'Shipping · nominated-day calendar + slots');
    tag($('billing-fields'), 'Payment · billing address');
  }

  /* floating control */
  const bar=document.createElement('div');
  bar.className='review-ctl';
  bar.innerHTML =
    '<span class="rv-dot"></span>'+
    '<button type="button" id="rv-show">Show all states</button>'+
    '<button type="button" id="rv-success">Confirmation</button>'+
    '<button type="button" id="rv-reset">Reset</button>';
  document.body.appendChild(bar);
  $('rv-show').addEventListener('click', ()=>{ enableReview(); bar.classList.add('on'); });
  $('rv-success').addEventListener('click', ()=>{ const s=$('success'); if(s){ s.hidden=false; if(window.lucide) lucide.createIcons(); } });
  $('rv-reset').addEventListener('click', ()=> location.reload());

  if(/[?&]review=1/.test(location.search)) { enableReview(); bar.classList.add('on'); }
})();
