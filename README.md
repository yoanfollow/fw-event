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
| date de creation | datetime | Oui |
| date de mise a jour | datetime | Non |
| date de suppression | datetime | Non |

### Utilisateur

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| email | string | Oui |
| mot de passe | string | Oui |
| role | string[] | Oui |
| pseudonyme| string | Oui |
| avatar | string ou blob | Non |

> Il n'y qu'un seul role requis pour le moment ROLE_USER, 

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
| numero de rue | string | Oui |
| ville | string | Oui |
| rue | string | Oui |
| code postale | string | Oui |
| pays | string | Oui |

### Commentaire

| Champ | Type | Obligatoire | 
| ----------- | ----------- | ----- |
| utilisateur | Utilisateur | Oui |
| commentaire | string | Oui |
| note | int | Non |
| evenement | Evenement | Oui |

## Travail attendu

Realiser une api aucun (travail front n'est requis) authentifier selon les moyens actuels.

Un crud d'api sur les differentes resources, sauf utilisateurs.

Un utilisateur doit pouvoir creer un evenement et y inviter d'autres utilisateurs.

Si le lieu choisi n'existe pas, il doit etre possible de le creer aussi.
 