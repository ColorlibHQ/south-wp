#!/usr/bin/env python3
"""Generate South's patterns, template parts and templates.

Run from the theme root:

    python3 .dev/build_patterns.py
    node .dev/normalize-blocks.mjs      # then let the editor re-serialise the patterns
    node .dev/validate-blocks.mjs       # and refuse anything it calls invalid

Every file in patterns/, parts/ and templates/ is committed as generated.
Edit this file, never those.

Vocabulary every section shares (all styled in style.css, all checked by
.dev/dead-selectors.py):

  south-section-head / -title / -sub   the centred uppercase title + italic line
  south-band                           white uppercase words on the black band
  south-card / __media / __tag / __price / __body / __title / __location /
  __excerpt / __facts                  the property card
  south-fact--{new,beds,baths,garages,area}   one fact, its icon drawn by CSS
  south-line--{phone,mail,pin}         a contact line with its icon
  south-requires-listings              rendered only when the South Listings
                                       plugin's `property` type exists
  south-without-listings               rendered only when it does not

Dynamic content is never PHP inside a pattern: activation expands patterns
into post content, where PHP never runs again (Academia froze "No courses yet"
into its home page that way). Listings come from core/query loops with block
bindings to the plugin's `south/property` source, and the forms are
shortcodes the theme owns.
"""

import json
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from patternlib import (  # noqa: E402
    attrs, button, buttons, column, columns, cover, group, heading, image,
    paragraph, shortcode,
)

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PATTERNS = os.path.join(ROOT, "patterns")
WRITTEN = []

SECTIONS = ["south-sections"]
LISTINGS = ["south-listings"]
PAGES = ["south-pages"]


def write(slug, title, content, categories=None, keywords=None,
          description=None, inserter=True, block_types=None, post_types=None):
    header = ["Title: " + title, "Slug: south/" + slug]
    if categories:
        header.append("Categories: " + ", ".join(categories))
    if keywords:
        header.append("Keywords: " + ", ".join(keywords))
    if block_types:
        header.append("Block Types: " + ", ".join(block_types))
    if post_types:
        header.append("Post Types: " + ", ".join(post_types))
    if description:
        header.append("Description: " + description)
    if not inserter:
        header.append("Inserter: no")

    body = (
        "<?php\n/**\n * " + "\n * ".join(header) + "\n *\n * @package South\n */\n\n"
        "defined( 'ABSPATH' ) || exit;\n?>\n" + content.strip() + "\n"
    )
    with open(os.path.join(PATTERNS, slug + ".php"), "w") as fh:
        fh.write(body)
    WRITTEN.append(slug)


def u(path):
    """An address on this site, resolved when the pattern renders."""
    return "<?php echo esc_url( home_url( '%s' ) ); ?>" % path


def raw(name, data=None, inner=None):
    """A block comment written directly, for blocks with no saved markup."""
    if inner is None:
        return "<!-- wp:%s%s /-->" % (name, attrs(data or {}))
    return "<!-- wp:%s%s -->\n%s\n<!-- /wp:%s -->" % (name, attrs(data or {}), inner, name)


def flex(inner, justify=None, gap=None, wrap="wrap", vertical=None, extra_class=None, tag="div"):
    """A horizontal group (patternlib's group() has no vertical alignment)."""
    layout = {"type": "flex", "flexWrap": wrap}
    if justify:
        layout["justifyContent"] = justify
    if vertical:
        layout["verticalAlignment"] = vertical
    data = {}
    if extra_class:
        data["className"] = extra_class
    if gap:
        data["style"] = {"spacing": {"blockGap": "var:preset|spacing|" + gap}}
    data["layout"] = layout
    if tag != "div":
        data["tagName"] = tag
    cls = "wp-block-group" + (" " + extra_class if extra_class else "")
    return '<!-- wp:group %s -->\n<%s class="%s">\n%s\n</%s>\n<!-- /wp:group -->' % (
        json.dumps(data, separators=(",", ":")), tag, cls, inner, tag
    )


def section(inner, top="70", bottom="70", background=None, extra_class=None, anchor=None):
    """A full-width band. 70 is the design's 100px; 50 its 50px."""
    padding = {}
    if top:
        padding["top"] = top
    if bottom:
        padding["bottom"] = bottom
    return group(inner, align="full", background=background, padding=padding or None,
                 layout="constrained", extra_class=("south-section " + (extra_class or "")).strip(),
                 anchor=anchor)


def section_head(title, sub=None, align="center", level=2):
    parts = [heading(title, level=level, align=align, extra_class="south-section-title")]
    if sub:
        parts.append(paragraph(sub, align=align, extra_class="south-section-sub"))
    return group("\n".join(parts), layout="default", extra_class="south-section-head")


def line(kind, text):
    """A contact line; its icon is drawn from the class, so it shows in the editor too."""
    return paragraph(text, extra_class="south-line south-line--" + kind)


def icon(name):
    """An icon on its own line. An empty paragraph with a blank placeholder, so the
    editor draws the icon instead of "Type / to choose a block" (Unioncorp 1.1.2)."""
    return paragraph("", extra_class="south-icon south-icon--" + name, placeholder=" ")


