#!/usr/bin/env bash
# Builds the installable ZIP file from the current commit into dist/.
set -euo pipefail

cd "$(dirname "$0")/.."
mkdir -p dist

version=$(sed -n 's/^ \* Version: *//p' puppypress.php)

git archive --format=zip --prefix=puppypress/ -o "dist/puppypress-${version}.zip" HEAD

ls -l dist
