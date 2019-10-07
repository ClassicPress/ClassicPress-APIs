#!/bin/bash

set -e

cd "$(dirname "$0")"

# Create git repository if needed
if [ ! -d twemoji ] || [ ! -d twemoji/.git ]; then
	mkdir -p twemoji
	pushd twemoji > /dev/null
		git clone https://github.com/twitter/twemoji .
	popd > /dev/null
fi

cd twemoji

# Update git repository
git fetch origin

# Generate each of the JSON file listings we need
for spec in 6f3545b9:2/svg; do
	commit="$(echo "$spec" | cut -d: -f1)"
	subdir="$(echo "$spec" | cut -d: -f2)"
	json_filename_base="$(echo "$commit")_$(echo "$subdir" | tr / _).json"
	json_filename="../../v1/twemoji/$json_filename_base"
	git checkout "$commit"
	echo -n '[' > "$json_filename"
	ls "$subdir" \
		| sed 's#^#"#; s#$#"#' \
		| tr '\n' ',' \
		| sed 's#,$##' \
		>> "$json_filename"
	echo -n ']' >> "$json_filename"
	echo "$json_filename_base"
done
