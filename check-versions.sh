#!/usr/bin/env bash
set -euo pipefail

if ( ! command -v jq > /dev/null )
then
  echo 'The jq command is required for this script.'
  exit 1
fi

allMatch=1
function checkVersionMatch() {
    echo "- $1: $2"
    if [ ! "$installVersion" = "$2" ]
    then
      allMatch=0
    fi
}

echo "Detected versions:"

installVersion="$(jq -r .version 'install.json')"
checkVersionMatch 'install.json' "$installVersion"

checkVersionMatch 'CHANGELOG.md' "$(sed -nE 's/^## V(.*)$/\1/p' 'CHANGELOG.md' | head -n 1)"
checkVersionMatch 'admin/language/dutch/shipping/parcel_pro.php' "$(sed -nE "s/^.version = '(.*)'.*/\1/p" 'admin/language/dutch/shipping/parcel_pro.php')"
checkVersionMatch 'admin/language/en-gb/shipping/parcel_pro.php' "$(sed -nE "s/^.version = '(.*)'.*/\1/p" 'admin/language/en-gb/shipping/parcel_pro.php')"
checkVersionMatch 'admin/language/dutch/sale/pp_order.php' "$(sed -nE "s/^.version = '(.*)'.*/\1/p" 'admin/language/dutch/sale/pp_order.php')"
checkVersionMatch 'admin/language/en-gb/sale/pp_order.php' "$(sed -nE "s/^.version = '(.*)'.*/\1/p" 'admin/language/en-gb/sale/pp_order.php')"

if [ ! "$allMatch" = 1 ]
then
  echo 'Not all versions match.'
  exit 1
else
  echo 'All version match!'
fi
