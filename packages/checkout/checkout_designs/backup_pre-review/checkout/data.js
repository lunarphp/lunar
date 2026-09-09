/* ════════════════════════════════════════════════════════════════════
   CHECKOUT · data + pricing
   Part of the Tender checkout. Classic (non-module) script — shares the
   global lexical scope with the other checkout/*.js files.
   Load order: 1 — FIRST
   ════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════
   STATE — single source of truth (maps to a Vue reactive store)
   ════════════════════════════════════════════════════════════════════ */
const fmt = (p) => '£' + (p/100).toLocaleString('en-GB',{minimumFractionDigits:2,maximumFractionDigits:2});

const state = {
  fulfilment:'delivery',          // 'delivery' | 'collect'
  method:'card',                  // payment method
  items:[
    { id:'knit',    title:'Merino crew knit',       variant:'Charcoal · M', sku:'AH-2241-CHM', qty:1, price:12800, icon:'shirt' },
    { id:'oxford',  title:'Cotton oxford shirt',     variant:'White · M',    sku:'AH-1180-WHM', qty:2, price:5800,  icon:'shirt' },
    { id:'card',    title:'Leather card holder',     variant:'Tan',          sku:'AH-0563-TAN', qty:1, price:4500,  icon:'wallet' },
    { id:'coat',    title:'Wool overcoat',           variant:'Camel · L',    sku:'AH-3390-CAM', qty:1, price:32000, icon:'shirt' },
    { id:'scarf',   title:'Lambswool scarf',         variant:'Forest',       sku:'AH-0712-FOR', qty:1, price:6500,  icon:'shirt' },
    { id:'belt',    title:'Bridle leather belt',     variant:'Black · 34',   sku:'AH-0488-BLK', qty:1, price:7900,  icon:'minus' },
    { id:'socks',   title:'Ribbed cotton socks',     variant:'Grey · 3-pack',sku:'AH-0119-GRY', qty:3, price:2400,  icon:'footprints' },
    { id:'watch',   title:'Field watch',             variant:'Stainless',    sku:'AH-9001-STL', qty:1, price:18500, icon:'watch' },
    { id:'glasses', title:'Acetate sunglasses',      variant:'Tortoise',     sku:'AH-0640-TOR', qty:1, price:9500,  icon:'glasses' },
    { id:'umbrella',title:'Compact umbrella',        variant:'Navy',         sku:'AH-0205-NVY', qty:1, price:3800,  icon:'umbrella' },
    { id:'bag',     title:'Canvas weekender',        variant:'Olive',        sku:'AH-3120-OLV', qty:1, price:14500, icon:'briefcase' },
    { id:'key',     title:'Brass key fob',           variant:'Polished',     sku:'AH-0077-BRS', qty:2, price:2200,  icon:'key' },
  ],
  shippingId:'standard',
  nominatedDate:null,             // 'YYYY-MM-DD' chosen for Nominated-day delivery
  nominatedSlot:null,             // index into NOMINATED_SLOTS
  nomEditing:false,               // true while the calendar is open (vs collapsed chip)
  store:null,                     // merchant store (click & collect)
  pickupPoint:null,               // 3rd-party pickup point (InPost/DPD/Evri) when shippingId==='pickup'
  signedIn:false,                 // recognised account verified via phone code
  shipAddrId:null,                // chosen saved delivery address id, or 'manual'
  billAddrId:null,                // chosen saved billing address id, or 'manual'
  addressValid:false,
  discount:null,                  // { code, pct }
  VAT_RATE:0.20,
};

const shippingMethods = [
  { id:'standard', name:'Standard delivery', sub:'Free over £50',  price:0,   etaMin:3, etaMax:5 },
  { id:'express',  name:'Express delivery',  sub:'Jump the queue', price:695, etaMin:1, etaMax:1 },
  { id:'nominated',name:'Nominated day',     sub:'Choose a weekday',price:495, etaMin:2, etaMax:4 },
];

/* Third-party pickup points (InPost lockers + courier ParcelShops). Ranked by distance.
   carrier ∈ 'inpost' | 'dpd' | 'evri'. type ∈ 'locker' | 'shop'. */
