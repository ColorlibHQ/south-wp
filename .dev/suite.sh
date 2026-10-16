#!/bin/sh
# Every check, in order, against one fresh Playground. Logs in .dev/tmp/suite/.
cd "$(dirname "$0")/.."
export WP_URL="${WP_URL:-http://127.0.0.1:9481}" WP_USER=admin WP_PASS=password
out=.dev/tmp/suite; mkdir -p $out
run() { name=$1; shift; "$@" > "$out/$name.txt" 2>&1; echo "exit=$?" >> "$out/$name.txt"; echo "$name: $(tail -2 "$out/$name.txt" | head -1 | cut -c1-160) [$(tail -1 "$out/$name.txt")]"; }
run validate   .dev/to.sh 600  node .dev/validate-blocks.mjs
run dead       python3 .dev/dead-selectors.py
run shared     sh .dev/no-shared-functions.sh
run overflow   .dev/to.sh 2400 node .dev/overflow-check.mjs
run alignment  .dev/to.sh 2400 node .dev/alignment-check.mjs
run buttons    env SOUTH_PALETTES=all .dev/to.sh 4000 node .dev/button-boundary.mjs
run search     .dev/to.sh 900  node .dev/search-check.mjs
run editor     .dev/to.sh 1500 node .dev/editor-shots.mjs
run contrast   env SOUTH_PALETTES=all .dev/to.sh 9000 node .dev/contrast-rendered.mjs
echo suite-done
