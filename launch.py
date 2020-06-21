from botPresentation import botClient

if __name__ == "__main__":
    try:
        key = open("key.txt").read()
    except FileNotFoundError:
        key = input("Bot Key :")
        open("key.txt", "w").write(key.rstrip())
    finally:
        botClient = botClient.botClient()
        botClient.run(key)
