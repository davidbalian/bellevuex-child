# How To: Multi-Language Sites (URL Prefix, PHP Dictionaries, Menus, Lang Switcher)

A **path-prefix** model for locales: the **default language** has **no prefix** (e.g. `/services/`); alternate languages live under **`/ru/…`** and **`/el/…`**. Detection uses WordPress **`query_vars`** populated by **`add_rewrite_rule`**, with **`REQUEST_URI` parsing** as a fallback.

**Strings** ship as **English keys** mapped to localized values in **per-locale PHP return arrays**. **URLs** built in PHP prepend the active prefix.**Navigation** combines **automatic internal link rewriting** with **optional separate menu locations** per language.**First-visit routing** optionally reads **`Accept-Language`** and sets a **`mci_lang` cookie** so repeat visits skip redirects.

HTML **`lang`** and SEO **`hreflang`** should stay consistent with these rules — wire your meta layer off the **same resolver** described below.

The **language switcher** UI (buttons, dropdown JS) matches the companion guide: [header-top-bar-sticky-nav.md](./header-top-bar-sticky-nav.md).

---

## Supported languages (conceptual)

| Code | URL role | Example |
|------|----------|---------|
| **`en`** (default) | No leading segment | `/`, `/contact/` |
| **`ru`** | First path segment | `/ru/`, `/ru/contact/` |
| **`el`** | First path segment | `/el/`, `/el/contact/` |

Store the list in one constant/array; **default** is the unprefixed language.

---

## Detection: `get_current_lang()`

1. **Static cache** per request.
2. Read **`lang`** from **`get_query_var('lang')`** after rewrite registration; **allowlist** against supported codes.
3. **Fallback:** parse **`REQUEST_URI`**: if the first segment is **`ru`** or **`el`**, use it.
4. Otherwise return **default**.

```php
function get_current_lang(): string {
    static $lang = null;
    if ( $lang !== null ) {
        return $lang;
    }
    $qv = get_query_var( 'lang', '' );
    if ( $qv && in_array( $qv, SUPPORTED_LANGS, true ) ) {
        $lang = $qv;
        return $lang;
    }
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    $segments = explode( '/', $path );
    if ( ! empty( $segments[0] ) && in_array( $segments[0], array( 'ru', 'el' ), true ) ) {
        $lang = $segments[0];
        return $lang;
    }
    $lang = DEFAULT_LANG;
    return $lang;
}
```

---

## Rewrite rules

Register on **`init`** (priority top so rules match first):

- **Front page in a locale:** `^(ru|el)/?$` → `index.php?lang=$matches[1]`
- **Inner pages:** `^(ru|el)/(.+?)/?$` → `index.php?lang=$matches[1]&pagename=$matches[2]`

Register **`lang`** with **`query_vars`**.

**`pre_get_posts`:** When **`lang`** is set and **`pagename`** is empty, force the **static front page** (`page_id` = `page_on_front`) so `/ru/` renders the homepage in Russian.

**Flush:** Call **`flush_rewrite_rules()`** on **theme activation** and optionally when you bump an **i18n version option** so active sites pick up rules without reinstalling the theme.

---

## Canonical redirect guard

WordPress may try to “fix” **`/ru/…`** into unprefixed URLs. Add **`redirect_canonical`** filter: if the requested path **starts with** an alternate language segment, **return `false`** to disable that redirect.

---

## Translation helpers

**Files:** `translations/{lang}.php` each **`return array( 'English key' => 'Localized value', … );`**. **Default language** files are often **empty** — the key *is* the display string.

```php
function get_translations( string $lang ): array {
    static $cache = [];
    if ( isset( $cache[ $lang ] ) ) {
        return $cache[ $lang ];
    }
    if ( $lang === DEFAULT_LANG ) {
        $cache[ $lang ] = [];
        return $cache[ $lang ];
    }
    $file = get_template_directory() . "/translations/{$lang}.php";
    $cache[ $lang ] = file_exists( $file ) ? include $file : [];
    return $cache[ $lang ];
}

function t( string $key ): string {
    $lang = get_current_lang();
    if ( $lang === DEFAULT_LANG ) {
        return $key;
    }
    $map = get_translations( $lang );
    return $map[ $key ] ?? $key;
}

function te( string $key ): void {
    echo esc_html( t( $key ) );
}
```

