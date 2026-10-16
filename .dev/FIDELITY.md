# Fidelity: South (WordPress) against the South 3.0.0 HTML template

Measured 2026-10-16. HTML template: `colorlib-premium-upgrade/templates/south`
at commit 51a0a8a, served locally. WordPress: a fresh Playground
(`.dev/serve.sh`, WP latest, PHP 8.3) with the theme, South Listings and its
demo import (the template's own nine homes, three agents, three posts).
Logged out, reduced motion, 1440 and 390 wide.

## Method

1. **Side-by-side full-page renders** of seven pages at both widths
   (`.dev/shots.mjs` + `.dev/sidebyside.py`): home, listings, a property,
   the journal, a post, about, contact. Reviewed by eye after every change.
2. **Computed styles with paired selectors** (`.dev/fidelity.mjs`): the same
   component measured on both sites, one selector each; box, font size,
   weight, case, style and colour. A row is flagged when width differs by more
   than 5%, height by more than 8%, or the type differs on an element that
   holds words.

## Result

**146 component measurements, 4 flagged, none a real difference** (below).
Page heights: within **±3.0%** at both widths on all seven pages.

| page | 1440 | 390 |
| --- | --- | --- |
| home | 5399 → 5415 (0.3%) | 9757 → 9796 (0.4%) |
| listings | 2971 → 2991 (0.7%) | 7287 → 7330 (0.6%) |
| property | 3919 → 3856 (-1.6%) | 6917 → 6883 (-0.5%) |
| journal | 3951 → 3833 (-3.0%) | 6369 → 6389 (0.3%) |
| post | 4132 → 4101 (-0.8%) | 6912 → 6895 (-0.2%) |
| about | 3957 → 3921 (-0.9%) | 7999 → 7774 (-2.8%) |
| contact | 2605 → 2593 (-0.5%) | 4663 → 4597 (-1.4%) |

Matched exactly (same box, type and colour on both): the 45px top bar and
the 90px menu bar over the photograph; the 800px hero (700/650/550 at
smaller widths) with its 60px uppercase title on the black band (16px at
390, as the template); the 260×45 brown "Search for your home" tab at the
hero's lower edge and the 1110px search box under it (30/50 padding,
1px border, 0 5px 30px shadow); the 38px fields and 170×50 button; the 30px
uppercase section titles with the 14px italic brown line; the 350×558 card
(253px photo, 35px tag, 24px price badge 20px from the corner, 30px body,
18px title, brown address, excerpt, facts row on the bottom edge, equal
heights per row); the 650px rent call to action (48px/30px italic); the
741px testimonials; the 728px realtor band on #f1f6f8; the 726px footer
(16px uppercase headings with the 2px brown rule, 14px #8c8c8c copy, 60px
copyright bar); the 450px banners with the title band; the 1110×559
property photo, 24px price, 30px title, facts grid, features with check
discs, 285px panel buttons, the 350px agent box at +80px; journal posts at
the template's exact offsets (date @946, headline @977, byline @1022,
excerpt @1096, button @1202); 540px map cards.

### The four flags

| flag | why it is not a difference |
| --- | --- |
| search tab width (1440 and 390) | The template measures its `<p>` inside a 260px tab with 40px padding (180px); the theme's tab is the paragraph itself (260px). The tab renders identically. |
| order select at 390 | The template's custom select is 136px at 390; the native select sizes to its chosen option (98px). |
| menu link at 390 | Hidden behind the menu button on both. |

## Differences that remain, on purpose or by WordPress

- **The logo** is the site title set as the template's wordmark (the
  diamond-and-square mark and SOUTH in brown caps), so it follows the site's
  name; uploading a logo replaces it. The template draws an SVG image.
- **A dark-mode switch** (moon/sun) sits after the search icon. The template
  has none; it can be switched off with `south_enable_dark_mode`.
- **Menu:** Home, About Us, Properties, Blog, a "Find a Home" submenu (five
  searches) and Contact. The template's demo-only "Pages" dropdown is left
  out, and "Find a Home" is a submenu rather than a four-column mega menu.
