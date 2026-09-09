/* ════════════════════════════════════════════════════════════════════
   CHECKOUT · validation + completion
   Part of the Tender checkout. Classic (non-module) script — shares the
   global lexical scope with the other checkout/*.js files.
   Load order: 3 — after render.js
   ════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════
   VALIDATION — on blur; re-validate live once errored; value-preserving
   ════════════════════════════════════════════════════════════════════ */
const validators = {
  email:   v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) || 'Your email address looks incomplete.',
  required:v => v.trim().length>0 || 'This field is required.',
  postcode:v => /^[A-Za-z]{1,2}\d[A-Za-z\d]?\s*\d[A-Za-z]{2}$/.test(v.trim()) || 'Enter a valid UK postcode.',
  phone:   v => /^[\d\s+()-]{7,}$/.test(v.trim()) || 'Enter a valid phone number.',
  card:    v => (v.replace(/\s/g,'').length>=15 && /^\d+$/.test(v.replace(/\s/g,''))) || 'Your card number looks incomplete.',
  exp:     v => /^(0[1-9]|1[0-2])\s*\/\s*\d{2}$/.test(v.trim()) || 'Use the MM / YY format.',
  cvc:     v => /^\d{3,4}$/.test(v.trim()) || 'Your security code is 3–4 digits.',
};
function fieldError(input){
  const rule=input.dataset.validate; if(!rule) return true;
  if(input.disabled || input.closest('[hidden]')) return true;
  const r = validators[rule] ? validators[rule](input.value) : true;
  return r===true ? true : r;
}
function showError(input,msg){
  const fl = input.closest('.fl, .card-group'); if(fl) fl.classList.add('has-error');
  input.setAttribute('aria-invalid','true');
  const err = document.getElementById(input.getAttribute('aria-describedby'));
  if(err){ err.querySelector('.t').textContent=msg; err.classList.add('show'); }
}
function clearError(input){
  const fl = input.closest('.fl, .card-group'); if(fl) fl.classList.remove('has-error');
  input.removeAttribute('aria-invalid');
  // only hide the message if no sibling input still errors (card-group shares one err)
  const err = document.getElementById(input.getAttribute('aria-describedby'));
  if(err){
    const grp = input.closest('.card-group');
    if(grp){ const stillBad=[...grp.querySelectorAll('input')].some(i=>i.getAttribute('aria-invalid')==='true'); if(stillBad) return; }
    err.classList.remove('show');
  }
}
function validateField(input){ const r=fieldError(input); if(r===true){ clearError(input); return true; } showError(input,r); return false; }

document.querySelectorAll('[data-validate]').forEach(input=>{
  input.addEventListener('blur', ()=> validateField(input));
  input.addEventListener('input', ()=>{ if(input.getAttribute('aria-invalid')==='true') validateField(input); });
});

/* ════════════════════════════════════════════════════════════════════
   COMPLETION ticks + shipping unlock
   ════════════════════════════════════════════════════════════════════ */
function setDone(block,done){ const el=document.querySelector(`[data-block="${block}"]`); if(el) el.classList.toggle('is-done',!!done); }
function val(id){ return (document.getElementById(id)?.value || '').trim(); }
function hasShippableAddress(){ return !!(val('line1') && val('city') && val('postcode')); }
function updateCompletion(){
  setDone('contact', validators.email(val('email'))===true);

  let addrDone;
  if(state.shippingId==='pickup'){
    // pickup mode: recipient name + phone + a chosen point (no street address)
    addrDone = val('first') && val('last') && validators.phone(val('phone'))===true && !!state.pickupPoint;
  } else {
    addrDone = ['first','last','line1','city'].every(id=>val(id)) && validators.postcode(val('postcode'))===true;
  }
  setDone('address', !!addrDone);
  state.addressValid = !!addrDone;
  if(state.shippingId!=='pickup') toggleShippingLock();
  const nomReady = state.shippingId!=='nominated' || (!!state.nominatedDate && state.nominatedSlot!=null);
  setDone('shipping', state.shippingId!=='pickup' && hasShippableAddress() && !!state.shippingId && nomReady);

  setDone('store', !!state.store);
  const colDone = ['c-first','c-last'].every(id=>val(id)) && validators.phone(val('c-phone'))===true;
  setDone('collector', colDone);

  let payDone;
  if(state.method==='card'){
    payDone = validators.card(val('card-number'))===true && validators.exp(val('card-exp'))===true
           && validators.cvc(val('card-cvc'))===true && !!val('card-name') && billingComplete();
  } else { payDone = true; }
  setDone('payment', payDone);
  updatePayDisabled();
}

/* Pay button stays disabled until everything required for the current flow + method is filled in. */
function isComplete(){
  if(validators.email(val('email'))!==true) return false;
  if(state.fulfilment==='delivery'){
    if(state.shippingId==='pickup'){
      if(!(val('first') && val('last') && validators.phone(val('phone'))===true)) return false;
      if(!state.pickupPoint) return false;
    } else {
      if(!(val('first') && val('last') && val('line1') && val('city') && validators.postcode(val('postcode'))===true)) return false;
      if(!state.shippingId) return false;
      if(state.shippingId==='nominated' && (!state.nominatedDate || state.nominatedSlot==null)) return false;
    }
  } else {
    if(!state.store) return false;
    if(!(val('c-first') && val('c-last') && validators.phone(val('c-phone'))===true)) return false;
    if(document.getElementById('alt-collector').checked && !val('alt-name')) return false;
  }
  if(state.method==='card'){
    if(validators.card(val('card-number'))!==true) return false;
    if(validators.exp(val('card-exp'))!==true) return false;
    if(validators.cvc(val('card-cvc'))!==true) return false;
    if(!val('card-name')) return false;
    if(!billingComplete()) return false;
  }
  return true;
}
function updatePayDisabled(){
  if(processing) return;
  const ok=isComplete();
  document.querySelectorAll('#pay-btn,#m-pay-btn').forEach(b=> b.disabled=!ok);
}
/* billing address counts as complete when 'same as delivery' is ticked, or when its fields are filled */
function billingComplete(){
  if(document.getElementById('billing-same').checked) return true;
  return !!(val('bill-first') && val('bill-last') && val('bill-line1') && val('bill-city') && validators.postcode(val('bill-postcode'))===true);
}
function toggleShippingLock(){
  const locked=document.getElementById('shipping-locked'); const list=document.getElementById('shipping-list');
  if(hasShippableAddress()){ if(!locked.hidden){ locked.hidden=true; list.hidden=false; list.classList.add('reveal'); renderShipping(); if(window.lucide) lucide.createIcons(); } }
  else { locked.hidden=false; list.hidden=true; }
}