const pickupPoints = [
  { id:'ip-oldst',   carrier:'inpost', type:'locker', name:'InPost Locker — Tesco Express',      addr:'Old Street, EC1V 9NR',     dist:0.3, hours:'Open 24/7' },
  { id:'dpd-shore',  carrier:'dpd',    type:'shop',   name:'DPD Pickup — Shoreditch News',        addr:'Kingsland Rd, E2 8AA',     dist:0.6, hours:'Mon–Sun 7am–11pm' },
  { id:'ip-angel',   carrier:'inpost', type:'locker', name:"InPost Locker — Sainsbury's",          addr:'Angel, N1 9PT',            dist:0.9, hours:'Open 24/7' },
  { id:'evri-barb',  carrier:'evri',   type:'shop',   name:'Evri ParcelShop — Barbican Off-licence', addr:'Whitecross St, EC1Y 8JL', dist:1.1, hours:'Mon–Sat 8am–9pm' },
  { id:'dpd-clerk',  carrier:'dpd',    type:'shop',   name:'DPD Pickup — Clerkenwell Post',       addr:'Farringdon Rd, EC1R 3AF',  dist:1.4, hours:'Mon–Fri 9am–6pm' },
  { id:'ip-wall',    carrier:'inpost', type:'locker', name:'InPost Locker — Pret',                addr:'London Wall, EC2M 5QD',    dist:1.7, hours:'Open 24/7' },
];

/* Carrier marks (placeholders — source official artwork in production). fee = pence, charged per locker delivery. */
const CARRIERS = {
  inpost:{ label:'InPost', bg:'#FFCC00', fg:'#1A1A1A', fee:249 },
  dpd:   { label:'DPD',    bg:'#DC0032', fg:'#FFFFFF', fee:349 },
  evri:  { label:'Evri',   bg:'#0E1A6B', fg:'#FFFFFF', fee:299 },
};
/* fee for the currently-chosen pickup point (0 until one is selected) */
function pickupFee(){ return state.pickupPoint ? (CARRIERS[state.pickupPoint.carrier]?.fee || 0) : 0; }

/* Saved addresses on a recognised account — offered as pickable cards once signed in,
   for both the delivery step and the billing step. */
const savedAddresses = [
  { id:'home', label:'Home',   isDefault:true, first:'Jordan', last:'Avery', line1:'12 Marchmont Street', line2:'',        city:'London', postcode:'WC1N 1AB', phone:'07700 900123' },
  { id:'work', label:'Work',                   first:'Jordan', last:'Avery', line1:'140 Old Street',       line2:'Floor 3', city:'London', postcode:'EC1V 9BD', phone:'07700 900124' },
  { id:'fam',  label:'Family',                 first:'Pat',    last:'Avery', line1:'8 Lavender Hill',      line2:'',        city:'London', postcode:'SW11 5RW', phone:'07700 900125' },
];

const allStores = [
  { id:'covent',     name:'Covent Garden',       addr:'21 Long Acre, WC2E 9LD',  dist:0.8, stock:'in',  lead:'~2 hrs', hours:'Mon–Sat 10–8 · Sun 12–6' },
  { id:'shoreditch', name:'Shoreditch',          addr:'48 Redchurch St, E2 7DP', dist:2.3, stock:'low', lead:'~3 hrs', hours:'Mon–Sat 10–7 · Sun 11–5' },
  { id:'stratford',  name:'Westfield Stratford', addr:'Montfichet Rd, E20 1EJ',  dist:4.1, stock:'in',  lead:'~2 hrs', hours:'Mon–Sun 10–9' },
  { id:'kingston',   name:'Kingston',            addr:'17 Clarence St, KT1 1NP', dist:9.7, stock:'out', lead:'—',      hours:'Mon–Sat 9–6 · Sun 11–5' },
];

const addressIndex = [
  { line1:'12 Marchmont Street', city:'London',     postcode:'WC1N 1AB' },
  { line1:'8 Lavender Hill',     city:'London',     postcode:'SW11 5RW' },
  { line1:'140 Old Street',      city:'London',     postcode:'EC1V 9BD' },
  { line1:'27 Bold Street',      city:'Liverpool',  postcode:'L1 4DN'   },
  { line1:'45 Gloucester Road',  city:'Bristol',    postcode:'BS7 8AE'  },
  { line1:'3 King’s Parade',     city:'Cambridge',  postcode:'CB2 1SJ'  },
  { line1:'92 Deansgate',        city:'Manchester', postcode:'M3 2QG'   },
];

