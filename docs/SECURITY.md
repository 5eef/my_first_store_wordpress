# Sécurité

## Contrôles appliqués

- Nonces WordPress sur contact, newsletter et mise à jour admin ;
- validation côté serveur et limites de longueur ;
- `sanitize_text_field`, `sanitize_textarea_field`, `sanitize_email`, `sanitize_key` ;
- escaping tardif avec `esc_html`, `esc_attr`, `esc_url` ;
- droits `manage_woocommerce` pour les messages et abonnés ;
- contenu contact enregistré en `private` ;
- honeypot et limite de quatre contacts par dix minutes ;
- honeypot, déduplication et limite légère sur la newsletter ;
- seeding impossible en production et absent du cycle d’activation normal ;
- six échecs de connexion maximum par couple IP/utilisateur pendant quinze minutes ;
- IP pseudonymisée avec HMAC avant stockage en transient ;
- en-têtes `nosniff`, `SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy` ;
- erreurs journalisées localement mais non affichées ;
- éditeur de fichiers WordPress désactivé ;
- aucun secret de paiement ni mot de passe administrateur versionné ; les mots de passe restent hachés par WordPress en base.

## Périmètre

Ce projet est local et en HTTP. Une production exigerait HTTPS, un compte MariaDB dédié avec mot de passe, rotation des salts, sauvegardes, politique CSP adaptée aux scripts WooCommerce, SMTP transactionnel et durcissement serveur.

## Uploads

Le seeder génère uniquement des SVG statiques de confiance ; aucun formulaire public d’upload SVG n’est exposé et la liste MIME WordPress n’est pas élargie.
