=== WPCB ===Contributors: 6WWWDonate link: http://wpcb.fr/donate/Tags: wp-e-commerce, atos, sips, carte bancaire, wpcb, mercanet, 6WWW, mailchimp, trello, paypal, ExpeditorRequires at least: 2.7Tested up to: 3.4.1Stable tag: 2.4.8

Paiement par cartes bancaires (majoritée des banques françaises), paypal, chèques et virement pour le plugin WP e-Commerce.
Calcul de frais de port basé sur la poste (colis, chronopost, et d'autres à venir...)
== Description ==Paiement par cartes bancaires (majoritée des banques françaises), paypal, chèques et virement pour le plugin WP e-Commerce.

Fonctionne pour de nombreuses banques françaises :* Banque Populaire (CyberPlus, tm)* Société Générale (Sogenactif, tm)* Crédit Lyonnais (Sherlock, tm)* Crédit du Nord (Webaffaires, tm)* CCF (Elysnet, tm)* BNP (Mercanet, tm)
* et de nombreuses autres banques basée sur la technologie ATOS SIPS ou SYSTEMPAY CYBERPLUS

= Gestion des factures =
Préfixe de facture et numéro de facture incremental.
Le lien vers la facture peut être envoyé dans l'email au client pour qu'il l'imprime.= Paypal =
Système Paypal fonctionel (avec option sandbox!)

= Customisation de l'email de confirmation =
Vous pouvez utiliser %billingfirstname%, etc pour personaliser votre email de confirmation !
Voir la faq pour les shortcodes disponibles.
= Ajout dans trello =Toutes les ventes s'ajoutent dans votre tableau de bord Trello !

= Ajout dans google spreadsheet =Toutes les ventes s'ajoutent dans votre tableau google spreadsheet (excel en ligne) !

= Affichage du nombre de ventes =

Vous pouvez afficher aux visiteurs le nombre de vente de chaque produit

= Compte à rebours =

Vous pouvez afficher un compte à rebours sur vos produits (vente flash par exemple). Necessite le plugin : http://wordpress.org/extend/plugins/wordpress-countdown-widget/
En plus de l'affichage du compte à rebours, le produit est désactivé quand le temps est écoulé.

== Installation ==1. Envoyer `wpcb` vers le dossier `/wp-content/plugins/`2. Activer le plugin dans le menu 'Extensions' de Wordpress
3. Régler les paramètres suivant les indications
4. Ajouter le shortcode [wpcb] sur une page vierge de votre site (optionel)

== Frequently Asked Questions ==

= Comment télcharger le fichier CSV pour Expéditor (Coliposte) ? =
Aller sur http://monsite.fr/wp-admin/index.php?page=wpsc-purchase-logs
Tout en bas télécharge le csv pour Coliposte. 

= Comment personaliser l'email de confirmation = 

Aller sur http://monsite.fr/wp-admin/options-general.php?page=wpsc-settings&tab=admin
Shortcode disponibles : 4ème colonne de la page http://monsite.fr/wp-admin/options-general.php?page=wpsc-settings&tab=checkout

Cher %billingfirstname% %billinglastname%,
Nous vous remercions pour votre commande ...

= Comment ajouter le lien vers la facture dans l'email de confirmation ? =

Aller sur http://monsite.fr/wp-admin/options-general.php?page=wpsc-settings&tab=admin
Saisir : Vous pouvez télécharger votre facture ici : %facture%

= Comment personaliser l'entête de la facture et le préfixe ? =

Rendez-vous ici : http://monsite.fr/wp-admin/plugins.php?page=wpcb&tab=misc


= Quel type de fichier dois-je télécharger chez ma banque ? =

Quand vous avez le choix entre .php, .jsp, .asp, .aspx ou standard, choisissez .php .

= Que faire des fichiers envoyé par ma banque ? =

Configurer correctement vos dossiers/fichiers obtenus par votre banque (dossier crypté)
Pour les banques ATOS SIPS : 
Sur votre ftp, dans un dossier (non visible depuis Internet exemple unb dossier cgi-bin ou apache à coté de votre dossier www) vous devez avoir :

* parcom.nomdelabanque 
* parcom.005009461540411 (votre numéro de marchand à la place de celui là) (le contenu du fichier n'a pas a être modifé)
* log.txt
* certif.fr.005009461540411 (votre numéro de marchand à la place de celui là)
* pathfile (à modifier suivant cet exemple !!! )
* request
* response

Note : les fichiers call_request.php, call_response.php et call_auto_response.php dans le package fourni par la banque ne sont pas necessaires car wpcb les remplace.

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-14.20.411.png
= Pour les banques Systempay Cyberplus, comment faire ? =
Dans votre interface admin de gestion vad, régler : 
Url serveur : http://monsite.fr/?gateway=systempaycyberplus

= Comment activer/déscativer le paiement par carte bancaire ? =
Réglages > Boutique > Paiements
http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.30.06.png

= Comment identifier le reçu Systempay Cyberplus avec la commande wpec ? =
Le numéro de référence commande correspond au numéro de commande de wpec

= A quoi sert le mode test ? =
Le mode test permet de vérifier automatiquement le paiement sans passer par l'étape de saisie du numéro de carte bancaire. Il vous permet de vérifier que tout se passe bien dans votre processus.

= A quoi sert le mode demo ? =
Le mode demo permet d'utiliser le kit de démo fournit par votre banque. (Ne marche pas toujours très bien...)
= Y-a-t-il un mode debug ? =Oui, editer wp-config.php à la racine de votre site et mettez la variable globale WP_DEBUG = true . 

= Pourquoi me renvoit-t-ton sur monsite.fr?action=ReglerLesOptionsAvantTout ? =
Vous devez vous rendre dans l'onglet Dev des réglages de wpcb pour vérifier que vous avez sauvegardé tous les options ! Sinon cliquez bien sur les gros boutton bleu de chaque onglet !

= Je lis : "Error calling the atos api : exec request not found", alors que le fichier est bien là, pourquoi ? =
Il faut bien mettre le fichier request en droit chmod 755 !

= Je lis : "Error calling the atos api : response request not found", alors que le fichier est bien là, pourquoi ? =
Il faut bien mettre le fichier response en droit chmod 755 !

= Si mon certif a une extension php que faire ? =
Supprimer l'extension puis supprimer les 8 premières lignes de ce fichier et les deux dernières. Ensuite dans votre fichier pathfile rajouter un # espace devant F_CTYPE!php! : # F_CTYPE!php! (pour commenter la ligne)
= Autre question ? =
Merci de poser vos questions sur le forum en cliquant à droite sur le bouton vert ->

Attention : Nous ne sommes pas responsable de la mauvaise utilisation du plugin WPCB mis à votre disposition gratuitement et toujours en phase d'amélioration. Vous l'utilisez en tout conscience et vous vous assurez de la protection de vos pages internet.

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
= Ou placer le fichier automatic_response.php ? =Ce fichier est automatiquement copié à la racine de votre blog wordpress c'est à dire à coté du fichier wp-config.phpSi cela n'est pas fait, faite le manuellement.A la désinstallation du plugin, ce fichier est supprimé.A chaque mise-à jour ce fichier est remplacé donc ne le modifiez pas != Comment configurer google drive pour recevoir les ventes ? =Télécharger le fichier <a href="https://docs.google.com/spreadsheet/ccc?key=0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc">https://docs.google.com/spreadsheet/ccc?key=0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc</a>Envoyer ce fichier dans votre google drive et noter votre nouvelle cle de fichier (dans mon fichier, à titre d'exemple, la clé est : 0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc cela se lit dans l'url)Ne changer pas les entetes et attention à ce que ce soit la feuille numéro 1 du classeur !!

= Mes ventes restent incomplètes après le paiement par Carte Bancaire, que faire ? =

* Vérifier que le fichier automatic_response.php à la racine de votre site (en ftp) est bien en droits 604
* Faire une demande de support sur http://wpcb.fr/support
= Autre question ? =Attention : Nous ne sommes pas responsable de la mauvaise utilisation du plugin WPCB mis à votre disposition gratuitement et toujours en phase d'amélioration. Vous l'utilisez en tout conscience et vous vous assurez de la protection de vos pages internet.= Vous ne comprenez pas ce charabia ? =Nous pouvons installer le plugin pour vous, la marche à suivre est indiquée ici : http://wpcb.fr/support/== Screenshots ==1. Réglages du module2. Réglages ATOS3. Réglages Chèque ou Virement
4. Réglages Paypal
5. Réglages Systempay Cyberplus (Banque Populaire)
6. Réglages Mailchimp7. Livraison Poste française (Colis, chronopost, et d'autres mode de livraison à venir)
8. Réglage du multiplicateur d'affichage du nombre de vente et réglage du compte à rebours de vente
== Changelog ==
= 2.4.8 =* Correction du bug cheque et virement
= 2.4.7 =

* Suppression du système de livraison. Ne pas mettre à jour pour ceux qui ont une clé API.
= 2.4.6 =* checkConnection function has been renamned to wpcb_trello_checkConnection for compatibility with other plugins= 2.4.5 =* Bug error fatal on activation suite à un @ mal placé !
= 2.4.4 =

* Correction d'un bug qui empeche le panier de se vider.
* Suppression du mode demo atos (pas necessaire)

= 2.4.3 =

* Il est à nouveau possible d'utiliser le shortcode wpcb

= 2.4.2 =

* Bug corrigé, affiche toujours un text erroné

= 2.4.1 =

* Suppression de la fonction ajout des reponses Atos dans google qui bloquer la vente en incomplete chez certaines personnes
* Correction d'un bug qui afficher Corriger les Options avant tout
* Ajout de deux champs pour modifier le titre de la page de paiement et le contenu avant les icones de CB

= 2.4 =
* Plus besoin de placer le shortcode wpcb sur une page (Ne pas mettre à jour si tout marche chez vous!)
* Fix un bug dans l'affichage du nombre de vente
* Système de facturation (suivant les normes françaises cad incrémentation du numéro de facture)
* Peronalisation avancé de l'email de confirmation d'achat avec lien vers la facture

= 2.3.11 =
* Toutes les ventes réussies s'ajoutent dans le tableau google spreadsheet

= 2.3.10 =
* Correction d'un bug avec atos en mode sandbox

= 2.3.9 =
* Ajout du compte à rebours de vente
* Ajout de l'affichage du nombre de vente
* Ajout d'icônes de cartes bancaires manquantes

= 2.3.8 =
* Mise à jour de compatibilité avec la version 3.4.1 de Wordpress
* Amélioration du débug pour le support

= 2.3.7 =
* Correction d'un warning (bug notifié ici : http://wordpress.org/support/topic/plugin-wpcb-warning-array_key_exists-ligne-25-atosmerchantphp)

= 2.3.6 =
* Correction d'un bug notifié par : http://wordpress.org/support/topic/plugin-wpcb-incomplete-transaction-when-paying-with-credit-card Merci !

= 2.3.5 =
* Correction d'un bug notifié par : http://wordpress.org/support/topic/plugin-wpcb-incomplete-transaction-when-paying-with-credit-card Merci !
= 2.3.4 =* Vos ventes s'ajoutent dasn votre tableau de bord trello : http://trello.com= 2.3.3 =* Mise à jour non-indispensable si ça marche chez vous !* Ajout du mode debug avancé pour plus de contrôle sur atos.* Mise à jour de la documentation= 2.3.2 =* Ajout du mode de livraison Mini-max de la poste pour les petits objets
= 2.3.1 =
* Correction d'un bug pour certains serveurs (maj non-indispensable si ça marche chez vous!)

= 2.3 =
* Nouveau mode de livraison : Lettre Prioritaire
* Nouveau mode de livraison : Lettre Verte

= 2.2 =
* Livraison mondial relais (beta)
* Ajout sur github : https://github.com/6WWW/wpcb/

= 2.1 =
* Pour les détenteurs de l'API : Ajout du module de calcul de frais de port. Mon autre plugin : http://wordpress.org/extend/plugins/wp-e-commerce-livraison-france/ va être remplacé par celui çi.

= 2 =* Version beta !!!* Ajout d'une fonction sandbox pour tester vos paiement et le bon fonctionnement de votre fichier automatique response* Ajout du mode de paiement par chèque !* Ajout du mode de paiement par virement bancaire !
* Changement d'interface de réglage
* Ajout de Cyberplus Systempay= 1.1.9 =* Ajout de la session id en get dans les normal et cancel return* Ajout de automatic_response_url comme choix libre par le commercant dans le cas ou son site bloque certains dossier (deplacement manuel dans ce cas)* Correction d'une erreur avec l'affichage des milliers et decimaux dans le calcul des prix (>1000€!)* Correction mineures à droite à gauche pour plus de simplicité...= 1.1.8.1 =* Bug si Zend non installé, corrigé
= 1.1.8 =
* Mise à jour
= 1.1.5 =* Correction d'un bug de vidage de panier* Réorganisation des options* Ajout de la clé API= 1.1.3 =* Amélioration de la sécurité importante (Merci à Cyril Lecomte).= 1.1.2 =* Correction d'une erreur de suppression du plugin.=1.1.1=* Le mode test a été amélioré.= 1.1 =* Internationalized* Atos currency_code added to the settings of the plugin* Atos language added to the settings of the plugin* Atos merchant_country added to the settings of the plugin* Atos header_flag added to the settings of the plugin= 1.0.4 =* Update to wpec api v2.0= 1.0.3 =* Syntax correction= 1.0.2 =* Mises à jour mineures= 1.0.1 =* Mises à jour mineures= 1.0 =* Première version

== Upgrade Notice ==Merci de noter vos paramètres car ils peuvent êtres effacer à la mise à jour !!!!