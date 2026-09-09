# Tender Checkout — Design System

> A clean, trustworthy hosted-checkout brand. Stripe-adjacent in spirit: neutral,
> precise, and quietly confident. Deep-indigo primary, cool slate neutrals,
> geometric sans typography, medium corner rounding, balanced density.

**Tender** is a (fictional) payments brand. Its flagship surface is the
**hosted checkout page** — a full-page, standalone payment screen that a merchant
redirects their customer to. The entire system is engineered around one job:
*move a nervous buyer from "review" to "paid" with zero friction and maximum trust.*

This project was created **from scratch** (no source codebase, Figma, or brand
assets were provided). The brand name, voice, palette, and components below are an
original system built to the brief: *"generic checkout, clean & trustworthy,
deep indigo, geometric sans, balanced."* Treat every value here as canonical.

---

## Sources

None were supplied. There is **no upstream codebase or Figma file** — this is a
greenfield design system. If you later have a real product to align to, re-attach
it and the tokens here can be reconciled against it.

---

## Content fundamentals — how Tender writes

The voice is **plain, calm, and reassuring**. Checkout copy is where conversions
are won or lost, so every word reduces anxiety and removes doubt.

- **Person & tone.** Address the customer as **"you"**; the brand refers to itself
  as **"we"** sparingly and never centers itself. The customer is the subject of
  every sentence. *"You won't be charged until you confirm."*
- **Casing.** **Sentence case everywhere** — buttons, headings, labels, menu items.
  Never Title Case, never ALL CAPS except the tiny tracked **eyebrow / overline**
  label (e.g. `ORDER SUMMARY`) and acronyms (`CVC`, `USD`).
- **Brevity.** Labels are one or two words (`Card number`, `Pay now`). Helper text
  is a single short clause. Legal text is unavoidably longer but stays one sentence
  where possible.
- **Action verbs are concrete and present-tense.** Primary CTA reads the literal
  outcome with the amount: **"Pay $128.00"**, not "Submit" or "Continue" — naming
  the amount on the button is a deliberate trust pattern.
- **Reassurance over salesmanship.** Microcopy answers the silent question:
  *"Is this safe? Will I be overcharged? Can I undo this?"* Examples:
  *"Secured by 256-bit encryption."* · *"Cancel anytime."* ·
  *"You can review your order before paying."*
- **No exclamation marks, no hype, no emoji.** Confidence is conveyed by precision,
  not enthusiasm. The one exception is a single ✓ / success state, expressed with an
  icon, never punctuation.
- **Errors are specific and blameless.** *"Your card number looks incomplete."*
  not *"Invalid input."* Never blame the user; describe what to fix.
- **Numbers are exact and tabular.** Always two decimal places and a currency code
  (`$128.00 USD`). Amounts use tabular figures so columns align.

**Vibe in one line:** *the unobtrusive professionalism of a good bank teller —
fast, exact, and entirely on your side.*

---

## Visual foundations

### Color
- **Primary — deep indigo.** `--indigo-600 #4F46E5` is the single action color
  (primary button, links, focus ring, selected state). `700` is hover, `800` is
  press. Indigo is used **surgically** — one primary action per screen. The page is
  overwhelmingly neutral so the indigo CTA is unmistakable.
- **Neutrals — cool slate.** A 12-step slate ramp from `#FFFFFF` to `#020617`
  carries 95% of the UI: text, borders, surfaces, wells.
- **Semantic.** Green `#16A34A` (paid / valid), red `#DC2626` (declined / error),
  amber `#D97706` (warning / pending). Each pairs a solid tone with a soft tint
  background for badges and inline alerts.
- **Imagery vibe.** Minimal photography. When used (e.g. a product thumbnail in the
  order summary), images are **clean, neutral, well-lit, no heavy filter** — cool
  white balance to match the slate UI. No grain, no duotone.

### Type
- **Manrope** (geometric sans, loaded from Google Fonts) for everything —
  UI, headings, body. Weights 400/500/600/700/800. Headings are **tight**
  (`letter-spacing: -0.02em`) and heavy (700–800). Body is 400 at 16px. The
  **wordmark** is "tender" set in Manrope 800, lowercase, with an indigo period as
  its only accent (currently text-only — no symbol mark).
- **JetBrains Mono** for monospaced numerics where alignment matters — card numbers
  while typing, and optional for amounts. Tabular figures throughout.
- Minimum body size **16px** (also prevents iOS input zoom). Legal text 12px.

### Spacing & density
- **4px base step.** Balanced density: form fields are ~44px tall, 12–16px internal
  padding, 16–24px between field groups, 24–32px section gaps. Generous but not airy.

### Radii & shape
- **Medium rounding.** Buttons `10px`, cards & sheets `14px`, large
  containers `20px`, pills `999px`. **Inputs/fields use a tighter `6px`** for a
  more precise, data-entry feel. Consistent, friendly-but-serious — never sharp
  (cold) nor pill-soft (toy-like).

