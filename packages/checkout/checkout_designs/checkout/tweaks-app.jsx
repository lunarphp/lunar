// Tweaks island for the Tender checkout.
// Always-mounted React app: the floating panel only shows when the host
// activates Tweaks, but the side-effects below run on every render so the
// chosen A/B state (and persisted tweaks) always apply — even panel-hidden.
//
// Each boolean toggle ON = the *improved* (post-review) behaviour, which is
// also the file's baked-in default. Toggling OFF reverts to the original via
// an html-level class that the override block in checkout_v2.html keys off.

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "fields16": true,
  "contrastAA": true,
  "ctaLegible": true,
  "notesOptional": true,
  "vatQuiet": true,
  "plainSummary": true,
  "accent": "#4F46E5"
}/*EDITMODE-END*/;

const ACCENT_DEFAULT = "#4F46E5";

function applyNotes(optional){
  const sec = document.querySelector('[data-block="notes"]');
  if(!sec) return;
  const head = sec.querySelector('.block-head');
  if(!head) return;
  if(optional){
    sec.classList.add('block-optional');
    head.innerHTML = '<h2 class="block-title opt-title"><span class="opt-ico ico"><i data-lucide="pencil-line"></i></span>Notes &amp; references <span class="opt-tag">Optional</span></h2>';
  } else {
    sec.classList.remove('block-optional');
    head.innerHTML = '<h2 class="block-title"><span class="block-step"><span class="num">4</span><span class="chk ico"><i data-lucide="check"></i></span></span>Notes &amp; references</h2>';
  }
  if(window.lucide) window.lucide.createIcons();
  if(window.renumberSteps) window.renumberSteps();
}

function applyAccent(c){
  const root = document.documentElement;
  const accentProps = ['--accent','--accent-hover','--accent-press','--accent-ring',
                       '--indigo-50','--indigo-100','--indigo-200','--indigo-300'];
  if(!c || c.toUpperCase() === ACCENT_DEFAULT){
    accentProps.forEach(p => root.style.removeProperty(p));
    return;
  }
  root.style.setProperty('--accent', c);
  root.style.setProperty('--accent-hover', `color-mix(in srgb, ${c} 84%, #000)`);
  root.style.setProperty('--accent-press', `color-mix(in srgb, ${c} 70%, #000)`);
  root.style.setProperty('--accent-ring',  `color-mix(in srgb, ${c} 26%, transparent)`);
  // keep selected-state tints in family with the chosen accent
  root.style.setProperty('--indigo-50',  `color-mix(in srgb, ${c} 8%,  #fff)`);
  root.style.setProperty('--indigo-100', `color-mix(in srgb, ${c} 16%, #fff)`);
  root.style.setProperty('--indigo-200', `color-mix(in srgb, ${c} 30%, #fff)`);
  root.style.setProperty('--indigo-300', `color-mix(in srgb, ${c} 45%, #fff)`);
}

function App(){
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);

  React.useEffect(() => {
    const root = document.documentElement;
    root.classList.toggle('tw-fields-14',     !t.fields16);
    root.classList.toggle('tw-contrast-orig', !t.contrastAA);
    root.classList.toggle('tw-cta-ghost',     !t.ctaLegible);
    root.classList.toggle('tw-vat-emph',      !t.vatQuiet);
    root.classList.toggle('tw-pattern',       !t.plainSummary);
    applyAccent(t.accent);
    if(typeof updatePayDisabled === 'function') updatePayDisabled();
  });

  React.useEffect(() => { applyNotes(t.notesOptional); }, [t.notesOptional]);

  return (
    <TweaksPanel>
      <TweakSection label="Accessibility & contrast" />
      <TweakToggle label="16px fields (no iOS zoom)" value={t.fields16}
                   onChange={v => setTweak('fields16', v)} />
      <TweakToggle label="AA text contrast" value={t.contrastAA}
                   onChange={v => setTweak('contrastAA', v)} />
      <TweakToggle label="Legible Pay button + hint" value={t.ctaLegible}
                   onChange={v => setTweak('ctaLegible', v)} />

      <TweakSection label="Hierarchy" />
      <TweakToggle label="Optional Delivery notes" value={t.notesOptional}
                   onChange={v => setTweak('notesOptional', v)} />
      <TweakToggle label="Quiet VAT line" value={t.vatQuiet}
                   onChange={v => setTweak('vatQuiet', v)} />
      <TweakToggle label="Plain summary backdrop" value={t.plainSummary}
                   onChange={v => setTweak('plainSummary', v)} />

      <TweakSection label="Brand" />
      <TweakColor label="Accent" value={t.accent}
                  options={[ACCENT_DEFAULT, '#2A6FDB', '#1F8A5B', '#7C3AED']}
                  onChange={v => setTweak('accent', v)} />
    </TweaksPanel>
  );
}

ReactDOM.createRoot(document.getElementById('tweaks-root')).render(<App />);
