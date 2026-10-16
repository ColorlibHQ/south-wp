#!/bin/sh
# Install BOTH release zips on a fresh Playground (nothing from the working
# tree is mounted), theme first, then the plugin, then the demo: the order a
# buyer follows. Serves on port ${1:-9483}.
here="$( cd "$( dirname "$0" )/.." && pwd )"
build="$here/.dev/tmp/build"
v=$(sed -n 's/^Version: *//p' "$here/style.css" | head -1)
pv=$(sed -n 's/^ \* Version: *//p' "$here/plugin/south-listings/south-listings.php" | head -1)
cat > "$build/blueprint-zips.json" <<JSON
{
  "preferredVersions": { "php": "8.3", "wp": "latest" },
  "steps": [
    { "step": "setSiteOptions", "options": { "blogname": "South", "permalink_structure": "/%postname%/" } },
    { "step": "installTheme", "themeData": { "resource": "vfs", "path": "/zips/south-$v.zip" }, "options": { "activate": true } },
    { "step": "installPlugin", "pluginData": { "resource": "vfs", "path": "/zips/south-listings-$pv.zip" }, "options": { "activate": true } },
    { "step": "runPHP", "code": "<?php require '/wordpress/wp-load.php'; south_listings_import_demo(); echo 'ok';" }
  ]
}
JSON
exec npx -y @wp-playground/cli@3.1.54 server --port="${1:-9483}" --php=8.3 --wp=latest \
	--mount-before-install="$build:/zips" --blueprint="$build/blueprint-zips.json"
