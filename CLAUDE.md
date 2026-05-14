# Chic Centre Suites — Theme Conventions

## Scroll fade-in animations

This theme ships a global scroll-triggered fade-in system (`css/scroll-fade-in.css` + `js/scroll-fade-in.js`), enqueued on every page in `functions.php`. **All new pages and new content blocks should opt in.**

- Add class `fade-in` to any block you want to reveal on scroll.
- Stagger is **capped at 3 levels** (delay-0 = 0s, delay-1 = 50ms, delay-2 = 100ms). Never go beyond `fade-in-delay-2` — it makes trailing items feel slow.
- `data-fade-stagger` on a parent auto-adds `fade-in` + capped delay to direct children (index 0→delay-0, 1→delay-1, 2+→delay-2).
- For hand-written markup: add `fade-in-delay-0/1/2` manually; 4th+ elements share delay-2.
- Animation auto-cleans on `animationend` (classes stripped), so hover/transform effects on the same element work normally after reveal.
- `prefers-reduced-motion`: content shows immediately, no animation.
- No-JS safety: `fade-in-ready` is added to `<html>` via an inline `wp_head` script; without it the `.fade-in` selectors don't match and content stays visible.

**Treat this as default.** Building a new template or section without `fade-in` is a regression of the site's motion language.

## Hero slider

The homepage hero uses Swiper v12 (vendored at `assets/vendor/swiper/`) with:
- Grid-stacked overlay layout (`.home-hero__slider-bleed` + `.home-hero__overlay` in one grid cell)
- Ken Burns zoom on active slide (Web Animations API)
- Scroll parallax on slider bleed only — copy layer does not move
- Chevron scroll-cue to `#home-intro`

Slides come from the "Home Hero Slides" meta box on the Home page (WP admin → Pages → Home). Data stored as JSON array of attachment IDs in `_chic_home_hero_slides` post meta; managed in `inc/home-admin-ui.php`. Helper: `chic_home_hero_slides( $post_id )` in `inc/home-data.php`.

When building hero sections on other pages, reuse the `.home-hero__*` CSS structure and `js/page-home-hero.js` pattern. Do not enqueue Swiper a second time — register against the existing `chic-swiper` handle.

## Greek (el) typography — all caps

For **modern Greek (monotonic)** copy in this theme:

- **Fully uppercase Greek** (hero lines, shouty labels, any span that is only Greek capitals, plus spaces, `<br>`, digits, punctuation) must **not** use the tonos on vowels. Use **Α Ε Η Ι Ο Υ Ω**, never the precomposed stressed capitals **Ά Έ Ή Ί Ό Ύ Ώ**.
- **Sentence or title case** keeps normal monotonic spelling: tonos on lowercase where required, and accented capitals at word starts when applicable (e.g. `Άνοιγμα μενού`, `Έως 2 άτομα` in `translations/el.php`).
- **Dialytika:** emphasis is on tonos; in rare all-caps cases with **Ϊ / Ϋ**, Greek practice often omits dialytika in caps as well—prefer plain **Ι Υ** when the whole word is caps.

**Where this applies:** `translations/el.php`, PHP templates, alt text, `tools/` SEO strings, and any other hard-coded Greek.

**`text-transform: uppercase`:** several stylesheets force caps in CSS. Spot-check Greek in a real browser when copy is transformed; if casing looks wrong, fix the **source string** rather than relying on the transform alone.

**Quick check:** after editing Greek, search for `Ά|Έ|Ή|Ί|Ό|Ύ|Ώ` and confirm those characters only appear in mixed/sentence case, not inside an all-caps Greek headline. For new caps headlines you can also scan for long runs of `[Α-Ω]` in the changed lines.
