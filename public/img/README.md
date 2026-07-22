# Landing page images

Drop files here using the exact names below and they replace the dashed
placeholders on the landing page automatically — no code change needed.
Any file that isn't present just keeps showing its placeholder, so you can
add them one at a time.

Referenced from `resources/views/landing.blade.php` via `<x-img-slot src="...">`.

| Section | File | Suggested size / notes |
|---|---|---|
| §2 Hero left  | `img/hero-left.jpg`  | 4:3, ~1200×900, photo |
| §2 Hero right | `img/hero-right.jpg` | 4:3, ~1200×900, photo |
| §3 Intro      | `img/intro.jpg`      | 4:3, ~1200×900, photo |
| §4 Why cards  | `img/why-1.jpg` … `img/why-4.jpg` | 16:10, ~800×500 |
| §5 Insurers   | `img/insurers/*.png` | transparent PNG/SVG, ~400×260, logo centred |
| §6 & §8 Badges| `img/badge-1.png` … `img/badge-5.png` | square, transparent PNG, ~300×300 |
| §7 Google     | `img/google-review.png` | 3:2, transparent PNG |

Insurer logo filenames currently expected (edit the `$insurers` array in
`landing.blade.php` to add/rename):

```
img/insurers/etiqa.png
img/insurers/allianz.png
img/insurers/zurich.png
img/insurers/takaful-malaysia.png
img/insurers/liberty.png
img/insurers/rhb.png
img/insurers/amgeneral.png
img/insurers/tokio-marine.png
```

## Tips
- Prefer **WebP or optimised JPG** for photos, **PNG/SVG** for logos.
- Keep photos under ~300 KB; the page loads them lazily but size still matters on mobile.
- Logos look best on a transparent background — the slots already add white padding.
- The company logo in the header/contact/footer is **not** here; it's uploaded in
  admin Settings and served from storage.
