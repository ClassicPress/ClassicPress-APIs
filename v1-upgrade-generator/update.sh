#!/usr/bin/env bash

# Exit on error
set -e

cd "$(dirname "$0")"

echo 'Activating virtualenv'
. bin/activate
echo 'Activated virtualenv'

# Show commands as they are executed
set -x

pushd ClassicPress-nightly
	git fetch
	git fetch --tags
popd
pushd ClassicPress-v2-nightly
	git fetch
	git fetch --tags
popd
pushd ClassicPress-release
	git fetch
	git fetch --tags
popd

python generate-upgrade-json.py
