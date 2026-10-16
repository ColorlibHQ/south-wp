#!/usr/bin/env python3
"""
Generates theme.json and every style variation from one table of colours.

South's brand brown has three shades in the HTML template, and the reason is
contrast: #947054 is 4.46:1 on white, fine for icons, rules and borders and a
fail for text. So the palette keeps it as `accent` (decoration only), uses
#86634a as `primary` (fills under a white label: 5.4:1) and #7a5a42 as
`primary-text` (brown words on a light ground: 6.2:1). Those are the
template's own tokens, from reports/south/NOTES.md.

Nothing here is eyeballed: audit() computes every pair the design actually
produces, in every palette, in light and in dark mode (reading the dark-mode
rules out of assets/css/scheme.css), and refuses to write a palette that
fails.

Usage:  python3 .dev/build_theme.py
"""

import collections
import json
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)

# ---------------------------------------------------------------------------
# Palette
# ---------------------------------------------------------------------------
# Fourteen slugs, the same in every variation, so a pattern written against
# them works under all eight. Each names a JOB, not a colour:
#
#   base / surface / panel   the page, the light band (realtor note), side panels
#   contrast                 headings and dark text (the template's "ink")
#   copy                     body copy  (never `text`: core emits `has-text-color`)
#   muted                    meta: dates, card facts, labels
#   accent                   the decorative brown: icons, rules, focus borders
#   primary                  FILLS under a label: buttons, tags, the search tab
#   primary-text             brown WORDS on a light ground: subtitles, prices
#   dark                     the black of the header, title band and footer
#   divider                  field and card borders
#   overlay                  text on a photograph or on `dark`; never flips
#   on-dark                  body copy on the footer
#   on-primary               the label on a `primary` fill
#
# No slug is named after a core utility class (`border`, `text`, `background`,
# `link`): Academia's `border` slug turned every `has-border-color` element's
# TEXT that colour.
PALETTE = [
    ("Base", "base"), ("Surface", "surface"), ("Panel", "panel"),
    ("Contrast", "contrast"), ("Copy", "copy"), ("Muted", "muted"),
    ("Accent", "accent"), ("Primary", "primary"), ("Primary text", "primary-text"),
    ("Dark", "dark"), ("Divider", "divider"), ("Overlay", "overlay"),
    ("On dark", "on-dark"), ("On primary", "on-primary"),
]

