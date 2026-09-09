/* ════════════════════════════════════════════════════════════════════
   ORDER-SUMMARY DIRECTIONS · panels + canvas
   Four standalone Tender order-summary panels, laid side-by-side on a
   DesignCanvas for comparison. All share one data set + ledger so the
   only thing that varies is how each LINE ITEM is structured.
   ════════════════════════════════════════════════════════════════════ */
const { useEffect } = React;

/* Shared basket (3 representative lines from the live checkout) */
const ITEMS = [
  { id:'knit',   name:'Merino crew knit',   variant:'Charcoal · M', chips:['Charcoal','M'], qty:1, price:'£128.00' },
  { id:'oxford', name:'Cotton oxford shirt', variant:'White · M',    chips:['White','M'],    qty:2, price:'£116.00' },
  { id:'card',   name:'Leather card holder', variant:'Tan',          chips:['Tan'],          qty:1, price:'£45.00'  },
];
const ITEM_COUNT = ITEMS.reduce((s,i)=>s+i.qty,0);

/* ── Inline icons (no Lucide-timing dependency inside the canvas) ───── */
const PhotoIcon = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>
  </svg>
);
const Ico = ({ d, size=16 }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">{d}</svg>
);
const IcoPlus   = () => <Ico d={<><path d="M12 5v14"/><path d="M5 12h14"/></>} />;
const IcoLock   = () => <Ico d={<><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></>} />;
const IcoReturn = () => <Ico d={<><path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-7.7L3 8"/></>} />;
const IcoShield = () => <Ico d={<><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></>} />;

/* Neutral product-photo placeholder */
const Photo = () => <span className="sd-photo"><PhotoIcon /></span>;

/* ── Shared ledger + total + trust (consistent across directions) ───── */
function Ledger({ discount }){
  return (
    <>
      <div className="sd-rule"></div>
      {discount}
      <div className="sd-rule"></div>
      <div className="sd-ledger">
        <div className="sd-line is-sub"><span>Subtotal</span><span className="v">£289.00</span></div>
        <div className="sd-line"><span>VAT (20%, included)</span><span className="v">£48.17</span></div>
        <div className="sd-line"><span>Shipping</span><span className="sd-free"><span className="dot"></span>Free</span></div>
        <div className="sd-total">
          <span className="tl">Total</span>
          <span className="tv"><span className="cur">GBP</span><span className="amt">£289.00</span></span>
        </div>
      </div>
      <div className="sd-trust">
        <div className="line"><IcoLock /> Secured with 256-bit TLS encryption</div>
        <div className="line"><IcoReturn /> Free 30-day returns on every order</div>
      </div>
      <div className="sd-powered"><IcoShield /> Payments secured by <span className="pw">tender<span className="dot">.</span></span></div>
    </>
  );
}

const DiscountLink = () => (
  <button type="button" className="sd-disc"><IcoPlus /> Add a discount code</button>
);
const DiscountForm = () => (
  <div className="sd-disc-form">
    <input placeholder="Discount code" aria-label="Discount code" />
    <button type="button">Apply</button>
  </div>
);

function Head(){
  return (
    <div className="sd-head">
      <h2>Order summary</h2>
      <span className="sd-count">{ITEM_COUNT} items</span>
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   A · Aligned ledger
   ════════════════════════════════════════════════════════════════════ */
function PanelA(){
  return (
    <div className="sd-panel sdA">
      <Head />
      <ul className="sd-items">
        {ITEMS.map(i=>(
          <li className="sd-row" key={i.id}>
            <span className="sd-photo" style={{position:'relative'}}>
              <PhotoIcon />
              <span className="qty-badge">{i.qty}</span>
            </span>
            <div className="sd-meta">
              <span className="sd-name">{i.name}</span>
              <span className="sd-var">{i.variant}</span>
            </div>
            <span className="sd-price">{i.price}</span>
          </li>
        ))}
      </ul>
      <Ledger discount={<DiscountLink />} />
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   B · Quantity column
   ════════════════════════════════════════════════════════════════════ */
function PanelB(){
  return (
    <div className="sd-panel sdB">
      <Head />
      <ul className="sd-items">
        {ITEMS.map(i=>(
          <li className="sd-row" key={i.id}>
            <span className="sd-qty">{i.qty}×</span>
            <Photo />
            <div className="sd-meta">
              <span className="sd-name">{i.name}</span>
              <span className="sd-var">{i.variant}</span>
            </div>
            <span className="sd-price">{i.price}</span>
          </li>
        ))}
      </ul>
      <Ledger discount={<DiscountForm />} />
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   C · Carded rows
   ════════════════════════════════════════════════════════════════════ */
function PanelC(){
  return (
    <div className="sd-panel sdC">
      <Head />
      <ul className="sd-items">
        {ITEMS.map(i=>(
          <li className="sd-row" key={i.id}>
            <Photo />
            <div className="sd-meta">
              <div className="sd-nameline">
                <span className="sd-name">{i.name}</span>
                <span className="qty-pill">×{i.qty}</span>
              </div>
              <span className="sd-var">{i.variant}</span>
            </div>
            <span className="sd-price">{i.price}</span>
          </li>
        ))}
      </ul>
      <Ledger discount={<DiscountLink />} />
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   D · Editorial
   ════════════════════════════════════════════════════════════════════ */
function PanelD(){
  return (
    <div className="sd-panel sdD">
      <Head />
      <ul className="sd-items">
        {ITEMS.map(i=>(
          <li className="sd-row" key={i.id}>
            <Photo />
            <div className="sd-meta">
              <span className="sd-name">{i.name}</span>
              <span className="sd-chips">{i.chips.map(c=><span className="sd-chip" key={c}>{c}</span>)}</span>
            </div>
            <span className="sd-pricecol">
              {i.qty>1 && <span className="sd-qty">× {i.qty}</span>}
              <span className="sd-price">{i.price}</span>
            </span>
          </li>
        ))}
      </ul>
      <Ledger discount={<DiscountLink />} />
    </div>
  );
}

/* ════════════════════════════════════════════════════════════════════
   CANVAS
   ════════════════════════════════════════════════════════════════════ */
const W = 452;
function App(){
  return (
    <DesignCanvas>
      <DCSection id="order-summary" title="Order summary — directions"
        subtitle="Same basket · 4 takes on line-item structure · Tender DS">
        <DCArtboard id="a" label="A · Aligned ledger"   width={W} height={690} style={{background:'var(--bg-page)'}}><PanelA /></DCArtboard>
        <DCArtboard id="b" label="B · Quantity column"  width={W} height={720} style={{background:'var(--bg-page)'}}><PanelB /></DCArtboard>
        <DCArtboard id="c" label="C · Carded rows"      width={W} height={720} style={{background:'var(--bg-page)'}}><PanelC /></DCArtboard>
        <DCArtboard id="d" label="D · Editorial"        width={W} height={720} style={{background:'var(--bg-page)'}}><PanelD /></DCArtboard>
      </DCSection>
    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
