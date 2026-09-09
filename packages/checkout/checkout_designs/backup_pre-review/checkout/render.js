/* ════════════════════════════════════════════════════════════════════
   CHECKOUT · rendering
   Part of the Tender checkout. Classic (non-module) script — shares the
   global lexical scope with the other checkout/*.js files.
   Load order: 2 — after data.js
   ════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════
   THUMBNAILS — monochrome line glyphs (Lucide names)
   ════════════════════════════════════════════════════════════════════ */
function thumb(icon, qty){
  return `<span class="os-thumb"><span class="ico" style="font-size:24px"><i data-lucide="${icon}"></i></span><span class="qty">${qty}</span></span>`;
}

/* ════════════════════════════════════════════════════════════════════
   SUMMARY render (shared → desktop card + mobile panel)
   ════════════════════════════════════════════════════════════════════ */
function summaryHTML(){
  const c = calc();
  const itemCount = state.items.reduce((s,i)=>s+i.qty,0);

  let collect = '';
  if(state.fulfilment==='collect' && state.store){
    const s = state.store;
    collect = `<div class="collect-banner">
      <span class="ico"><i data-lucide="map-pin"></i></span>
      <div style="flex:1;min-width:0">
        <p class="eyebrow" style="margin:0 0 2px">Collecting from</p>
        <p class="sname" style="margin:0">${s.name}</p>
        <p class="sline" style="margin:2px 0 0">${s.addr} · ${s.dist} mi · ready ${s.lead}</p>
      </div>
      <button type="button" class="change" data-change-store>Change</button>
    </div>`;
  }
  if(state.fulfilment==='delivery' && state.shippingId==='pickup' && state.pickupPoint){
    const p = state.pickupPoint;
    collect = `<div class="collect-banner">
      <span class="ico"><i data-lucide="package"></i></span>
      <div style="flex:1;min-width:0">
        <p class="eyebrow" style="margin:0 0 3px">Shipping to pickup point</p>
        <p class="sname" style="margin:0;display:flex;align-items:center;gap:7px">${carrierMark(p.carrier)}<span style="min-width:0;overflow:hidden;text-overflow:ellipsis">${p.name}</span></p>
        <p class="sline" style="margin:3px 0 0">${p.addr} · ${p.dist} mi</p>
      </div>
      <button type="button" class="change" data-change-pickup-summary>Change</button>
    </div>`;
  }

  const lines = state.items.map(i=>`
    <li class="os-item">
      ${thumb(i.icon, i.qty)}
      <div class="meta">
        <div class="iname">${i.title}</div>
        <div class="ivar">${i.variant}</div>
        ${i.qty>1?`<div class="iunit">${i.qty} × ${fmt(i.price)} each</div>`:''}
      </div>
      <div class="iprice">${fmt(i.price*i.qty)}</div>
    </li>`).join('');

  const discIcon = state.discount && (state.discount.type==='freeship'||state.discount.type==='shippct') ? 'truck' : 'badge-percent';
  const discountBlock = state.discount
    ? `<div class="disc-applied">
        <span class="tag"><span class="ico"><i data-lucide="${discIcon}"></i></span>${state.discount.code} · ${state.discount.label}</span>
        <button type="button" class="remove" data-remove-discount>Remove</button>
      </div>`
    : `<div class="disc-form">
         <label class="sr-only" for="disc-input">Discount code</label>
         <input id="disc-input" class="disc-input" placeholder="Discount code" aria-describedby="disc-err" />
         <button type="button" class="disc-apply-btn" id="disc-apply">Apply</button>
       </div>
       <div class="err-msg" id="disc-err" role="alert"><span class="ico"><i data-lucide="alert-circle"></i></span><span class="t"></span></div>`;

  const shipLabel = state.fulfilment==='collect' ? 'Collection'
                  : state.shippingId==='pickup' ? 'Pickup point'
                  : (state.shippingId==='nominated' && state.nominatedDate) ? `Nominated · ${fmtDay(parseKey(state.nominatedDate))}`
                  : (getShipping()?.name || 'Shipping');
  const shipFree = (state.fulfilment==='collect' || c.shipping===0);
  const shipOrig = c.discShip>0 ? `<span class="v strike">${fmt(c.baseShipping)}</span>` : '';
  const shipValue = (state.shippingId==='pickup' && !state.pickupPoint)
    ? `<span class="pmuted" style="margin-top:0">Choose a point</span>`
    : shipFree
    ? `${shipOrig}<span class="pill free"><span class="dot" style="background:var(--success)"></span>Free</span>`
    : `${shipOrig}<span class="v">${fmt(c.shipping)}</span>`;

  return `
    ${collect}
    <div class="os-h"><h2>Order summary</h2><span class="count">${itemCount} item${itemCount!==1?'s':''}</span></div>
    <ul class="os-items${state.items.length>4?' is-scroll':''}">${lines}</ul>
    <div class="os-rule"></div>
    ${discountBlock}
    <div class="os-rule"></div>
    <div class="os-lines">
      <div class="os-line"><span>Subtotal</span><span class="v">${fmt(c.subtotal)}</span></div>
      <div class="os-line tax"><span>VAT (20%, included)</span><span class="v">${fmt(c.vat)}</span></div>
      ${c.discGoods>0?`<div class="os-line discount"><span>Discount (${state.discount.code})</span><span class="v">−${fmt(c.discGoods)}</span></div>`:''}
      <div class="os-line"><span>${shipLabel}</span><span class="ship-v">${shipValue}</span></div>
      <div class="os-total">
        <span class="tl">Total</span>
        <span class="tv"><span class="cur">GBP</span><span class="amt">${fmt(c.total)}</span></span>
      </div>
    </div>
    <div class="os-trust">
      <div class="line"><span class="ico"><i data-lucide="lock"></i></span> Secured with 256-bit TLS encryption</div>
      <div class="line"><span class="ico"><i data-lucide="shield-check"></i></span> Card details never touch the merchant's servers</div>
      <div class="line"><span class="ico"><i data-lucide="rotate-ccw"></i></span> Free 30-day returns on every order</div>
    </div>
    <div class="os-powered"><span class="ico"><i data-lucide="shield-check"></i></span> Payments secured by <span class="pw">tender<span class="dot">.</span></span></div>`;
}

