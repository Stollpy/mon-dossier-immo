# mon-dossier-immo

## Instructions


1. installer les dependances `composer install`

2. Creer le fichier .env.local et ecrire la configuration de BDD ainsi que celle pour mailer
  et l'auhtentification via réseaux sociaux.

3. Creer la base de donnees `symfony console doctrine:database:create` et faire les migrations
`symfony console make:migrations` puis `symfony console doctrine:migration:migrate`
