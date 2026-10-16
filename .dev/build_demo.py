#!/usr/bin/env python3
"""Build South Listings' demo content from the HTML template's data.

Writes plugin/south-listings/demo/data.php (a PHP file returning an array)
and resized JPEGs in plugin/south-listings/demo/images/. The importer
(includes/demo.php) reads only those, so the demo homes are exactly the
template's nine listings: same titles, prices, facts, agents, descriptions,
features, room sizes, photographs and alt text.

Demo photographs live in the PLUGIN zip, never the theme's (theme demo
content standard). JPEG, not AVIF, so WordPress can make its image sizes on
any host.

    python3 .dev/build_demo.py [path-to-south-html-template]
"""

import json
import os
import sys

from PIL import Image

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TEMPLATE = sys.argv[1] if len(sys.argv) > 1 else "/Users/silkalns/Fresh Projects/colorlib-premium-upgrade/templates/south"
DEMO = os.path.join(ROOT, "plugin", "south-listings", "demo")
IMAGES = os.path.join(DEMO, "images")


def resize(src, dest, width, quality=80):
    im = Image.open(os.path.join(TEMPLATE, src)).convert("RGB")
    if im.width > width:
        im = im.resize((width, round(im.height * width / im.width)), Image.LANCZOS)
    im.save(os.path.join(IMAGES, dest), "JPEG", quality=quality, optimize=True, progressive=True)
    return dest


def php(value, indent=1):
    """A Python value as PHP source."""
    pad = "\t" * indent
    if isinstance(value, bool):
        return "true" if value else "false"
    if value is None:
        return "null"
    if isinstance(value, (int, float)):
        return repr(value)
    if isinstance(value, str):
        return "'" + value.replace("\\", "\\\\").replace("'", "\\'") + "'"
    if isinstance(value, list):
        if not value:
            return "array()"
        return "array(\n" + "".join(pad + php(v, indent + 1) + ",\n" for v in value) + "\t" * (indent - 1) + ")"
    if isinstance(value, dict):
        return "array(\n" + "".join(pad + php(k) + " => " + php(v, indent + 1) + ",\n" for k, v in value.items()) + "\t" * (indent - 1) + ")"
    raise TypeError(value)


JOURNAL = [
    {
        "slug": "how-to-get-the-best-deal-for-your-house",
        "title": "How to get the best deal for your house",
        "date": "2026-10-08 09:00:00",
        "category": "Buying",
        "author": "Jeremy Scott",
        "image": ("img/blog-img/blog1.jpg", "Looking up at a narrow wedge-shaped office tower against a blue sky"),
        "excerpt": "A good price starts long before the offer: with the comparable sales you read, the questions you ask at the viewing and the conditions you are willing to drop.",
        "body": [
            ("p", "A good price starts long before the offer. By the time you sit down to write a number, the deal has mostly been decided by what you learned at the viewing, the sales you compared and how ready you are to move."),
            ("h2", "Read the last six months of sales"),
            ("p", "Asking prices tell you what a seller hopes for. Completed sales tell you what buyers paid. Ask your agent for every sale within half a mile in the last six months, with the price per square foot, and compare like with like: a renovated three-bedroom house next to another renovated three-bedroom house."),
            ("img", "img/bg-img/hero3.jpg", "A white apartment tower with a grid of deep balconies against a blue sky"),
            ("h2", "Ask the questions sellers expect"),
            ("p", "How long has the home been on the market? Has the price changed? Why are the owners moving, and when do they need to? A seller who has already bought elsewhere often values a quick, certain sale more than the last ten thousand dollars."),
            ("h2", "Make your offer easy to accept"),
            ("p", "A mortgage agreement in principle, a surveyor booked and a flexible completion date can be worth more than a higher number with conditions attached. Put the price and your terms in writing, and give a short, reasonable deadline for the answer."),
        ],
        "comments": [
            ("Anna Kowalski", "2026-10-09 10:12:00", "The point about completed sales is so true. We were about to offer the asking price until our agent showed us three similar houses that sold for less.", None),
            ("Jeremy Scott", "2026-10-09 14:40:00", "Thanks, Anna. Asking prices are a starting point; the sales list is the evidence. Glad it helped.", 0),
            ("Daniel Reyes", "2026-10-10 08:25:00", "We dropped two conditions and offered a flexible move-in date. The seller accepted the same day, even though another offer was a little higher.", None),
        ],
    },
    {
        "slug": "what-a-neighbourhood-walk-tells-you",
        "title": "What a neighbourhood walk tells you that a listing never will",
        "date": "2026-09-22 09:00:00",
        "category": "Neighbourhoods",
        "author": "Maria Williams",
        "image": ("img/blog-img/blog2.jpg", "Old stone towers above a busy avenue with traffic in the evening light"),
        "excerpt": "Before you book a second viewing, walk the streets around the home at rush hour and after dark. Here is the route we give our buyers.",
        "body": [
            ("p", "Photographs show a home at its best. A walk around the block at the wrong time of day shows you how it will feel to live there on a Tuesday in February."),
            ("h2", "Go twice: at rush hour and after dark"),
            ("p", "Traffic noise, school runs and parking are at their worst between eight and nine in the morning. Late in the evening you will hear the neighbours, see how well the street is lit and find out whether the corner bar is a quiet one."),
            ("h2", "Walk to the things you use every week"),
            ("p", "Time the walk to the nearest grocery store, the bus or Metro stop and the park. Ten minutes on a map can be twenty on foot if there is a freeway to cross. If you have children, walk the route to school."),
            ("h2", "Talk to someone who lives there"),
            ("p", "People watering their front gardens are usually happy to talk. Ask what they like about the street and what they would change. In ten minutes you will learn more than a listing can tell you."),
        ],
        "comments": [],
    },
    {
        "slug": "renting-in-los-angeles-the-paperwork-checklist",
        "title": "Renting in Los Angeles: the paperwork checklist",
        "date": "2026-09-03 09:00:00",
        "category": "Renting",
        "author": "Patrick Joe",
        "image": ("img/blog-img/blog3.jpg", "Bright meeting room with two long tables and black chairs"),
        "excerpt": "Landlords decide quickly. Having these documents ready before the viewing is often the difference between getting the apartment and joining the waiting list.",
        "body": [
            ("p", "Good rentals rarely stay on the market for more than a few days. Landlords decide quickly, and the tenant who can hand over a complete application at the viewing is usually the one who gets the keys."),
            ("h2", "Proof of income"),
            ("p", "Most landlords look for a monthly income of about three times the rent. Bring your last three pay slips or, if you are self-employed, last year's tax return and recent bank statements."),
            ("h2", "References and history"),
            ("p", "A short letter from your current landlord and the contact details of the one before make a real difference. If this is your first rental, a reference from your employer helps."),
            ("h2", "Deposit and move-in costs"),
            ("p", "Plan for the first month's rent and a security deposit, usually equal to one month's rent for an unfurnished home. Ask what the rent includes (water, trash, parking) and get the answer in the lease."),
        ],
        "comments": [],
    },
]

