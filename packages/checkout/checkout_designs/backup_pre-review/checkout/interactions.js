/* ════════════════════════════════════════════════════════════════════
   CHECKOUT · interactions + init
   Part of the Tender checkout. Classic (non-module) script — shares the
   global lexical scope with the other checkout/*.js files.
   Load order: 4 — LAST, runs init
   ════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════
   FLOW TOGGLE
   ════════════════════════════════════════════════════════════════════ */
function setFlow(flow){
  state.fulfilment=flow;
  const seg=document.getElementById('fulfilment-seg'); seg.dataset.active=flow;
  seg.querySelectorAll('.seg-btn').forEach(b=> b.setAttribute('aria-selected', b.dataset.flow===flow));
  document.querySelectorAll('[data-flow]').forEach(el=>{ if(el.classList.contains('seg-btn')) return; el.hidden = el.dataset.flow!==flow; });
  // billing label follows fulfilment
  const bl=document.getElementById('billing-label'); if(bl) bl.textContent = flow==='collect' ? 'Use collector details as billing address' : 'Use delivery address as billing address';
  // keep the shipping-method section hidden while in pickup mode (delivery flow only)
  const shipSec=document.querySelector('[data-block="shipping"]'); if(shipSec) shipSec.hidden = flow!=='delivery' || state.shippingId==='pickup';
  toggleNominatedPanel();
  render();
}
document.querySelectorAll('#fulfilment-seg .seg-btn').forEach(b=> b.addEventListener('click', ()=> setFlow(b.dataset.flow)));

/* ════════════════════════════════════════════════════════════════════
   CONTACT · passwordless OTC
   ════════════════════════════════════════════════════════════════════ */
const emailInput=document.getElementById('email');
const acctRecognised=document.getElementById('acct-recognised');
const acctGuest=document.getElementById('acct-guest');

// Mock account directory: every email is treated as a "recognised" account (passwordless
// phone-code sign-in) EXCEPT these guest-only domains, which fall through to the guest flow.
const GUEST_DOMAINS = ['guest.com','trade.com'];

// On blur, branch on the email's domain into one of two states:
//   • recognised account  → show the 6-digit phone-code entry
//   • guest               → offer to save details for next time
function classifyEmail(){
  const valid = validators.email(emailInput.value)===true;
  const signedIn = !document.getElementById('signed-in').hidden;
  if(!valid){ acctRecognised.hidden=true; acctGuest.hidden=true; return; }
  if(signedIn) return; // already verified — leave the signed-in state in place
  const domain = emailInput.value.split('@')[1].toLowerCase();
  if(!GUEST_DOMAINS.includes(domain)){
    acctGuest.hidden=true;
    acctRecognised.hidden=false;
    document.getElementById('otc-intro').hidden=false;
    document.getElementById('otc-wrap').hidden=false;
    clearOTC();
    announce('Welcome back — we texted a 6-digit code to your phone.');
  } else {
    acctRecognised.hidden=true;
    acctGuest.hidden=false;
  }
}
emailInput.addEventListener('blur', ()=>{ classifyEmail(); updateCompletion(); });

document.getElementById('acct-dismiss').addEventListener('click', ()=>{ acctRecognised.hidden=true; announce('Continuing as a guest.'); });
document.getElementById('otc-cancel').addEventListener('click', ()=>{ acctRecognised.hidden=true; announce('Continuing as a guest.'); });
document.getElementById('otc-resend').addEventListener('click', ()=> announce('A new code is on its way.'));
document.getElementById('guest-save').addEventListener('change', e=>{
  document.getElementById('guest-email').textContent = emailInput.value || 'your phone';
  document.getElementById('guest-saved-note').hidden = !e.target.checked;
});

