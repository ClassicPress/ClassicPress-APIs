#!/bin/bash

# Exit on error
set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo 'Activating virtualenv'
. bin/activate
echo 'Activated virtualenv'

# Show commands as they are executed
set -x

update_repo() {
    echo "Updating repository: $1"
    cd "$1"
    git pull
    cd -
}

update_repo ClassicPress-nightly
update_repo ClassicPress-v2-nightly
update_repo ClassicPress-release

python generate-upgrade-json.py