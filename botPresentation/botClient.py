import discord

botClient = discord.Client()


@botClient.event
async def on_message(message):
    if message.author == botClient.user:
        return

    if message.content.startswith('$pres'):
        await message.channel.send(
            '{0.author.mention} regarde dans tes messages privées pour te présenter! :D'.format(message)
        )

        await message.author.send(
            'Voici le lien de la présentation :'
        )

class botClient(discord.Client):

    async def on_ready(self):
        print(f"Bot {self.user} is ready")
