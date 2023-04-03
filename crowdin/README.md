# Groupe de gillet_p 956891

## Prérequis

Nous utilisons composer pour le projet, par conséquent il vous faut composer. De plus, il vous faut une base de donnée disponible. Et enfin, 
un moyen d'utiliser javascript comme yarn ou npm.

## Installation

Deplacer vous dans le dossier `crowdin` et utiliser la commande `composer install`, `npm install` et `npm run build`.

Ensuite, verifiez les informations dans le .env et adaptez la variable `DATABASE_URL` avec le lien de votre base de donnée.

Pour créer les tables, utilisez la commande `php bin/console doctrine:migrations:migrate`.

Si vous avez déjà des données, il est préférable de supprimer la base de donnée avec `php bin/console doctrine:schema:drop --force`
et de la recréer avec `php bin/console doctrine:schema:create`. Cela permet de reinitialiser les id.

Pour remplir votre base de données avec des données pré-défini, utilisez la commande `php bin/console doctrine:fixtures:load`
Il est conseillé de le faire pour avoir au moins les langues et d'avoir 'reinitialiser' la base de donnée comme indiquer auparavent
pour avoir des données coherentes.

Il ne vous restera plus qu'à lancer `php bin/console server:run` pour lancer le serveur symfony.