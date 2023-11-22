#!/usr/bin/env bash
set -euo pipefail

rm -f 'parcelpro.ocmod.zip'
zip -r 'parcelpro.ocmod.zip' 'admin' 'catalog' 'system' 'install.json' 'README.md'
