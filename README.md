# FW Event

Fw event est un projet de gestion des evenement interne organiser.

## Description

Cette application permet a un utilisateur de creer un evenement et d'y inviter d'autres utilisateurs.
Un evennment peut s'etendre d'une heures a plusieurs jours. 
Le contenu d'evenement sont libre de choix. 
Les personnes invitees peuvent confirmer leurs presences ou non a l'evennement.
Une fois l'evenement derouler les utilisateurs ont la possibilites de laisser un commentaire avec une note.

Le model metier est composer de 4 entites.

* Utilisateur
* Evenemment
* Invitation
* Lieu
* Commentaire


## Detail des entites

Toutes les entites doivents porter les informations suivantes:

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| Date de creation | datetime | Oui |
| Date de mise a jour | datetime | Non |
| Date de suppression | datetime | Non |

### Utilisateur

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| Email | string | Oui |
| Mot de passe | string | Oui |
| Role | string[] | Oui |
| Pseudonyme| string | Oui |
| Avatar | string ou blob | Non |

> Il n'y qu'un seul role requis pour le moment ROLE_USER

### Evenemment

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| Nom | string | Oui |
| description | string | Oui |
| Organisteur | Utilisateur | Oui |
| Participant| Invitation[] | Non |
| date de debut | Datetime | Oui |
| date de fin | Datetime | Oui |
| lieu | Lieu | Oui |

### Invitation

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| Evenement | Evenement | Oui |
| Destinataire | utilisateur | Oui |
| Confirmation | booleen | Oui |
| Date limite | datetime | Non |

### Lieu

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| Nom | string | Oui |
| Numero de rue | string | Oui |
| Ville | string | Oui |
| Rue | string | Oui |
| Code postale | string | Oui |
| Pays | string | Oui |

### Commentaire

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| Utilisateur | Utilisateur | Oui |
| Commentaire | string | Oui |
| Note | int | Non |
| Evenement | Evenement | Oui |

## Travail attendu

Réaliser une api authentifié selon les moyens actuels.
Un utilisateur doit pouvoir créer un compte et s'authentifier.
Toutes les actions hormis la création de comptes et login doivent être authentifiées.

Un CRUD d'api sur les différentes ressources, évènement, invitations, lieu, commentaires.
Bien entendu seul l'auteur des ressources à les droits d'édition sur sa ressource.

Un utilisateur doit pouvoir créer un évènement et y inviter d'autres utilisateurs.

Si le lieu choisi n'existe pas, il doit être possible de le créer aussi.

Une invitation est considéré comme expirée si non répondus après la date limite si celle-ci est renseignée,
Sinon, c'est la date de début d'évènement qui est considérer comme date limite.

Un utilisateur doit pouvoir accéder à la liste des personnes participantes a l'évènement. (ayant confirmé sur l'invitation.)

Un utilisateur doit pouvoir accéder à la liste des personnes n'ayant pas encore confirmé, pour relance (Facultatif: via l'e-mail ou autre canal).

Un utilisateur doit pouvoir accéder à la liste de toutes les personnes invitées a l'évènement.

Un utilisateur doit pouvoir accéder à la liste de ses évènements créés.

Un utilisateur doit pouvoir accéder à la liste des évènements auxquels il a participé.

Un utilisateur doit pouvoir laisser un commentaire concernant un évènement.