### Buttons
- Buttons carry a subtle **bevel**: a faint top highlight
  (`inset 0 1px 0 rgba(255,255,255,.16)`), a bottom inner shadow
  (`inset 0 -1px 0 rgba(0,0,0,.16)`) and a soft drop shadow. On **press** the
  button sinks `translateY(1px)` and loses its drop shadow — a tactile "physical
  key" feel that reinforces the moment of paying. Faithful to Tailwind's
  before/after bevel pattern, implemented with inset shadows.

### Backgrounds
- **Flat, layered neutrals.** Page canvas is `--slate-50`; the checkout card is pure
  white floating on it with a soft shadow. **No gradients** in chrome, **no textures,
  no patterns, no illustrations** behind content. The only permissible gradient is a
  faint top "trust band" on the hosted page (optional) and image protection scrims.
- A subtle **brand bar** (thin indigo rule or the merchant logo lockup) anchors the
  top. Otherwise the canvas stays quiet so content leads.

### Elevation & shadows
- Shadows are **soft, cool-tinted (slate), low-spread**, never black or harsh.
  Scale: `xs → sm → md → lg`, plus a dedicated `--shadow-card` for the floating
  checkout panel. Elevation communicates "this is the thing you act on."
- Borders are **hairline** `--slate-200`; inputs use a slightly stronger
  `--slate-300`. Focus replaces/adds a **3px indigo ring** (`--ring-focus`).

### Motion
- **Quick and confident, no bounce.** `120–300ms`, ease-out
  (`cubic-bezier(0.16,1,0.3,1)`). Fades and small (2–4px) slides only. The success
  checkmark may draw-on. Nothing springy — playfulness undercuts trust.

### Interaction states
- **Hover:** buttons darken one step (`600 → 700`); secondary/ghost get a
  `--slate-100` fill; rows tint `--bg-subtle`.
- **Press:** darken another step (`700 → 800`) and a near-imperceptible
  `scale(0.99)` — tactile, not bouncy.
- **Focus:** always a visible **3px indigo ring** with a 2px white offset on inputs;
  never remove outlines.
- **Disabled:** `--slate-200` fill, `--slate-400` text, no shadow, `not-allowed`.
- **Loading:** primary button swaps label for a spinner and keeps its width
  (no layout shift); the page never blocks with a full-screen spinner.

### Transparency & blur
- Used sparingly. The mobile pay sheet and any modal use a `rgba(15,23,42,0.4)`
  scrim. Optional `backdrop-filter: blur(2px)` behind sticky summary bars on mobile.
  Chrome itself is opaque — trust surfaces shouldn't feel ephemeral.

---

## Iconography

- **Lucide** (https://lucide.dev) is the canonical icon set — loaded from CDN.
  It is an open-source set of clean, consistent **1.75–2px stroke, rounded-join**
  line icons that sit perfectly with Manrope and the medium-radius shapes.
  Stroke style (not filled) is the default; reserve filled glyphs for tiny status
  dots only.
- **No emoji. No Unicode glyph icons.** Icons are always Lucide SVGs at a fixed
  size (16 / 20 / 24px) inheriting `currentColor`.
- **Card-network and wallet marks** (Visa, Mastercard, Amex, Apple Pay, etc.) are
  the one place we use **brand-colored, filled** marks rather than line icons — they
  must be instantly recognizable. These are not invented here; in production, source
  official network artwork. The UI kit uses simple recognizable placeholders and
  flags this.
- Common icons in checkout: `lock` (security), `credit-card`, `check` / `check-circle`
  (success/valid), `alert-circle` (error), `chevron-down` (selects), `info`,
  `shield-check` (trust), `loader` (pending), `x` (close), `pencil` (edit).

> **Substitution flag:** Lucide is loaded from CDN, not vendored into `assets/`.
> Fonts (Manrope, JetBrains Mono) are loaded from Google Fonts CDN rather than
> stored as local `.ttf` files. If you need fully offline/vendored assets, ask and
> I'll bundle them into `fonts/` and `assets/icons/`.

---

## Index — what's in this system

| Path | What it is |
|---|---|
| `README.md` | This file — context, voice, visual foundations, iconography, index |
| `colors_and_type.css` | **Canonical design tokens** — color scales, semantic roles, type scale + classes, spacing, radii, shadows, motion. Import this in any artifact. |
| `assets/logo-wordmark.svg` | "tender" text wordmark (for light backgrounds) |
| `assets/logo-wordmark-light.svg` | Wordmark for dark backgrounds |
| `assets/logo-mark.svg` | (Retired for now) square checkmark symbol — kept in case a symbol mark returns |
| `preview/*.html` | Design-system cards (type, color, spacing, components) shown in the Design System tab |
| `ui_kits/checkout/` | **Hosted checkout UI kit** — JSX components + interactive `index.html` click-through |
| `ui_kits/checkout/README.md` | What the kit covers + how to use the components |
| `directions/` | Side-by-side exploration of checkout visual directions (design canvas) |
| `SKILL.md` | Agent Skill manifest — makes this folder usable as a Claude Skill |

### Quick start for an agent
1. Read this README and `colors_and_type.css`.
2. `@import "colors_and_type.css"` (or copy its `:root`) into any new artifact.
3. Pull components from `ui_kits/checkout/` and assets from `assets/`.
4. Load Lucide from CDN for icons; follow the voice rules above for copy.