AGENT_PHOTOS = {"jeremy-scott": "img/bg-img/team1.jpg", "maria-williams": "img/bg-img/team2.jpg",
                "patrick-joe": "img/bg-img/team3.jpg"}
AGENT_ALT = {
    "jeremy-scott": "Jeremy Scott, a bearded man in a dark suit and red tie",
    "maria-williams": "Maria Williams, smiling in glasses and a pale blue jacket",
    "patrick-joe": "Patrick Joe, smiling in a black suit and tie on a city street",
}


def main():
    os.makedirs(IMAGES, exist_ok=True)
    for f in os.listdir(IMAGES):
        os.remove(os.path.join(IMAGES, f))
    data = json.load(open(os.path.join(TEMPLATE, "data", "properties.json")))

    agents = []
    for slug, a in data["agents"].items():
        agents.append({
            "slug": slug, "name": a["name"], "role": a["role"], "phone": a["phone"], "email": a["email"],
            "photo": resize(AGENT_PHOTOS[slug], "agent-%s.jpg" % slug, 640), "alt": AGENT_ALT[slug],
        })

    homes = []
    for order, p in enumerate(data["properties"]):
        slug = os.path.splitext(os.path.basename(p["photo"]))[0]
        card = resize(p["photo"], "card-%s.jpg" % slug, 1086, 74)
        gallery = []
        for i, g in enumerate(p["gallery"]):
            name = os.path.splitext(os.path.basename(g))[0]
            gallery.append(resize(g, "photo-%s.jpg" % name, 1400, 72))
        homes.append({
            "slug": p["page"].replace("property-", "").replace(".html", "") if p["page"].startswith("property-") else "apartment-with-modern-architecture",
            "reference": p["id"], "title": p["title"], "type": p["type"], "status": p["status"],
            "price": p["price"], "city": p["city"], "neighbourhood": p["neighbourhood"], "address": p["address"],
            "lat": p["lat"], "lon": p["lon"], "beds": p["beds"], "baths": p["baths"], "garages": p["garages"],
            "area": p["area"], "year": p["year"], "outdoor": ",".join(p["outdoor"]), "furnished": p["furnished"],
            "pets": p["pets"], "view": p["view"], "listed": p["listed"] + " 09:00:00", "new": p["isNew"],
            "featured": p["featured"], "order": order, "summary": p["summary"], "description": p["description"],
            "features": p["features"], "rooms": [" | ".join(r) for r in p["rooms"]], "agent": p["agent"],
            "image": card, "alt": p["alt"], "gallery": gallery,
        })

    posts = []
    for post in JOURNAL:
        src, alt = post["image"]
        body = []
        for block in post["body"]:
            if block[0] == "img":
                body.append(("img", resize(block[1], "journal-" + os.path.splitext(os.path.basename(block[1]))[0] + ".jpg", 1200, 74), block[2]))
            else:
                body.append(block)
        posts.append({
            "slug": post["slug"], "title": post["title"], "date": post["date"], "category": post["category"],
            "author": post["author"], "excerpt": post["excerpt"],
            "image": resize(src, "journal-%s.jpg" % os.path.splitext(os.path.basename(src))[0], 1200, 74), "alt": alt,
            "body": [list(b) for b in body],
            "comments": [list(c) for c in post["comments"]],
        })

    out = ("<?php\n/**\n * South Listings demo content: the South HTML template's nine homes, three\n"
           " * agents and three journal posts. Generated by .dev/build_demo.py; do not edit.\n *\n"
           " * @package South_Listings\n */\n\ndefined( 'ABSPATH' ) || exit;\n\nreturn "
           + php({"agents": agents, "homes": homes, "posts": posts}) + ";\n")
    with open(os.path.join(DEMO, "data.php"), "w") as fh:
        fh.write(out)

    total = sum(os.path.getsize(os.path.join(IMAGES, f)) for f in os.listdir(IMAGES))
    print("%d agents, %d homes, %d posts, %d images, %d KB" % (
        len(agents), len(homes), len(posts), len(os.listdir(IMAGES)), total // 1024))


if __name__ == "__main__":
    main()