const otc=[...document.querySelectorAll('#otc input')];
function clearOTC(){ otc.forEach(i=>i.value=''); }
function checkOTC(){ if(otc.map(i=>i.value).join('').length===6){ document.getElementById('otc-intro').hidden=true; document.getElementById('otc-wrap').hidden=true; document.getElementById('signed-in').hidden=false; signInUser(); } }
otc.forEach((box,i)=>{
  box.addEventListener('input', ()=>{ box.value=box.value.replace(/\D/g,'').slice(0,1); if(box.value && i<otc.length-1) otc[i+1].focus(); checkOTC(); });
  box.addEventListener('keydown', e=>{ if(e.key==='Backspace'&&!box.value&&i>0){ otc[i-1].focus(); otc[i-1].value=''; } if(e.key==='ArrowLeft'&&i>0) otc[i-1].focus(); if(e.key==='ArrowRight'&&i<otc.length-1) otc[i+1].focus(); });
  box.addEventListener('paste', e=>{ e.preventDefault(); const d=(e.clipboardData.getData('text')||'').replace(/\D/g,'').slice(0,6).split(''); d.forEach((x,k)=>{ if(otc[k]) otc[k].value=x; }); (otc[Math.min(d.length,5)]||otc[5]).focus(); checkOTC(); });
});

/* ════════════════════════════════════════════════════════════════════
   ADDRESS · autocomplete + manual escape + country
   ════════════════════════════════════════════════════════════════════ */
const addrSearch=document.getElementById('addr-search'); const addrResults=document.getElementById('addr-results'); const addrFields=document.getElementById('addr-fields');
let acResults=[];
function showAddrFields(){ addrFields.hidden=false; }
function fillFloating(id){ const el=document.getElementById(id); el.closest('.fl')?.classList.toggle('is-filled', !!el.value); }
addrSearch.addEventListener('input', ()=>{
  const raw=addrSearch.value.trim(); const q=raw.toLowerCase();
  if(q.length<2){ addrResults.hidden=true; addrSearch.setAttribute('aria-expanded','false'); return; }
  const matches=addressIndex.filter(a=>(a.line1+' '+a.city+' '+a.postcode).toLowerCase().includes(q)).slice(0,5);
  acResults=matches.slice();
  // If the query looks like a postcode (contains a digit), offer a generated list at that postcode.
  if(/\d/.test(raw) && acResults.length<5){
    const pc=raw.toUpperCase();
    const streets=['High Street','Station Road','Park Avenue','Church Lane','Queens Road'];
    for(let i=0; acResults.length<5 && i<streets.length; i++){ acResults.push({ line1:`${i+1} ${streets[i]}`, city:'London', postcode:pc }); }
  }
  if(!acResults.length){ addrResults.hidden=true; addrSearch.setAttribute('aria-expanded','false'); return; }
  addrResults.innerHTML=acResults.map((a,i)=>`<button type="button" class="ac-item" role="option" data-ac="${i}">
    <span class="ico"><i data-lucide="map-pin"></i></span>
    <span><span class="l1">${a.line1}</span><br><span class="l2">${a.city}, ${a.postcode}</span></span></button>`).join('');
  addrResults.hidden=false; addrSearch.setAttribute('aria-expanded','true'); if(window.lucide) lucide.createIcons();
});
addrResults.addEventListener('click', e=>{
  const it=e.target.closest('[data-ac]'); if(!it) return;
  const a=acResults[+it.dataset.ac];
  document.getElementById('line1').value=a.line1; document.getElementById('city').value=a.city; document.getElementById('postcode').value=a.postcode;
  ['line1','city','postcode'].forEach(fillFloating);
  addrResults.hidden=true; addrSearch.setAttribute('aria-expanded','false');
  showAddrFields(); document.getElementById('addr-found').hidden=false;
  if(!val('first')) document.getElementById('first').focus();
  updateCompletion();
});
document.addEventListener('click', e=>{ if(!e.target.closest('#addr-search-wrap')) addrResults.hidden=true; });
document.getElementById('addr-manual').addEventListener('click', ()=>{ showAddrFields(); document.getElementById('addr-found').hidden=true; document.getElementById('first').focus(); });
document.getElementById('addr-manual').addEventListener('keydown', e=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); document.getElementById('addr-manual').click(); } });
document.getElementById('country-toggle').addEventListener('click', ()=>{ const f=document.getElementById('country-field'); f.hidden=!f.hidden; if(!f.hidden) document.getElementById('country').focus(); });
document.getElementById('country').addEventListener('change', e=>{ document.getElementById('country-label').textContent=e.target.selectedOptions[0].textContent; });