- **Buttons on photographs have a 1px light border** (rent CTA, any cover):
  the brown fill alone measured 1.3–2.5:1 against the lit windows behind it,
  under WCAG 1.4.11's 3:1. The dark "Calculate mortgage" button is the
  `contrast` colour (#323232) rather than #000 so it also shows in dark mode.
- **Sliders:** the hero cross-fades (the template slides) with the same
  numbered squares; testimonials bring the next quote to the centre by
  reordering rather than animating.
- **"More filters" is open without JavaScript** (every filter works as a
  plain GET form); with it, it folds as in the template.
- **Journal:** the template shows a decorative 01/02/03 pagination with only
  three posts; WordPress shows page numbers only when there is a second page
  (the 3% shorter journal page). Comment avatars are Gravatar defaults (the
  demo commenters have no photographs), and the reply form adds WordPress's
  cookie-consent checkbox.
- **In the editor**, a featured-homes grid previews the newest homes: the
  "featured only" rule is applied on the site by `query_loop_block_query_vars`,
  which the editor's REST preview does not run.

## Full tables

## 1440px

### home (page height 5399 → 5415, 0.3%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| top bar | 1440×45 @0 · 16px 400 · 33, 37, 41 | 1440×45 @0 · 14px 600 · 107, 107, 107 | = |
| menu bar | 1440×90 @45 · 16px 400 · 33, 37, 41 | 1440×90 @45 · 14px 600 · 107, 107, 107 | = |
| menu link | 94×35 @73 · 14px 600 UC · 255, 255, 255 | 94×34 @73 · 14px 600 UC · 255, 255, 255 | = |
| hero | 1440×800 @0 · 16px 400 · 33, 37, 41 | 1440×800 @0 · 14px 600 · 107, 107, 107 | = |
| hero title band | 549×80 @360 · 60px 600 UC · 255, 255, 255 | 549×80 @360 · 60px 600 UC · 255, 255, 255 | = |
| search tab | 180×45 @755 · 14px 600 UC · 255, 255, 255 | 260×45 @755 · 14px 600 UC · 255, 255, 255 | **w** |
| search box | 1110×248 @800 · 16px 400 · 33, 37, 41 | 1110×257 @800 · 14px 600 · 107, 107, 107 | = |
| search field | 230×38 @831 · 14px 500 · 0, 0, 0 | 230×38 @831 · 14px 500 · 50, 50, 50 | = |
| search button | 170×50 @967 · 14px 600 UC · 255, 255, 255 | 170×50 @976 · 14px 600 UC · 255, 255, 255 | = |
| section title | 1110×36 @1148 · 30px 600 UC · 50, 50, 50 | 1110×36 @1157 · 30px 600 UC · 50, 50, 50 | = |
| section subtitle | 1110×28 @1189 · 14px 600 it · 122, 90, 66 | 1110×28 @1198 · 14px 600 it · 122, 90, 66 | = |
| card | 350×558 @1317 · 16px 400 · 33, 37, 41 | 350×559 @1326 · 14px 600 · 107, 107, 107 | = |
| card photo | 350×253 @1317 · 16px 400 · 33, 37, 41 | 350×253 @1326 · 14px 600 · 107, 107, 107 | = |
| card tag | 94×35 @1337 · 12px 600 UC · 255, 255, 255 | 94×35 @1346 · 12px 600 UC · 255, 255, 255 | = |
| card price | 162×44 @1506 · 24px 600 · 122, 90, 66 | 162×44 @1515 · 24px 600 · 122, 90, 66 | = |
| card body | 350×304 @1570 · 16px 400 · 33, 37, 41 | 350×305 @1579 · 14px 600 · 107, 107, 107 | = |
| card title | 288×22 @1601 · 18px 600 · 50, 50, 50 | 288×22 @1610 · 18px 600 · 50, 50, 50 | = |
| card address | 288×28 @1631 · 14px 600 · 122, 90, 66 | 288×28 @1640 · 14px 600 · 122, 90, 66 | = |
| card excerpt | 288×56 @1684 · 14px 600 · 107, 107, 107 | 288×56 @1693 · 14px 600 · 107, 107, 107 | = |
| card facts row | 288×24 @1820 · 16px 400 · 33, 37, 41 | 288×24 @1830 · 14px 600 · 107, 107, 107 | = |
| rent CTA | 1440×650 @2554 · 16px 400 · 33, 37, 41 | 1440×650 @2565 · 14px 600 · 255, 255, 255 | = |
| CTA title | 1110×58 @2768 · 48px 600 UC · 255, 255, 255 | 1110×58 @2783 · 48px 600 UC · 255, 255, 255 | = |
| CTA line | 1110×36 @2846 · 30px 600 it · 255, 255, 255 | 1110×36 @2861 · 30px 600 it · 255, 255, 255 | = |
| testimonials | 1440×741 @3204 · 16px 400 · 33, 37, 41 | 1440×741 @3215 · 14px 600 · 107, 107, 107 | = |
| realtor band | 1440×728 @3945 · 16px 400 · 33, 37, 41 | 1440×727 @3956 · 14px 600 · 107, 107, 107 | = |
| realtor name | 576×36 @4082 · 30px 600 UC · 50, 50, 50 | 576×36 @4092 · 30px 600 UC · 50, 50, 50 | = |
| realtor text | 576×140 @4201 · 14px 600 · 107, 107, 107 | 576×140 @4211 · 14px 600 · 107, 107, 107 | = |
| footer | 1440×726 @4673 · 16px 400 · 33, 37, 41 | 1440×732 @4683 · 14px 600 · 255, 255, 255 | = |
| footer heading | 79×31 @4773 · 16px 600 UC · 255, 255, 255 | 79×31 @4783 · 16px 600 UC · 255, 255, 255 | = |
| footer text | 255×112 @5127 · 14px 400 · 140, 140, 140 | 255×112 @5143 · 14px 400 · 140, 140, 140 | = |
| copyright | 1440×60 @5339 · 16px 400 · 33, 37, 41 | 1440×60 @5355 · 14px 600 · 255, 255, 255 | = |

### listings (page height 2971 → 2991, 0.7%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| banner | 1440×450 @0 · 16px 400 · 33, 37, 41 | 1440×450 @0 · 14px 600 · 255, 255, 255 | = |
| banner title | 300×72 @254 · 60px 600 UC · 255, 255, 255 | 300×72 @254 · 60px 600 UC · 255, 255, 255 | = |
| results line | 1110×24 @862 · 14px 600 · 107, 107, 107 | 1110×24 @871 · 14px 600 · 107, 107, 107 | = |
| order select | 98×34 @798 · 14px 400 · 33, 37, 41 | 98×34 @807 · 14px 400 · 50, 50, 50 | = |
| card | 350×558 @932 · 16px 400 · 33, 37, 41 | 350×559 @941 · 14px 600 · 107, 107, 107 | = |
| page number | 20×20 @2119 · 12px 600 · 255, 255, 255 | 20×20 @2135 · 12px 600 · 255, 255, 255 | = |

### property (page height 3919 → 3856, -1.6%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| photo | 1110×559 @798 · 16px 400 · 33, 37, 41 | 1110×559 @807 · 14px 600 · 107, 107, 107 | = |
| price | 730×48 @1427 · 24px 600 · 122, 90, 66 | 730×48 @1436 · 24px 600 · 122, 90, 66 | = |
| title | 730×36 @1475 · 30px 600 · 50, 50, 50 | 730×36 @1484 · 30px 600 · 50, 50, 50 | = |
| address | 730×28 @1521 · 14px 600 · 107, 107, 107 | 730×28 @1530 · 14px 600 · 107, 107, 107 | = |
| fact cell | 243×70 @1902 · 14px 600 · 50, 50, 50 | 243×70 @1871 · 14px 600 · 50, 50, 50 | = |
| feature | 243×27 @2112 · 15px 400 · 50, 50, 50 | 243×23 @2081 · 15px 400 · 50, 50, 50 | = |
| panel button | 285×50 @2338 · 14px 600 UC · 255, 255, 255 | 285×50 @2268 · 14px 600 UC · 255, 255, 255 | = |
| agent box | 350×818 @1437 · 16px 400 · 33, 37, 41 | 350×818 @1446 · 14px 600 · 107, 107, 107 | = |
| agent name | 260×29 @1697 · 24px 600 UC · 50, 50, 50 | 260×29 @1706 · 24px 600 UC · 50, 50, 50 | = |
| agent field | 260×45 @1870 · 12px 500 it · 0, 0, 0 | 260×45 @1879 · 12px 500 · 50, 50, 50 | = |
| map | 1110×540 @2553 · 16px 400 · 50, 50, 50 | 1110×540 @2483 · 14px 600 · 107, 107, 107 | = |

### journal (page height 3951 → 3833, -3.0%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| post photo | 730×346 @550 · 14px 600 · 0, 123, 255 | 730×346 @550 · 14px 600 · 122, 90, 66 | = |
| date | 730×21 @946 · 14px 600 · 122, 90, 66 | 730×21 @946 · 14px 600 · 122, 90, 66 | = |
| headline | 730×45 @977 · 30px 600 · 50, 50, 50 | 730×45 @977 · 30px 600 · 50, 50, 50 | = |
| byline | 730×24 @1022 · 12px 600 · 112, 112, 112 | 730×24 @1022 · 12px 600 · 112, 112, 112 | = |
| excerpt | 730×56 @1096 · 14px 600 · 107, 107, 107 | 730×56 @1096 · 14px 600 · 107, 107, 107 | = |
| read more | 170×50 @1202 · 14px 600 UC · 255, 255, 255 | 170×50 @1202 · 14px 600 UC · 255, 255, 255 | = |
| sidebar search | 350×43 @550 · 12px 400 it · 0, 0, 0 | 348×43 @551 · 12px 400 it · 50, 50, 50 | = |
| sidebar heading | 350×19 @663 · 16px 600 · 50, 50, 50 | 350×19 @665 · 16px 600 · 50, 50, 50 | = |

### post (page height 4132 → 4101, -0.8%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| title | 730×39 @977 · 30px 600 · 50, 50, 50 | 730×39 @977 · 30px 600 · 50, 50, 50 | = |
| subheading | 730×24 @1196 · 20px 600 · 50, 50, 50 | 730×24 @1196 · 20px 600 · 50, 50, 50 | = |
| paragraph | 730×56 @1090 · 14px 600 · 107, 107, 107 | 730×56 @1090 · 14px 600 · 107, 107, 107 | = |
| comments title | 730×24 @2311 · 20px 600 · 50, 50, 50 | 730×24 @2252 · 20px 600 · 50, 50, 50 | = |
| reply field | 730×43 @2899 · 12px 500 it · 0, 0, 0 | 730×43 @2825 · 12px 500 · 50, 50, 50 | = |
| reply button | 170×50 @3255 · 14px 600 UC · 255, 255, 255 | 170×50 @3209 · 14px 600 UC · 255, 255, 255 | = |

### about (page height 3957 → 3921, -0.9%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| about title | 730×36 @798 · 30px 600 UC · 50, 50, 50 | 730×36 @807 · 30px 600 UC · 50, 50, 50 | = |
| about photo | 730×281 @967 · 16px 400 · 33, 37, 41 | 730×281 @976 · 14px 600 · 107, 107, 107 | = |
| about text | 730×140 @1288 · 14px 600 · 107, 107, 107 | 730×140 @1297 · 14px 600 · 107, 107, 107 | = |
| team photo | 350×350 @2544 · 16px 400 · 33, 37, 41 | 350×350 @2503 · 14px 600 · 107, 107, 107 | = |
| team name | 350×36 @2971 · 30px 600 UC · 50, 50, 50 | 350×36 @2929 · 30px 600 UC · 50, 50, 50 | = |

### contact (page height 2605 → 2593, -0.5%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| heading | 117×31 @550 · 16px 600 UC · 50, 50, 50 | 117×31 @550 · 16px 600 UC · 50, 50, 50 | = |
| hours row | 350×37 @679 · 16px 400 · 33, 37, 41 | 350×37 @671 · 14px 600 · 107, 107, 107 | = |
| form field | 730×43 @679 · 12px 500 it · 0, 0, 0 | 730×43 @671 · 12px 500 · 50, 50, 50 | = |
| message field | 730×200 @838 · 12px 500 it · 0, 0, 0 | 730×200 @830 · 12px 500 · 50, 50, 50 | = |
| send button | 170×50 @1088 · 14px 600 UC · 255, 255, 255 | 170×50 @1070 · 14px 600 UC · 255, 255, 255 | = |
| map | 1110×540 @1238 · 16px 400 · 50, 50, 50 | 1110×540 @1220 · 14px 600 · 107, 107, 107 | = |


## 390px

### home (page height 9757 → 9796, 0.4%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| top bar | 390×91 @0 · 16px 400 · 33, 37, 41 | 390×91 @0 · 14px 600 · 107, 107, 107 | = |
| menu bar | 390×70 @91 · 16px 400 · 33, 37, 41 | 390×70 @91 · 14px 600 · 107, 107, 107 | = |
| menu link | — | — | **missing** |
| hero | 390×550 @0 · 16px 400 · 33, 37, 41 | 390×550 @0 · 14px 600 · 107, 107, 107 | = |
| hero title band | 168×36 @257 · 16px 600 UC · 255, 255, 255 | 168×36 @257 · 16px 600 UC · 255, 255, 255 | = |
| search tab | 180×45 @505 · 14px 600 UC · 255, 255, 255 | 210×45 @505 · 14px 600 UC · 255, 255, 255 | **w** |
| search box | 360×672 @550 · 16px 400 · 33, 37, 41 | 360×662 @550 · 14px 600 · 107, 107, 107 | = |
| search field | 318×38 @581 · 14px 500 · 0, 0, 0 | 318×38 @581 · 14px 500 · 50, 50, 50 | = |
| search button | 170×50 @1141 · 14px 600 UC · 255, 255, 255 | 170×50 @1131 · 14px 600 UC · 255, 255, 255 | = |
| section title | 360×29 @1322 · 24px 600 UC · 50, 50, 50 | 360×29 @1312 · 24px 600 UC · 50, 50, 50 | = |
| section subtitle | 360×56 @1356 · 14px 600 it · 122, 90, 66 | 360×56 @1346 · 14px 600 it · 122, 90, 66 | = |
| card | 360×515 @1512 · 16px 400 · 33, 37, 41 | 360×515 @1502 · 14px 600 · 107, 107, 107 | = |
| card photo | 360×261 @1512 · 16px 400 · 33, 37, 41 | 360×261 @1502 · 14px 600 · 107, 107, 107 | = |
| card tag | 94×35 @1532 · 12px 600 UC · 255, 255, 255 | 94×35 @1522 · 12px 600 UC · 255, 255, 255 | = |
| card price | 162×44 @1708 · 24px 600 · 122, 90, 66 | 162×44 @1698 · 24px 600 · 122, 90, 66 | = |
| card body | 360×255 @1772 · 16px 400 · 33, 37, 41 | 360×255 @1762 · 14px 600 · 107, 107, 107 | = |
| card title | 298×22 @1803 · 18px 600 · 50, 50, 50 | 298×22 @1793 · 18px 600 · 50, 50, 50 | = |
| card address | 298×28 @1833 · 14px 600 · 122, 90, 66 | 298×28 @1823 · 14px 600 · 122, 90, 66 | = |
| card excerpt | 298×56 @1886 · 14px 600 · 107, 107, 107 | 298×56 @1876 · 14px 600 · 107, 107, 107 | = |
| card facts row | 298×24 @1972 · 16px 400 · 33, 37, 41 | 298×24 @1962 · 14px 600 · 107, 107, 107 | = |
| rent CTA | 390×500 @5024 · 16px 400 · 33, 37, 41 | 390×500 @5017 · 14px 600 · 255, 255, 255 | = |
| CTA title | 360×58 @5162 · 24px 600 UC · 255, 255, 255 | 362×58 @5159 · 24px 600 UC · 255, 255, 255 | = |
| CTA line | 360×38 @5240 · 16px 600 it · 255, 255, 255 | 362×38 @5237 · 16px 600 it · 255, 255, 255 | = |
| testimonials | 390×734 @5524 · 16px 400 · 33, 37, 41 | 390×734 @5517 · 14px 600 · 107, 107, 107 | = |
| realtor band | 390×1156 @6258 · 16px 400 · 33, 37, 41 | 390×1155 @6251 · 14px 600 · 107, 107, 107 | = |
| realtor name | 351×29 @6395 · 24px 600 UC · 50, 50, 50 | 350×29 @6387 · 24px 600 UC · 50, 50, 50 | = |
| realtor text | 351×224 @6507 · 14px 600 · 107, 107, 107 | 350×224 @6499 · 14px 600 · 107, 107, 107 | = |
| footer | 390×2343 @7414 · 16px 400 · 33, 37, 41 | 390×2390 @7406 · 14px 600 · 255, 255, 255 | = |
| footer heading | 79×31 @7514 · 16px 600 UC · 255, 255, 255 | 79×31 @7506 · 16px 600 UC · 255, 255, 255 | = |
| footer text | 360×84 @7874 · 14px 400 · 140, 140, 140 | 360×84 @7929 · 14px 400 · 140, 140, 140 | = |
| copyright | 390×60 @9697 · 16px 400 · 33, 37, 41 | 390×60 @9736 · 14px 600 · 255, 255, 255 | = |

### listings (page height 7287 → 7330, 0.6%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| banner | 390×450 @0 · 16px 400 · 33, 37, 41 | 390×450 @0 · 14px 600 · 255, 255, 255 | = |
| banner title | 170×42 @269 · 30px 600 UC · 255, 255, 255 | 170×42 @269 · 30px 600 UC · 255, 255, 255 | = |
| results line | 360×24 @1286 · 14px 600 · 107, 107, 107 | 360×24 @1276 · 14px 600 · 107, 107, 107 | = |
| order select | 136×34 @1222 · 14px 400 · 33, 37, 41 | 98×34 @1212 · 14px 400 · 50, 50, 50 | **w** |
| card | 360×515 @1356 · 16px 400 · 33, 37, 41 | 360×515 @1346 · 14px 600 · 107, 107, 107 | = |
| page number | 20×20 @4819 · 12px 600 · 255, 255, 255 | 20×20 @4817 · 12px 600 · 255, 255, 255 | = |

### property (page height 6917 → 6883, -0.5%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| photo | 360×181 @1222 · 16px 400 · 33, 37, 41 | 360×181 @1212 · 14px 600 · 107, 107, 107 | = |
| price | 360×48 @1473 · 24px 600 · 122, 90, 66 | 360×48 @1463 · 24px 600 · 122, 90, 66 | = |
| title | 360×24 @1521 · 20px 600 · 50, 50, 50 | 360×24 @1511 · 20px 600 · 50, 50, 50 | = |
| address | 360×28 @1555 · 14px 600 · 107, 107, 107 | 360×28 @1545 · 14px 600 · 107, 107, 107 | = |
| fact cell | 180×70 @2048 · 14px 600 · 50, 50, 50 | 180×70 @1998 · 14px 600 · 50, 50, 50 | = |
| feature | 360×27 @2328 · 15px 400 · 50, 50, 50 | 360×23 @2278 · 15px 400 · 50, 50, 50 | = |
| panel button | 285×50 @2814 · 14px 600 UC · 255, 255, 255 | 285×50 @2703 · 14px 600 UC · 255, 255, 255 | = |
| agent box | 360×824 @3009 · 16px 400 · 33, 37, 41 | 360×824 @2928 · 14px 600 · 107, 107, 107 | = |
| agent name | 300×29 @3276 · 24px 600 UC · 50, 50, 50 | 300×29 @3195 · 24px 600 UC · 50, 50, 50 | = |
| agent field | 300×45 @3448 · 12px 500 it · 0, 0, 0 | 300×45 @3367 · 12px 500 · 50, 50, 50 | = |
| map | 360×540 @3933 · 16px 400 · 50, 50, 50 | 360×540 @3852 · 14px 600 · 107, 107, 107 | = |

### journal (page height 6369 → 6389, 0.3%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| post photo | 360×170 @550 · 14px 600 · 0, 123, 255 | 360×171 @550 · 14px 600 · 122, 90, 66 | = |
| date | 360×21 @770 · 14px 600 · 122, 90, 66 | 360×21 @771 · 14px 600 · 122, 90, 66 | = |
| headline | 360×90 @801 · 30px 600 · 50, 50, 50 | 360×90 @802 · 30px 600 · 50, 50, 50 | = |
| byline | 360×24 @891 · 12px 600 · 112, 112, 112 | 360×24 @892 · 12px 600 · 112, 112, 112 | = |
| excerpt | 360×112 @965 · 14px 600 · 107, 107, 107 | 360×112 @966 · 14px 600 · 107, 107, 107 | = |
| read more | 170×50 @1127 · 14px 600 UC · 255, 255, 255 | 170×50 @1128 · 14px 600 UC · 255, 255, 255 | = |
| sidebar search | 360×43 @2749 · 12px 400 it · 0, 0, 0 | 358×43 @2781 · 12px 400 it · 50, 50, 50 | = |
| sidebar heading | 360×19 @2862 · 16px 600 · 50, 50, 50 | 360×19 @2895 · 16px 600 · 50, 50, 50 | = |

### post (page height 6912 → 6895, -0.2%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| title | 360×62 @801 · 24px 600 · 50, 50, 50 | 360×62 @802 · 24px 600 · 50, 50, 50 | = |
| subheading | 360×24 @1128 · 20px 600 · 50, 50, 50 | 360×24 @1128 · 20px 600 · 50, 50, 50 | = |
| paragraph | 360×140 @938 · 14px 600 · 107, 107, 107 | 360×140 @938 · 14px 600 · 107, 107, 107 | = |
| comments title | 360×24 @2193 · 20px 600 · 50, 50, 50 | 360×24 @2133 · 20px 600 · 50, 50, 50 | = |
| reply field | 360×43 @2911 · 12px 500 it · 0, 0, 0 | 360×43 @2784 · 12px 500 · 50, 50, 50 | = |
| reply button | 170×50 @3267 · 14px 600 UC · 255, 255, 255 | 170×50 @3196 · 14px 600 UC · 255, 255, 255 | = |

### about (page height 7999 → 7774, -2.8%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| about title | 360×58 @1222 · 24px 600 UC · 50, 50, 50 | 360×58 @1212 · 24px 600 UC · 50, 50, 50 | = |
| about photo | 360×139 @1413 · 16px 400 · 33, 37, 41 | 360×139 @1403 · 14px 600 · 107, 107, 107 | = |
| about text | 360×308 @1591 · 14px 600 · 107, 107, 107 | 360×308 @1581 · 14px 600 · 107, 107, 107 | = |
| team photo | 360×360 @3588 · 16px 400 · 33, 37, 41 | 360×360 @3458 · 14px 600 · 107, 107, 107 | = |
| team name | 360×29 @4025 · 24px 600 UC · 50, 50, 50 | 360×29 @3894 · 24px 600 UC · 50, 50, 50 | = |

### contact (page height 4663 → 4597, -1.4%)

| component | HTML template | WordPress | |
| --- | --- | --- | --- |
| heading | 117×31 @550 · 16px 600 UC · 50, 50, 50 | 117×31 @550 · 16px 600 UC · 50, 50, 50 | = |
| hours row | 360×37 @679 · 16px 400 · 33, 37, 41 | 360×37 @671 · 14px 600 · 107, 107, 107 | = |
| form field | 360×43 @1021 · 12px 500 it · 0, 0, 0 | 360×43 @1018 · 12px 500 · 50, 50, 50 | = |
| message field | 360×200 @1180 · 12px 500 it · 0, 0, 0 | 360×200 @1177 · 12px 500 · 50, 50, 50 | = |
| send button | 170×50 @1430 · 14px 600 UC · 255, 255, 255 | 170×50 @1417 · 14px 600 UC · 255, 255, 255 | = |
| map | 360×540 @1680 · 16px 400 · 50, 50, 50 | 360×540 @1567 · 14px 600 · 107, 107, 107 | = |


146 component measurements, 4 outside tolerance (w ±5%, h ±8%, any type difference).
