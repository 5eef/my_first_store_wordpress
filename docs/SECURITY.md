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
- analyse de l’historique Git avec Gitleaks dans la CI ;
- aucun secret de paiement ni mot de passe administrateur versionné ; les mots de passe restent hachés par WordPress en base.

## Périmètre

### Environnement local

Le développement s’effectue sous XAMPP. HTTP peut être utilisé dans cet environnement local.

### Démo portfolio publique

La démo est publiée sur [https://seef-store.free.nf](https://seef-store.free.nf), en HTTPS, chez InfinityFree. Elle utilise uniquement des données de démonstration : aucune transaction ni aucun paiement réel ne doit y être effectué. Elle n’est pas destinée à l’exploitation d’un commerce réel.

### Production commerciale réelle

Une production commerciale réelle est hors périmètre de ce projet. Elle nécessiterait notamment un hébergement professionnel, des sauvegardes automatiques, un SMTP transactionnel, du monitoring, une politique CSP adaptée, une gestion juridique et de la confidentialité conforme, ainsi que de vrais moyens de paiement correctement sécurisés. Les contrôles décrits ici réduisent les risques sans constituer une garantie absolue de sécurité.

## Uploads

Le seeder génère uniquement des SVG statiques de confiance ; aucun formulaire public d’upload SVG n’est exposé et la liste MIME WordPress n’est pas élargie.
