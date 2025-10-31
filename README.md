# FryoSearch — Website

Small search engine website with PHP frontend : [FryoSearch ↗](https://fryonys.ovh/search.php).

## Quick preview
- For full search (requires PHP + local DB): run
  `php -S 127.0.0.1:8000`
  then open http://127.0.0.1:8000/search.php

## Files
- [index.html](index.html) : home page (outdated)
- [search.php](search.php) : search UI (PHP, uses hostingDb.sqlite)
- [index.css](index.css), [search.css](search.css) : styles
- [search.xml](search.xml) : OpenSearch descriptor
- [site.webmanifest](site.webmanifest) : web app manifest

## Database
To run the search engine, a database is needed (name : hostingDb.sqlite)

The database has 7 columns :
 - id, url, title, domain, lang, pageRank, desc

Example (column by column) :
 - 1
 - https%3A//fr.wikipedia.org/wiki/Wikip%25C3%25A9dia%253AAccueil_principal
 - Wikipédia, l'encyclopédie libre
 - fr.wikipedia.org
 - 0.000006729621588583531
 - Article labellisé du jour Rayman est un jeu vidéo de plateformes en 2D à défilement horizontal, développé par Ludimédia (plus tard devenu Ubisoft Montpellier), et édité par Ubisoft. D'abord développé pour la console Jaguar, il est commercialisé en se


## License
[MIT](https://choosealicense.com/licenses/mit/)
