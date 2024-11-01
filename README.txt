=== Stan Easy Connect ===
Contributors: jnser
Tags: stanapp, stan, sso, signin, signup, oauth, oauth2, openid, oidc, connect, login, password
Requires at least: 5.0.0
Tested up to: 6.3.1
Stable tag: 1.4.8
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Vous perdez des utilisateurs lorsque vous demandez de s'inscrire, remplir les formulaires est la première raison qui mène les utilisateurs à quitter un site. Avec Stan Connect vos utilisateurs s'inscrivent sans formulaire, sans contrainte.

== Description ==

Stan est une application d'identité digitale. Nos utilisateurs sont inscrits avec leurs documents légaux d'identité, certifiés par un service eIDAS.

Avec le plugin Stan Easy Connect, vous ouvrez encore plus votre site à de nouveaux utilisateurs qui s'inscrivent et se connectent sans passer par un formulaire d'inscription. Stan Easy Connect intègre un standard OAUTH2 avec OpenID pour transmettre les données des utilisateurs sur votre site en toute sécurité.

Nos utilisateurs ont confiance en Stan, ils l'utilisent ! En proposant une connexion Stan Connect sur votre site, c'est une confiance déjà acquise pour vous, et vous proposez une superbe expérience utilisateur.

== Installation ==

# DEPUIS VOTRE COMPTE ADMIN WORDPRESS

Sur https://votre-site.fr/wp-admin

1. Allez sur Extensions > Ajouter
2. Cherchez Stan Easy Connect, trouvez et installez Stan Easy Connect.
3. Activez le plugins depuis la page Extensions

# DEPUIS WORDPRESS.ORG

1. Téléchargez Stan Easy Connect
2. Dé-zippez and téléversez le dossier stan-easy-connect dans `/wp-content/plugins/directory`
3. Activez Stan Easy Connect depuis la page des plugins

# UNE FOIS ACTIVE

1. Allez dans Réglages > Stan Easy Connect, et remplissez les informations
2 (optionnel). Ajoutez le shortcode `[stan_easy_connect_button]` sur votre site

== F.A.Q. ==

= Pourquoi utiliser Stan Easy Connect ? =

Stan Easy Connect améliore l'expérience de vos utilisateurs à l'inscription et à la connexion, tout se fait sans formulaires.

= Comment avoir un identifiant Client et le code secret ? =

Inscrivez vous sur notre site [Stan-app](https://compte.stan-app.fr), ces accès vous seront transmis par email.

= Est-ce sécurisé ? =

Stan Connect est basé sur le standard OAUTH2 avec OpenID pour transmettre les informations. Ces standards sont soigneusement élaborés et sont utilisés par les plus grands services tels que Google, Facebook, Github, etc.

= Comment les utilisateurs s'enregistrent et se connectent sur mon site avec Stan Easy Connect ? =

Les utilisateurs se connectent sur votre site avec le bouton "Se connecter avec Stan".

Ce bouton redirige vers leur application Stan pour confirmer leur connexion sécurisée. Stan ne demande aucun mot de passe, c'est simple tout est sécurisé dans l'application.

Les informations de l'utilisateur sont ensuite transmises vers votre site, l'utilisateur s'est inscrit et s'est connecté sans avoir rempli de formulaire !

= Que se passe t-il si un utilisateur déjà inscrit sur mon site se connecte avec Stan ? =

Le plugin utilise pour identifiant l'adresse email de l'utilisateur. Lorsqu'un utilisateur déjà inscrit sur votre site décide de passer par une connexion avec Stan, il se connectera avec le compte initial si il utilise la même adresse mail sur Stan et sur votre site.

== Captures ==

1. Bouton "Se connecter avec Stan"
2. Page de connexion vue par l'utilisateur sur l'application Stan
3. Connexion réussie vue par l'utilisateur sur l'application Stan

== Mise à jour ==

= 1.4.0 =
* Ajout automatique du coupon "STANNER" si il existe

= 1.3.0 =
* Meilleure gestion des scopes

= 1.2.0 =
* Nouveau graphisme du menu de configuration

= 1.1.0 =
* Options supplémentaires pour configurer Stan Connect

= 1.0.0 =
* Première version officielle.

= 0.1 =
* Première version de Stan Easy Connect.