/* ════════════════════════════════════════════════════════════════════
   SHIPPING + STORE render
   ════════════════════════════════════════════════════════════════════ */
function renderShipping(){
  document.getElementById('shipping-list').innerHTML = shippingMethods.map(m=>{
    const checked = m.id===state.shippingId;
    const price = m.price===0 ? '<span class="pill free"><span class="dot" style="background:var(--success)"></span>Free</span>' : `<span class="pprice">${fmt(m.price)}</span>`;
    const sub = m.id==='express'
      ? `<div class="ship-cutoff" data-cutoff><span class="ico"><i data-lucide="zap"></i></span><span class="t"></span></div>`
      : `<div class="pmuted">${m.sub}</div>`;
    const meta = m.id==='nominated'
      ? (state.nominatedDate
          ? `Delivery ${fmtDay(parseKey(state.nominatedDate))}${state.nominatedSlot!=null?' · '+NOMINATED_SLOTS[state.nominatedSlot].split(' · ')[0]:''}`
          : 'Pick a delivery date below')
      : etaLabel(m);
    return `<div class="pick" role="radio" tabindex="${checked?0:-1}" aria-checked="${checked}" data-ship="${m.id}">
      <span class="radio"></span>
      <div class="pbody">
        <div class="ptop"><span class="pname">${m.name}</span>${price}</div>
        <div class="pmeta">${meta}</div>
        ${sub}
      </div>
    </div>`;
  }).join('');
  if(window.lucide) lucide.createIcons();
  updateCutoff();
}

/* Live order-cutoff countdown shown on the Express option (cutoff = 2pm). */
function updateCutoff(){
  const els=document.querySelectorAll('[data-cutoff] .t'); if(!els.length) return;
  const now=new Date(); const cut=new Date(now); cut.setHours(14,0,0,0);
  let txt;
  if(now < cut){
    let ms=cut-now; const h=Math.floor(ms/3.6e6); ms-=h*3.6e6; const m=Math.floor(ms/6e4); ms-=m*6e4; const s=Math.floor(ms/1000);
    const rem = h>0 ? `${h}h ${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}s` : `${m}m ${String(s).padStart(2,'0')}s`;
    txt = `Order within ${rem} for delivery tomorrow`;
  } else {
    txt = 'Order before 2 PM for next-day delivery';
  }
  els.forEach(e=> e.textContent=txt);
}
setInterval(updateCutoff, 1000);

