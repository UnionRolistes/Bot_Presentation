import asyncio
import discord
from discord.ext import commands


class Prez(commands.Cog, name='prez'):
    def __init__(self, bot):
        self.bot = bot
        self._last_member = None

    async def cog_load(self):  # called when the cog is loaded
        print(self.__class__.__name__ + " is loaded")

    @commands.command(name="prez")
    async def prez(Self, event):
        embed = discord.Embed(url="http://presentation.unionrolistes.fr/?webhook=https://discord.com/api/webhooks/875068900612665396/DJusy0eGs9Xyx2os-dodBVfWia2fbhfBzfmnDM9g-30ozoFYAuZBHVXaD9TKaC1wwBwg",
                              description="⬆️ Here is the link to create your presentation.", title="Union Roliste - Presentation", color=0x0CC1EE)
        embed.set_author(name=event.author.display_name,
                         icon_url=event.author.avatar)
        DATA = ["**:pen_ballpoint:  Nom\n**", "**:pen_ballpoint:  Prenom\n**", ":round_pushpin:  **Address\n**", ":telephone:   **N° Telephone\n**",
                ":postbox: **Code postal\n**", "**:computer:  Support (Windows / Linux / Mac)\n**", "**Expérience en programmation\n**"]
        embed.add_field(
            name="**\n**", value="**───────────────────────────────**", inline=False)
        embed.set_footer(text="Union Roliste dev presentation.",
                         icon_url="https://avatars.githubusercontent.com/u/62179928?s=200&v=4")
        embed.set_thumbnail(
            url="https://avatars.githubusercontent.com/u/62179928?s=200&v=4")
        # envoie un message de presentation privée à l'auteur qui a fait a commandes
        await event.author.send(embed=embed)


async def setup(bot):
    await bot.add_cog(Prez(bot))