# ---------------------------------------------------------------------------
# The property card: a core/query loop whose words come from block bindings
# ---------------------------------------------------------------------------
def bound(key, sample, cls, tag="p"):
    """A paragraph whose content is the plugin's formatted `key` for the post in
    context. `sample` is what shows wherever the source is not available."""
    data = {"metadata": {"bindings": {"content": {"source": "south/property", "args": {"key": key}}}},
            "className": cls}
    return '<!-- wp:paragraph %s -->\n<p class="%s">%s</p>\n<!-- /wp:paragraph -->' % (
        json.dumps(data, separators=(",", ":")), cls, sample)


def card(excerpt=True):
    media = group("\n".join([
        raw("post-featured-image", {"aspectRatio": "1000/724", "className": "south-card__image"}),
        bound("status", "For Sale", "south-card__tag"),
        bound("price", "$945 679", "south-card__price"),
    ]), layout="default", extra_class="south-card__media")
    facts = flex("\n".join([
        bound("new", "", "south-fact south-fact--new"),
        bound("beds", "2", "south-fact south-fact--beds"),
        bound("baths", "2", "south-fact south-fact--baths"),
        bound("garages", "1", "south-fact south-fact--garages"),
        bound("area", "1,200 sq ft", "south-fact south-fact--area"),
    ]), justify="space-between", wrap="nowrap", extra_class="south-card__facts")
    body = [
        raw("post-title", {"level": 3, "isLink": True, "className": "south-card__title"}),
        bound("address", "Upper Road 3411, Los Angeles, CA", "south-card__location"),
    ]
    if excerpt:
        body.append(raw("post-excerpt", {"moreText": "", "excerptLength": 24, "className": "south-card__excerpt"}))
    body.append(facts)
    return group(media + "\n" + group("\n".join(body), layout="default", extra_class="south-card__body"),
                 layout="default", extra_class="south-card")


def property_query(per_page=6, featured=True, inherit=False, extra_class="", query_id=11,
                   pagination=False, empty=None):
    query = {"perPage": per_page, "pages": 0, "offset": 0, "postType": "property", "order": "desc",
             "orderBy": "date", "inherit": inherit}
    if featured:
        # Read by the plugin's query_loop_block_query_vars filter. An extra key
        # in the query object survives the editor's serialiser.
        query["southFeatured"] = True
    data = {"queryId": query_id, "query": query, "className": ("south-properties " + extra_class).strip(),
            "layout": {"type": "default"}}
    inner = [raw("post-template", {"className": "south-properties__grid",
                                   "layout": {"type": "grid", "columnCount": 3}}, card())]
    if pagination:
        inner.append(raw("query-pagination", {"paginationArrow": "none", "showLabel": False,
                                              "className": "south-pagination",
                                              "layout": {"type": "flex", "justifyContent": "right"}},
                         raw("query-pagination-numbers")))
    inner.append(raw("query-no-results", None, paragraph(
        empty or "No homes match that search yet. Try fewer filters, or call the office: new homes often sell before they are listed.",
        extra_class="south-empty")))
    return '<!-- wp:query %s -->\n<div class="wp-block-query %s">%s</div>\n<!-- /wp:query -->' % (
        json.dumps(data, separators=(",", ":")), data["className"], "\n" + "\n".join(inner) + "\n")


# ---------------------------------------------------------------------------
# Parts
# ---------------------------------------------------------------------------
def build_header():
    topbar = flex("\n".join([
        paragraph('<a href="mailto:office@example.com">office@example.com</a>', extra_class="south-topbar__email"),
        flex("\n".join([
            paragraph("", extra_class="south-topbar__icon", placeholder=" "),
            paragraph('<a href="tel:+13105550140">+1 (310) 555-0140</a>', extra_class="south-topbar__number"),
        ]), wrap="nowrap", extra_class="south-topbar__phone"),
    ]), justify="space-between", wrap="nowrap", extra_class="south-topbar")

    brand = flex("\n".join([
        raw("site-logo", {"width": 136, "className": "south-logo"}),
        raw("site-title", {"level": 0, "className": "south-wordmark"}),
    ]), wrap="nowrap", extra_class="south-brand")
    # The switch needs words inside it: an empty core/button renders nothing.
    toggle = buttons([button('<span class="screen-reader-text">Switch between light and dark mode</span>', "#",
                             extra_class="south-scheme-toggle")])
    end = flex("\n".join([
        raw("navigation", {"overlayMenu": "mobile", "overlayBackgroundColor": "dark", "overlayTextColor": "overlay",
                           "className": "south-nav",
                           "layout": {"type": "flex", "justifyContent": "right", "flexWrap": "nowrap"}}),
        raw("search", {"label": "Search", "showLabel": False, "placeholder": "Search properties ...",
                       "buttonText": "Search", "buttonPosition": "button-only", "buttonUseIcon": True,
                       "isSearchFieldHidden": True, "className": "south-header-search"}),
        toggle,
    ]), wrap="nowrap", extra_class="south-navbar__end")
    navbar = flex(brand + "\n" + end, justify="space-between", wrap="nowrap", extra_class="south-navbar")
    write("header", "Header", group(topbar + "\n" + navbar, layout="default", extra_class="south-header"),
          keywords=["header", "navigation"], block_types=["core/template-part/header"],
          description="The black top bar with the office email and phone, then the logo, menu, search and the dark mode switch over the first photograph.")


