<?php
session_start();
header("Access-Control-Allow-Origin: *");

if (isset($_GET['webhook']))
    $_SESSION['webhook'] = $_GET['webhook'];

/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/


# this is not to leak authotification information
# stored in config.php when pushing to github
if (!file_exists("php/config.php")) {
    copy("php/config.php.default", "php/config.php");
}

$xml = simplexml_load_file('data/regions.xml');
$regions = $xml->region;
//Récupère la liste des régions depuis le xml

$xml = simplexml_load_file('data/tranchesAge.xml');
$tranches = $xml->tranche;
//Récupère les tranches d'ages depuis le xml
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Présentation</title>

    <link rel="stylesheet" href="css/master.css"> <!--Gère la disposition de la page-->
    <link rel="stylesheet" href="css/styleDark.css"><!--Gère les couleurs, affichage sombre par défaut-->

    <link rel="icon" type="image/png" href="img/ur-bl2.png">
    <script src="js/age_switch.js"></script>
    <script src="js/color_mode_switch.js"></script>
    <script src="js/requireMJ.js"></script>

</head>

<body>


    <h1 class="titleCenter">Formulaire de présentation</h2>

        <?php
        if (isset($_GET['error'])) {
            //Affichage des erreurs. Rajouter des lignes si on rajoute d'autres codes d'erreurs (optimisable en les mettant dans un fichier si on commence à en avoir beaucoup)
            $error = $_GET['error'];
            if ($error == 'invalidData' && isset($_GET['type']))
                echo '<span class="rouge">Données invalides. ' . $_GET['type'] . '</span>';
            if ($error == 'isPosted')
                echo '<span class="vert">Votre présentation a bien été postée</span>';
        }
        ?>

        <div id="modeDiv">
            <label id="mode">Sombre 🌙</label>

            <label class="switch">
                <input type="checkbox" onclick="chgMode()">
                <span class="slider round"></span>
            </label>
        </div>

        <form method=post action="http://api.unionrolistes.fr:80/prez/create" name="URform" id="URform"
            onsubmit="sendForm(event)">

            <!-- Connection area -->
            <input type=hidden name="webhook_url"
                value="<?= isset($_SESSION['webhook']) ? $_SESSION['webhook'] : "" ?>">
            <!--Car parfois le contenu de $_SESSION expire-->
            <input type=hidden name="user_id" value="<?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "" ?>">
            <input type=hidden name="pseudo" value="<?= isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : "" ?>">

            <fieldset id="connectField">
                <legend>Connexion Discord <span class="rouge">*</span></legend>
                <!--<label>Connexion Discord <span class="rouge">*</span></label>-->
                <?php
                if (isset($_SESSION['avatar_url']) and isset($_SESSION['username'])) {
                    echo '<div>';
                    echo "<img id='username' src=\"" . $_SESSION['avatar_url'] . "\"/>";
                    echo $_SESSION['username'];
                    echo '<input type="button" value="Deconnexion" id="deconnexion" onclick="window.location.href=\'php/logout.php\'"/>';
                    echo '</div>';
                } else
                    echo '<div><input type="button" value="Me connecter" id="connexion" onclick="window.location.href=\'php/get_authorization_code.php\'"/></div>'
                    ?>
                <input type="hidden" name="access_token"
                    value="<?= isset($_SESSION['access_token']) ? $_SESSION['access_token'] : "" ?>">
            </fieldset>



            <label>Région : <span class="rouge">*</span></label>
            <select name="region" id="region" required>
                <option value="" selected>--Choisir--</option>

                <?php foreach ($regions as $i => $region) { ?>
                <option value="<?= $region ?>">
                    <?= $region ?>
                </option>
                <?php } ?>
            </select>

            <label>Ville :</label>
            <input type="text" name="ville" placeholder="Ville" />


            <!-- <fieldset>
        <legend>Age : <span class="rouge">*</span></legend> -->
            <label>
                Age : <span class="rouge">*</span>
                <input type="checkbox" id="checkAge" onclick="chgAgeDisplay()"> Indiquer une tranche d'âge
            </label>
            <input type="number" name="age" id="age" min="1" max="150" required placeholder="20 ans, 27 ans, ..">

            <select name="age" id="trancheAge" style="display: none">
                <option value="" selected>--Choisir--</option>

                <?php foreach ($tranches as $tranche) { ?>
                <option value="<?= $tranche ?>">
                    <?= $tranche ?> ans
                </option>
                <?php } ?>

            </select>
            <!--</fieldset>-->


            <label>Ancienneté dans le JDR :</label>
            <input type="text" name="experience" placeholder="3 ans, initié, ..." />


            <label>Comment avez-vous connu le serveur : <span class="rouge">*</span></label>
            <input type="text" name="connaissance" placeholder="Association partenaire, groupe Facebook, ..."
                required />

            <label>Hobby :</label>
            <input type="text" name="hobby" id="hobby" placeholder="Lecture, jeux, ...">


            <label>MJ/PJ : <span class="rouge">*</span></label>
            <div id="checkboxesMJ">
                <input type="checkbox" name="MJ" id="MJ" value="MJ" required onclick="chgMjRequire()">MJ&nbsp&nbsp&nbsp
                <input type="checkbox" name="PJ" id="PJ" value="PJ" onclick="chgMjRequire()">PJ
            </div>

            <!-- Information sur nos systèmes jdr -->
            <label><img src="img/info.png" alt="Info" title="Nos Jdr" style="width: 20px; height: 20px;">INFO : Nos Jdr
                🎲</label>

            <select id="infoJDR"> <!--Juste là pour l'information, ne sera pas envoyé au serveur-->
                <option selected value="">INFO : liste des JdR proposés</option>
                <?php
                if (!file_exists('data/jdr_systems.xml')) {
                    exit('Echec lors de la récupération des parties');
                }
                # Generates all the options from an xml file
                $systems = simplexml_load_file("data/jdr_systems.xml");
                foreach ($systems as $optgroup) {
                    echo '<optgroup label ="' . $optgroup['label'] . '">';
                    foreach ($optgroup as $option) {
                        echo '<option disabled>' . $option . '</option>';
                    }
                    echo '</optgroup>';
                }
                ?>
            </select>

            <label>JDR 🎲: <span class="rouge">*</span></label>
            <input type="text" name="jdr" id="jdr" placeholder="Vos JDR préférés" required>


            <label>J'aime :</label>
            <input type="text" name="like" id="like" placeholder="Le JDR, lire, ...">

            <label>J'aime pas :</label>
            <input type="text" name="dislike" id="dislike" placeholder="Rien, les légumes, ...">

            <label>Disponibilités :</label>
            <input type="text" name="dispos" id="dispos" placeholder="Quelques soirs, toutes les nuits, ...">

            <label>Jobs :</label>
            <input type="text" name="job" id="job" placeholder="">

            <label>Autre (votre OS par exemple) :</label>
            <input type="text" name="autre" id="autre" placeholder="">

            <label>Expression libre :</label>
            <textarea rows="3" name="expression" id="expression" style="resize: vertical;"></textarea>


            <label>Je veux être notifié des news autour du JDR </label>
            <input type="checkbox" name="news">

            <label>Je suis intéressé par du JDR grandeur nature </label>
            <input type="checkbox" name="gn">


            <div></div>

            <div id="submitButtons">
                <button type="reset">Réinitialiser 🔄</button>
                <br><br>
                <button type="submit" name="submit" id="submit" <?php if (!isset($_SESSION['avatar_url']) or
                    !isset($_SESSION['username'])) {
                    echo 'disabled ><b>Veuillez vous connecter';
                } else {
                    echo
                        'style="background-color:#169719;"' ?>><b>Valider ✔
                        <?php } ?>
                    </b></button>
                <!--Bloque le bouton si on s'est pas connecté-->
            </div>

            <span class="beta"><b>Attention cet outil est en beta-test</b><br>
                <a href="https://github.com/UnionRolistes/Web_Presentation"
                    uk-icon="icon: github; ratio:1.5">GitHub</a></span>
        </form>
        <?php echo getenv('TEST'); ?>

        <script src="js/record_form.js"></script>
        <!-- nedd to be moved to specific file -->
        <script>
            function sendForm(e) {
                // Empêche le formulaire de soumettre normalement
                e.preventDefault()

                // Récupère les données du formulaire
                var formData = new FormData(document.getElementById("URform"))
                // Récupère l'URL du formulaire
                var url = document.getElementById("URform").getAttribute("action")

                // Envoie les données du formulaire au serveur
                fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-type": "application/json",
                        Authorization: "Bearer " + "<?= $_SESSION['access_token'] ?>",
                    },
                    body: JSON.stringify(Object.fromEntries(formData)),
                })
                    .then(async (response) => {
                        data = {
                            status: response.status,
                            statusText: response.statusText,
                            headers: response.headers,
                            url: response.url,
                            ok: response.ok,
                            data: await response.json()
                        }
                        return data
                    })
                    .then((data) => {
                        console.log(data)
                        if (data.ok) {
                            // Si la réponse est bonne, ont dit que c'est ok
                            document.getElementById("URform").innerHTML =
                                `<h3 class="center-all" '>Votre fiche a bien été envoyée</h3>
                                <button class="center-all" onClick="window.location.reload();">retour</button>`
                        } else {
                            // Sinon on dit que c'est pas bon et on affiche l'erreur example ```{"detail": [{"loc": ["string",0],"msg": "string","type": "string"}]}```
                            document.getElementById("URform").innerHTML =
                                `<h3 class="center-all">Erreur : ${data.data.detail[0].msg == "field required" ? "entré requis" : data.data.detail}</h3>
                                <p class="center-all" >Erreur : ${data.data.detail[0].loc[1]}</p>
                                <button class="center-all" onClick="window.location.reload();">retour</button>

                                `
                        }
                    })
            }
        </script>
</body>
<?php include('php/footer.html'); ?>

</html>