COLOR_SETS = collections.OrderedDict([
    # South's own: the HTML template's tokens, unchanged.
    ("colors-1-south", ("South", {
        "base": "#ffffff", "surface": "#f1f6f8", "panel": "#f5f5f5",
        "contrast": "#323232", "copy": "#6b6b6b", "muted": "#707070",
        "accent": "#947054", "primary": "#86634a", "primary-text": "#7a5a42",
        "dark": "#000000", "divider": "#e1dddd", "overlay": "#ffffff",
        "on-dark": "#8c8c8c", "on-primary": "#ffffff",
    })),
    ("colors-2-terracotta", ("Terracotta", {
        "base": "#ffffff", "surface": "#f8f2ef", "panel": "#f6f1ee",
        "contrast": "#2f2926", "copy": "#675f5a", "muted": "#6c645f",
        "accent": "#c46d4a", "primary": "#a24e2e", "primary-text": "#984829",
        "dark": "#120d0b", "divider": "#e6dbd5", "overlay": "#ffffff",
        "on-dark": "#a1958f", "on-primary": "#ffffff",
    })),
    ("colors-3-olive", ("Olive", {
        "base": "#ffffff", "surface": "#f3f5ec", "panel": "#f3f4ee",
        "contrast": "#2a2c21", "copy": "#616356", "muted": "#66685c",
        "accent": "#8b914c", "primary": "#5a632d", "primary-text": "#545c2a",
        "dark": "#14160e", "divider": "#dee1d1", "overlay": "#ffffff",
        "on-dark": "#9fa291", "on-primary": "#ffffff",
    })),
    ("colors-4-harbour", ("Harbour", {
        "base": "#ffffff", "surface": "#eff3f8", "panel": "#f1f4f7",
        "contrast": "#1f2935", "copy": "#58636f", "muted": "#5f6974",
        "accent": "#5a80a8", "primary": "#2e5279", "primary-text": "#2b4c70",
        "dark": "#0a141f", "divider": "#d8dfe8", "overlay": "#ffffff",
        "on-dark": "#94a3b3", "on-primary": "#ffffff",
    })),
    ("colors-5-sage", ("Sage", {
        "base": "#ffffff", "surface": "#eef5f4", "panel": "#f0f5f4",
        "contrast": "#1e2c2b", "copy": "#576665", "muted": "#5e6c6b",
        "accent": "#4a9a92", "primary": "#1f6a64", "primary-text": "#1c615c",
        "dark": "#091615", "divider": "#d3e4e1", "overlay": "#ffffff",
        "on-dark": "#8ea8a5", "on-primary": "#ffffff",
    })),
    ("colors-6-charcoal", ("Charcoal", {
        "base": "#ffffff", "surface": "#f2f3f4", "panel": "#f5f5f5",
        "contrast": "#222222", "copy": "#626262", "muted": "#686868",
        "accent": "#8a8a8a", "primary": "#454545", "primary-text": "#454545",
        "dark": "#000000", "divider": "#dedede", "overlay": "#ffffff",
        "on-dark": "#8f8f8f", "on-primary": "#ffffff",
    })),
    # Dark palettes. `base` is the page, so it is dark here, and the button
    # fill is LIGHT with a dark label: the opposite of the usual rule, which is
    # why every label is measured, never assumed.
    ("colors-7-midnight", ("Midnight", {
        "base": "#121821", "surface": "#18202c", "panel": "#1c2533",
        "contrast": "#eef2f7", "copy": "#b2bccb", "muted": "#a5afbe",
        "accent": "#c7a27f", "primary": "#c69c76", "primary-text": "#d8b794",
        "dark": "#070b11", "divider": "#2b3647", "overlay": "#ffffff",
        "on-dark": "#9aa6b5", "on-primary": "#121821",
    })),
    ("colors-8-graphite", ("Graphite", {
        "base": "#161616", "surface": "#1e1e1e", "panel": "#232323",
        "contrast": "#f3f1ee", "copy": "#bdb8b1", "muted": "#aaa59f",
        "accent": "#b08a6a", "primary": "#b88f6c", "primary-text": "#d2ae8d",
        "dark": "#0a0a0a", "divider": "#333333", "overlay": "#ffffff",
        "on-dark": "#9c968f", "on-primary": "#161616",
    })),
])

DEFAULT_COLORS = "colors-1-south"

# Typography. Open Sans is the design's only face; the presets add one serif
# and one geometric sans for headings, plus the system stack. Weights are the
# ones theme.json declares and .dev/build-fonts.mjs puts in assets/fonts/.
LATIN = ("U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,"
         "U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD")
LATIN_EXT = ("U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,"
             "U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF")

FAMILIES = collections.OrderedDict([
    ("open-sans", ("Open Sans", "'Open Sans', system-ui, -apple-system, 'Segoe UI', sans-serif",
                   [(w, s) for w in ("300", "400", "600", "700") for s in ("latin", "latin-ext")])),
    ("playfair-display", ("Playfair Display", "'Playfair Display', Georgia, 'Times New Roman', serif",
                          [("400", "latin"), ("700", "latin")])),
    ("montserrat", ("Montserrat", "Montserrat, system-ui, -apple-system, 'Segoe UI', sans-serif",
                    [("400", "latin"), ("600", "latin"), ("700", "latin")])),
    ("system", ("System", "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif", [])),
])

TYPE_SETS = collections.OrderedDict([
    ("type-1-open-sans", ("Open Sans", "open-sans", "open-sans")),
    ("type-2-playfair", ("Playfair Display headings, Open Sans text", "playfair-display", "open-sans")),
    ("type-3-montserrat", ("Montserrat headings, Open Sans text", "montserrat", "open-sans")),
    ("type-4-montserrat-all", ("Montserrat throughout", "montserrat", "montserrat")),
    ("type-5-system", ("System fonts", "system", "system")),
])


