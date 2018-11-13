#!/usr/bin/env python

from dulwich.repo import Repo
from json import dumps
from os import symlink
from pprint import pprint
from semver import parse_version_info


def write_json(ver, file, action):
    filename = file+'.'+action
    print 'Creating', filename

    with open(filename, 'w') as json:
        json.write("""\
{{
"offers": [
    {{
        "response":"{action}",
        "download":"https://github.com/ClassyBot/ClassicPress-builds/{ver}.zip",
        "locale":"en_US",
        "packages":{{
            "full":"https://github.com/ClassyBot/ClassicPress-builds/{ver}.zip",
            "no_content":"https://github.com/ClassyBot/ClassicPress-builds/{ver}-no-content.zip",
            "new_bundled":"https://github.com/ClassyBot/ClassicPress-builds/{ver}-new-bundled.zip",
            "partial":false,
            "rollback":false
        }},
        "current":"{ver}",
        "version":"{ver}",
        "php_version":"5.6.4",
        "mysql_version":"5.0",
        "new_bundled":"4.7",
        "partial_version":false
    }}
]
}}""".format(action=action,ver=str(ver)))

def write_and_link_latest_json(vecs, ver):
    vecs[ver.major][str(ver)] = 'latest'
    write_json(ver, 'tree/'+str(ver), 'latest')
    write_json(ver, 'tree/'+str(ver), 'update')
    symlink(str(ver)+'.latest', 'tree/'+str(ver))



tags = {}

with Repo('../ClassicPress-nightly') as r:
    tags.update(r.refs.as_dict('refs/tags'))

with Repo('../ClassicPress-release') as r:
    tags.update(r.refs.as_dict('refs/tags'))


vers = {}

# we only care about nightly builds
for tag in tags:
    try:
        ver = parse_version_info(tag)
        if not ver.build or ver.build[:7] == 'nightly':
            if ver.major in vers:
                vers[ver.major].append(ver)
            else:
                vers[ver.major] = [ver]
    except ValueError:
        # ignore non-semver tags
        pass

vecs = {}

for major, version_list in vers.iteritems():
    max_bld_ver = None
    max_pre_ver = None
    max_rel_ver = None
    vecs[major] = {}

    for version in version_list:
        if version.build:
            if version > max_bld_ver or version.build > max_bld_ver.build:
                max_bld_ver = version
        elif version.prerelease:
            if version > max_pre_ver:
                max_pre_ver = version
        else:
            if version > max_rel_ver:
                max_rel_ver = version

    if max_rel_ver > max_pre_ver:
        max_pre_ver = max_rel_ver

    if max_bld_ver > None: write_and_link_latest_json(vecs, max_bld_ver)
    if max_pre_ver > None: write_and_link_latest_json(vecs, max_pre_ver)
    if max_rel_ver > None: write_and_link_latest_json(vecs, max_rel_ver)

    for version in version_list:
        if version.build:
            if str(version) != str(max_bld_ver):
                vecs[major][str(version)] = str(max_bld_ver)
                symlink(str(max_bld_ver)+'.update', 'tree/'+str(version))
        elif version.prerelease:
            if version < max_rel_ver:
                vecs[major][str(version)] = str(max_rel_ver)
                symlink(str(max_rel_ver)+'.update', 'tree/'+str(version))
            elif version != max_pre_ver:
                vecs[major][str(version)] = str(max_pre_ver)
                symlink(str(max_pre_ver)+'.update', 'tree/'+str(version))
        else:
            if version != max_rel_ver:
                vecs[major][str(version)] = str(max_rel_ver)
                symlink(str(max_rel_ver)+'.update', 'tree/'+str(version))

print dumps(vecs, sort_keys=True, indent=2)