def hours_rows():
    rows = [("Monday - Friday", "9 AM - 7 PM"), ("Saturday", "9 AM - 2 PM"), ("Sunday", "Closed")]
    return group("\n".join(flex(paragraph(day) + "\n" + paragraph(time), justify="space-between", wrap="nowrap",
                                extra_class="south-hours__row") for day, time in rows),
                 layout="default", extra_class="south-hours")


def office_lines():
    return group("\n".join([
        line("phone", '<a href="tel:+13105550140">+1 (310) 555-0140</a>'),
        line("mail", '<a href="mailto:office@example.com">office@example.com</a>'),
        line("pin", "Main Str. no 45-46, b3, Los Angeles, CA 90012"),
    ]), layout="default", extra_class="south-lines")


def build_footer():
    def title(text):
        return heading(text, level=2, extra_class="south-widget-title")

    links = [("Home", "/"), ("About us", "/about/"), ("Our realtors", "/about/#team"), ("Properties", "/listings/"),
             ("For sale", "/listings/?status=sale"), ("For rent", "/listings/?status=rent"),
             ("Testimonials", "/#testimonials"), ("Journal", "/blog/"), ("Contact", "/contact/"),
             ("Office hours", "/contact/#office-hours")]
    link_list = ('<!-- wp:list {"className":"south-links"} -->\n<ul class="wp-block-list south-links">'
                 + "".join('<!-- wp:list-item -->\n<li><a href="%s">%s</a></li>\n<!-- /wp:list-item -->' % (u(p), t)
                           for t, p in links)
                 + "</ul>\n<!-- /wp:list -->")

    featured = '<!-- wp:query %s -->\n<div class="wp-block-query south-footer-homes south-requires-listings">%s</div>\n<!-- /wp:query -->' % (
        json.dumps({"queryId": 31, "query": {"perPage": 3, "pages": 0, "offset": 0, "postType": "property",
                                             "order": "desc", "orderBy": "date", "inherit": False,
                                             "southFeatured": True},
                    "className": "south-footer-homes south-requires-listings", "layout": {"type": "default"}},
                   separators=(",", ":")),
        "\n" + raw("post-template", {"className": "south-slider__track"},
                   raw("post-featured-image", {"isLink": True, "aspectRatio": "258/333",
                                               "className": "south-footer-homes__image"})) + "\n")
    # Without the plugin there are no homes to show; the newest journal post
    # keeps the fourth column from going empty.
    journal = '<!-- wp:query %s -->\n<div class="wp-block-query south-footer-homes south-without-listings">%s</div>\n<!-- /wp:query -->' % (
        json.dumps({"queryId": 32, "query": {"perPage": 1, "pages": 0, "offset": 0, "postType": "post",
                                             "order": "desc", "orderBy": "date", "inherit": False},
                    "className": "south-footer-homes south-without-listings", "layout": {"type": "default"}},
                   separators=(",", ":")),
        "\n" + raw("post-template", None,
                   raw("post-featured-image", {"isLink": True, "aspectRatio": "258/333",
                                               "className": "south-footer-homes__image"})) + "\n")

    cols = columns([
        column("\n".join([
            title("About Us"),
            image("footer", "A low glass house lit up at dusk beside a rock pool, with trees behind",
                  ratio="792/471", extra_class="south-footer__photo"),
            raw("site-title", {"level": 0, "className": "south-wordmark south-wordmark--footer"}),
            paragraph("South is a small independent agency selling and letting homes across Los Angeles since 2009. "
                      "Valuations are free and come with a written report."),
        ])),
        column("\n".join([title("Hours"), hours_rows(), office_lines()])),
        column("\n".join([title("Useful Links"), link_list])),
        column("\n".join([title("Featured Properties"), featured, journal])),
    ], gap="40", extra_class="south-footer__columns")

    legal = group(paragraph('Copyright &copy;<?php echo esc_html( gmdate( \'Y\' ) ); ?> All rights reserved | '
                            'This theme is made with <span aria-hidden="true">&#9825;</span>'
                            '<span class="screen-reader-text">love</span> by '
                            '<a href="https://colorlib.com" rel="nofollow">Colorlib</a>', align="center"),
                  align="full", layout="constrained", extra_class="south-copyright")
    inner = group(cols, layout="constrained", extra_class="south-footer__main") + "\n" + legal
    write("footer", "Footer",
          cover(inner, "cta", dim=100, extra_class="south-footer",
                gradient="linear-gradient(180deg,rgb(0,0,0) 0%,rgba(0,0,0,0.9) 90%,rgba(0,0,0,0) 100%)"),
          keywords=["footer"], block_types=["core/template-part/footer"],
          description="Four columns on a darkened photograph: about, office hours, links and featured homes, with a copyright line.")


def build_sidebar():
    featured = group(
        heading("Featured home", level=2, extra_class="south-widget__title") + "\n"
        + property_query(per_page=3, extra_class="south-slider south-properties--single", query_id=41),
        layout="default", extra_class="south-widget south-requires-listings")
    inner = "\n".join([
        raw("search", {"label": "Search the journal", "showLabel": False, "placeholder": "Search the journal",
                       "buttonText": "Search", "buttonPosition": "button-inside", "buttonUseIcon": True,
                       "className": "south-sidebar-search"}),
        group(heading("Archive", level=2, extra_class="south-widget__title") + "\n" + raw("archives"),
              layout="default", extra_class="south-widget"),
        group(heading("Categories", level=2, extra_class="south-widget__title") + "\n" + raw("categories"),
              layout="default", extra_class="south-widget"),
        featured,
    ])
    write("sidebar", "Journal sidebar", group(inner, layout="default", extra_class="south-sidebar"),
          keywords=["sidebar"], inserter=False,
          description="Search, archive, categories and a featured home beside the journal.")


