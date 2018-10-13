# ClassicPress-APIs

Basic API endpoints to get ClassicPress up and running:
https://api-v1.classicpress.net/

## Local development

Ensure you are using at least PHP 7.0, and run:

```
php -S localhost:8000 router-v1.php
```

You can then visit http://localhost:8000/ in a browser.

Note that some endpoints respond differently depending on whether they are
requested by a browser or another client, based on whether the `Accept` header
contains `text/html`.
