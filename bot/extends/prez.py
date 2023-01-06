import asyncio
import os
import discord
from discord.ext import commands

from dotenv import load_dotenv

load_dotenv()
VERSION = os.getenv('BOT_PREZ_VERSION')
PREZ_URL = os.getenv('URL_SITE_PRESENTATION')
print(f'VERSION_PREZ : {VERSION}')


class Prez(commands.Cog, name='Prez'):
    def __init__(self, bot: commands.Bot):
        self.bot = bot
        self._last_member = None

    async def cog_load(self):  # called when the cog is loaded
        print(self.__class__.__name__ + " is loaded")

    @commands.command(name="prez", help='envoie un lien pour se présenter', aliases=['presentation'],)
    async def prez(Self, event):
        await event.author.send(f'veuiller suivre le lient suivant : {PREZ_URL}')


async def setup(bot):
    await bot.add_cog(Prez(bot))


# loaded in bot\extends\_base\base.py
def version():
    return f'URBot_ version : {VERSION}'


# loaded in bot\extends\_base\base.py
def _credits():
    # get the path of the file
    pwd = os.path.dirname(os.path.abspath(__file__))
    # get the credits file
    with open(f'{pwd}/credits.txt', 'r') as f:
        credits = f.read()
        return credits
