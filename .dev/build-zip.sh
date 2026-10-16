#!/bin/sh
# Build the two distributable zips: the theme, and the South Listings plugin.
#
# WordPress unpacks an update zip and uses its top-level directory as the
# install directory, so each zip holds exactly one root folder: `south` and
# `south-listings`. The plugin is a SEPARATE zip, never bundled inside the
# theme: a plugin inside a theme zip is reinstalled on every theme update.
#
# Theme Check must run against THIS output, not the working tree, or it
# reports the plugin's register_post_type() and the tooling as the theme's.
#
#   sh .dev/build-zip.sh [outdir]      (default: .dev/tmp/build)
set -e

ROOT=$(cd "$(dirname "$0")/.." && pwd)
OUT=${1:-$ROOT/.dev/tmp/build}
VERSION=$(sed -n 's/^Version: *//p' "$ROOT/style.css" | head -1)
PLUGIN_VERSION=$(sed -n 's/^ \* Version: *//p' "$ROOT/plugin/south-listings/south-listings.php" | head -1)

rm -rf "$OUT"
mkdir -p "$OUT/south"

cd "$ROOT"
rsync -a --quiet \
	--exclude '.git' \
	--exclude '.gitignore' \
	--exclude '.dev' \
	--exclude '.claude' \
	--exclude 'CLAUDE.md' \
	--exclude 'plugin' \
	--exclude 'node_modules' \
	--exclude '.DS_Store' \
	./ "$OUT/south/"

rsync -a --quiet --exclude '.DS_Store' "$ROOT/plugin/south-listings/" "$OUT/south-listings/"

cd "$OUT"
zip -qr "south-$VERSION.zip" south
zip -qr "south-listings-$PLUGIN_VERSION.zip" south-listings

if unzip -l "south-$VERSION.zip" | grep -q 'south/plugin/'; then
	echo "ERROR: the theme zip contains plugin/" >&2
	exit 1
fi
if unzip -l "south-$VERSION.zip" | grep -qE 'south/(\.dev|node_modules)/'; then
	echo "ERROR: the theme zip contains development files" >&2
	exit 1
fi

echo "theme:  $OUT/south-$VERSION.zip ($(du -h "south-$VERSION.zip" | cut -f1))"
echo "plugin: $OUT/south-listings-$PLUGIN_VERSION.zip ($(du -h "south-listings-$PLUGIN_VERSION.zip" | cut -f1))"
