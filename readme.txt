=== WP e-Commerce Atos SIPS ===Contributors: 6WWWDonate link: http://wpcb.fr/donate/Tags: wp-e-commerce, atos, sips, carte bancaire, wpcb, mercanet, 6WWWRequires at least: 2.7Tested up to: 3.3Stable tag: 1.1.6

Paiement par cartes bancaires Atos SIPS (majoritée des banques françaises) pour le plugin WP e-Commerce.== Description ==Paiement par cartes bancaires Atos SIPS (majoritée des banques françaises) pour le plugin WP e-Commerce.Atos SIPS est la technologie utilisée par de nombreuses banques françaises :* Banque Populaire (CyberPlus, tm)* Société Générale (Sogenactif, tm)* Crédit Lyonnais (Sherlock, tm)* Crédit du Nord (Webaffaires, tm)* CCF (Elysnet, tm)* BNP (Mercanet, tm)
* et de nombreuses autres banques basée sur la technologie ATOS SIPS

= A venir pour les détenteurs d'une clé API =
* Ajout dans google drive de toutes vos ventes !
* Ajout de tous vos acheteurs dans votre outil de mailling MailChimp
== Installation ==1. Envoyer `wpcb` vers le dossier `/wp-content/plugins/`2. Activer le plugin dans le menu 'Extensions' de Wordpress3. Placer `[wpcb]` sur une (et une seule!) page
4. Régler les paramètres suivant les indications

== Frequently Asked Questions ==

= Est-ce que ma banque est prise en charge ? =
Si vous avez reçu un set de fichier comme ci-dessous alors oui.
= Que faire des fichiers envoyé par ma banque ? =

Configurer correctement vos dossiers/fichiers obtenus par votre banque (dossier crypté)
Dans le dossier cgi-bin (non visible depuis Internet) vous devez avoir :
* parcom.mercanet
* parcom.005009461540411 (votre numéro de marchand à la place de celui là)
* log.txt
* certif.fr.005009461540411 (votre numéro de marchand à la place de celui là)
* pathfile (à modifier suivant cet exemple)
* request
* response
Note : les fichiers call_request.php, call_response.php et call_auto_response.php dans le package fourni par la banque ne sont pas necessaires car wpcb les remplace.

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-14.20.411.png

= Comment activer/déscativer le paiement par carte bancaire ? =
Réglages > Boutique > Paiements
http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.30.06.png

= Comment personaliser la page des icones de cartes bancaires ? =
Créer une page WordPress avec le shortcode : '[wpcb]'.
Vous venez de créer la page qui affichera les icônes des cartes bleues une fois que le client aura cliqué sur Achat(Voir l'image ci-dessous, partie droite). Vous pouvez ajouter du texte comme bon vous semble sur cette page.

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.24.50.png

= A quoi sert le mode test ? =
Le mode test permet de vérifier automatiquement le paiement sans passer par l'étape de saisie du numéro de carte bancaire. Il vous permet de vérifier que tout se passe bien dans votre processus.

= A quoi sert le mode demo ? =
Le mode demo permet d'utiliser le kit de démo fournit par votre banque. (Ne marche pas toujorus très bien...)

= Autre question ? =
Merci de poser vos questions sur le forum en cliquant à droite sur le bouton vert ->

Attention : Nous ne sommes pas responsable de la mauvaise utilisation du plugin WPCB mis à votre disposition gratuitement et toujours en phase d'amélioration. Vous l'utilisez en tout conscience et vous vous assurez de la protection de vos pages internet.== Screenshots ==1. Réglages du module2. Choix de la méthode de paiement3. Les cartes bancaires qui redirigent vers l'espace sécurisé Atos SIPS== Changelog ==


= 1.1.5 =* Correction d'un bug de vidage de panier* Réorganisation des options* Ajout de la clé API= 1.1.3 =* Amélioration de la sécurité importante (Merci à Cyril Lecomte).= 1.1.2 =* Correction d'une erreur de suppression du plugin.=1.1.1=* Le mode test a été amélioré.= 1.1 =* Internationalized* Atos currency_code added to the settings of the plugin* Atos language added to the settings of the plugin* Atos merchant_country added to the settings of the plugin* Atos header_flag added to the settings of the plugin= 1.0.4 =* Update to wpec api v2.0= 1.0.3 =* Syntax correction= 1.0.2 =* Mises à jour mineures= 1.0.1 =* Mises à jour mineures= 1.0 =* Première version

== Upgrade Notice ==Rien de particulier.