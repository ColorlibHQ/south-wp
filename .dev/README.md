# Build and verification tooling

Nothing in `patterns/`, `parts/`, `templates/`, `theme.json` or `styles/` is
written by hand. Edit the generator, run it, commit what it produces.

```bash
python3 .dev/build_theme.py      # theme.json + styles/colors/* + styles/typography/* (audits AA first)
python3 .dev/build_patterns.py   # patterns/*.php, parts/*.html, templates/*.html
python3 .dev/build_demo.py       # plugin/south-listings/demo/ from the HTML template's data
node    .dev/build-fonts.mjs     # assets/fonts/ (Open Sans copied, the alternates fetched)
sh      .dev/make-pot.sh         # languages/south.pot and the plugin's .pot
sh      .dev/build-zip.sh        # .dev/tmp/build/south-1.0.0.zip and south-listings-1.0.0.zip
```

`.dev/tmp/` is gitignored scratch space: captures, logs, the build, Theme
Check output. Never stage anything at a fixed `/tmp` name.

## A WordPress to run against

Playground, never `local-wp` (it is shared, and other sessions rotate its
theme). The theme and the plugin are mounted live, so edits show on reload:

```bash
sh .dev/serve.sh 9481                                         # theme + plugin + demo content
sh .dev/serve.sh 9482 "$PWD/.dev/blueprint-theme-only.json"   # theme alone (activation tests)
```

`blueprint.json` activates the theme, THEN the plugin (the order Academia's
fatal needed), imports the demo and deletes WordPress's "Hello world" post
(the only step a real site would not do). The admin is `admin` / `password`.
Starter pages are COPIES made at activation: after changing a pattern,
restart Playground before believing a rendered check.

## The order that matters

```bash
export WP_URL=http://127.0.0.1:9481 WP_USER=admin WP_PASS=password
node .dev/normalize-blocks.mjs   # re-serialise every pattern as the editor would (recovers invalid blocks)
node .dev/validate-blocks.mjs    # then fail on anything invalid: templates, parts, patterns, stored pages, menus
```

## The checks

All rendered checks read `SOUTH_PATHS` (default: home, about, contact,
journal, listings, a property, a post, a search, a 404 — see `lib.mjs`).

| Check | What it catches |
| --- | --- |
| `contrast-rendered.mjs` | Text under 4.5:1 (3:1 when large), photographs measured by their pixels. `SOUTH_PALETTES=all` for all eight palettes, light and dark. |
| `button-boundary.mjs` | A button that does not read as a button: fill or border under 3:1 against what is behind it, label under 4.5:1. |
| `overflow-check.mjs` | An element wider than its parent's content box, at 1440/768/390. |
| `alignment-check.mjs` | A child of a constrained layout that took the width cap but not the centring. |
| `dead-selectors.py` | A `south-*` selector nothing emits (theme or plugin), a registered block style with no CSS. |
| `search-check.mjs` | Filter, sort and page with JavaScript off (GET forms) and on (no page load). |
| `activation-check.mjs` | Plugin activated with the theme already active, deactivated, under another theme, switched back. Port 9482. |
| `editor-shots.mjs` | Every pattern opened in the editor: placeholders, invalid blocks, screenshots in `tmp/editor/`. |
| `no-shared-functions.sh` | A function declared by both theme and plugin ("Cannot redeclare"). |
| `fidelity.mjs` | The same components measured on the HTML template and the theme (`HTML_URL`, `WP_URL`). |
| `theme-check.sh` | Theme Check on the BUILT theme. Expect exactly the documented REQUIRED findings. |

To serve the HTML template for `fidelity.mjs` and `shots.mjs`:
`python3 -m http.server 8731 --bind 127.0.0.1` from its folder.

`build_theme.py` audits every palette before writing, in light mode and in
dark mode as `assets/css/scheme.css` applies it (read from the file), and
refuses to write one that fails. Spacing and colour are vocabularies:
`sp()` refuses a step off the scale, `var()` a slug the palette lacks.
