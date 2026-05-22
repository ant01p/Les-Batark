# Projet Symfony avec Docker (from scratch)

## Prérequis

* Docker
* Docker Compose
* Git

## Initialisation du projet

1. Créer votre dossier de travail :

```bash
mkdir mon-projet
cd mon-projet
```

2. Ajouter le socle Docker en déplacant dans ce dossier :

- le dossier docker
- le fichier docker-compose.yml

3. Gérer les droits

```bash
sudo chown -R $(id -u):$(id -g) ./app
```

4. Adapter le docker-compose.yaml

- Renommer les conteneurs

Dans docker-compose.yaml, renommer les conteneurs en remplaçant symfony par un nom approprié à votre projet.
ex : symfony_php --> mon_super_site_php

- Définir vos identifiants de connexion à mysql (mysql_user, mysql_password)
- Définir le nom de votre base de données (mysql_database)

5. Initialiser Git :

```bash
git init
```

6. Initialisation de GitHub

- Créer un repo sur GitHub
- Lier le projet local :

```bash
git remote add origin https://github.com/<user>/<repo>.git
git branch -M main
git add .
git commit -m "Initial commit"
git push -u origin main
```

7. Builder et lancer les conteneurs :

```bash
docker compose up -d --build
```

Si nécessaire arréter les conteneurs qui seraient en conflit.
Ou arréter tous les conteneurs en une seule commande :
```bash
docker stop $(docker ps -q)
```
Ou plus radical, supprimer tous les conteneurs en une seule commande :
```bash
docker rm -f $(docker ps -qa)
```

8. Installer Symfony dans le conteneur PHP :

```bash
docker exec -it <nom_du_conteneur_php> sh
composer create-project symfony/skeleton .
```

Si vous souhaitez installer les principales dépendences
```bash
composer require webapp
```

## Configuration

Créez un fichier `.env.local` et placez-y :

```
DATABASE_URL="mysql://mysql_user:mysql_password@mysql:3306/mysql_database?serverVersion=8.0.32&charset=utf8mb4"
MAILER_DSN=smtp://mailhog:1025
MESSENGER_TRANSPORT_DSN=sync://
```

## Lancer le projet

* Application : http://localhost:8080
* PhpMyAdmin : http://localhost:8081
* MailHog : http://localhost:8025

## Commandes utiles

Accéder au conteneur PHP :

```bash
docker exec -it <nom_du_conteneur_php> sh
```

Arrêter les conteneurs :

```bash
docker compose down
```
