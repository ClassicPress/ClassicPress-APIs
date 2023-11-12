#!/usr/bin/env python3

import collections
import git
import hashlib
import json
import os
import semver

script_dir = os.path.dirname(os.path.abspath(__file__))
repo_root = os.path.dirname(script_dir)

def dump(name, obj):
    print(name + ': ' + json.dumps(obj, sort_keys=True, indent=2))

def symlink_versions(ver_after, action, ver_before):
    src = upgrade_json_filename(ver_after, action)
    dst = upgrade_json_filename(ver_before)
    print(f'symlink({os.path.basename(dst)} -> {os.path.basename(src)})')
    os.symlink(os.path.basename(src), dst + '.tmp')
    os.rename(dst + '.tmp', dst)

def upgrade_json_filename(ver, action=None):
    if action is None:
        filename = f'{ver}.json'
    else:
        filename = f'{ver}.{action}.json'
    return os.path.join(repo_root, 'v1', 'upgrade', filename)

def write_upgrade_json(ver, action):
    filename = upgrade_json_filename(ver, action)
    print(f'write_upgrade_json({os.path.basename(filename)})')

    if ('+nightly' in str(ver) and str(ver).startswith('1')):
        url = f'https://github.com/ClassyBot/ClassicPress-nightly/archive/{ver}.zip'
    elif ('+nightly' in str(ver) and str(ver).startswith('2')):
        url = f'https://github.com/ClassyBot/ClassicPress-v2-nightly/archive/{ver}.zip'
    else:
        url = f'https://github.com/ClassicPress/ClassicPress-release/archive/{ver}.zip'

    php_version = '5.6.4' if str(ver).startswith('1') else '7.4'

    with open(filename + '.tmp', 'w') as json_file:
        json_file.write(f'''\
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
        "php_version":"{php_version}",
        "mysql_version":"5.0",
        "new_bundled":"4.7",
        "partial_version":false
    }}
]
}}''')

    os.rename(filename + '.tmp', filename)

def write_and_link_latest_json(vecs, ver):
    vecs[ver.major][str(ver)] = 'latest'
    write_upgrade_json(ver, 'latest')
    write_upgrade_json(ver, 'upgrade')
    symlink_versions(ver, 'latest', ver)

def checksums_json_filename(ver, format):
    filename = f'{ver}.json'
    return os.path.join(repo_root, 'v1', 'checksums', format, filename)

def write_checksums_json(tag, tag_data):
    json_filename = checksums_json_filename(tag, 'md5')

    if os.path.exists(json_filename):
        return

    print(f'checksums for version {tag}: start')
    tag_sha = tag_data['sha']
    repo = tag_data['repo']
    tag_or_commit = repo.commit(tag_sha)
    checksums = {}

    for blob in tag_or_commit.tree.blobs:
        file_path = blob.path
        sha = blob.hexsha
        hash_md5 = hashlib.md5()
        hash_md5.update(repo.git.show(sha).encode())
        checksums[file_path] = hash_md5.hexdigest()

    with open(json_filename + '.tmp', 'w') as json_file:
        json_file.write(json.dumps({
            'version': tag,
            'format': 'md5',
            'checksums': collections.OrderedDict(sorted(checksums.items())),
        }))

    os.rename(json_filename + '.tmp', json_filename)

    print(f'checksums for version {tag}: {len(checksums)} files')

tags = {}
vers = {}

def load_repo(folder_name):
    return git.Repo(os.path.join(script_dir, folder_name))

with load_repo('ClassicPress-nightly') as r_nightly:
    for tag in r_nightly.tags:
        sha = tag.commit.hexsha
        tags[tag.name] = {'repo': r_nightly, 'sha': sha}

with load_repo('ClassicPress-v2-nightly') as r2_nightly:
    for tag in r2_nightly.tags:
        sha = tag.commit.hexsha
        tags[tag.name] = {'repo': r2_nightly, 'sha': sha}

with load_repo('ClassicPress-release') as r_release:
    for tag in r_release.tags:
        sha = tag.commit.hexsha
        tags[tag.name] = {'repo': r_release, 'sha': sha}

dump('tags', {t: tags[t]['sha'] for t in tags})

for tag in tags:
    write_checksums_json(tag, tags[tag])
    try:
        ver = semver.parse_version_info(tag)
        if not ver.build or ver.build[:7] == 'nightly':
            if ver.major in vers:
                vers[ver.major].append(ver)
            else:
                vers[ver.major] = [ver]
    except ValueError:
        pass

dump('vers', {major: sorted(str(v) for v in arr) for major, arr in vers.items()})

vecs = {}

for major, version_list in vers.items():
    max_bld_ver = None
    max_pre_ver = None
    max_rel_ver = None
    vecs[major] = {}

    for version in version_list:
        if version.build:
            if not max_bld_ver or version > max_bld_ver or version.build > max_bld_ver.build:
                max_bld_ver = version
        elif version.prerelease:
            if not max_pre_ver or version > max_pre_ver:
                max_pre_ver = version
        else:
            if not max_rel_ver or version > max_rel_ver:
                max_rel_ver = version

    if max_rel_ver and max_rel_ver > max_pre_ver:
        max_pre_ver = max_rel_ver

    if max_bld_ver:
        write_and_link_latest_json(vecs, max_bld_ver)
    if max_pre_ver:
        write_and_link_latest_json(vecs, max_pre_ver)
    if max_rel_ver:
        write_and_link_latest_json(vecs, max_rel_ver)

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