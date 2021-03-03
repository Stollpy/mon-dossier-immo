# mon-dossier-immo

## Instructions


1. installer les dependances `composer install`


2. Creer le fichier .env.local et ecrire : 
    - la configuration de BDD ( MySql )
    - ainsi que celle pour mailer ( utilisation d'un serveur smtp ou autre MailTrap par exemple )
    - l'auhtentification via réseaux sociaux. ( clé API Google )
    - Indiquer dans la constanste `SITE_BASE_URL` l'url de base ( ex : localhost:8000 )


3. Creer la base de donnees : 
  - `symfony console doctrine:database:create`
  -  faire les migrations `symfony console make:migrations`
  -  lancer les migration `symfony console doctrine:migration:migrate`
 
 
4. Dernière étape l'installation du projet version minima :
   
   Deux options :
      1. Dans votre terminal lancer la commande `symfony console app:create-project-minima`, 
         cette commande vous permettra de créer le project de version minima sans avoir de contenu de test.
      
      2. Démarrer les fixtures `symfony console doctrine:fixtures:load` ! Une recette de fixtures est préparer 
         en lanceant le projet minima avec les donners de test.
