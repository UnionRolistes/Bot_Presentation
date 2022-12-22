from enum import Enum
import json
from fastapi import APIRouter, Header, HTTPException, Depends
from fastapi.security import HTTPBearer
from pydantic import BaseModel, Field
from ..base._discord import get_discord_client  # in base api repo
import requests
import re
from dotenv import load_dotenv
import os
from discord import Client

load_dotenv()
PREZ_CHANNEL = int(os.getenv("PREZ_CHANNEL"))


class Region(str, Enum):
    Auvergne_Rhône_Alpes = "Auvergne-Rhône-Alpes"
    Bourgogne_Franche_Comté = "Bourgogne-Franche-Comté"
    Bretagne = "Bretagne"
    Centre_Val_de_Loire = "Centre-Val de Loire"
    Grand_Est = "Grand Est"
    Hauts_de_France = "Hauts-de-France"
    Île_de_France = "Île-de-France"
    Normandie = "Normandie"
    Nouvelle_Aquitaine = "Nouvelle-Aquitaine"
    Occitanie = "Occitanie"
    Pays_de_la_Loire = "Pays de la Loire"
    Provence_Alpes_Côte_d_Azur = "Provence-Alpes-Côte d'Azur"
    Corse = "Corse"
    Guadeloupe = "Guadeloupe"
    Guyane = "Guyane"
    La_Réunion = "La Réunion"
    Martinique = "Martinique"
    Mayotte = "Mayotte"
    Belgique = "Belgique"
    Suisse = "Suisse"
    Luxembourg = "Luxembourg"
    Europe = "Europe"
    Québec = "Québec"


class AgeRange(str, Enum):
    enfant = "Mineur -15"
    ado = "Mineur +15"
    age18_20 = "18/20"
    age20_30 = "20/30"
    age30_40 = "30/40"
    age40_50 = "40/50"
    age50_60 = "50/60"
    age60_70 = "60/70"
    age70_80 = "70/80"
    age80_plus = "80+"


class postInput(BaseModel):
    region: Region
    ville: None | str
    age: int | AgeRange = Field(alias="ageRange")
    # Ancienneté dans le JDR
    experience: str | None
    # Comment avez-vous connu le serveur ?
    connaissance: str
    # Hobby
    hobby: str | None
    # mj et ou joueur (checkbox)
    mj: bool = False
    pj: bool = False
    # liste des jdr jouer
    jdr: str
    # j'aime
    like: str | None
    # j'aime pas
    dislike: str | None
    # disponibilité
    dispos: str | None
    # jobs
    job: str | None
    # autre
    autre: str | None
    # expression libre
    expression: str | None
    # Je veux être notifié des news autour du JDR
    news: bool = False
    # Je suis intéressé par du JDR grandeur nature
    gn: bool = False


# start
security = HTTPBearer()
router = APIRouter(
    prefix="/prez",
    tags=["prez"],
    responses={404: {"description": "Not found"}},
)


# dependancie that check discord oauth token and get user info
async def check_token_dep(authorization: HTTPBearer = Depends(security)):
    if authorization:
        token = authorization.credentials
        try:
            response = requests.get("https://discord.com/api/users/@me", headers={
                "Authorization": f"Bearer {token}"
            })
            user = response.json()
            return user
        except Exception as e:
            print(e)
            raise HTTPException(status_code=401, detail="Invalid token")
    else:
        raise HTTPException(status_code=401, detail="No token provided")


@ router.get("/", tags=["prez"])
async def prez():
    return {"message": "Hello from prez API!"}


@ router.post("/create", tags=["prez"])
async def createPrez(input: postInput, user: dict = Depends(check_token_dep), discord_client: Client = Depends(get_discord_client)):
    msg = ""  # res

    checks = []
    if input.news:
        checks.append("**News: ** ✅")
    if input.gn:
        checks.append("**GN: ** ☑")

    MJs = []
    if input.mj:
        MJs.append("MJ")
    if input.pj:
        MJs.append("PJ")

    kwargs = {
        'pseudo': f"<@{user['id']}> [{user['username']}]",
        'home': f"{input.region}{' - ' + input.ville if input.ville else ''}",
        'age': input.age,
        'experience': input.experience,
        'origin':  input.connaissance,
        'hobby': input.hobby,
        'mj_pj': "  et  ".join(MJs),
        'jdr': input.jdr,
        'i_like': input.like,
        'i_dislike': input.dislike,
        'availability': input.dispos,
        'jobs': input.job,
        'other': input.autre,
        'free_expression': input.expression,
        'checks': "  **|**  ".join(checks)
    }
    # get pwd (actual path)
    pwd = os.path.dirname(os.path.abspath(__file__))
    print(f"{pwd}/pres_template.txt")
    with open(f"{pwd}/pres_template.txt", 'r', encoding='utf-8') as f:
        for line in f:
            is_empty = True
            is_field = False
            # checks line is not a comment
            if not re.match(" *#", line):
                for match in re.finditer("{([A-Za-z0-9_]*)}", line):
                    is_field = True
                    key = match.group(1)
                    if key in kwargs and kwargs[key]:
                        is_empty = False
                        break
                if not is_empty or not is_field:
                    msg += line.format(**kwargs)
    channel = discord_client.get_channel(PREZ_CHANNEL)
    if not channel:
        print("channel not found")
        raise HTTPException(status_code=500, detail="Channel not found")
    await channel.send(msg)

    return {"message": "Hello from prez API!"}
