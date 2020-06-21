import discord


class botClient(discord.Client):

    async def on_ready(self):
        print(f"Bot {self.user} is ready")
