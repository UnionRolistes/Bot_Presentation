import sys
from importlib import resources

import discord
from discord.ext import commands
import urpy
from urpy import utils
import cog_presentation.info
from cog_presentation import strings
from cog_presentation import settings


class Presentation(urpy.MyCog):
    """
    This cog serves the purpose of automating the making of presentations on the guild.
    """

    def __init__(self, bot):
        super().__init__(bot, 'cog_presentation')

    @commands.command()
    async def prez(self, ctx):
        """
        Envoie un lien pour se présenter
        """
        ctx = urpy.MyContext(ctx)

        # checks that $cal is called in the right place
        if isinstance(ctx.channel, discord.DMChannel):
            await ctx.send(strings.on_prez_dm_channel)
        elif isinstance(ctx.channel, discord.TextChannel):
            anncmnt_channel = discord.utils.get(ctx.guild.channels, name=settings.announcement_channel)
            if not anncmnt_channel:
                await ctx.send(strings.on_prez_channel_not_found.format(channel=settings.announcement_channel))
            else:
                try:
                    webhooks = await anncmnt_channel.webhooks()
                    webhook: discord.Webhook = webhooks[0]
                except discord.errors.Forbidden:
                    print("ERROR:BOT| Impossible d'obtenir les webhooks.",
                          "Le bot nécessite la permission de gérer les webhooks", file=sys.stderr)
                    await ctx.author.send(strings.on_permission_error)
                except IndexError:
                    await ctx.send(strings.on_prez_webhook_not_found.format(channel=settings.announcement_channel))
                else:
                    await ctx.send(strings.on_prez)
                    await ctx.author.send(
                        strings.on_prez_link.format(link=f"http://presentation.unionrolistes.fr?webhook={webhook.url}"))

    @staticmethod
    def get_credits():
        return resources.read_text(cog_presentation.info, 'credits.txt')

    @staticmethod
    def get_version():
        return resources.read_text(cog_presentation.info, 'version.txt')

    @staticmethod
    def get_name():
        return resources.read_text(cog_presentation.info, 'delete_name.txt')