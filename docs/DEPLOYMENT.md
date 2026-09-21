# Déploiement de la démo

## Déploiement actuel

La démo portfolio est actuellement publiée sur [https://seef-store.free.nf](https://seef-store.free.nf), en HTTPS, chez **InfinityFree**. Elle ne traite aucune transaction réelle et n’est pas destinée à une boutique commerciale.

Le déploiement vers InfinityFree est **manuel**. GitHub Actions automatise uniquement les contrôles de qualité et de sécurité ; aucune automatisation GitHub vers InfinityFree n’est actuellement considérée comme fiable. L’hébergement gratuit implique des ressources partagées, une disponibilité sans garantie, un envoi d’e-mails limité et des sauvegardes à gérer soi-même.

## Avant l’envoi des fichiers

1. Sauvegarder le dossier local et exporter la base avec phpMyAdmin.
2. Créer un nouvel administrateur avec un mot de passe unique et supprimer ou désactiver tous les comptes de démonstration locaux.
3. Conserver uniquement les paiements fictifs. Ne jamais ajouter de clé Stripe, PayPal ou bancaire à cette démo.
4. Dans `wp-config.php`, utiliser les identifiants MySQL fournis par l’hébergeur et appliquer :

```php
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISALLOW_FILE_EDIT', true );
```

5. Générer de nouvelles clés secrètes WordPress ; ne pas réutiliser les salts du poste local.

## Archive de release

Créer l’archive depuis un commit propre avec `git archive --format=zip --output=seef-store-release.zip HEAD`. Les attributs `export-ignore` excluent les fichiers Git/éditeur, les backups, les tests, le log debug, les fichiers d’environnement, `wp-config.php` et le `.htaccess` local. Inspecter la liste de l’archive avant envoi et injecter les secrets uniquement sur l’hébergeur.

## Migration

1. Créer une base MySQL depuis le panneau de l’hébergeur.
2. Importer l’export SQL dans phpMyAdmin.
3. Envoyer les fichiers WordPress dans le dossier web (`htdocs` ou équivalent) via FTP.
4. Remplacer `wp-config.php` par la configuration de production.
5. `.htaccess-local.example` documente XAMPP. Si le site est à la racine du domaine, utiliser uniquement le contenu de `.htaccess-production.example` comme `.htaccess` ; le `.htaccess` local est exclu des releases.
6. Mettre à jour `home` et `siteurl` dans la table `wp_options` avec l’URL HTTPS publique.
7. Se connecter à l’administration, ouvrir **Réglages → Permaliens** puis enregistrer sans modifier la structure.
8. Vérifier les URLs encore liées à `localhost`. Pour les données sérialisées, utiliser un outil de recherche-remplacement compatible WordPress plutôt qu’un remplacement SQL brut.

## Mise à jour de la démo existante

Pour une petite correction du thème ou du plugin :

1. Identifier uniquement les fichiers custom modifiés.
2. Sauvegarder les fichiers actuellement publiés.
3. Envoyer uniquement les fichiers modifiés.
4. Ne pas remplacer inutilement WordPress core ou WooCommerce.
5. Effectuer les smoke tests sur la démo publique.
6. En cas de problème, restaurer la sauvegarde précédente.

Ne consigner aucun mot de passe ni secret FTP dans le dépôt ou cette documentation.

## Activation SEO

Dans **Réglages → Lecture** :

- décocher « Demander aux moteurs de recherche de ne pas indexer ce site » ;
- vérifier que `https://votre-domaine.example/wp-sitemap.xml` répond ;
- vérifier qu’une page produit contient une description, une URL Open Graph HTTPS et les données structurées produit ;
- définir une icône de site et une image mise en avant pour de meilleurs aperçus sociaux ;
- inscrire ensuite le sitemap dans Google Search Console et Bing Webmaster Tools.

Le site local garde `blog_public = 0`, ce qui explique la balise `noindex` et le sitemap désactivé sous XAMPP. Il ne faut activer l’indexation qu’après avoir remplacé toutes les URLs locales.

## Vérifications de sortie

- accueil, boutique, produit, panier, commande, compte et contact en HTTPS ;
- menu, recherche, mode sombre, œil du mot de passe et notifications sur mobile et desktop ;
- français, anglais et arabe/RTL ;
- aucun mot de passe de démonstration valide sur l’administration publique ;
- aucun secret ni sauvegarde SQL accessible dans le dossier web ;
- permissions de fichiers restrictives et mises à jour WordPress/WooCommerce appliquées ;
- sauvegarde téléchargeable conservée hors de l’hébergement gratuit.

## Important

Cette procédure publie une **démo portfolio**. Pour accepter de vrais paiements ou des données personnelles, il faut un hébergement fiable, des sauvegardes automatiques, une politique de confidentialité, un domaine propre, un SMTP transactionnel, une supervision et les obligations légales adaptées au pays ciblé.
