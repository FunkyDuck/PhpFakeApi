PhpFakeAPI – API REST simulée en PHP
====================================

PhpFakeAPI est un petit serveur d'API REST simulée, écrit en PHP, permettant d'exposer des fichiers `db.json` comme s’il s’agissait de vraies bases de données. Inspiré par json-server.

Fonctionnalités
---------------
- Lecture, écriture, modification de données à partir de plusieurs fichiers JSON
- Validation des données par un schéma JSON `db-schema.json`
- Routes dynamiques générées selon les clés du fichier (ex: /users, /posts)
- Support des méthodes GET, POST, PUT, DELETE
- Aucune base de données nécessaire
- Gestion des erreurs et codes RESTful
- CORS activé pour les appels cross-origin

Installation
------------
1. Clone ce dépôt :
   git clone https://github.com/FunkyDuck/PhpFakeAPI.git
2. Crée un fichier `data/db.json` avec ta structure et `data/db-schema.json` pour valider les données 
3. Lance le serveur :
   php -S localhost:8000 -t public

Exemple de structure (db.json)
------------------------------
[
  { "id": 1, "name": "Alice" },
  { "id": 2, "name": "Bob" }
]

Exemple de schema (db-schema.json)
----------------------------------
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "User",
  "type": "object",
  "properties": {
    "id": { "type": "integer", "minimum": 1 },
    "name": { "type": "string" }
  },
  "required": ["name"]
}


Routes disponibles
------------------
| Méthode | URL      | Description                      |
| ------- | -------- | -------------------------------- |
| GET     | /users   | Liste tous les utilisateurs      |
| GET     | /users/1 | Récupère l’utilisateur avec id=1 |
| POST    | /users   | Crée un nouvel utilisateur       |
| PUT     | /users/1 | Modifie l’utilisateur avec id=1  |
| DELETE  | /users/1 | Supprime l’utilisateur avec id=1 |
| OPTIONS | /users   | Pré-vol CORS                     |

Recommandations
---------------
- Ne pas utiliser en production, conçu pour le prototypage rapide de REST API
- Idéal pour tester un front-end rapidement
- Le champ `id` est géré automatiquement

Licence
-------
MIT – libre d'utilisation et de modification.
