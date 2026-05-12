# Chic Centre Suites — Theme Conventions

## Scroll fade-in animations

This theme ships a global scroll-triggered fade-in system (`css/scroll-fade-in.css` + `js/scroll-fade-in.js`), enqueued on every page in `functions.php`. **All new pages and new content blocks should opt in.**

- Add class `fade-in` to any block you want to reveal on scroll. **Do not add delay classes** — stagger delays make the last item in a group feel slow. All elements in a group should fade in simultaneously.
- `data-fade-stagger` on a parent auto-adds `fade-in` to direct children (no delays).
- The `fade-in-delay-*` CSS tokens exist but should not be used.
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
