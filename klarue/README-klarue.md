# README klarue

## Documentation sous format OpenApi

Fichier documentation_web_service.json

## Authentification JWT

L'authentification est fait via JWT,

Les routes pour :
- Créer un user
POST /api/register
Passez les paramètres suivant via form-data
_username
_password
_email

-Se connecter
POST /api/login_check


## Routes selon les spécifications fonctionnelles

Un utilisateur doit pouvoir créer un évènement
**POST /api/events**

et y inviter d’autres utilisateurs.
 **POST /api/invitations**

Si le lieu choisi n’existe pas, il doit être possible de le créer aussi.
**POST /api/events**

Un utilisateur doit pouvoir accéder à la liste des personnes participantes a l’évènement. (ayant confirmé sur l’invitation.)
**GET /api/events/{id}/participants?is_confirmed=1**

Un utilisateur doit pouvoir accéder à la liste des personnes n’ayant pas encore confirmé, pour relance (Facultatif: via l’e-mail ou autre canal).
**GET /api/events/{id}/participants?is_confirmed=0**

Un utilisateur doit pouvoir accéder à la liste de toutes les personnes invitées a l’évènement.
**GET /api/events/{id}/participants**

Un utilisateur doit pouvoir accéder à la liste de ses évènements créés.
**GET /api/users/{id}/events**

Un utilisateur doit pouvoir accéder à la liste des évènements auxquels il a participé.
**GET /api/users/{id}/invitations?is_confirmed=1**

Un utilisateur doit pouvoir laisser un commentaire concernant un évènement.
**POST /api/comments**