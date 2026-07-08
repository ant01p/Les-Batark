# Installation du projet Symfony (docker) Batark

## Prérequis

- Docker
- Docker Compose

## Conteneurs utilisés

`batark_php` `batark_nginx` `batark_mysql` `batark_phpmyadmin`

## Étapes d'installation

1. Cloner le dépôt

```bash
git clone https://github.com/ant01p/Les-Batark.git
cd Les-Batark
```

2. Corriger les permissions (indispensable)
Le montage de volume Docker écrase les permissions du dossier hôte. À exécuter une seule fois après le clonage :

```bash
sudo chown -R $(id -u):$(id -g) ./app
```

3. Démarrer les conteneurs

Vérifier que les ports ne sont pas déjà utilisés (8080, 8081, 3306).

```bash
docker compose up -d --build
```

4. Gérer les droits

```bash
sudo chown -R $(id -u):$(id -g) ./app
```

5. Installer les dépendances Symfony

```bash
docker exec -it batark_php composer install
```

6. Configurer l'environnement

Créer le fichier `.env.local` :

```bash
cp app/.env app/.env.local
```

Vérifier la configuration de la base de données :

```
DATABASE_URL="mysql://user:pwd@mysql:3306/batark?serverVersion=8.0.32&charset=utf8mb4"
MESSENGER_TRANSPORT_DSN=sync://
```

## Lancer le projet

- Application : http://localhost:8080
- PhpMyAdmin : http://localhost:8081

## Commandes utiles

Accéder au conteneur PHP :

```bash
docker exec -it batark_php sh
```

Voir les logs :

```bash
docker compose logs -f
```

Arrêter les conteneurs :

```bash
docker compose down
```
