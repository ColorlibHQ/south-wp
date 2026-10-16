#!/bin/sh
# Theme Check on the BUILT theme (.dev/build-zip.sh first), in a throwaway
# Playground: installs the theme-check plugin from WordPress.org, runs every
# check against `south`, and writes the findings to .dev/tmp/tc-out/.
set -e
here="$( cd "$( dirname "$0" )/.." && pwd )"
out="$here/.dev/tmp/tc-out"
mkdir -p "$out"
cat > "$out/blueprint.json" <<'JSON'
{
  "preferredVersions": { "php": "8.3", "wp": "latest" },
  "steps": [
    { "step": "installPlugin", "pluginData": { "resource": "wordpress.org/plugins", "slug": "theme-check" } },
    { "step": "activateTheme", "themeFolderName": "south" },
    { "step": "runPHP", "code": "<?php require '/wordpress/wp-load.php'; require_once WP_PLUGIN_DIR . '/theme-check/checkbase.php'; global $themechecks; run_themechecks_against_theme( wp_get_theme( 'south' ), 'south' ); $out = array(); foreach ( $themechecks as $check ) { if ( $check instanceof themecheck ) { foreach ( (array) $check->getError() as $e ) { $out[] = trim( preg_replace( '/\\s+/', ' ', wp_strip_all_tags( $e ) ) ); } } } file_put_contents( '/out/themecheck.txt', implode( \"\\n\", $out ) . \"\\n\" );" }
  ]
}
JSON
npx -y @wp-playground/cli@3.1.54 run-blueprint --php=8.3 --wp=latest \
	--mount-before-install="$here/.dev/tmp/build/south:/wordpress/wp-content/themes/south" \
	--mount="$out:/out" --blueprint="$out/blueprint.json"
echo "---"; cat "$out/themecheck.txt"
