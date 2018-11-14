## Upgrade JSON generator

The Python script in this folder generates the upgrade JSON blobs that
ClassicPress uses to determine upgrade paths.

### Development

The script runs on Python 2.7 and uses a
[virtualenv](https://virtualenv.pypa.io/en/stable/)
to isolate its dependencies from the rest of the system.

Assuming that your system's default Python version is `2.7` or higher (but not
`3.x` as the script is untested there), here is how to set up the development
environment:

```
virtualenv .
. bin/activate
pip install -r requirements.txt
```

Then run the script using `python generate-upgrade-json.py` and the results
will be placed in the `v1/upgrade/` folder in the root of this repository.
