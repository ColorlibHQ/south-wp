"""Downscale a tall capture into viewable slices: python3 .dev/view.py in.png out_prefix [width] [slice_h]"""
import sys
from PIL import Image
src, prefix = sys.argv[1], sys.argv[2]
w = int(sys.argv[3]) if len(sys.argv) > 3 else 720
sh = int(sys.argv[4]) if len(sys.argv) > 4 else 1400
im = Image.open(src).convert('RGB')
scale = w / im.width
im = im.resize((w, int(im.height * scale)))
n = 0
for y in range(0, im.height, sh):
    im.crop((0, y, w, min(im.height, y + sh))).save('%s-%d.jpg' % (prefix, n), quality=80)
    n += 1
print(n, 'slices')
