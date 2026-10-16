#!/bin/sh
# A throwaway WordPress with the theme and the plugin mounted (never local-wp:
# it is shared, and other sessions rotate its theme).
#   sh .dev/serve.sh [port] [blueprint]
here="$( cd "$( dirname "$0" )/.." && pwd )"
port="${1:-9481}"
blueprint="${2:-$here/.dev/blueprint.json}"
exec npx -y @wp-playground/cli@3.1.54 server --port="$port" --php=8.3 --wp=latest \
	--mount-before-install="$here:/wordpress/wp-content/themes/south" \
	--mount-before-install="$here/plugin/south-listings:/wordpress/wp-content/plugins/south-listings" \
	--blueprint="$blueprint" --login
