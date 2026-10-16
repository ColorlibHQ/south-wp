"""Side-by-side contact sheet: HTML template left, WordPress right.
   python3 .dev/sidebyside.py html.png wp.png out.jpg [width-per-side]"""
import sys
from PIL import Image, ImageDraw
a, b, out = sys.argv[1:4]
w = int(sys.argv[4]) if len(sys.argv) > 4 else 480
ims = []
for f in (a, b):
    im = Image.open(f).convert("RGB")
    ims.append(im.resize((w, int(im.height * w / im.width))))
h = max(i.height for i in ims)
sheet = Image.new("RGB", (w * 2 + 10, h), "#ff00ff")
sheet.paste(ims[0], (0, 0)); sheet.paste(ims[1], (w + 10, 0))
sheet.save(out, quality=78)
print(out, sheet.size, "heights", [i.height for i in ims])