# ---------------------------------------------------------------------------
# Sections
# ---------------------------------------------------------------------------
def search_section():
    return group(shortcode("[south_property_search]"), align="full", layout="constrained",
                 extra_class="south-search-area")


def build_hero():
    slides = [
        ("hero1", 1, "Find your home"),
        ("hero2", 2, "Find your dream house"),
        ("hero3", 2, "Find your perfect house"),
    ]
    covers = "\n".join(
        cover(heading(text, level=level, align="center", extra_class="south-band"), slug, dim=0,
              extra_class="south-hero__slide")
        for slug, level, text in slides)
    hero = group(covers, align="full", layout="default", extra_class="south-hero south-slider--fade")
    write("hero", "Hero slider with property search", hero + "\n" + search_section(),
          categories=SECTIONS + LISTINGS, keywords=["hero", "slider", "search", "banner"],
          description="Three full-height photographs that fade from one to the next, each with an uppercase title on a black band, and the property search box overlapping their lower edge.")

    write("hero-single", "Hero photograph with property search",
          cover(heading("Find your home", level=1, align="center", extra_class="south-band"), "hero1", dim=0,
                extra_class="south-hero south-hero__slide") + "\n" + search_section(),
          categories=SECTIONS + LISTINGS, keywords=["hero", "banner", "search"],
          description="One full-height photograph with the title on a black band and the property search overlapping it.")


def build_search():
    write("property-search", "Property search box", search_section(),
          categories=SECTIONS + LISTINGS, keywords=["search", "filter", "properties"],
          description="The search box with its brown tab. Place it straight after a banner or photograph and it overlaps the edge. Without the South Listings plugin it searches the site.")


def build_featured():
    inner = section_head("Featured Properties", "Hand-picked homes for sale and to rent across Los Angeles.") + "\n" + property_query()
    write("featured-properties", "Featured properties grid",
          section(inner, bottom="50", extra_class="south-featured south-requires-listings"),
          categories=SECTIONS + LISTINGS, keywords=["properties", "listings", "featured", "grid"],
          description="Six homes marked Featured, three to a row, each card with its tag, price, address and facts. Needs the South Listings plugin; without it the section is not shown.")

    latest = section_head("New on the market", "The three most recent listings.") + "\n" + property_query(per_page=3, featured=False, query_id=12)
    write("latest-properties", "Latest properties",
          section(latest, bottom="50", extra_class="south-featured south-requires-listings"),
          categories=SECTIONS + LISTINGS, keywords=["properties", "latest", "new"],
          description="The three newest listings in one row. Needs the South Listings plugin.")


def build_cta():
    inner = "\n".join([
        heading("Are you looking for a place to rent?", level=2, align="center", extra_class="south-cta__title"),
        paragraph("Furnished lofts, apartments and townhouses, ready to move into.", align="center",
                  extra_class="south-cta__lead"),
        buttons([button("See rentals", u("/listings/?status=rent"))], align="center"),
    ])
    write("rent-cta", "Call to action: rentals",
          cover(group(inner, layout="constrained"), "cta", dim=70, parallax=True, extra_class="south-cta"),
          categories=SECTIONS, keywords=["call to action", "rent", "banner"],
          description="A fixed photograph under a dark veil with an uppercase question, an italic line and a button.")


def build_testimonials():
    quotes = [
        ("A fair price, quickly",
         "We sold our townhouse through South. The valuation came with a clear written report, the photographs "
         "were excellent and the first open house brought three offers.",
         "Grace Okafor", "Seller, Long Beach", "client-3"),
        ("Perfect Home for me",
         "We had looked at houses for a year. Maria listened to what we actually needed, showed us four places "
         "instead of forty, and the Tudor house in Pasadena was the second one. The paperwork was done in six weeks.",
         "Daiane Smith", "Home buyer, Pasadena", "client-1"),
        ("Honest about every flat",
         "Jeremy told us which apartments were noisy before we went to see them, which saved us whole weekends. "
         "We rented the loft in Santa Monica and the move-in checklist covered everything.",
         "Tom Alvarez", "Tenant, Santa Monica", "client-2"),
    ]
    cols = []
    for title, text, name, role, avatar in quotes:
        cols.append(column(group("\n".join([
            heading(title, level=3, align="center", extra_class="south-quote__title"),
            paragraph(text, align="center", extra_class="south-quote__text"),
            image(avatar, "", ratio="1/1", rounded="50%", width="50px", extra_class="south-quote__avatar"),
            paragraph("%s, <span>%s</span>" % (name, role), align="center", extra_class="south-quote__author"),
        ]), layout="default", extra_class="south-quote")))
    inner = section_head("Client testimonials", "What buyers and tenants said after they moved in.") + "\n" + group(
        columns(cols, gap="40", extra_class="south-quotes__track"), layout="default",
        extra_class="south-quotes south-slider--center")
    write("testimonials", "Testimonials",
          section(inner, extra_class="south-testimonials", anchor="testimonials"),
          categories=SECTIONS, keywords=["testimonials", "reviews", "quotes"],
          description="Three client quotes, the middle one in focus; the arrows bring the next one to the centre.")