// Test coupons. type: 'pct' (% off goods) · 'fixed' (£ off goods, in pence) · 'freeship' · 'shippct' (% off shipping)
const VALID_CODES = {
  TEST10:   { type:'pct',     value:10,   label:'10% off' },
  FIXED10:  { type:'fixed',   value:1000, label:'£10 off' },
  FREESHIP: { type:'freeship',            label:'Free shipping' },
  SHIP10:   { type:'shippct', value:10,   label:'10% off shipping' },
};
let processing=false;   // true while a mock payment is in flight (keeps the CTA disabled)

/* ════════════════════════════════════════════════════════════════════
   DATE HELPERS — business-day ETAs
   ════════════════════════════════════════════════════════════════════ */
function addBusinessDays(date,n){ const d=new Date(date); let a=0; while(a<n){ d.setDate(d.getDate()+1); const w=d.getDay(); if(w!==0&&w!==6) a++; } return d; }
function fmtDay(d){ return d.toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short'}); }
function etaLabel(m){ const now=new Date();
  if(m.etaMin===m.etaMax){ const d=addBusinessDays(now,m.etaMin); return (m.id==='express')?('Tomorrow · '+fmtDay(d)):fmtDay(d); }
  return fmtDay(addBusinessDays(now,m.etaMin))+' – '+fmtDay(addBusinessDays(now,m.etaMax)); }

/* ════════════════════════════════════════════════════════════════════
   NOMINATED-DAY DELIVERY — calendar availability + time slots
   ════════════════════════════════════════════════════════════════════ */
const NOMINATED_SLOTS = ['Morning · 8am–12pm', 'Afternoon · 12–5pm', 'Evening · 5–9pm'];
function dateKey(d){ return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
function parseKey(k){ const [y,m,d]=k.split('-').map(Number); return new Date(y, m-1, d); }
function addDays(d,n){ const x=new Date(d); x.setHours(0,0,0,0); x.setDate(x.getDate()+n); return x; }
/* a few specific weekdays are fully booked (relative to today) */
const NOMINATED_BLACKOUTS = new Set([3,4,11,17,18].map(n=> dateKey(addDays(new Date(),n))));
function isNominatedDay(d){
  const day=d.getDay();
  if(day===0||day===6) return false;                 // weekends not offered
  if(d < addDays(new Date(),1)) return false;          // must be tomorrow or later
  return !NOMINATED_BLACKOUTS.has(dateKey(d));          // not a blackout date
}
/* deterministic per-date slot availability so some slots read as "Full" */
function nominatedSlotUnavailable(key, idx){
  const n = key.split('-').reduce((a,b)=> a + Number(b), 0);
  return (n + idx) % 4 === 0;
}

/* ════════════════════════════════════════════════════════════════════
   PRICING — recompute() derives every figure from state
   ════════════════════════════════════════════════════════════════════ */
function getShipping(){ return shippingMethods.find(m=>m.id===state.shippingId); }
function calc(){
  const subtotal = state.items.reduce((s,i)=>s+i.price*i.qty,0);
  const baseShipping = state.fulfilment==='collect' ? 0
                     : state.shippingId==='pickup' ? pickupFee()
                     : (getShipping()?.price ?? 0);
  const d = state.discount;
  let discGoods = 0, discShip = 0;
  if(d){
    if(d.type==='pct')        discGoods = Math.round(subtotal*d.value/100);
    else if(d.type==='fixed') discGoods = Math.min(d.value, subtotal);
    else if(d.type==='freeship') discShip = baseShipping;
    else if(d.type==='shippct')  discShip = Math.round(baseShipping*d.value/100);
  }
  const shipping = Math.max(0, baseShipping - discShip);
  const discount = discGoods;                 // goods discount (legacy name)
  const total = Math.max(0, subtotal + shipping - discGoods);
  const vat = Math.round(total*state.VAT_RATE/(1+state.VAT_RATE));
  return { subtotal, baseShipping, shipping, discount, discGoods, discShip, total, vat };
}
