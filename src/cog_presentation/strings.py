from urpy import lcl

jdr_brief = lcl("Sends a link to create your presentation")
jdr_help = jdr_brief
# TODO edit presentation
# on_edit_without_reply = "Vous devez répondre à un message pour l'éditer !"
# on_edit_wrong_channel = "Vous ne pouvez pas éditer dans ce salon !"
# on_edit_start = "C'est parti ! Rejoignez-moi dans vos dms."
# on_edit_not_editable = "Ce message n'est pas éditable."
# on_edit_not_author = "Vous n'êtes pas l'auteur de cette présentation ! Vous ne pouvez donc pas l'éditer."
# on_edit_prompt = "\|~ Quelles informations souhaitez-vous modifier ? (**$done** pour valider, **$cancel** pour annuler) ~|"
# on_edit_unrecognized = "\|~ Information non reconnue. Veuillez réessayer. (**$done** pour valider, **$cancel** pour annuler) ~|"
# on_edit_content_prompt = "\|~ Nouveau contenu pour {info} ? ~|"
# on_edit_invalid = ""
# on_edit_success = "\|~ Félicitations, le message a été édité avec brio. ~|"
# on_edit_cancel = "\|~ Annulation. ~|"

on_prez = lcl("A link has been sent into your DMs to introduce yourself !")
on_prez_link = lcl("Here is the link to create your presentation :\n{link}")
on_prez_channel_not_found = lcl("*Impossible.*. The following channel does not exist : {channel}.")
on_prez_dm_channel = lcl("This command is only usable in a server !")
on_prez_webhook_not_found = lcl("*Impossible. The following channel does not own a webhook : **{channel}**.*")
on_permission_error = lcl("Error, the bot requires more permission. Please contact the admin.")

