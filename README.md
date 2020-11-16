## Installation


```php
git clone https://github.com/maximeaudy/immo_app.git
cd immo_app/
docker build -t immo_app/php-apache .
```

## Configuration base de donnée

**Hôte:** mysql

**Port:** 3308

## Démarrer l'application
```php
docker-compose up -d
```
L'application tourne sur : **http://localhost:3333/**