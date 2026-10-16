#!/bin/sh
# Fail if the theme and the plugin declare a function of the same name.
# (Academia's "Cannot redeclare" fatal on plugin activation, made static.)
cd "$(dirname "$0")/.."
decl() { grep -rhoE '^\s*function [a-z0-9_]+' "$@" | awk '{print $2}' | sort -u; }
theme=$(mktemp); plugin=$(mktemp)
decl functions.php inc patterns > "$theme"
decl plugin/south-listings > "$plugin"
shared=$(comm -12 "$theme" "$plugin")
echo "$(wc -l < "$theme" | tr -d ' ') theme functions, $(wc -l < "$plugin" | tr -d ' ') plugin functions"
if [ -n "$shared" ]; then echo "declared by both: $shared"; exit 1; fi
echo "no function is declared by both"