def fluid(minimum, maximum):
    return collections.OrderedDict([("min", minimum), ("max", maximum)])


# The template's scale: 12/14/18/30/60, plus the 24 of prices and the realtor
# name and the 48 of the call to action. In px, as the design is drawn.
FONT_SIZES = [
    ("Tiny", "x-small", "12px", None),
    ("Small", "small", "14px", None),
    ("Medium", "medium", "18px", None),
    ("Large", "large", "24px", fluid("20px", "24px")),
    ("X Large", "x-large", "30px", fluid("24px", "30px")),
    ("XX Large", "xx-large", "48px", fluid("24px", "48px")),
    ("Huge", "huge", "60px", fluid("30px", "60px")),
]

# Spacing: the template's 10/15/30/50/70/100. Section padding is 100px at
# every width in the design, so 70 is fixed rather than fluid.
SPACING = [
    ("20", "10px"),
    ("30", "15px"),
    ("40", "30px"),
    ("50", "50px"),
    ("60", "70px"),
    ("70", "100px"),
]

# Every foreground/background pair the design puts together.
CONTRAST_CHECKS = [
    ("contrast", "base"), ("contrast", "surface"), ("contrast", "panel"),
    ("copy", "base"), ("copy", "surface"), ("copy", "panel"),
    ("muted", "base"), ("muted", "panel"),
    ("primary-text", "base"), ("primary-text", "surface"), ("primary-text", "panel"),
    # Labels on fills: buttons, FOR SALE tags, the search tab, pagination.
    ("on-primary", "primary"),
    # Hovered buttons and the title band: the label on `dark`.
    ("overlay", "dark"),
    # The footer's body copy.
    ("on-dark", "dark"),
]
# Non-text: a button or tag must be visible as a shape (WCAG 1.4.11).
BOUNDARY_CHECKS = [("primary", "base")]


# ---------------------------------------------------------------------------
# Contrast
# ---------------------------------------------------------------------------
def _channel(value):
    value = value / 255
    return value / 12.92 if value <= 0.04045 else ((value + 0.055) / 1.055) ** 2.4


def luminance(hex_colour):
    r, g, b = (int(hex_colour[i:i + 2], 16) for i in (1, 3, 5))
    return 0.2126 * _channel(r) + 0.7152 * _channel(g) + 0.0722 * _channel(b)


def contrast_ratio(a, b):
    la, lb = luminance(a), luminance(b)
    return (max(la, lb) + 0.05) / (min(la, lb) + 0.05)


def mix_with_white(hex_colour, percent):
    """CSS `color-mix(in srgb, <colour> <percent>%, white)`, per channel."""
    channels = [int(hex_colour[i:i + 2], 16) for i in (1, 3, 5)]
    share = percent / 100
    return "#" + "".join("%02x" % round(c * share + 255 * (1 - share)) for c in channels)


# ---------------------------------------------------------------------------
# theme.json
# ---------------------------------------------------------------------------
def od(*pairs):
    return collections.OrderedDict(pairs)


def var(slug):
    if slug not in {s for _, s in PALETTE}:
        raise SystemExit("colour %r is not in the palette" % slug)
    return "var(--wp--preset--color--%s)" % slug


def fs(slug):
    if slug not in {s for _, s, _, _ in FONT_SIZES}:
        raise SystemExit("font size %r is not on the scale" % slug)
    return "var(--wp--preset--font-size--%s)" % slug


def ff(slug):
    return "var(--wp--preset--font-family--%s)" % slug


def sp(slug):
    valid = {s for s, _ in SPACING}
    if slug not in valid:
        raise SystemExit("spacing %r is not on the scale %s" % (slug, sorted(valid)))
    return "var(--wp--preset--spacing--%s)" % slug


def palette(colors):
    return [od(("name", name), ("slug", slug), ("color", colors[slug])) for name, slug in PALETTE]


def font_families():
    out = []
    for key, (name, stack, faces) in FAMILIES.items():
        entry = od(("name", name), ("slug", key), ("fontFamily", stack))
        if faces:
            entry["fontFace"] = [od(
                ("fontFamily", name), ("fontStyle", "normal"), ("fontWeight", weight),
                ("fontDisplay", "swap"),
                ("src", ["file:./assets/fonts/%s-%s-%s-normal.woff2" % (key, subset, weight)]),
                ("unicodeRange", LATIN if subset == "latin" else LATIN_EXT),
            ) for weight, subset in faces]
        out.append(entry)
    return out