def build_realtor():
    text = "\n".join([
        icon("award"),
        section_head("Jeremy Scott", "Realtor", align="left"),
        paragraph("I started South in 2009 with one desk and a borrowed car. Fifteen years later we are a team of "
                  "three realtors, and we still work the same way: we visit every home before we list it, we tell "
                  "buyers what we would want to know ourselves, and we answer the phone on Saturdays. If you are "
                  "thinking of buying, selling or renting in Los Angeles, call me directly or stop by the office "
                  "for a coffee."),
        group(line("phone", '<a href="tel:+13105550142">+1 (310) 555-0142</a>') + "\n"
              + line("mail", '<a href="mailto:jeremy@example.com">jeremy@example.com</a>'),
              layout="default", extra_class="south-lines"),
        image("signature", "Jeremy Scott's signature", ext="svg", width="126px", extra_class="south-signature"),
    ])
    cols = columns([
        column(text, width="50%", extra_class="south-editor__text"),
        column(image("editor", "Jeremy Scott, a bearded man in a dark suit and red tie, straightening his jacket",
                     extra_class="south-editor__photo"), width="50%", extra_class="south-editor__media"),
    ], align="full", extra_class="south-editor")
    write("realtor-note", "Realtor's note",
          group(cols, align="full", background="surface", layout="default", extra_class="south-editor-area"),
          categories=SECTIONS, keywords=["about", "founder", "realtor", "editor"],
          description="The founder's note on a pale band, with his number, email and signature beside his portrait.")


def build_about():
    left = "\n".join([
        section_head("We search for the perfect home", "An independent Los Angeles agency since 2009", align="left"),
        image("about", "A white stairwell with oak treads, black railings and brass pendant lights",
              extra_class="south-about__photo"),
        paragraph("South helps people buy, sell and rent homes across Los Angeles, from Malibu to Long Beach. We are "
                  "three realtors and an office manager, and we keep it that way on purpose: every client talks to "
                  "the same person from the first viewing to the day the keys change hands. We visit and photograph "
                  "every home ourselves, write the descriptions from what we saw, and publish the real room sizes. "
                  "Sellers get a free written valuation; buyers and tenants get honest notes about noise, light and "
                  "parking before they book a viewing."),
    ])
    right = "\n".join([
        section_head("Featured Properties", "New on the market this month", align="left"),
        property_query(per_page=4, extra_class="south-slider south-properties--single", query_id=21),
    ])
    inner = columns([column(left, width="65.77%"),
                     column(right, width="31.53%", extra_class="south-requires-listings")],
                    gap="40", extra_class="south-about")
    write("about", "About the agency",
          section(inner, extra_class="south-about-area"),
          categories=SECTIONS, keywords=["about", "agency", "story"],
          description="The agency's story with a photograph, beside a slider of featured homes (shown when South Listings is active).")


def build_team():
    people = [
        ("team1", "Jeremy Scott", "+1 (310) 555-0142", "jeremy@example.com",
         "Jeremy Scott, a bearded man in a dark suit and red tie"),
        ("team2", "Maria Williams", "+1 (310) 555-0157", "maria@example.com",
         "Maria Williams, smiling in glasses and a pale blue jacket as she reads her phone"),
        ("team3", "Patrick Joe", "+1 (310) 555-0168", "patrick@example.com",
         "Patrick Joe, smiling in a black suit and tie on a city street"),
    ]
    cols = []
    for slug, name, phone, mail, alt in people:
        tel = "+1" + "".join(c for c in phone if c.isdigit())[1:]
        cols.append(column("\n".join([
            image(slug, alt, ratio="1/1", extra_class="south-member__photo"),
            group("\n".join([
                icon("award"),
                heading(name, level=3, align="center", extra_class="south-member__name"),
                paragraph("Realtor", align="center", extra_class="south-section-sub"),
                group(line("phone", '<a href="tel:%s">%s</a>' % (tel, phone)) + "\n"
                      + line("mail", '<a href="mailto:%s">%s</a>' % (mail, mail)),
                      layout="default", extra_class="south-lines south-lines--center"),
            ]), layout="default", extra_class="south-member__info"),
        ]), extra_class="south-member"))
    inner = section_head("Meet The Team", "Call your realtor directly") + "\n" + columns(cols, gap="40", extra_class="south-team")
    write("team", "Team",
          section(inner, bottom="70", extra_class="south-team-area", anchor="team"),
          categories=SECTIONS, keywords=["team", "realtors", "agents", "staff"],
          description="Three realtors with portraits, direct numbers and email addresses.")


def map_block(address, note, embed, link):
    return (
        '<!-- wp:html -->\n'
        '<div class="south-map" data-map-src="%s">'
        '<div class="south-map__placeholder">'
        '<p class="south-map__address">%s<span>%s</span></p>'
        '<div class="south-map__actions"><button type="button" class="wp-element-button south-map__show" hidden>Show map</button>'
        '<a href="%s">Open in maps</a></div>'
        '</div></div>\n'
        '<!-- /wp:html -->' % (embed, address, note, link)
    )