/* ════════════════════════════════════════════════════════════════════
   RADIOGROUP a11y — roving tabindex + arrow keys (shipping & stores)
   ════════════════════════════════════════════════════════════════════ */
function wireRadiogroup(groupEl,onSelect){
  groupEl.addEventListener('click', e=>{ const p=e.target.closest('[role="radio"]'); if(!p||p.getAttribute('aria-disabled')==='true') return; onSelect(p); });
  groupEl.addEventListener('keydown', e=>{
    const radios=[...groupEl.querySelectorAll('[role="radio"]:not([aria-disabled="true"])')];
    const cur=document.activeElement.closest('[role="radio"]'); let i=radios.indexOf(cur);
    if(['ArrowDown','ArrowRight'].includes(e.key)){ e.preventDefault(); i=(i+1)%radios.length; radios[i].focus(); }
    else if(['ArrowUp','ArrowLeft'].includes(e.key)){ e.preventDefault(); i=(i-1+radios.length)%radios.length; radios[i].focus(); }
    else if([' ','Enter'].includes(e.key)){ e.preventDefault(); if(cur) onSelect(cur); }
  });
}
wireRadiogroup(document.getElementById('shipping-list'), p=>{
  state.shippingId=p.dataset.ship; renderShipping();
  document.querySelector(`[data-ship="${state.shippingId}"]`)?.focus();
  toggleNominatedPanel();
  render();
  announce(getShipping().name+' selected'+(getShipping().price===0?', free':', '+fmt(getShipping().price)));
});

/* ════════════════════════════════════════════════════════════════════
   NOMINATED-DAY · calendar + time-slot interactions
   ════════════════════════════════════════════════════════════════════ */