function stockPill(s){
  if(s==='in')  return '<span class="pill in"><span class="dot" style="background:var(--success)"></span>In stock</span>';
  if(s==='low') return '<span class="pill low"><span class="dot" style="background:var(--warning)"></span>Low stock</span>';
  return '<span class="pill out"><span class="dot" style="background:var(--slate-400)"></span>Out of stock</span>';
}
function renderStores(stores){
  const wrap=document.getElementById('store-list'); const empty=document.getElementById('store-empty');
  empty.hidden = stores.length>0;
  wrap.innerHTML = stores.map(s=>{
    const out = s.stock==='out'; const checked = state.store && state.store.id===s.id;
    return `<div class="pick" role="radio" tabindex="${checked?0:-1}" aria-checked="${checked}" aria-disabled="${out}" data-store="${s.id}">
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
   PICKUP-POINT render (3rd-party — InPost / DPD / Evri)
   ════════════════════════════════════════════════════════════════════ */
function carrierMark(carrier){
  const c = CARRIERS[carrier]; if(!c) return '';
  return `<span class="cmk" style="background:${c.bg};color:${c.fg}">${c.label}</span>`;
}
function renderPickupPoints(list){
  const wrap=document.getElementById('pickup-list'); const empty=document.getElementById('pickup-empty');
  if(!wrap) return;
  if(empty) empty.hidden = list.length>0;
  wrap.innerHTML = list.map(p=>{
    const checked = state.pickupPoint && state.pickupPoint.id===p.id;
    const typeLabel = p.type==='locker' ? 'Locker' : 'ParcelShop';
    const typeIcon  = p.type==='locker' ? 'package' : 'store';
    return `<div class="pick" role="radio" tabindex="${checked?0:-1}" aria-checked="${checked}" data-pickup="${p.id}">
      <span class="radio"></span>
      <div class="pbody">
        <div class="ptop">
          <span class="pmark">${carrierMark(p.carrier)}<span class="ptype"><span class="ico"><i data-lucide="${typeIcon}"></i></span>${typeLabel}</span></span>
          <span class="pdist"><span class="ico"><i data-lucide="map-pin"></i></span>${p.dist} mi</span>
        </div>
        <div class="pname" style="margin-top:6px">${p.name}</div>
        <div class="pmuted">${p.addr}</div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:6px">
          <span class="lead-time"><span class="ico"><i data-lucide="clock"></i></span>${p.hours}</span>
          <span class="pprice">${fmt(CARRIERS[p.carrier].fee)}</span>
        </div>
      </div>
    </div>`;
  }).join('');
  if(window.lucide) lucide.createIcons();
}
function showPickupSelected(){
  const finder=document.getElementById('pickup-finder'); const sel=document.getElementById('pickup-selected');
  if(!finder||!sel) return;
  if(state.pickupPoint){
    const p=state.pickupPoint;
    finder.hidden=true; sel.hidden=false;
    sel.innerHTML = `<div class="pickup-chosen">
      <span class="pin"><span class="ico"><i data-lucide="${p.type==='locker'?'package':'store'}"></i></span></span>
      <div class="body">
        <div class="row">${carrierMark(p.carrier)}<span class="ptype">${p.type==='locker'?'Locker':'ParcelShop'} · ${p.dist} mi</span></div>
        <div class="name">${p.name}</div>
        <div class="line">${p.addr}</div>
        <div class="hours"><span class="ico"><i data-lucide="clock"></i></span>${p.hours} · ${fmt(CARRIERS[p.carrier].fee)} delivery</div>
      </div>
      <button type="button" class="change" data-change-pickup>Change</button>
    </div>`;
  } else {
    finder.hidden=false; sel.hidden=true;
  }
  if(window.lucide) lucide.createIcons();
}

/* ════════════════════════════════════════════════════════════════════
   SAVED ADDRESSES (signed-in) — pickable cards for delivery + billing
   ════════════════════════════════════════════════════════════════════ */
function savedAddrCardHTML(a, checked){
  const def = a.isDefault ? `<span class="addr-default">Default</span>` : '';
  return `<div class="pick" role="radio" tabindex="${checked?0:-1}" aria-checked="${checked}" data-addr="${a.id}">
    <span class="radio"></span>
    <div class="pbody">
      <div class="ptop"><span class="pname">${a.label}${def}</span></div>
      <div class="pmeta">${a.first} ${a.last}</div>
      <div class="pmuted">${a.line1}${a.line2?', '+a.line2:''}, ${a.city} ${a.postcode}</div>
    </div>
  </div>`;
}
function renderSavedAddresses(listId, selectedId){
  const wrap=document.getElementById(listId); if(!wrap) return;
  const manualChecked = selectedId==='manual';
  const manual = `<div class="pick" role="radio" tabindex="${manualChecked?0:-1}" aria-checked="${manualChecked}" data-addr="manual">
    <span class="radio"></span>
    <div class="pbody">
      <div class="ptop"><span class="pname">Use a different address</span></div>
      <div class="pmuted">Enter a new address manually</div>
    </div>
  </div>`;
  wrap.innerHTML = savedAddresses.map(a=> savedAddrCardHTML(a, a.id===selectedId)).join('') + manual;
  if(window.lucide) lucide.createIcons();
}

/* ════════════════════════════════════════════════════════════════════
   NOMINATED-DAY render — month calendar + time slots
   ════════════════════════════════════════════════════════════════════ */
let calCursor = null;   // first-of-month Date currently shown
function ensureCalCursor(){
  if(calCursor) return;
  const base = state.nominatedDate ? parseKey(state.nominatedDate) : new Date();
  calCursor = new Date(base.getFullYear(), base.getMonth(), 1);
}
function renderCalendar(){
  const host=document.getElementById('nom-cal'); if(!host) return;
  ensureCalCursor();
  const y=calCursor.getFullYear(), m=calCursor.getMonth();
  const first=new Date(y,m,1);
  const startDow=(first.getDay()+6)%7;                 // Monday-first
  const daysInMonth=new Date(y,m+1,0).getDate();
  const todayK=dateKey(new Date());
  const now=new Date(); const curMonth=new Date(now.getFullYear(),now.getMonth(),1);
  const prevDisabled = new Date(y,m,1) <= curMonth;
  let cells='';
  for(let i=0;i<startDow;i++) cells+='<span class="cal-day empty"></span>';
  for(let d=1; d<=daysInMonth; d++){
    const date=new Date(y,m,d); const k=dateKey(date);
    const avail=isNominatedDay(date);
    const sel = state.nominatedDate===k;
    cells += `<button type="button" class="cal-day${sel?' is-selected':''}${k===todayK?' today':''}" ${avail?'':'disabled aria-disabled="true"'} aria-label="${date.toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long'})}${avail?'':' — unavailable'}" data-day="${k}">${d}</button>`;
  }
  host.innerHTML = `
    <div class="cal-head">
      <button type="button" class="cal-nav" data-cal-prev ${prevDisabled?'disabled':''} aria-label="Previous month"><span class="ico"><i data-lucide="chevron-left"></i></span></button>
      <span class="cal-title">${calCursor.toLocaleDateString('en-GB',{month:'long',year:'numeric'})}</span>
      <button type="button" class="cal-nav" data-cal-next aria-label="Next month"><span class="ico"><i data-lucide="chevron-right"></i></span></button>
    </div>
    <div class="cal-dow">${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(x=>`<span>${x}</span>`).join('')}</div>
    <div class="cal-grid">${cells}</div>`;
  if(window.lucide) lucide.createIcons();
}
function renderNominatedSlots(){
  const host=document.getElementById('nom-slots'); if(!host) return;
  if(!state.nominatedDate){ host.hidden=true; host.innerHTML=''; return; }
  host.hidden=false;
  const k=state.nominatedDate;
  host.innerHTML = `<p class="nom-slots-label">Choose a time slot</p><div class="nom-slots-row">` +
    NOMINATED_SLOTS.map((s,i)=>{
      const un=nominatedSlotUnavailable(k,i); const on=state.nominatedSlot===i;
      const [t,win]=s.split(' · ');
      return `<button type="button" class="slot${on?' on':''}" ${un?'disabled aria-disabled="true"':''} data-slot="${i}">
        <span class="slot-t">${t}</span><span class="slot-s">${un?'Fully booked':win}</span></button>`;
    }).join('') + `</div>`;
}
function renderNomChosen(){
  const el=document.getElementById('nom-date-label');
  if(el && state.nominatedDate) el.textContent = parseKey(state.nominatedDate).toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long'});
}
/* the calendar shows only while editing/unchosen; otherwise a compact date chip + slot row */
function showNominatedView(){
  const picker=document.getElementById('nom-picker'); const chosen=document.getElementById('nom-chosen');
  if(!picker||!chosen) return;
  const editing = state.nomEditing || !state.nominatedDate;
  picker.hidden = !editing;
  chosen.hidden = editing;
  if(editing) renderCalendar();
  else { renderNomChosen(); renderNominatedSlots(); }
  if(window.lucide) lucide.createIcons();
}

/* ════════════════════════════════════════════════════════════════════
   MASTER render
   ════════════════════════════════════════════════════════════════════ */
function render(){
  const html = summaryHTML();
  document.getElementById('summary-card').innerHTML = html;
  document.getElementById('m-summary-panel').innerHTML = html;
  const c = calc();
  document.querySelectorAll('[data-total]').forEach(el=> el.textContent = fmt(c.total));
  document.getElementById('m-total').textContent = fmt(c.total);
  const itemCount = state.items.reduce((s,i)=>s+i.qty,0);
  document.getElementById('m-count').textContent = itemCount + (itemCount!==1?' items':' item');
  renderInstal();
  updateCtaLabels();
  wireSummary();
  updateCompletion();
  renumberSteps();
  if(window.lucide) lucide.createIcons();
}

/* number the visible sections in order — keeps numbering correct across flows */
function renumberSteps(){
  const blocks=[...document.querySelectorAll('.block')].filter(b=>!b.hidden);
  blocks.forEach((b,i)=>{ const n=b.querySelector('.block-step .num'); if(n) n.textContent=i+1; });
}