def build_contact():
    left = "\n".join([hours_rows(), office_lines()])
    inner = "\n".join([
        heading("Contact info", level=2, extra_class="south-underline-title"),
        columns([column(group(left, layout="default", extra_class="south-office", anchor="office-hours"), width="31.53%"),
                 column(shortcode("[south_enquiry_form]"), width="65.77%")],
                gap="40", extra_class="south-contact"),
    ])
    write("contact", "Contact: office hours and form",
          section(inner, extra_class="south-contact-area"),
          categories=SECTIONS, keywords=["contact", "form", "hours", "office"],
          description="Office hours, phone, email and address beside the enquiry form.")

    office_map = map_block(
        "Main Str. no 45-46, b3, Los Angeles, CA 90012",
        "The office is a short walk from the Civic Center Metro station. The map loads from OpenStreetMap when you ask for it.",
        "https://www.openstreetmap.org/export/embed.html?bbox=-118.2637%2C34.0385%2C-118.2253%2C34.0625&amp;layer=mapnik",
        "https://www.openstreetmap.org/#map=15/34.0505/-118.2445")
    write("map", "Map (loads on request)",
          section(office_map, top=None, bottom="70", extra_class="south-map-area"),
          categories=SECTIONS, keywords=["map", "location", "openstreetmap"],
          description="An address card with a Show map button: nothing is loaded from OpenStreetMap until a visitor asks for it, and Open in maps works without JavaScript.")


def build_journal():
    query = '<!-- wp:query %s -->\n<div class="wp-block-query south-journal">%s</div>\n<!-- /wp:query -->' % (
        json.dumps({"queryId": 51, "query": {"perPage": 3, "pages": 0, "offset": 0, "postType": "post",
                                             "order": "desc", "orderBy": "date", "inherit": False},
                    "className": "south-journal", "layout": {"type": "default"}}, separators=(",", ":")),
        "\n" + raw("post-template", {"layout": {"type": "grid", "columnCount": 3}}, group("\n".join([
            raw("post-featured-image", {"isLink": True, "aspectRatio": "730/346"}),
            raw("post-date", {"className": "south-post__date"}),
            raw("post-title", {"level": 3, "isLink": True, "className": "south-journal__title"}),
            raw("post-excerpt", {"moreText": "", "excerptLength": 22}),
            raw("read-more", {"content": "Read More", "className": "south-button"}),
        ]), layout="default", extra_class="south-journal__item")) + "\n"
        + raw("query-no-results", None, paragraph("The journal is empty for now.", extra_class="south-empty")) + "\n")
    inner = section_head("From the journal", "Notes on buying, renting and the neighbourhoods we know.") + "\n" + query
    write("journal", "Journal: latest three posts",
          section(inner, extra_class="south-journal-area"),
          categories=SECTIONS, keywords=["blog", "journal", "posts", "news"],
          description="The three newest journal posts, each with its photograph, date, title and a Read More button.")


# ---------------------------------------------------------------------------
# Hidden patterns: the pieces templates are built from
# ---------------------------------------------------------------------------
def banner(inner_block, slug="hero1"):
    return cover(inner_block, slug, dim=0, extra_class="south-banner")


def build_banners():
    def band_heading(text):
        return heading(text, level=1, align="center", extra_class="south-band")

    def band_p(text):
        return paragraph(text, align="center", extra_class="south-band")

    write("hidden-banner-page", "Page banner",
          banner(raw("post-title", {"level": 1, "textAlign": "center", "className": "south-band"})),
          inserter=False, description="The photograph a page title sits on, on its black band.")
    write("hidden-banner-archive", "Archive banner",
          banner(raw("query-title", {"type": "archive", "showPrefix": False, "textAlign": "center", "className": "south-band"})),
          inserter=False, description="The photograph an archive title sits on.")
    write("hidden-banner-search", "Search banner",
          banner(raw("query-title", {"type": "search", "textAlign": "center", "className": "south-band"})),
          inserter=False, description="The photograph search results sit under.")
    write("hidden-banner-blog", "Journal banner", banner(band_heading("Blog")),
          inserter=False, description="The heading of the posts page.")
    write("hidden-banner-post", "Post banner", banner(band_p("Blog Details")),
          inserter=False, description="The banner over a single post; the post title below is the page's heading.")
    write("hidden-banner-listings", "Listings banner", banner(band_heading("Listings")),
          inserter=False, description="The banner over the property listings.")
    write("hidden-banner-property", "Property banner", banner(band_p("Property")),
          inserter=False, description="The banner over a single property; its title below is the page's heading.")
    write("hidden-banner-404", "Not found banner", banner(band_heading("Not found")),
          inserter=False, description="The banner over the not-found page.")


def post_meta():
    return flex("\n".join([
        raw("post-author", {"showAvatar": False, "byline": "By", "isLink": True, "className": "south-post__author"}),
        raw("post-terms", {"term": "category", "prefix": "in ", "className": "south-post__terms"}),
    ]), wrap="wrap", extra_class="south-post__meta")


