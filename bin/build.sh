#!/usr/bin/env bash
# Builds installable ZIP files of both plugins from the current commit into dist/.
set -euo pipefail

cd "$(dirname "$0")/.."
mkdir -p dist

version=$(sed -n 's/^ \* Version: *//p' puppypress.php)
migrator_version=$(sed -n 's/^ \* Version: *//p' puppypress-migrator/puppypress-migrator.php)

git archive --format=zip --prefix=puppypress/ -o "dist/puppypress-${version}.zip" HEAD
git archive --format=zip --prefix=puppypress-migrator/ -o "dist/puppypress-migrator-${migrator_version}.zip" HEAD:puppypress-migrator \
	$(git ls-tree -r --name-only HEAD:puppypress-migrator | grep -v '^tests/' | grep -v '^\.gitattributes$')

ls -l dist
