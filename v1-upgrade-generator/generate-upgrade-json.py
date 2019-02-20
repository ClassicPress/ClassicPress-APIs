#!/usr/bin/env python

from dulwich.repo import Repo
from json import dumps
from os import rename, symlink
from os.path import abspath, basename, dirname, join
from semver import parse_version_info


script_dir = dirname(abspath(__file__))
repo_root = dirname(script_dir)

def dump(name, obj):
    print name + ': ' + dumps(obj, sort_keys=True, indent=2)

def symlink_versions(ver_after, action, ver_before):
    src = json_filename(ver_after, action)
    dst = json_filename(ver_before)
    print 'symlink(%s -> %s)' % (basename(dst), basename(src))
    symlink(basename(src), dst + '.tmp')
    rename(dst + '.tmp', dst)

def json_filename(ver, action = None):
    if action is None:
        filename = '%s.json' % ver
    else:
        filename = '%s.%s.json' % (ver, action)
    return join(repo_root, 'v1', 'upgrade', filename)

def write_json(ver, action):
    filename = json_filename(ver, action)
    print 'write_json(%s)' % basename(filename)

    if '+nightly' in str(ver):
        url = 'https://github.com/ClassyBot/ClassicPress-nightly/archive/%s.zip' % ver
    else:
        url = 'https://github.com/ClassicPress/ClassicPress-release/archive/%s.zip' % ver

    with open(filename + '.tmp', 'w') as json:
        json.write("""\
{{
"offers": [
    {{
        "response":"{action}",
        "download":"{url}",
        "locale":"en_US",
        "packages":{{
            "full":"{url}",
            "no_content":null,
            "new_bundled":null,
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
}}""".format(action=action, url=url, ver=str(ver)))

    rename(filename + '.tmp', filename)

def write_and_link_latest_json(vecs, ver):
    vecs[ver.major][str(ver)] = 'latest'
    write_json(ver, 'latest')
    write_json(ver, 'upgrade')
    symlink_versions(ver, 'latest', ver)



tags = {}

with Repo(join(script_dir, 'ClassicPress-nightly')) as r:
    tags.update(r.refs.as_dict('refs/tags'))

with Repo(join(script_dir, 'ClassicPress-release')) as r:
    tags.update(r.refs.as_dict('refs/tags'))

dump('tags', tags)

vers = {}

for tag in tags:
    try:
        ver = parse_version_info(tag)
        # we only care about release and nightly builds
        if not ver.build or ver.build[:7] == 'nightly':
            if ver.major in vers:
                vers[ver.major].append(ver)
            else:
                vers[ver.major] = [ver]
    except ValueError:
        # ignore non-semver tags
        pass

dump('vers', dict((major, sorted(str(v) for v in arr)) for (major, arr) in vers.iteritems()))

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
                symlink_versions(max_bld_ver, 'upgrade', version)
        elif version.prerelease:
            if version < max_rel_ver:
                vecs[major][str(version)] = str(max_rel_ver)
                symlink_versions(max_rel_ver, 'upgrade', version)
            elif version != max_pre_ver:
                vecs[major][str(version)] = str(max_pre_ver)
                symlink_versions(max_pre_ver, 'upgrade', version)
        else:
            if version != max_rel_ver:
                vecs[major][str(version)] = str(max_rel_ver)
                symlink_versions(max_rel_ver, 'upgrade', version)

dump('vecs', vecs)
