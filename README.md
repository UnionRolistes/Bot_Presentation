# Bot Presentation
============
Description
-------------
Le BotPresentation(Python3.7) permet à un utilisateur d'accéder à un formulaire de présentation via la commande $prez. 
Les informations saisies sont ensuite mises en forme et postées sur le discord de l'union des Rôlistes via un Webhook dans la section #presentation



# Installation
Pour une 1ère installation : 
"cd /usr/local/src && sudo git clone https://github.com/UnionRolistes/Bot_Base && cd Bot_Base && sudo bash updateBot.sh"

Pour une mise à jour :
"cd /usr/local/src/Bot_Base && sudo git checkout . && sudo git pull && sudo bash updateBot.sh"



# Credits
Voir src/cog_presentation/info/credits.txt

# Donation link 
http://site.unionrolistes.fr/

## Développer sous windows avec BotBase
```cmd
 cmd /c mklink /D ..\Bot_Base\bot\extends\Presentation ..\..\..\Bot_Presentation\bot\extends 
```
Penser à ajouter les variables d'environnement

L'utilisation du lien symbolique est la meilleur solution dans ce cas.
car copier-coler a chaque fois ou git submodule sont des solution plus compliquer a tester pour dev.