def build_settings():
    return od(
        ("appearanceTools", True),
        ("useRootPaddingAwareAlignments", True),
        # The template's container: 1140px with 15px gutters, so 1110px of
        # content, which is also what a 15px root padding leaves at 390px.
        ("layout", od(("contentSize", "1110px"), ("wideSize", "1140px"))),
        ("color", od(("custom", True), ("defaultPalette", False), ("defaultGradients", False),
                     ("defaultDuotone", False),
                     ("palette", palette(COLOR_SETS[DEFAULT_COLORS][1])))),
        ("typography", od(
            ("fluid", True), ("customFontSize", True), ("defaultFontSizes", False),
            ("fontFamilies", font_families()),
            ("fontSizes", [od(("name", name), ("slug", slug), ("size", size)) if not f else
                           od(("name", name), ("slug", slug), ("size", size), ("fluid", f))
                           for name, slug, size, f in FONT_SIZES]),
        )),
        ("spacing", od(("units", ["px", "em", "rem", "vh", "vw", "%"]),
                       ("padding", True), ("margin", True), ("blockGap", True),
                       ("defaultSpacingSizes", False),
                       ("spacingSizes", [od(("name", name), ("slug", name), ("size", size))
                                         for name, size in SPACING]))),
        ("border", od(("color", True), ("radius", True), ("style", True), ("width", True))),
        ("shadow", od(("defaultPresets", False), ("presets", [
            od(("name", "Search box"), ("slug", "search"), ("shadow", "0 5px 30px rgba(0, 0, 0, 0.15)")),
            od(("name", "Card hover"), ("slug", "card"), ("shadow", "0 0 50px rgba(0, 0, 0, 0.1)")),
        ]))),
    )


def build_styles():
    return od(
        ("color", od(("background", var("base")), ("text", var("copy")))),
        # Body copy in the design is 14px, weight 600, line height 2.
        ("typography", od(("fontFamily", ff("open-sans")), ("fontSize", fs("small")),
                          ("fontWeight", "600"), ("lineHeight", "2"))),
        ("spacing", od(("blockGap", "16px"),
                       # Units required: WP 7's navigation overlay reads these
                       # inside clamp(), and a unitless 0 voids the declaration.
                       ("padding", od(("left", "15px"), ("right", "15px"),
                                      ("top", "0px"), ("bottom", "0px"))))),
        ("elements", od(
            ("heading", od(("typography", od(("fontFamily", ff("open-sans")), ("fontWeight", "600"),
                                             ("lineHeight", "1.2"))),
                           ("color", od(("text", var("contrast")))))),
            ("h1", od(("typography", od(("fontSize", fs("x-large")))))),
            ("h2", od(("typography", od(("fontSize", fs("x-large")))))),
            ("h3", od(("typography", od(("fontSize", fs("medium")))))),
            ("h4", od(("typography", od(("fontSize", "20px"))))),
            ("h5", od(("typography", od(("fontSize", "16px"))))),
            ("h6", od(("typography", od(("fontSize", fs("small")))))),
            ("link", od(("color", od(("text", var("primary-text")))),
                        ("typography", od(("textDecoration", "none"))),
                        (":hover", od(("color", od(("text", var("contrast")))))))),
            ("button", od(
                ("color", od(("background", var("primary")), ("text", var("on-primary")))),
                ("typography", od(("fontSize", fs("small")), ("fontWeight", "600"),
                                  ("lineHeight", "50px"), ("textTransform", "uppercase"))),
                ("border", od(("radius", "0px"), ("width", "0px"))),
                ("spacing", od(("padding", od(("top", "0px"), ("bottom", "0px"),
                                              ("left", "30px"), ("right", "30px"))))),
                (":hover", od(("color", od(("background", var("dark")), ("text", var("overlay")))))),
                (":focus", od(("color", od(("background", var("dark")), ("text", var("overlay")))))),
            )),
            ("caption", od(("typography", od(("fontSize", fs("x-small")))),
                           ("color", od(("text", var("muted")))))),
        )),
        ("blocks", od(
            ("core/separator", od(("color", od(("text", var("divider")))))),
            ("core/post-title", od(("elements", od(("link", od(
                ("color", od(("text", "inherit"))),
                (":hover", od(("color", od(("text", var("primary-text")))))),
            )))))),
            ("core/post-date", od(("typography", od(("fontSize", fs("small")))),
                                  ("color", od(("text", var("primary-text")))))),
            ("core/navigation", od(("typography", od(("fontSize", fs("small")), ("fontWeight", "600"),
                                                     ("textTransform", "uppercase"))))),
            ("core/quote", od(("typography", od(("fontStyle", "italic"))),
                              ("border", od(("left", od(("color", var("accent")), ("width", "3px"),
                                                        ("style", "solid"))))),
                              ("spacing", od(("padding", od(("left", sp("40")))))))),
            ("core/table", od(("typography", od(("fontSize", fs("small")))))),
            ("core/code", od(("color", od(("background", var("panel")))))),
        )),
    )


