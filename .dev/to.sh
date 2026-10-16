#!/bin/sh
# Run a command with a time limit (macOS has no timeout(1)).
#   sh .dev/to.sh <seconds> <command...>
secs=$1; shift
exec perl -e 'alarm shift; exec @ARGV or die "exec: $!"' "$secs" "$@"
