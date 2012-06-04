=== WP e-Commerce Atos SIPS ===Contributors: 6WWWDonate link: http://wpcb.fr/donate/Tags: wp-e-commerce, atos, sips, carte bancaire, wpcb, mercanet, merc@netRequires at least: 2.7Tested up to: 3.3Stable tag: 1.1.6Module de paiement Atos SIPS (majoritée des banques françaises) pour le plugin WP e-Commerce== Description ==Module de paiement Atos SIPS (majoritée des banques françaises) pour le plugin WP e-Commerce.Atos SIPS est la technologie utilisée par de nombreuses banques françaises :* Banque Populaire (CyberPlus, tm)* Société Générale (Sogenactif, tm)* Crédit Lyonnais (Sherlock, tm)* Crédit du Nord (Webaffaires, tm)* CCF (Elysnet, tm)* BNP (Mercanet, tm)
* et d'autres !== Installation ==1. Envoyer `wpcb` vers le dossier `/wp-content/plugins/`2. Activer le plugin dans le menu 'Extensions' de Wordpress3. Placer `[atos]` sur une (et une seule!) page
4. Régler vos paramètres personalisé

== Frequently Asked Questions === Que faire des fichiers de la banque ? =

Configurer correctement vos dossiers/fichiers obtenus par votre banque (dossier crypté)
Dans le dossier cgi-bin (non visible depuis Internet) vous devez avoir :
- parcom.mercanet
- parcom.005009461540411 (votre numéro de marchand à la place de celui là)
- log.txt
- certif.fr.005009461540411 (votre numéro de marchand à la place de celui là)
- pathfile(à modifier suivant cet exemple)
- request
- response
Note : les fichiers call_request.php, call_response.php et call_auto_response.php dans le package fourni par la banque ne sont pas necessaires car wpcb les remplace.

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-14.20.411.png

= Comment activer/déscativer le paiement par CB ? =
Régler le WPCB dans : Réglages > Boutique > Paiements
http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.30.06.png

= Les icones de CB sont ou ? =
Créer une page WordPress avec le shortcode : '[wpcb]'. Vous venez de créer la page qui affichera les icônes des cartes bleues une fois que le client aura cliqué sur Achat(Voir l'image ci-dessous, partie droite).

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.24.50.png


= Comment configurer la facture ? =
L'acheteur reçoit ensuite sa facture par email. Celle-ci peut être personnalisée dans Réglages > Boutique > Admin > Messages Personnalisés >Reçu d'achat

Exemple :
'Merci pour votre commande sur %shop_name%, vos courses vont vous être expédiées aussi vite que possible.

Numéro de commande : %purchase_id%
You ordered these items:
%product_list%%total_shipping%%total_price%
Les prix sont TTC.

A bientôt sur %shop_name%'

Note : La phrase : "L'opération a été effectuée avec succès" s'ajoute au début du message. L'objet de l'email est : "Reçu d'Achat"

= Que reçoit le vendeur ? =

Configuration du message de confirmation du règlement au vendeur

Le vendeur (vous) reçoit un email pour lui avertir qu'une commande a été réglée. Cet email se personnalise dans Réglages > Boutique > Admin > Messages Personalisés > Rapport d'administration

Exemple:

'Une commande vient d'être passée sur  le site %shop_name% !

Numéro de commande : %purchase_id%
%product_list%%total_shipping%%total_price%
Les prix sont TTC.
Note : les coordonnées de l'acheteur s'ajoute au dessus de ce message : Nom, Email, Coordonnées postales, etc.'

= Autre question ? =thomas@6www.net
Attention : Nous ne sommes pas responsable de la mauvaise utilisation du plugin WPCB mis à votre disposition gratuitement et toujours en phase d'amélioration. Vous l'utilisez en tout conscience et vous vous assurez de la protection de vos pages internet.== Screenshots ==1. Réglages du module2. Choix de la méthode de paiement3. Les cartes bancaires qui redirigent vers l'espace sécurisé Atos SIPS== Changelog ==

= 1.1.6 =
* Ajout de l'aide= 1.1.5 =* Correction d'un bug de vidage de panier* Réorganisation des options* Ajout de la clé API= 1.1.3 =* Amélioration de la sécurité importante (Merci à Cyril Lecomte).= 1.1.2 =* Correction d'une erreur de suppression du plugin.= 1.1.1 =* Le mode test a été amélioré.= 1.1 =* Internationalized* Atos currency_code added to the settings of the plugin* Atos language added to the settings of the plugin* Atos merchant_country added to the settings of the plugin* Atos header_flag added to the settings of the plugin= 1.0.4 =* Update to wpec api v2.0= 1.0.3 =* Syntax correction= 1.0.2 =* Mises à jour mineures= 1.0.1 =* Mises à jour mineures= 1.0 =* Première version== Upgrade Notice ==Sauvegardez vos sites avant de mettre à jour