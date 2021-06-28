import logging
import sys
import asyncio

import discord
from discord.ext import commands, tasks

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
        print("plp")
