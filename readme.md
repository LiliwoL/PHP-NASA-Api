# Projet Gif Of Earth

Un petit projet PHP qui va chercher les images de la terre fournies par la NASA à une date donnée.

Les images sont téléchargées en local, dans un dossier correspondant à la date.
Les images sont redimensionnées.
Puis un gif est généré et envoyé par mail.

## L'API

* Plus d'infos sur l'API en question:
https://epic.gsfc.nasa.gov/about/api

* Attention, toutes les dates ne sont pas disponibles!
https://epic.gsfc.nasa.gov/api/natural/available

Sur cette adresse, on peut trouver la liste des dates disponibles.

***

## TODO:

* Pour redimensionner des images et créer un gif en ligne de commande, on utilise **ImageMagick**.
* Définir le fichier **.env** avec les variables personnalisées suivantes:

`env
API_ENDPOINT="https://epic.gsfc.nasa.gov/api/enhanced/date/" # You can keep it
API_KEY=''                                             # Your NASA's API Key
MAILER_DSN='smtp://user_name:password@smtp.host:port'  # Use Mailtrap.io
`

***

## Requirements

### ImageMagick

https://gist.github.com/LiliwoL/4adf9d249b54812fea0a0c1329553ace

#### Install on Mac

`brew install ImageMagick`
or
`sudo port install imagemagick`

#### Install on Debian

`sudo apt install -y imagemagick`

***

### SymfonyMailer

https://symfony.com/doc/current/mailer.html#installation

`composer require symfony/mailer`

Librairie pour générer et envoyer des emails.
Made in symfony!

### DotEnv

Sera utilisé pour disposer de variables d'environnement

https://packagist.org/packages/vlucas/phpdotenv

`composer require vlucas/phpdotenv`

***

