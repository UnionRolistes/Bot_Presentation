import asyncio
import os
import discord
from discord.ext import commands

from dotenv import load_dotenv

load_dotenv()
VERSION = os.getenv("BOT_PREZ_VERSION")
PREZ_URL = os.getenv("URL_SITE_PRESENTATION")
presentation_channels = {}
print(f"VERSION_PREZ : {VERSION}")


class Prez(commands.Cog, name="Prez"):
    def __init__(self, bot: commands.Bot):
        self.bot = bot
        self._last_member = None

    async def cog_load(self):  # called when the cog is loaded
        print(self.__class__.__name__ + " is loaded")

    @commands.command(
        name="prez", help="envoie un lien pour se présenter", aliases=["presentation"]
    )
    async def prez(self, event):
        await event.author.send(f"Veuillez suivre le lien suivant : {PREZ_URL}")
        channel = event.channel
        await channel.send(
            "Un lien a été envoyé dans vos messages privés pour vous présenter dignement !"
        )

    @commands.command(
        name="set",
        help="définir un canal pour les présentations avec la commande `$set prez #nomduchannel`",
    )
    async def set_presentation_channel(self, ctx, channel_name: str):
        if channel_name.startswith("#"):
            channel_name = channel_name[1:]
        
        channel = discord.utils.get(ctx.guild.text_channels, name=channel_name)
        
        if channel:
            presentation_channels[ctx.guild.id] = channel.id
            await ctx.send("Canal de présentation défini sur #{channel_name}.")
        else:
            await ctx.send(
                "Le canal spécifié n'existe pas ou n\'est pas un salon textuel valide."
            )


async def setup(bot):
    await bot.add_cog(Prez(bot))
