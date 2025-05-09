PhpFakeAPI – API REST simulée en PHP
====================================

PhpFakeAPI est un petit serveur d'API REST simulée, écrit en PHP, permettant d'exposer un fichier `db.json` comme s’il s’agissait d’une vraie base de données. Inspiré par json-server.

Fonctionnalités
---------------
- Lecture, écriture, modification de données à partir d’un seul fichier JSON
- Routes dynamiques générées selon les clés du fichier (ex: /users, /posts)
- Support des méthodes GET, POST, PUT, DELETE
- Aucune base de données nécessaire

Installation
------------
1. Clone ce dépôt :
   git clone https://github.com/FunkyDuck/PhpFakeAPI.git
2. Crée un fichier `data/db.json` avec ta structure
3. Lance le serveur :
   php -S localhost:8000 -t public

Exemple de structure (db.json)
------------------------------
{
  "users": [
    { "id": 1, "name": "Alice" }
  ],
  "posts": [
    { "id": 1, "title": "Hello" }
  ]
}

Routes disponibles
------------------
- GET     /users
- GET     /users/1
- POST    /users
- PUT     /users/1
- DELETE  /users/1

Recommandations
---------------
- Ne pas utiliser en production
- Idéal pour tester un front-end rapidement
- Le champ `id` est obligatoire dans chaque objet

Licence
-------
MIT – libre d'utilisation et de modification.
