#!/opt/virtualenv/URBot/bin/python
import re
import sys

from discord import Webhook, RequestsWebhookAdapter
import discord
from discord.embeds import Embed
import cgi
import cgitb
from importlib import resources
import pickle

import xml.etree.ElementTree as ET # Fonctions pour lire un xml

# Logging
cgitb.enable(display=1)
# Getting form data
# form = cgi.FieldStorage()

#UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
#To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
#Ask a derogation at Contact.unionrolistes@gmail.com


# Verification des données :
def verify_data(form: cgi.FieldStorage) -> str:
    
    try:
        # Verification des champs obligatoires :
        if form.getvalue('region') == "" or form.getvalue('connaissance') == "" or form.getvalue('JDR') == "":
            return 'Champs requis non remplis'

        if form.getvalue('age') == "" and form.getvalue('trancheAge') == "": # Si aucun des 2 n'est rempli
            return 'Age non rempli'

        #if not form.getvalue('age').isdigit():
            #return 'Age non numerique'

      #  if form.getvalue('age') != "": #TODO : bug, le code rentre dans cette boucle si on rentre que la tranche d'age, et plante alors car int(NoneType) ne peut pas se faire
      #      age = int(form.getvalue('age'))
      #      if age <= 0 or age > 150:
      #          return 'Age non realiste'
      #  else:
        if form.getvalue('trancheAge')!="":
            tranche_age = form.getvalue('trancheAge')
            # On vérifie que la tranche corresponde à une qui existe dans le xml :
            is_in_list = False

            with open("../../data/tranchesAge.xml", 'r', encoding='utf-8') as f:
                xml_root = ET.fromstring(f)
                for x in xml_root.findall('tranche'):
                    tranche = x.text
                    if tranche == tranche_age:
                        is_in_list = True
            if not is_in_list:
                return 'Tranche d\'age inconnue'

        # On vérifie MJ/PJ :
        if not form.getvalue('MJ') and not form.getvalue('PJ'): # Si aucun des 2 n'a été coché
            return 'MJ/PJ non rempli'
        #Si tout est bon :
        return 'OK'

    except Exception as e: #Sécurité supplémentaire, si une fonction fait une erreur
        raise e

def get_payload(form: cgi.FieldStorage) -> str:
    """ Process form data to create webhook payload. """

    if (False):  # (verify_data(form)!='OK'):  # Si les données sont invalides, on affiche l'erreur :
        print("Location: http://presentation.unionrolistes.fr?error=invalidData&type="+verify_data(form))
    else:
        res = ""
        checks = []
        if form.getvalue('news'):
            checks.append("**News: ** ✅")
        if form.getvalue('gn'):
            checks.append("**GN: ** ☑")

        MJs = []
        if form.getvalue('MJ'):
            MJs.append("MJ")
        if form.getvalue('PJ'):
            MJs.append("PJ")

        kwargs = {
            'pseudo': f"<@{form['user_id'].value}> [{form['pseudo'].value}]",
            'home': f"{form.getvalue('region')}{' - ' + form.getvalue('ville') if form.getvalue('ville') else ''}",
            'age': form['age'].value if form.getvalue('age') else form['trancheAge'].value,
            'experience': form.getvalue('experience'),
            'origin':  form.getvalue('connaissance'),
            'hobby': form.getvalue('hobby'),
            'mj_pj': "  et  ".join(MJs),
            'jdr': form.getvalue('JDR'),
            'i_like': form.getvalue('like'),
            'i_dislike': form.getvalue('dislike'),
            'availability': form.getvalue('dispos'),
            'jobs': form.getvalue('job'),
            'other': form.getvalue('autre'),
            'free_expression': form.getvalue('expression'),
            'checks': "  **|**  ".join(checks)
        }

    with open("pres_template.txt", 'r', encoding='utf-8') as f:
        for line in f:
            is_empty = True
            is_field = False
            # checks line is not a comment
            if not re.match(" *#", line):
                for match in re.finditer("{([A-Za-z0-9_]*)}", line):
                    is_field = True
                    key = match.group(1)
                    if key in kwargs and kwargs[key]:
                        is_empty = False
                        break

                if not is_empty or not is_field:
                    res += line.format(**kwargs)
    return res


def get_webhook_url(_form: cgi.FieldStorage) -> str:
    #with open(f'/usr/local/src/URbot/whPrez', "rb") as f:
    #    array = pickle.load(f)
    #    wh_url, guild_id, channel_id = array[int(_form.getvalue('user_id'))]
    #    return str(wh_url)
    return _form['webhook_url'].value

def main():
    try:
        form = cgi.FieldStorage()       
        webhook = Webhook.from_url(get_webhook_url(form), adapter=RequestsWebhookAdapter())
        webhook.send(get_payload(form))
    except Exception as e:
        print("Content-Type: text/html")    # HTML is following
        print()
        raise e

        # print(cgitb.html(sys.exc_info()))
    else:
        # Redirects to main page
        print("Status: 303 See other")
        print("Location: http://presentation.unionrolistes.fr?error=isPosted")
        print()

if __name__ == '__main__':
    main()