def build_theme():
    return od(
        ("$schema", "https://schemas.wp.org/trunk/theme.json"),
        ("version", 3),
        ("settings", build_settings()),
        ("styles", build_styles()),
        ("customTemplates", [
            od(("name", "page-no-title"), ("title", "Page without banner"), ("postTypes", ["page"])),
            od(("name", "page-with-sidebar"), ("title", "Page with sidebar"), ("postTypes", ["page"])),
        ]),
        ("templateParts", [
            od(("name", "header"), ("title", "Header"), ("area", "header")),
            od(("name", "footer"), ("title", "Footer"), ("area", "footer")),
            od(("name", "sidebar"), ("title", "Journal sidebar"), ("area", "uncategorized")),
        ]),
    )


def build_color_variation(name, colors):
    # A colour partial must contain NOTHING but colour, or the Site Editor
    # stops listing it under Colors (Academia's lesson).
    return od(
        ("$schema", "https://schemas.wp.org/trunk/theme.json"),
        ("version", 3), ("title", name),
        ("settings", od(("color", od(("palette", palette(colors)))))),
    )


def build_type_variation(name, heading, body):
    return od(
        ("$schema", "https://schemas.wp.org/trunk/theme.json"),
        ("version", 3), ("title", name),
        ("styles", od(
            ("typography", od(("fontFamily", ff(body)))),
            ("elements", od(("heading", od(("typography", od(("fontFamily", ff(heading)))))))),
        )),
    )


def write(path, data):
    os.makedirs(os.path.dirname(path) or ".", exist_ok=True)
    with open(path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, indent="\t", ensure_ascii=False)
        handle.write("\n")
    return path


# ---------------------------------------------------------------------------
# Audit
# ---------------------------------------------------------------------------
def dark_scheme():
    """What assets/css/scheme.css does to the palette, read from the file.

    Read rather than restated, so the two cannot drift; and it refuses any
    colour slug the palette does not define.
    """
    css = open("assets/css/scheme.css", encoding="utf-8").read()
    css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
    defined = {slug for _, slug in PALETTE}
    referenced = set(re.findall(r"var\(--wp--preset--color--([a-z0-9-]+)\)", css))
    referenced |= set(re.findall(r"--wp--preset--color--([a-z0-9-]+)\s*:", css))
    unknown = sorted(referenced - defined)
    if unknown:
        raise SystemExit("scheme.css names colour slugs the palette does not define: %s" % ", ".join(unknown))

    fixed = dict((slug, value.lower()) for slug, value in
                 re.findall(r"--wp--preset--color--([a-z0-9-]+):\s*(#[0-9a-fA-F]{6})\s*;", css))
    mixes = dict((slug, (src, int(pct))) for slug, src, pct in re.findall(
        r"--wp--preset--color--([a-z0-9-]+):\s*color-mix\(in srgb,\s*var\(--wp--preset--color--([a-z0-9-]+)\)\s*(\d+)%,\s*white\)",
        css))
    for need in ("base", "surface", "panel", "contrast", "copy", "muted"):
        if need not in fixed:
            raise SystemExit("scheme.css: cannot read the dark-mode `%s`" % need)
    for need in ("primary-text", "primary"):
        if need not in mixes:
            raise SystemExit("scheme.css: cannot read the dark-mode `%s` mix" % need)
    if "on-primary" not in fixed:
        raise SystemExit("scheme.css: cannot read the dark-mode `on-primary`")
    return fixed, mixes