def build_hidden():
    posts = '<!-- wp:query %s -->\n<div class="wp-block-query south-posts">%s</div>\n<!-- /wp:query -->' % (
        json.dumps({"queryId": 0, "query": {"perPage": 6, "pages": 0, "offset": 0, "postType": "post",
                                            "order": "desc", "orderBy": "date", "inherit": True},
                    "className": "south-posts", "layout": {"type": "default"}}, separators=(",", ":")),
        "\n" + raw("post-template", None, group("\n".join([
            raw("post-featured-image", {"isLink": True, "aspectRatio": "730/346", "className": "south-post__image"}),
            group("\n".join([
                raw("post-date", {"isLink": True, "className": "south-post__date"}),
                raw("post-title", {"level": 2, "isLink": True, "className": "south-post__title"}),
                post_meta(),
                raw("post-excerpt", {"moreText": "", "excerptLength": 30, "className": "south-post__excerpt"}),
                raw("read-more", {"content": "Read More", "className": "south-button"}),
            ]), layout="default", extra_class="south-post__body"),
        ]), layout="default", extra_class="south-post")) + "\n"
        + raw("query-pagination", {"paginationArrow": "none", "showLabel": False, "className": "south-pagination",
                                   "layout": {"type": "flex", "justifyContent": "left"}},
              raw("query-pagination-numbers")) + "\n"
        + raw("query-no-results", None, paragraph("Nothing here yet. Try a search, or start again from the home page.",
                                                   extra_class="south-empty")) + "\n")
    write("hidden-posts-list", "Journal list", posts, inserter=False,
          description="The post list used by the journal, archives and search.")

    write("hidden-post-meta", "Post meta", post_meta(), inserter=False,
          description="Author and categories under a post title.")

    comments = (
        '<!-- wp:comments {"className":"south-comments"} -->\n'
        '<div class="wp-block-comments south-comments">\n'
        '<!-- wp:comments-title {"level":2} /-->\n'
        '<!-- wp:comment-template -->\n'
        + flex("\n".join([
            raw("avatar", {"size": 60, "className": "south-comment__avatar"}),
            group("\n".join([
                flex("\n".join([
                    raw("comment-author-name", {"className": "south-comment__name"}),
                    raw("comment-date", {"format": "M j, Y", "className": "south-comment__date"}),
                    raw("comment-reply-link", {"className": "south-comment__reply"}),
                ]), wrap="wrap", extra_class="south-comment__meta"),
                raw("comment-content", {"className": "south-comment__content"}),
            ]), layout="default", extra_class="south-comment__body"),
        ]), wrap="nowrap", vertical="top", extra_class="south-comment") + "\n"
        '<!-- /wp:comment-template -->\n'
        '<!-- wp:comments-pagination {"layout":{"type":"flex","justifyContent":"left"}} -->\n'
        '<!-- wp:comments-pagination-numbers /-->\n'
        '<!-- /wp:comments-pagination -->\n'
        '<!-- wp:post-comments-form {"className":"south-comment-form"} /-->\n'
        '</div>\n'
        '<!-- /wp:comments -->'
    )
    write("hidden-comments", "Comments", comments, inserter=False,
          description="The comments and the reply form under a single post.")

    notfound = "\n".join([
        heading("That page has moved", level=2, align="center", extra_class="south-section-title"),
        paragraph("The address may be old, or the home may have been sold and its page taken down. "
                  "Search the site, or start again from the home page.", align="center"),
        raw("search", {"label": "Search", "showLabel": False, "placeholder": "Search the site", "buttonText": "Search",
                       "buttonPosition": "button-inside", "buttonUseIcon": True, "className": "south-sidebar-search"}),
        buttons([button("Back to the home page", u("/"))], align="center"),
    ])
    write("hidden-404", "Not found content",
          section(group(notfound, layout="constrained", content_size="620px"), extra_class="south-404"),
          inserter=False, description="What a visitor sees when nothing is at the address.")

    results = group("\n".join([
        shortcode("[south_property_toolbar]"),
        property_query(per_page=6, featured=False, inherit=True, query_id=1, pagination=True),
    ]), layout="default", extra_class="south-results")
    write("hidden-listings-results", "Listing results",
          section(results, extra_class="south-listings"), inserter=False,
          description="The results line, view and order controls, the cards and the page numbers.")

    body = "\n".join([
        shortcode('[south_property part="gallery"]'),
        columns([
            column("\n".join([
                bound("price", "$2 450 000", "south-listing__price"),
                raw("post-title", {"level": 1, "className": "south-listing__title"}),
                bound("address", "Calle Serena 24, Malibu, CA", "south-listing__location"),
                raw("post-content", {"className": "south-listing__content", "layout": {"type": "default"}}),
                shortcode('[south_property part="details"]'),
            ]), width="65.77%"),
            column(shortcode('[south_property part="agent"]'), width="31.53%"),
        ], gap="40", extra_class="south-listing__columns"),
        shortcode('[south_property part="map"]'),
    ])
    write("hidden-property", "Single property",
          section(group(body, layout="default", extra_class="south-listing"), extra_class="south-listing-area"),
          inserter=False, description="One home: its photographs, price, title, address, description, facts, features, the agent and the map.")


# ---------------------------------------------------------------------------
# Whole pages
# (8/4 splits are 65.77% + 31.53% + a 30px gap: the template's 730 + 350 of 1110.)
# ---------------------------------------------------------------------------
def ref(slug):
    return '<!-- wp:pattern {"slug":"south/%s"} /-->' % slug


