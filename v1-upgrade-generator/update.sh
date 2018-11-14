#!/usr/bin/env bash

# Exit on error
set -e

echo 'Activating virtualenv'
. bin/activate
echo 'Activated virtualenv'

cd "$(dirname "$0")"

# Show commands as they are executed
set -x

pushd ClassicPress-nightly
	git fetch
	git fetch --tags
popd
pushd ClassicPress-release
	git fetch
	git fetch --tags
popd

python generate-upgrade-json.py
