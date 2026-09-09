// Tweaks island for the EXPRESS CHECKOUT · CONFIRM page.
// Switches the wallet we returned from, the fulfilment flow, how prominent the
// "authorised, not charged" reassurance is, and whether the required name gap
// is already filled (to preview the all-confirmed vs needs-attention state).

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "wallet": "apple",
  "fulfilment": "delivery",
  "reassurance": "prominent",
  "nameProvided": false,
  "accent": "#4F46E5"
}/*EDITMODE-END*/;

const ACCENT_DEFAULT = "#4F46E5";

function applyAccent(c){
  const root = document.documentElement;
  const props = ['--accent','--accent-hover','--accent-press','--accent-ring',
                 '--indigo-50','--indigo-100','--indigo-200','--indigo-300'];
  if(!c || c.toUpperCase() === ACCENT_DEFAULT){ props.forEach(p => root.style.removeProperty(p)); return; }
  root.style.setProperty('--accent', c);
  root.style.setProperty('--accent-hover', `color-mix(in srgb, ${c} 84%, #000)`);
  root.style.setProperty('--accent-press', `color-mix(in srgb, ${c} 70%, #000)`);
  root.style.setProperty('--accent-ring',  `color-mix(in srgb, ${c} 26%, transparent)`);
  root.style.setProperty('--indigo-50',  `color-mix(in srgb, ${c} 8%,  #fff)`);
  root.style.setProperty('--indigo-100', `color-mix(in srgb, ${c} 16%, #fff)`);
  root.style.setProperty('--indigo-200', `color-mix(in srgb, ${c} 30%, #fff)`);
  root.style.setProperty('--indigo-300', `color-mix(in srgb, ${c} 45%, #fff)`);
}

function App(){
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);

  React.useEffect(()=>{ if(typeof window.xcSetWallet==='function') window.xcSetWallet(t.wallet); }, [t.wallet]);
  React.useEffect(()=>{ if(typeof window.xcSetFulfilment==='function') window.xcSetFulfilment(t.fulfilment); }, [t.fulfilment]);
  React.useEffect(()=>{ if(typeof window.xcSetReassurance==='function') window.xcSetReassurance(t.reassurance); }, [t.reassurance]);
  React.useEffect(()=>{ if(typeof window.xcSetNameProvided==='function') window.xcSetNameProvided(t.nameProvided); }, [t.nameProvided]);
  React.useEffect(()=>{ applyAccent(t.accent); });

  return (
    <TweaksPanel>
      <TweakSection label="Express scenario" />
      <TweakRadio label="Returned from" value={t.wallet}
                  options={[{label:'Apple Pay', value:'apple'},
                            {label:'Google Pay', value:'google'},
                            {label:'PayPal', value:'paypal'}]}
                  onChange={v => setTweak('wallet', v)} />
      <TweakRadio label="Fulfilment" value={t.fulfilment}
                  options={[{label:'Delivery', value:'delivery'},
                            {label:'Click & collect', value:'collect'}]}
                  onChange={v => setTweak('fulfilment', v)} />

      <TweakSection label="Completion state" />
      <TweakToggle label="Name already provided" value={t.nameProvided}
                   onChange={v => setTweak('nameProvided', v)} />

      <TweakSection label="Reassurance" />
      <TweakRadio label="Authorised note" value={t.reassurance}
                  options={[{label:'Prominent', value:'prominent'},
                            {label:'Subtle', value:'subtle'}]}
                  onChange={v => setTweak('reassurance', v)} />

      <TweakSection label="Brand" />
      <TweakColor label="Accent" value={t.accent}
                  options={[ACCENT_DEFAULT, '#2A6FDB', '#1F8A5B', '#7C3AED']}
                  onChange={v => setTweak('accent', v)} />
    </TweaksPanel>
  );
}

ReactDOM.createRoot(document.getElementById('tweaks-root')).render(<App />);
