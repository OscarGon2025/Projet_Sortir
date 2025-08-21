
**- DESCRIPTION DU PROJET:**

Le projet Sortir.com est réalisé par un groupe de trois personnes dans le cadre de leur formation en tant que DWWM à ENI Ecole Informatique. 
Ce dernier est réalisé dans le cadre de l'apprentissage de Symfony.
Le projet à pour but de créer une plateforme web permettant aux stagiaires actuels ou anciens de l'ENI de créer, planifier ou participer à des sorties.

Le besoin était d'améliorer la communication et l'organisation d'évènenements car ces dernières étaient gérées manuellement par le Bureau Des Elèves.

Les différents objectifs sont les suivants:
  - Digitaliser l'organisation des sorties pour une meilleure accessibilité.
  - Faciliter la communication entre stagiaires grâce à une plateforme centralisée privée.
  - Automatiser la gestion des inscriptions et des participants.
  - Permettre une organisation géographique des événements via un rattachement aux différents campus.

---

**🛠️ Voici les technologies utilisées**

- PHP >= 8.3

- Symfony 6.4 (Framework principal)

- Doctrine ORM (Gestion BDD)

- MySQL (Base de données)

- Twig (Moteur de templates)

- Symfony Mailer (Envoi de mails, simulation par défaut)

- Symfony Messenger (Gestion de messages asynchrones via Doctrine)

- Symfony UX / Leaflet (Cartographie)

- Stimulus et Turbo (UX frontend)


---


**📦 Dépendances principales**

Les principales dépendances installées :

- symfony/framework-bundle

- symfony/security-bundle

- symfony/mailer

- symfony/mime

- symfony/validator

- symfony/form

- doctrine/orm

- doctrine/doctrine-migrations-bundle

- twig/twig

- symfony/ux-turbo

- symfony/stimulus-bundle

- symfony/ux-map (Leaflet)


Drivers/Extensions nécessaires :
- ext-ctype, ext-iconv, ext-http
- MySQL >= 8.0
- Composer >= 2.5

---

**⚙️ Installation**

1. Cloner le projet:
git clone https://github.com/ton-compte/openblog.git
cd openblog

2. Installer les dépendances:
composer install

3. Configurer l’environnement:

- Copiez le fichier .env en .env.local et modifiez les paramètres selon votre environnement :

Doctrine:
- DATABASE_URL="mysql://user:password@127.0.0.1:3306/openblog"


Symfony Mailer:
- Mode simulation (emails visibles dans la barre Symfony Profiler)
- MAILER_DSN=null://null

Exemple pour Mailtrap:
- MAILER_DSN=smtp://USERNAME:PASSWORD@smtp.mailtrap.io:2525
- Symfony Mailer

Messenger:
- MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0

UX Map (Leaflet):
- UX_MAP_DSN=leaflet://default


4. Créer la base de données et appliquer les migrations:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

5. Charger les fixtures (c'est optionnel):
php bin/console doctrine:fixtures:load

6. Lancer le serveur Symfony:
symfony serve -d

➡️ L’application sera disponible sur : http://localhost:8000

---

**🔐 Authentification & Sécurité**

- Système de connexion avec identifiant (username ou mail).

- Récupération de mot de passe avec token aléatoire stocké en base et lien de réinitialisation envoyé par e-mail.

- Hashage des mots de passe via UserPasswordHasherInterface.

---

**📧 Gestion des emails**

En mode dev : MAILER_DSN=null://null → les mails sont simulés et visibles dans la barre Symfony (profiler)

En mode prod : utiliser un vrai transport SMTP (ex: Gmail, Mailtrap, Sendgrid)

Exemple de configuration pour Gmail :
MAILER_DSN=smtp://user:password@smtp.gmail.com:587

---

**🧪 Tests**

Le projet intègre PHPUnit pour les tests :
php bin/phpunit

---

**👥 Contributeurs**

Projet réalisé par un groupe de 3 développeurs dans le cadre d'un projet pédagogique de la formation DWWM - ENI École Informatique.


Contributions:

- Backend : mise en place de Symfony, entités Doctrine, sécurité et gestion des utilisateurs.

- Frontend : intégration Twig, UX avec Stimulus/Turbo et Leaflet pour la cartographie.

- Base de données : conception MySQL, migrations et fixtures.

- Outils : gestion des mails, gestion des tokens sécurisés, mise en place des tests PHPUnit.

--- 

**📄 Licence**

Ce projet est distribué sous licence **propriétaire**.  
Son utilisation est limitée au cadre de la formation DWWM à l’ENI École Informatique.  
Toute reproduction, modification ou diffusion en dehors de ce cadre est interdite sans autorisation préalable.

---

**💡 Développé avec Symfony 6.4 dans le cadre d’un projet collaboratif.**