def audit():
    problems = []
    print("  palette      worst text pair            label   boundary")
    for slug, (name, colors) in COLOR_SETS.items():
        worst = (99, "")
        for fg, bg in CONTRAST_CHECKS:
            ratio = contrast_ratio(colors[fg], colors[bg])
            if ratio < worst[0]:
                worst = (ratio, "%s/%s" % (fg, bg))
            if ratio < 4.5:
                problems.append("%s: %s on %s is %.2f" % (name, fg, bg, ratio))
        for fg, bg in BOUNDARY_CHECKS:
            ratio = contrast_ratio(colors[fg], colors[bg])
            if ratio < 3:
                problems.append("%s: %s against %s is %.2f (boundary, needs 3)" % (name, fg, bg, ratio))
        print("  %-12s %5.2f %-22s %6.2f  %6.2f" % (
            name, worst[0], worst[1], contrast_ratio(colors["on-primary"], colors["primary"]),
            contrast_ratio(colors["primary"], colors["base"])))

    fixed, mixes = dark_scheme()
    src, pct = mixes["primary-text"]
    fsrc, fpct = mixes["primary"]
    print("\n  dark mode, as scheme.css applies it (primary-text = %s %d%% + white, primary = %s %d%% + white):"
          % (src, pct, fsrc, fpct))
    print("  palette      text/base  text/surface  fill/base  label")
    for slug, (name, colors) in COLOR_SETS.items():
        lifted = mix_with_white(colors[src], pct)
        fill = mix_with_white(colors[fsrc], fpct)
        rows = (
            ("primary-text on base", contrast_ratio(lifted, fixed["base"]), 4.5),
            ("primary-text on surface", contrast_ratio(lifted, fixed["surface"]), 4.5),
            ("button fill against the page", min(contrast_ratio(fill, fixed["base"]),
                                                  contrast_ratio(fill, fixed["surface"])), 3.0),
            ("button label on its fill", contrast_ratio(fixed["on-primary"], fill), 4.5),
        )
        for label, value, need in rows:
            if value < need:
                problems.append("%s (dark): %s is %.2f, needs %.1f" % (name, label, value, need))
        print("  %-12s %9.2f  %12.2f  %9.2f  %5.2f" % ((name,) + tuple(v for _, v, _ in rows)))
    for fg, bg in (("contrast", "base"), ("contrast", "surface"), ("contrast", "panel"),
                   ("copy", "base"), ("copy", "surface"), ("copy", "panel"),
                   ("muted", "base"), ("muted", "panel")):
        ratio = contrast_ratio(fixed[fg], fixed[bg])
        if ratio < 4.5:
            problems.append("dark: %s on %s is %.2f" % (fg, bg, ratio))

    print("\n  for the record: the template's decorative #947054 is %.2f:1 on white;"
          % contrast_ratio("#947054", "#ffffff"))
    print("  it ships as `accent`, never as text or as a fill under a label.")
    if problems:
        raise SystemExit("\nContrast failures:\n  " + "\n  ".join(problems))


def main():
    audit()
    written = [write("theme.json", build_theme())]
    for slug, (name, colors) in COLOR_SETS.items():
        if slug == DEFAULT_COLORS:
            # The default is theme.json itself; a variation that repeats it
            # is still listed, so the Site Editor can return to it by name.
            pass
        written.append(write("styles/colors/%s.json" % slug, build_color_variation(name, colors)))
    for slug, (name, heading, body) in TYPE_SETS.items():
        written.append(write("styles/typography/%s.json" % slug, build_type_variation(name, heading, body)))
    print("\n  %d files written" % len(written))


if __name__ == "__main__":
    main()
