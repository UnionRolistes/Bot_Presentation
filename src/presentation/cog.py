from importlib import resources
from discord.ext import commands

import presentation.info


class Presentation(commands.Cog):
    """
    This cog serves the purpose of automating the making of presentations on the guild.
    """
    def __init__(self):
        super(Presentation, self).__init__()

    @commands.Cog.listener()
    async def on_ready(self):
        print("\t| Presentation started.")

    @commands.command()
    async def prez(self, ctx):
        """
        Envoie un lien pour se présenter
        """
        print("plp")

    @staticmethod
    def get_credits():
        return resources.read_text(presentation.info, 'credits.txt')

    @staticmethod
    def get_version():
        """ Return version number. """
        return resources.read_text(presentation.info, 'version.txt')

    @staticmethod
    def get_name():
        return resources.read_text(presentation.info, 'name.txt')
