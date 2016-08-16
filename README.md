# AF-Deals
This is a web monitor that scans **deals** from websites: *Abercrombie &amp; Fitch* and *Hollister*. When there's a new **deal**, it will alert you by music.

[Demostration](http://dev96.com/af)

# Requirements
* Python or NodeJS
* PHP
* MySQL


# Quickstart

1. Import 'mysql.sql' into MySQL Server.
2. Edit config.php.
3. Add the crawler to Cron, set it to run for every minute.

### Crawler

There are two crawlers. One is written in NodeJS, the other is in Python.
* NodeJS - /srv2/srv.js
* Python - /srv/af.py

# License

This is a free software. Do whatever you want with it.
