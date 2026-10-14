# OUSWEAR — E-commerce de mode masculine

OUSWEAR est une application e-commerce dédiée à la vente de vêtements et d'accessoires pour hommes.

## Technologies

- Laravel : API backend
- PHP : logique serveur
- MySQL / MariaDB : base de données
- Laravel Sanctum : authentification API
- FedaPay : intégration du paiement en ligne
- Vue.js : interface frontend, hébergée dans un dépôt séparé

## Fonctionnalités

- Authentification des utilisateurs
- Consultation des catégories et des produits
- Gestion du panier
- Gestion des adresses de livraison
- Création et consultation des commandes
- Paiement à la livraison et intégration FedaPay
- Gestion des avis sur les produits
- Gestion des variantes et des images des produits

## Installation

1. Cloner le dépôt.
2. Installer les dépendances PHP avec `composer install`.
3. Copier `.env.example` vers `.env`.
4. Configurer la base de données dans `.env`.
5. Générer la clé de l'application avec `php artisan key:generate`.
6. Exécuter les migrations avec `php artisan migrate`.
7. Démarrer le serveur avec `php artisan serve`.

## Configuration

Configure les paramètres de connexion à la base de données et les identifiants FedaPay dans ton fichier `.env`.

Ne publie jamais ce fichier sur GitHub.

## Frontend

Le frontend Vue.js est géré dans un dépôt Git distinct.

## Statut

Projet en cours de développement.