**Accent spans:** Optional **`t_accent()`** runs `preg_replace` on **`{accent}...{/accent}`** in the *translated* string to wrap marked phrases in a **display font** class — keep markers in the English key and in every translation file that needs emphasis.

---

## Language-aware URLs

**Internal links** (same site, path only):

```php
function localized_url( string $path = '/' ): string {
    $lang = get_current_lang();
    $path = '/' . ltrim( $path, '/' );
    if ( $lang === DEFAULT_LANG ) {
        return home_url( $path );
    }
    return home_url( '/' . $lang . $path );
}
```

**Language switcher** (same page, different lang): strip a leading **`ru`/`el`** segment from the current path, then rebuild:

- Target **default:** `home_url( '/' . $clean_path . ( $clean_path ? '/' : '' ) )`
- Target **ru/el:** `home_url( '/' . $target . '/' . $clean_path . ( $clean_path ? '/' : '' ) )`

Avoid **double-prefixing** if the path already starts with a locale segment.

---

## Browser language redirect (first visit)

On **`template_redirect`**, only when the path is **exactly the site root** (no prefix):

- If **`mci_lang` cookie** already set → do nothing.
- Skip **crawlers** via **`User-Agent`** heuristics.
- Parse **`Accept-Language`**; pick **`ru` or `el`** with highest **`q`** (including **`ru-RU`**, **`el-GR`** via two-letter prefix).
- If matched → **`setcookie('mci_lang', …)`** and **`wp_safe_redirect( home_url('/' . $lang . '/') )`**.
- Else set cookie **`en`** so you do not re-run detection every visit.

**Later request:** **`template_redirect`** (later priority) **syncs cookie** to **`get_current_lang()`** whenever the user lands on a prefixed URL without a cookie — keeps manual switcher visits and bookmarks aligned.

---

## HTML `lang` attribute

Filter **`language_attributes`**: map internal codes **`en` → `en-US`**, **`ru` → `ru`**, **`el` → `el`** (adjust to your legal **BCP 47** policy).

---

## Menus: two complementary strategies

### A. Automatic prefix on internal menu links

Filter **`wp_nav_menu_objects`**: for each item, if the URL host matches **home** and the path **does not** already start with **`/ru/`** or **`/el/`**, prepend **`/{current_lang}`** when **`current_lang !== default`**.

Keeps **one** WordPress menu working while enforcing correct hrefs.

### B. Separate menu locations per locale

Register **`primary`**, **`primary-ru`**, **`primary-el`**, and the same for **footer**. In **header/footer**, if **`get_current_lang() !== default`** and **`has_nav_menu( 'primary-' . $lang )`**, use that location; else fall back to **`primary`**.

Use **B** when link **labels** or **structure** differ by language; use **A** for simple sites; **combine** both if some trees are shared and some are not.

---

## Forms and server-side context

Hidden fields (e.g. **`form_lang`**) can record **`get_current_lang()`** for email templates or CRM routing.

---

## SEO alignment (brief)

For each public URL, emit **`hreflang`** alternates for **every** language + **x-default** pointing at the default URL. **Canonical** should match the **prefixed** URL when the page is non-default. Reuse **one** URL builder so **meta**, **OG**, and **JSON-LD** never disagree with visible links.

---

## Operational checklist

- [ ] **Allowlist** languages everywhere (detection, rewrites, cookie redirect).  
- [ ] **Flush permalinks** after deploying rewrite changes.  
- [ ] **Canonical filter** so prefixed URLs are not stripped.  
- [ ] **Translation files** kept in sync when adding new **`t()`** keys.  
- [ ] **Menu** strategy documented for editors (one menu vs per-locale).  
- [ ] **Switcher hrefs** use **`lang_url()`**, not hard-coded paths.  
- [ ] **Crawler exclusion** on browser auto-redirect.  

---

## Related handoffs

| Topic | Doc |
|--------|-----|
| Header language dropdown (JS/CSS) | [header-top-bar-sticky-nav.md](./header-top-bar-sticky-nav.md) |
| Footer + same menu selection pattern | [site-footer.md](./site-footer.md) |

---

This pattern is **not** Polylang/WPML — it is a **lightweight custom layer** suitable when you control all templates and a finite string catalog. For editor-managed posts per language, consider a dedicated multilingual plugin or CPT-per-locale.