function toggleNominatedPanel(){
  const panel=document.getElementById('nominated-panel'); if(!panel) return;
  const on = state.fulfilment==='delivery' && state.shippingId==='nominated';
  panel.hidden=!on;
  if(on){ showNominatedView(); }
}
function shiftCalMonth(n){ ensureCalCursor(); calCursor=new Date(calCursor.getFullYear(), calCursor.getMonth()+n, 1); renderCalendar(); }
function pickNominatedDate(key){
  state.nominatedDate=key;
  const firstAvail=NOMINATED_SLOTS.findIndex((s,i)=>!nominatedSlotUnavailable(key,i));   // auto-pick first open slot
  state.nominatedSlot = firstAvail>=0 ? firstAvail : null;
  state.nomEditing=false;                                                                 // collapse calendar to the chip
  showNominatedView();
  renderShipping(); render(); updateCompletion();
  announce('Delivery date set to '+parseKey(key).toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long'})+'.');
}
function pickNominatedSlot(i){
  state.nominatedSlot=i;
  renderNominatedSlots(); renderShipping(); render(); updateCompletion();
  announce(NOMINATED_SLOTS[i].split(' · ')[0]+' slot selected.');
}
document.getElementById('nominated-panel').addEventListener('click', e=>{
  if(e.target.closest('[data-nom-change]')){ state.nomEditing=true; showNominatedView(); return; }
  if(e.target.closest('[data-cal-prev]:not([disabled])')){ shiftCalMonth(-1); return; }
  if(e.target.closest('[data-cal-next]')){ shiftCalMonth(1); return; }
  const day=e.target.closest('[data-day]'); if(day && !day.disabled){ pickNominatedDate(day.dataset.day); return; }
  const slot=e.target.closest('[data-slot]'); if(slot && !slot.disabled){ pickNominatedSlot(+slot.dataset.slot); }
});
wireRadiogroup(document.getElementById('store-list'), p=>{
  state.store=allStores.find(x=>x.id===p.dataset.store); renderStores(currentStoreList());
  document.querySelector(`[data-store="${state.store.id}"]`)?.focus(); render();
  announce(state.store.name+' selected for collection.');
});

/* store search + use my location */
let storeOrder=[...allStores].sort((a,b)=>a.dist-b.dist);
function currentStoreList(){ const q=val('store-search').toLowerCase(); return q?storeOrder.filter(s=>(s.name+' '+s.addr).toLowerCase().includes(q)):storeOrder; }
document.getElementById('store-search').addEventListener('input', ()=> renderStores(currentStoreList()));
document.getElementById('use-location').addEventListener('click', ()=>{
  storeOrder=[...allStores].map(s=>({...s,dist:+(Math.max(0.3,s.dist*(0.5+Math.random()))).toFixed(1)})).sort((a,b)=>a.dist-b.dist);
  document.getElementById('store-search').value=''; renderStores(storeOrder); announce('Stores re-ordered by distance from your location.');
});

/* ════════════════════════════════════════════════════════════════════
   PICKUP POINTS — 3rd-party finder (shipping method "Ship to a pickup point")
   ════════════════════════════════════════════════════════════════════ */
/* postcode-ish (or empty) query → show all nearby; otherwise filter by text */
function currentPickupList(){
  const raw=(document.getElementById('pickup-search')?.value||'').trim(); const q=raw.toLowerCase();
  const list=[...pickupPoints].sort((a,b)=>a.dist-b.dist);
  if(!q || /\d/.test(raw)) return list;
  return list.filter(p=>(p.name+' '+p.addr+' '+CARRIERS[p.carrier].label).toLowerCase().includes(q));
}
/* switch the Delivery-details section between address mode and pickup-point mode.
   pickup mode hides the street fields + the whole shipping-method section, shows the
   locker finder, and keeps just recipient name + phone — no home address required. */
function setPickupMode(on){
  state.shippingId = on ? 'pickup' : 'standard';
  if(!on) state.pickupPoint = null;
  document.getElementById('addr-mode').hidden = on;
  document.getElementById('pickup-mode').hidden = !on;
  document.getElementById('addr-street-group').hidden = on;
  document.getElementById('recipient-sub').hidden = !on;
  const found=document.getElementById('addr-found'); if(found) found.hidden = true;
  document.getElementById('addr-title-text').textContent = on ? 'Pickup point' : 'Delivery details';
  if(on) document.getElementById('addr-fields').hidden = false;     // reveal recipient name + phone
  const shipSec=document.querySelector('[data-block="shipping"]'); // shipping methods only apply to address delivery
  if(shipSec) shipSec.hidden = on || state.fulfilment!=='delivery';
  if(on){
    const inp=document.getElementById('pickup-search'); const pc=val('postcode');
    if(inp && !inp.value && pc) inp.value=pc;
    renderPickupPoints(currentPickupList()); showPickupSelected();
  }
  if(!on && state.signedIn) applyShippingSavedUI();
  render(); updateCompletion();
  if(window.lucide) lucide.createIcons();
}
document.getElementById('to-pickup').addEventListener('click', ()=>{ setPickupMode(true); announce('Find a pickup point below — no home address needed.'); document.getElementById('pickup-search')?.focus(); });
document.getElementById('to-address').addEventListener('click', ()=>{ setPickupMode(false); announce('Back to address delivery.'); document.getElementById('addr-search')?.focus(); });
function reopenPickupFinder(){
  const finder=document.getElementById('pickup-finder'); const sel=document.getElementById('pickup-selected');
  if(finder&&sel){ finder.hidden=false; sel.hidden=true; renderPickupPoints(currentPickupList()); }
  document.getElementById('pickup-search')?.focus();
}
document.getElementById('pickup-search').addEventListener('input', ()=> renderPickupPoints(currentPickupList()));
wireRadiogroup(document.getElementById('pickup-list'), p=>{
  state.pickupPoint=pickupPoints.find(x=>x.id===p.dataset.pickup);
  showPickupSelected(); render();
  announce(`${state.pickupPoint.name} selected — ${CARRIERS[state.pickupPoint.carrier].label}, ${state.pickupPoint.dist} miles away.`);
});
/* "Change" inside the chosen-point card → reopen the finder (keeps the current pick highlighted) */
document.getElementById('pickup-panel').addEventListener('click', e=>{ if(e.target.closest('[data-change-pickup]')) reopenPickupFinder(); });

/* ════════════════════════════════════════════════════════════════════
   PAYMENT accordion (radiogroup)
   ════════════════════════════════════════════════════════════════════ */
const pmTabs=document.getElementById('pm-tabs');
function selectMethod(method){
  state.method=method;
  pmTabs.querySelectorAll('.pm-tab').forEach(t=>{
    const on = t.dataset.method===method;
    t.classList.toggle('on', on);
    t.setAttribute('aria-checked',on); t.tabIndex=on?0:-1;
  });
  document.querySelectorAll('.pm-panel').forEach(p=> p.hidden = p.dataset.panel!==method);
  render();
}
wireRadiogroup(pmTabs, p=> selectMethod(p.dataset.method));

/* live instalment timelines (recompute with the order total) */
function renderInstal(){
  const c = calc();
  const plans = {
    clearpay:{ n:4, when:['Today','In 2 weeks','In 4 weeks','In 6 weeks'] },
    klarna:{   n:3, when:['Today','In 30 days','In 60 days'] },
  };
  Object.entries(plans).forEach(([k,p])=>{
    const wrap=document.querySelector(`[data-instal="${k}"]`); if(!wrap) return;
    const each=Math.round(c.total/p.n); const last=c.total-each*(p.n-1);
    wrap.innerHTML=Array.from({length:p.n}).map((_,i)=>
      `<div class="instal-step${i===0?' first':''}"><span class="a">${fmt(i===p.n-1?last:each)}</span><span class="w">${p.when[i]}</span></div>`).join('');
  });
}

/* CTA label adapts to the chosen method */
function updateCtaLabels(){
  const c=calc();
  const lab = state.method==='card'     ? `Pay <span class="mono">${fmt(c.total)}</span>`
            : state.method==='paypal'   ? 'Continue to PayPal'
            : state.method==='clearpay' ? 'Continue with Clearpay'
            :                             'Continue with Klarna';
  document.querySelectorAll('.cta-label').forEach(el=> el.innerHTML=lab);
}

/* card-group focus ring */
const cardGroup=document.getElementById('card-fields');
cardGroup.querySelectorAll('input').forEach(inp=>{
  inp.addEventListener('focus', ()=> cardGroup.classList.add('is-focus'));
  inp.addEventListener('blur', ()=> cardGroup.classList.remove('is-focus'));
});
/* auto-format card + expiry */
const cardNum=document.getElementById('card-number'); const cardExp=document.getElementById('card-exp');
cardNum.addEventListener('input', ()=>{ cardNum.value=cardNum.value.replace(/\D/g,'').slice(0,16).replace(/(.{4})/g,'$1 ').trim(); });
cardExp.addEventListener('input', ()=>{ const d=cardExp.value.replace(/\D/g,'').slice(0,4); cardExp.value=d.length>=3?d.slice(0,2)+' / '+d.slice(2):d; });
document.getElementById('card-cvc').addEventListener('input', e=>{ e.target.value=e.target.value.replace(/\D/g,'').slice(0,4); });

/* collector alt toggle */
document.getElementById('alt-collector').addEventListener('change', e=>{ document.getElementById('alt-fields').hidden=!e.target.checked; updateCompletion(); });

/* ════════════════════════════════════════════════════════════════════
   SIGNED-IN · saved addresses (delivery + billing)
   ════════════════════════════════════════════════════════════════════ */
const SHIP_ADDR_IDS = { first:'first', last:'last', line1:'line1', line2:'line2', city:'city', postcode:'postcode', phone:'phone' };
const BILL_ADDR_IDS = { first:'bill-first', last:'bill-last', line1:'bill-line1', line2:'bill-line2', city:'bill-city', postcode:'bill-postcode' };
function fillAddr(a, ids){
  Object.entries(ids).forEach(([k,id])=>{ const el=document.getElementById(id); if(el && a[k]!=null){ el.value=a[k]; el.closest('.fl')?.classList.toggle('is-filled', !!a[k]); } });
}
function clearAddr(ids){
  Object.values(ids).forEach(id=>{ const el=document.getElementById(id); if(el){ el.value=''; el.closest('.fl')?.classList.remove('is-filled'); } });
}
function defaultAddr(){ return savedAddresses.find(a=>a.isDefault) || savedAddresses[0]; }
/* called once the phone code is verified */
function signInUser(){
  state.signedIn = true;
  const def = defaultAddr();
  state.shipAddrId = def.id;
  fillAddr(def, { first:'first', last:'last', phone:'phone' });   // recipient name + phone (also covers pickup mode)
  document.getElementById('saved-addr').hidden = false;
  if(state.shippingId!=='pickup') applyShippingSavedUI();
  if(!document.getElementById('billing-fields').hidden) applyBillingSavedUI();
  render(); updateCompletion();
  announce('Signed in — choose from your saved addresses.');
}
function applyShippingSavedUI(){
  if(!state.signedIn) return;
  document.getElementById('saved-addr').hidden = false;
  selectShipAddr(state.shipAddrId || defaultAddr().id);
}
function selectShipAddr(id){
  state.shipAddrId = id;
  renderSavedAddresses('saved-addr-list', id);
  const manual = id==='manual';
  document.getElementById('addr-search-wrap').hidden = !manual;
  if(manual){
    clearAddr({ line1:'line1', line2:'line2', city:'city', postcode:'postcode' });
    document.getElementById('addr-fields').hidden = false;
    const found=document.getElementById('addr-found'); if(found) found.hidden = true;
    document.getElementById('addr-search').focus();
  } else {
    const a = savedAddresses.find(x=>x.id===id); if(a) fillAddr(a, SHIP_ADDR_IDS);
    document.getElementById('addr-fields').hidden = true;   // address carried in the (hidden) inputs
  }
  render(); updateCompletion();
}
function applyBillingSavedUI(){
  if(!state.signedIn) return;
  document.getElementById('saved-bill').hidden = false;
  selectBillAddr(state.billAddrId || defaultAddr().id);
}
function selectBillAddr(id){
  state.billAddrId = id;
  renderSavedAddresses('saved-bill-list', id);
  const manual = id==='manual';
  document.getElementById('bill-manual').hidden = !manual;
  if(manual){ clearAddr(BILL_ADDR_IDS); document.getElementById('bill-first').focus(); }
  else { const a=savedAddresses.find(x=>x.id===id); if(a) fillAddr(a, BILL_ADDR_IDS); }
  updateCompletion();
}
wireRadiogroup(document.getElementById('saved-addr-list'), p=> selectShipAddr(p.dataset.addr));
wireRadiogroup(document.getElementById('saved-bill-list'), p=> selectBillAddr(p.dataset.addr));

/* billing address reveal — show the form when 'use delivery address' is unchecked */
const billingSame=document.getElementById('billing-same');
const billingFields=document.getElementById('billing-fields');
function toggleBilling(){
  billingFields.hidden=billingSame.checked;
  if(!billingSame.checked && state.signedIn) applyBillingSavedUI();
  updateCompletion();
}
billingSame.addEventListener('change', toggleBilling);

/* delivery notes — mark the section done once the customer adds anything */
['delivery-notes','order-ref'].forEach(id=>{
  const el=document.getElementById(id);
  if(el) el.addEventListener('input', ()=> setDone('notes', !!(val('delivery-notes')||val('order-ref'))));
});

/* float-label sync for autofilled/selected fields */
document.querySelectorAll('.fl > input').forEach(inp=> inp.addEventListener('input', ()=> inp.closest('.fl').classList.toggle('is-filled', !!inp.value)));

/* ════════════════════════════════════════════════════════════════════
   SUMMARY interactions (delegated; re-wired each render)
   ════════════════════════════════════════════════════════════════════ */
function wireSummary(){
  document.querySelectorAll('#disc-apply').forEach(b=> b.addEventListener('click', ()=> applyDiscount(b)));
  document.querySelectorAll('#disc-input').forEach(i=> i.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); applyDiscount(i); } }));
  document.querySelectorAll('[data-remove-discount]').forEach(b=> b.addEventListener('click', ()=>{ state.discount=null; render(); announce('Discount removed.'); }));
  document.querySelectorAll('[data-change-store]').forEach(b=> b.addEventListener('click', ()=>{ document.getElementById('store-search')?.focus(); announce('Choose a different store above.'); }));
  document.querySelectorAll('[data-change-pickup-summary]').forEach(b=> b.addEventListener('click', ()=>{ reopenPickupFinder(); announce('Choose a different pickup point above.'); }));
}
function applyDiscount(fromEl){
  const form=fromEl.closest('.disc-form');
  const input=form.querySelector('#disc-input'); const err=form.parentElement.querySelector('#disc-err');
  const code=(input.value||'').trim().toUpperCase();
  const def=VALID_CODES[code];
  if(def){ state.discount={code,...def}; render(); announce(`Code ${code} applied — ${def.label}.`); }
  else { input.classList.add('is-err'); input.setAttribute('aria-invalid','true'); err.querySelector('.t').textContent='That code isn’t valid or has expired.'; err.classList.add('show'); input.focus(); }
}

