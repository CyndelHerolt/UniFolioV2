[![Symfony version](https://img.shields.io/badge/Symfony-7-blue?style=for-the-badge)](https://symfony.com/)
[![Bootstrap version](https://img.shields.io/badge/Bootstrap-5.3-purple?style=for-the-badge)](https://getbootstrap.com/)

[![wakatime](https://wakatime.com/badge/user/593f8558-cce2-4776-b789-4f687a124d15/project/018d8ea1-933d-45a5-aa00-270701d6e4c2.svg)](https://wakatime.com/badge/user/593f8558-cce2-4776-b789-4f687a124d15/project/018d8ea1-933d-45a5-aa00-270701d6e4c2)

# UniFolio

UniFolio est un outil de gestion de portfolios universitaires. Il permet aux étudiants de créer et de gérer leur portfolio autour d'une structure définie (référentiel de compétences, année universitaire, etc.). Les enseignants peuvent également suivre les productions des étudiants, les évaluer et leur donner un feedback.

Pour le moment développée uniquement pour l'IUT de Troyes, l'application pourrait à terme être adaptée à une utilisation universelle.

## Objectifs

- Simplifier la gestion des portfolios pour les étudiants et les enseignants.
- Permettre aux étudiants de créer et de gérer leurs portfolios universitaires.
- Permettre aux étudiants de créer et  partager des portfolios dits "de présentation" avec des professionnels.
- Permettre aux enseignants de suivre les productions des étudiants, de les évaluer et de leur donner un feedback.
- Permettre aux enseignants d'échanger autour des portfolios des étudiants pour une meilleure coordination.
- Clarifier et unifier les attentes, les objectifs et l'approche pédagogique des portfolios universitaires.
- Centraliser l'ensemble de ces processus pour une gestion simplifiée.

## Installation

### Clonage du projet
    
```bash
git clone git@github.com:CyndelHerolt/UniFolioV2.git
```

### Installation des dépendances

```bash
composer install
# ou
composer update
```

### Configuration de l'environnement

```bash
cp .env .env.local
```
Mettre à jour le fichier .env.local avec vos informations

### Création de la base de données

```bash
php bin/console doctrine:database:create
# ou
php bin/console d:d:c
```

### Création des tables

```bash
php bin/console doctrine:migrations:migrate
# ou
php bin/console d:m:m
```

### Installation des dépendances front

```bash
npm install
# ou
yarn install
```

### Compilation des assets

```bash
php bin/console sass:build 
# ou pour une compilation en mode watch
php bin/console sass:build --watch
```

### Lancement du serveur

```bash
symfony serve
```

### Accès à l'application

[http://localhost:8000](http://localhost:8000)

## Licence

[MPL-2.0](https://choosealicense.com/licenses/mpl-2.0/)


