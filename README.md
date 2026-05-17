## Objectif du projet

L'objectif de ce projet est de, sur la base d'un réseau social simple  utilisant le framework Laravel, implémenter une fonctionnalité de sondage utilisant le framework Vue dialoguant avec le backend via des appels à l'api. Ce projet permettra de mettre en pratique les concepts appris dans le cours.
Les nouvelles fonctionnalités sont les suivantes :
- afficher à la personne connectée la liste de ses sondages
- permettre à la personne connectée de créer, modifier et supprimer ses sondages
- permettre de définir la question, les options de réponse et les paramètres du sondage
- créer un sondage en mode brouillon, puis permettre de le démarrer soit au moment de sa création, soit plus tard
- permettre de configurer si le sondage accepte un choix simple ou plusieurs choix
- permettre de configurer si les résultats sont publics ou non
- permettre de configurer une durée de disponibilité du sondage
- permettre au créateur d'un sondage d'obtenir facilement le lien de partage contenant le token
- afficher une page de vote accessible via un lien contenant un token dans l'URL
- permettre à une personne authentifiée ayant reçu ce lien de voter au sondage
- permettre à une personne non authentifiée ayant reçu ce lien de consulter les résultats si, et seulement si, leur visibilité est publique
- afficher sur la page de vote les résultats en direct, via un polling régulier vers l'API
- afficher sur la page de vote un aperçu graphique des résultats ; le type de graphique est libre
- indiquer clairement sur la page de vote qu'il n'est plus possible de voter lorsque la date de fin d'un sondage avec durée est dépassée

## Décisions UX

Afin de rendre l'expérience utilisateur cohérente, les décisions suivantes ont été prises :
- un.e utilisateur.trice ne peut pas voter à son propre sondage
- un.e utilisateur.trice ne peut pas modifier les paramètres du sondage une fois celui-ci publié
- un.e utilisateur.trice ne défini que la durée du sondage. La date de début est défini au moment de la publication du sondage, et la date de fin est calculé à ce moment là en y additionnant la durée
- l'affichage des résultats d'un sondage est rafraichi toutes les 5 secondes (la valeur est arbitraire, ne sachant pas l'utilisation réel et la vitesse de changement des votes)

## Développement local

Pour développer et tester le projet en local, voici les étapes à suivre :

1. Forker ce dépôt

2. Installer les dépendances avec npm et Composer :

    ```bash
    npm install && npm run build

    composer install
    ```

3. Copier le fichier `.env.example` en `.env`.
4. Modifier les variables d'environnement si nécessaire (optionnel).
5. Générer la clé d'application Laravel :

    ```bash
    php artisan key:generate
    ```

6. Créer le lien symbolique pour les fichiers téléversés :

    ```bash
    php artisan storage:link
    ```

7. Créer la base de données et exécuter les migrations :

    ```bash
    php artisan migrate
    ```

    S'il est nécessaire de réinitialiser la base de données, utiliser la commande `php artisan migrate:reset` puis `php artisan migrate` à nouveau.

8. Optionnel : en mode développement, il est possible de peupler la base de données avec des données fictives. Ces données ont été configurée pour tester les différents cas de figures de l'application :

    ```bash
    php artisan db:seed
    ```

9. Démarrer le serveur de développement Laravel :

    ```bash
    composer run dev
    ```

i. Reset la base de donnée pour recommencer une phase de test (dans le cas où les tests auraient détruit les données) :

    ```bash
    php artisan migrate:fresh --seed
    ```

L'application sera accessible à l'adresse <http://127.0.0.1:8000>.