/* mobile summary accordion */
const mToggle=document.getElementById('m-summary-toggle');
mToggle.addEventListener('click', ()=>{ const p=document.getElementById('m-summary-panel'); const open=p.hidden; p.hidden=!open; mToggle.setAttribute('aria-expanded',open); });

/* ════════════════════════════════════════════════════════════════════
   SUBMIT — validate visible → focus first error → mock pay
   ════════════════════════════════════════════════════════════════════ */
function submitCheckout(triggerBtn){
  const fields=[...document.querySelectorAll('[data-validate]')].filter(f=> !f.disabled && !f.closest('[hidden]'));
  let firstError=null;
  fields.forEach(f=>{ if(!validateField(f) && !firstError) firstError=f; });
  if(state.fulfilment==='collect' && !state.store){ announce('Please choose a collection store.'); if(!firstError){ document.getElementById('store-search').focus(); return; } }
  if(state.fulfilment==='delivery' && state.shippingId==='pickup' && !state.pickupPoint){ announce('Please choose a pickup point.'); if(!firstError){ document.getElementById('pickup-search')?.focus(); return; } }
  if(firstError){ firstError.focus(); announce('Please fix the highlighted field.'); return; }

  const btns=document.querySelectorAll('#pay-btn,#m-pay-btn'); processing=true; btns.forEach(b=>b.disabled=true);
  triggerBtn.dataset.prev=triggerBtn.innerHTML; triggerBtn.innerHTML='<span class="spinner"></span><span>Processing…</span>';
  setTimeout(()=>{
    const msg = state.fulfilment==='collect'
      ? `We'll text you when your order is ready to collect from ${state.store?state.store.name:'your store'}.`
      : (state.shippingId==='pickup' && state.pickupPoint)
      ? `We'll email you a collection code when your parcel arrives at ${state.pickupPoint.name}.`
      : `Thanks — a receipt is on its way to your inbox.`;
    document.getElementById('success-msg').textContent=msg;
    document.getElementById('success').hidden=false;
    processing=false; if(triggerBtn.dataset.prev) triggerBtn.innerHTML=triggerBtn.dataset.prev; updatePayDisabled();
    if(window.lucide) lucide.createIcons();
  },1600);
}
document.getElementById('checkout-form').addEventListener('submit', e=>{ e.preventDefault(); submitCheckout(document.getElementById('pay-btn')); });
document.getElementById('m-pay-btn').addEventListener('click', ()=> submitCheckout(document.getElementById('m-pay-btn')));
document.getElementById('success-close').addEventListener('click', ()=> document.getElementById('success').hidden=true);

function announce(msg){ const l=document.getElementById('live'); l.textContent=''; setTimeout(()=> l.textContent=msg,30); }

/* ════════════════════════════════════════════════════════════════════
   INIT
   ════════════════════════════════════════════════════════════════════ */
renderShipping();
renderStores(storeOrder);
selectMethod('card');
render();
toggleNominatedPanel();
/* advertise the cheapest locker rate on the entry button */
(function(){ const min=Math.min(...Object.values(CARRIERS).map(c=>c.fee)); const s=document.querySelector('#to-pickup .fa-s'); if(s) s.textContent=`InPost, DPD & Evri · delivery from ${fmt(min)}`; })();
document.getElementById('checkout-form').addEventListener('input', updateCompletion);
if(window.lucide) lucide.createIcons();
