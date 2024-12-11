# XiX@t - *Application de brainstorm assisté par IA*

## Description

Ce projet est une application web de brainstorming développée avec Symfony.
Elle permet aux utilisateurs de rejoindre des "XatRooms" où ils peuvent échanger
en temps réels avec leurs camarades.

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- Symfony CLI
- MySQL ou MariaDB

## Installation

1. Clonez le dépôt :

    ```bash
    git clone https://github.com/MikkoPet/xiXat.git
    cd xiXat
    ```

2. Installez les dépendances :

    ```bash
   composer install
    ```

3. Déployez les conteneurs Docker de la BDD et de Mercure

    ```bash
   cd docker/
   docker compose up -d
   cd ../
   docker compose up -d
    ```

4. Exécutez les migrations et chargez les fixtures:

    ```bash
   php bin/console d:m:m
   php bin/console d:f:l
    ```

5. Lancez le serveur de développement :

    ```bash
   symfony server:start
    ```

## Utilisation

Accédez à l'application via votre navigateur à l'adresse ```http://localhost:8000```. Vous serez redirigé vers la page de connexion où vous pourrez utiliser un compte exemple:

```bash
Email: user1@example.com
Password: password1
```

## Technologies utilisées

- Symfony 7.1 comme framework PHP
- Doctrine ORM pour la gestion de la base de données
- Twig comme moteur de templates
- Groq Api pour l'intégration de l'IA

## Contributeur.ices

- ***Shanaz Petremand***

<a href="https://github.com/MikkoPet"><img src="https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white"></img></a>
<a href="https://www.linkedin.com/in/shanaz-petremand-1b7676240/"><img src="https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white"></img></a>

- ***Sarah Katz***

<a href="https://github.com/Sarah-Katz"><img src="https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white"></img></a>
<a href="https://www.linkedin.com/in/sarah-katz-dev/"><img src="https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white"></img></a>
