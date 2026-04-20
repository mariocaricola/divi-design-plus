# DIVI Design Plus

Premium CSS effects for **Divi 5** — liquid glass, bento cards, aurora gradients, magnetic hover and scroll-reveal animations, all without writing a single line of code.

---

## Installation

1. Download or clone this repository.
2. Upload the `divi-design-plus` folder to `/wp-content/plugins/`.
3. Activate the plugin from **Plugins › Installed Plugins**.

---

## How to apply effects in Divi 5

Divi 5 lets you add arbitrary HTML attributes to any module via the **Attributes** panel.

1. Select any module (Section, Row, Button, Text, Image…).
2. Open **Advanced › Attributes**.
3. Click **+ Add Attribute**.
4. Set **Attribute Name** → `class`
5. Set **Attribute Value** → one or more class names from the table below (space-separated).

> **Example:** `ddp-glass ddp-hover-lift`

---

## Effects reference

| Class | Effect | Works best on |
|---|---|---|
| `ddp-glass` | Liquid Glass — frosted-glass blur with inner light border | Sections, cards, modals |
| `ddp-bento` | Bento SaaS card — subtle border, premium shadow, 24 px radius | Blurbs, rows, text modules |
| `ddp-aurora` | Aurora Mesh Gradient — animated Stripe-like color flow | Hero sections, CTAs |
| `ddp-hover-lift` | Magnetic hover lift with spring cubic-bezier | Buttons, cards, images |
| `ddp-fade-in` | Fade in on scroll (Intersection Observer) | Any module |
| `ddp-slide-up` | Slide up + fade in on scroll | Headings, blurbs, columns |
| `ddp-reveal` | Scale + fade reveal on scroll | Cards, images, sections |

### Combining classes

Classes can be stacked freely:

```
ddp-bento ddp-hover-lift ddp-slide-up
ddp-glass ddp-fade-in
ddp-aurora ddp-reveal
```

---

## Scroll-reveal notes

- Effects trigger once per element as it enters the viewport.
- The `.is-visible` class is added by a lightweight Intersection Observer (no jQuery, no GSAP).
- On browsers without `IntersectionObserver` support (IE 11), elements are made visible immediately as a safe fallback.
- All animations respect `prefers-reduced-motion`.

---

## Customising variables

Override any CSS custom property in **Divi › Theme Options › Custom CSS** (or your child theme):

```css
:root {
  --ddp-glass-blur:      32px;      /* default: 20px  */
  --ddp-bento-radius:    16px;      /* default: 24px  */
  --ddp-lift-y:          -14px;     /* default: -10px */
  --ddp-reveal-duration: 0.8s;      /* default: 0.65s */
}
```

---

## Changelog

### 1.0.0
- Initial release: glass, bento, aurora, hover-lift, fade-in, slide-up, reveal.

---

## License

GPL-2.0-or-later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
