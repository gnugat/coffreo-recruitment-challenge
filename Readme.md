# Coffreo : Recruitment Challenge

## Objectif

Le but de cet exercice est de reprendre le code source de l'application fournie et de l'améliorer en utilisant les bonnes pratiques de développement.
Le projet est composé de plusieurs worker PHP utilisant RabbitMQ pour la communication asynchrone.

### Premier worker

Le premier worker consommera des messages contenant le nom du pays.
Il utilisera l’API REST https://restcountries.com/ pour récupérer la capitale du pays spécifié.
Il publiera le résultat pour un second worker.

### Deuxième worker

Le second worker consommera le message publié par le premier worker contenant la capitale du pays.
Il utilisera l'API REST https://restcountries.com/ pour récupérer des informations sur la capitale.

## Piste d'améliorations

- Refactoring : Améliorer la qualité du code en refactorisant les parties qui en ont besoin.
- Gestion des Erreurs : Gérer les erreurs et les scénarios d’échec de manière robuste et réfléchie.
- Tests Unitaires : Ajouter des tests unitaires pour les workers.
- Documentation : Ajouter des commentaires et de la documentation pour expliquer le fonctionnement de l’application.
- Sécurité : Assurer que l’application est sécurisée et qu’elle ne présente pas de vulnérabilités.
- Performances : Optimiser les performances de l’application.
- Scalabilité : Assurer que l’application est capable de gérer un grand nombre de requêtes simultanées.
- Extensibilité : Assurer que l’application est facile à étendre et à maintenir.
- Docker : Modifier le dockerfile ou le docker-compose pour faciliter le déploiement de l’application.
- CI/CD : Ajouter un pipeline CI/CD pour automatiser les tests et le déploiement de l’application.
- Toute autre amélioration que vous jugez pertinente.

## Bonus

Créer des nouveaux workers pour compléter le processus et ajouter des fonctionnalités à l'application.

## Livrable

- Le code source de l’application, y compris les fichiers de configuration.
- Une documentation comprenant au moins la manière de configurer et de lancer l’application.
– Une description des composants externes utilisés et la justification de leur choix.

## Évaluation

Les candidats seront évalués sur leur capacité à :

- Implémenter une communication efficace entre les workers via RabbitMQ.
- Interagir correctement avec une API externe et traiter les données reçues.
- Gérer les erreurs et les scénarios d’échec de manière robuste et réfléchie.
- Organiser et documenter leur code pour faciliter la maintenance et la compréhension.
- Opter pour des bibliothèques ou des composants qui sont strictement nécessaires pour accomplir les objectifs de l’exercice.
- Justifier ses décisions techniques prises, en particulier le choix des bibliothèques.
- Fournir un livrable facilement exécutable avec les instructions pour tester son application.

## Message au candidat

Alors que vous vous apprêtez à relever ce défi, nous tenons à vous exprimer notre soutien et notre encouragement.

Cet exercice est une opportunité pour vous de montrer vos compétences uniques et votre capacité à innover dans la résolution de problèmes.

Ne voyez pas cela seulement comme un test, mais comme une occasion de partager votre passion pour le développement.

Soyez assurés que nous recherchons plus qu’une solution fonctionnelle ; nous cherchons à comprendre votre approche, votre manière de penser et la façon dont vous relevez les défis.

Nous vous remercions d’avance pour votre engagement et pour jouer le jeu avec sérieux et créativité.
