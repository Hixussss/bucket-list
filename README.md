# Bucket List Project

## Description

Ce projet est une application de gestion de liste de souhaits (bucket list) développée avec Symfony. L'application permet aux utilisateurs de créer, gérer et suivre leurs objectifs personnels.

## Technologies Utilisées

- **Framework** : Symfony
- **Base de données** : MySQL
- **CSS** : Tailwind CSS
- **Outils de développement** : npm, novita.ai

## Installation

Pour installer et configurer le projet localement, suivez les étapes ci-dessous :

1. **Cloner le dépôt** :
   git clone https://github.com/Hixussss/bucket-list.git
   cd bucket-list

2. **Installer les dépendances PHP** :
   composer install

3. **Installer les dépendances JavaScript** :
   npm install

4. **Configurer les variables d'environnement** :
   - Créez un fichier `.env.local` à la racine du projet et ajoutez les variables nécessaires :
     DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
     NOVITA_API_KEY="votre_api_key"

5. **Mettre à jour le schéma de la base de données** :
   php bin/console doctrine:schema:update --force

6. **Démarrer le serveur de développement** :
   symfony server:start

## Commandes Utiles

### Démarrer le serveur de développement

Pour démarrer le serveur de développement et compiler le CSS avec Tailwind, utilisez la commande suivante :
npm run dev

### Clear Cache

Pour vider le cache de l'application, vous pouvez utiliser la commande suivante :
php bin/console cache:clear

### Compiler le CSS avec Tailwind

Pour compiler le CSS avec Tailwind, utilisez la commande suivante :
npm run build:css

### Exécuter le Worker

Pour exécuter un worker en parallèle de l'API, utilisez la commande suivante :
php bin/console messenger:consume async -vv

## Contribuer

Les contributions sont les bienvenues ! Pour contribuer, veuillez suivre les étapes ci-dessous :

1. **Fork le dépôt**
2. **Créer une branche pour votre fonctionnalité** (`git checkout -b feature/ma-fonctionnalité`)
3. **Commit vos modifications** (`git commit -m 'Ajouter ma fonctionnalité'`)
4. **Push vers la branche** (`git push origin feature/ma-fonctionnalité`)
5. **Ouvrir une Pull Request**

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Auteurs

- **Votre Nom** - *Développeur principal* - [Votre Profil GitHub](https://github.com/votre-profil)
- **Contributeurs** - Voir la liste des [contributeurs](https://github.com/Hixussss/bucket-list/contributors) qui ont participé à ce projet.

---

Merci d'utiliser notre application de gestion de liste de souhaits ! Si vous avez des questions ou des suggestions, n'hésitez pas à nous contacter.