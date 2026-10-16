#!/bin/sh
# languages/south.pot and the plugin's south-listings.pot, with xgettext
# (WP-CLI's i18n command is not installed here). Patterns are PHP files
# whose strings are in block markup, so they are not translatable through
# __(); like Unioncorp, the copy is meant to be edited in the editor.
set -e
cd "$(dirname "$0")/.."
K='--keyword=__ --keyword=_e --keyword=esc_html__ --keyword=esc_html_e --keyword=esc_attr__ --keyword=esc_attr_e --keyword=_x:1,2c --keyword=esc_html_x:1,2c --keyword=esc_attr_x:1,2c --keyword=_n:1,2 --keyword=_nx:1,2,4c --keyword=_n_noop:1,2'
xgettext --language=PHP --from-code=UTF-8 $K --add-comments=translators --package-name=South --package-version="$(sed -n 's/^Version: *//p' style.css)" \
	-o languages/south.pot functions.php inc/*.php
xgettext --language=PHP --from-code=UTF-8 $K --add-comments=translators --package-name="South Listings" \
	-o plugin/south-listings/languages/south-listings.pot plugin/south-listings/south-listings.php plugin/south-listings/includes/*.php
grep -c '^msgid ' languages/south.pot plugin/south-listings/languages/south-listings.pot
