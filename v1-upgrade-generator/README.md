## Upgrade JSON generator

The Python script in this folder generates the upgrade JSON blobs that
ClassicPress uses to determine upgrade paths.

### Development

The script runs on Python 3 and uses a
[virtualenv](https://virtualenv.pypa.io/en/stable/)
to isolate its dependencies from the rest of the system.

Assuming that your system's default Python version `3.x`,
here is how to set up the development environment:

```
cd v1-upgrade-generator/
virtualenv .
. bin/activate
pip install -r requirements.txt
```

The script relies on a couple of `git` repositories being in place:

```
cd v1-upgrade-generator/
git clone https://github.com/ClassyBot/ClassicPress-v1-nightly ClassicPress-v1-nightly
git clone https://github.com/ClassyBot/ClassicPress-v2-nightly ClassicPress-v2-nightly
git clone https://github.com/ClassicPress/ClassicPress-release ClassicPress-release
```

Once the above setup is done, you can run the script as follows:

```
python generate-upgrade-json.py
```

The resulting files will be placed in the `v1/upgrade/` folder in the root of
this repository.

See also `update.sh` in this folder, which will fetch from the ClassicPress
`git` repositories and then run the Python script.