def build_pages():
    pages = {
        "page-home": ("Page: home", ["hero", "featured-properties", "rent-cta", "testimonials", "realtor-note"],
                      "The home page: the hero slider and search, featured homes, the rentals call to action, testimonials and the founder's note."),
        "page-about": ("Page: about", ["property-search", "about", "rent-cta", "team"],
                       "The about page: the search box, the agency's story, the rentals call to action and the team."),
        "page-contact": ("Page: contact", ["contact", "map"],
                         "The contact page: office hours, phone and address beside the enquiry form, then the map."),
    }
    for slug, (title, refs, description) in pages.items():
        write(slug, title, "\n".join(ref(r) for r in refs), categories=PAGES,
              post_types=["page"], description=description)


# ---------------------------------------------------------------------------
# Template parts and templates
# ---------------------------------------------------------------------------
def write_file(path, content):
    with open(os.path.join(ROOT, path), "w") as fh:
        fh.write(content.strip() + "\n")


HEADER = '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header","className":"south-site-header"} /-->'
FOOTER = '<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer","className":"south-site-footer"} /-->'


def main_group(inner):
    return ('<!-- wp:group {"tagName":"main","align":"full","className":"south-main","layout":{"type":"default"}} -->\n'
            '<main class="wp-block-group alignfull south-main">\n%s\n</main>\n<!-- /wp:group -->' % inner)


def with_sidebar(left):
    return (
        '<!-- wp:group {"align":"full","className":"south-section south-blog-area","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->\n'
        '<div class="wp-block-group alignfull south-section south-blog-area" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">\n'
        '<!-- wp:columns {"className":"south-blog","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|40"}}}} -->\n'
        '<div class="wp-block-columns south-blog">\n'
        '<!-- wp:column {"width":"65.77%%"} -->\n<div class="wp-block-column" style="flex-basis:65.77%%">\n%s\n</div>\n<!-- /wp:column -->\n'
        '<!-- wp:column {"width":"31.53%%"} -->\n<div class="wp-block-column" style="flex-basis:31.53%%">\n'
        '<!-- wp:template-part {"slug":"sidebar","area":"uncategorized","className":"south-sidebar-part"} /-->\n'
        '</div>\n<!-- /wp:column -->\n'
        '</div>\n<!-- /wp:columns -->\n'
        '</div>\n<!-- /wp:group -->' % left
    )


def single_post_body():
    return "\n".join([
        raw("post-featured-image", {"aspectRatio": "730/346", "className": "south-post__image"}),
        '<!-- wp:group {"className":"south-post__body is-single","layout":{"type":"default"}} -->\n'
        '<div class="wp-block-group south-post__body is-single">\n'
        + raw("post-date", {"isLink": True, "className": "south-post__date"}) + "\n"
        + raw("post-title", {"level": 1, "className": "south-post__title"}) + "\n"
        + ref("hidden-post-meta") + "\n"
        + raw("post-content", {"className": "south-post__content", "layout": {"type": "default"}}) + "\n"
        + raw("post-terms", {"term": "post_tag", "prefix": "Tags: ", "className": "south-post__tags"}) + "\n"
        + '</div>\n<!-- /wp:group -->',
        ref("hidden-comments"),
    ])


def build_templates():
    write_file("parts/header.html", ref("header"))
    write_file("parts/footer.html", ref("footer"))
    write_file("parts/sidebar.html", ref("sidebar"))

    t = {}
    t["index"] = [ref("hidden-banner-archive"), with_sidebar(ref("hidden-posts-list"))]
    t["home"] = [ref("hidden-banner-blog"), with_sidebar(ref("hidden-posts-list"))]
    t["archive"] = [ref("hidden-banner-archive"), with_sidebar(ref("hidden-posts-list"))]
    t["search"] = [ref("hidden-banner-search"), with_sidebar(ref("hidden-posts-list"))]
    t["404"] = [ref("hidden-banner-404"), ref("hidden-404")]
    t["single"] = [ref("hidden-banner-post"), with_sidebar(single_post_body())]
    page_content = raw("post-content", {"align": "full", "className": "south-page-content", "layout": {"type": "constrained"}})
    t["page"] = [ref("hidden-banner-page"), page_content]
    t["page-no-title"] = [page_content]
    t["page-with-sidebar"] = [ref("hidden-banner-page"), with_sidebar(
        raw("post-content", {"className": "south-post__content", "layout": {"type": "default"}}))]
    # The static front page, without a banner: its content opens with the hero.
    # inc/listings.php drops this template from the hierarchy when the front
    # page shows the latest posts, so home.html renders that case.
    t["front-page"] = [page_content]
    t["archive-property"] = [ref("hidden-banner-listings"), ref("property-search"), ref("hidden-listings-results")]
    for tax in ("property_type", "property_city", "property_status"):
        t["taxonomy-" + tax] = [ref("hidden-banner-archive"), ref("property-search"), ref("hidden-listings-results")]
    t["single-property"] = [ref("hidden-banner-property"), ref("property-search"), ref("hidden-property")]

    for name, blocks in t.items():
        write_file("templates/%s.html" % name, "\n\n".join([HEADER, main_group("\n\n".join(blocks)), FOOTER]))
    return len(t)


def main():
    build_header()
    build_footer()
    build_sidebar()
    build_hero()
    build_search()
    build_featured()
    build_cta()
    build_testimonials()
    build_realtor()
    build_about()
    build_team()
    build_contact()
    build_journal()
    build_banners()
    build_hidden()
    build_pages()
    templates = build_templates()

    print("%d patterns, %d templates, 3 parts" % (len(WRITTEN), templates))


if __name__ == "__main__":
    main()
