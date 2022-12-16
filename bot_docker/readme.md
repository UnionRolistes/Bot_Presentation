# prez

## config

update environment variables in the docker-compose.yml file for oauth2 with discord

``` yaml
service:
  php:
    environment:
      - CLIENT_ID=1111111111
      - CLIENT_SECRET=1111111111
      - REDIRECT_URI=http://presentation.unionrolistes.fr/php/get_token.php
```

## bot

add $prez to the bot

## web

add prez web service to the web server
