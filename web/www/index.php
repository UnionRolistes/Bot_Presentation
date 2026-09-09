<?php
session_start();

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

$xml = simplexml_load_file('data/tranchesAge.xml');
$tranches = $xml->tranche;
//Récupère les tranches d'ages depuis le xml

$connected = isset($_SESSION['avatar_url']) && isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Présentation · L'Union des Rôlistes</title>

    <!-- Thème appliqué AVANT le CSS pour éviter un flash clair/sombre au chargement :
         même clé localStorage ('ur-theme') et même logique que site.unionrolistes.fr -->
    <script>(function(){try{var t=localStorage.getItem('ur-theme');if(!t)t=matchMedia('(prefers-color-scheme: light)').matches?'light':'dark';if(t==='light')document.documentElement.setAttribute('data-theme','light');}catch(e){}})();</script>

    <link rel="preload" href="fonts/OldNewspaperTypes.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="css/master.css"> <!--Disposition + palettes sombre/claire (voir css/master.css)-->

    <link rel="icon" type="image/png" href="img/ur-bl2.png">
    <script src="js/age_switch.js"></script>
    <script src="js/color_mode_switch.js"></script>
    <script src="js/requireMJ.js"></script>
    <script src="js/postal_code_lookup.js"></script>

</head>

<body>

    <img class="page-trame" src="img/ur-bl2.png" alt="" aria-hidden="true">

    <header class="site">
        <div class="wrap nav">
            <a class="brand" href="https://site.unionrolistes.fr/" aria-label="Site de l'Union des Rôlistes">
                <img class="crest" src="img/ur-bl2.png" alt="">
                <span>
                    <b>L'Union des Rôlistes</b>
                    <small>Association loi 1901</small>
                </span>
            </a>
            <div class="nav-cta">
                <button class="theme-toggle" id="themeToggle" type="button" aria-label="Basculer le thème clair / sombre">
                    <svg class="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/></svg>
                    <svg class="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/></svg>
                </button>
            </div>
        </div>
    </header>

    <main class="wrap wrap--form">

        <section class="hero">
            <span class="eyebrow">Communauté · Présentation</span><span class="tag">Bêta</span>
            <h1>Présentez-vous à <span class="glow">la communauté</span>.</h1>
            <p class="lead">Remplissez ce formulaire : votre présentation sera publiée automatiquement sur le Discord de l'Union des Rôlistes.</p>
        </section>

        <?php
        if (isset($_GET['error'])) {
            //Affichage des erreurs. Rajouter des lignes si on rajoute d'autres codes d'erreurs (optimisable en les mettant dans un fichier si on commence à en avoir beaucoup)
            $error = $_GET['error'];
            if ($error == 'invalidData' && isset($_GET['type']))
                echo '<p class="alert alert--ko">Données invalides. ' . htmlspecialchars($_GET['type']) . '</p>';
            if ($error == 'isPosted')
                echo '<p class="alert alert--ok">Votre présentation a bien été postée.</p>';
            if ($error == 'envoi')
                echo '<p class="alert alert--ko">L\'envoi vers Discord a échoué. Réessayez, ou signalez-le sur le Discord si ça persiste.</p>';
        }
        ?>

        <form method="post" action="php/create_presentation.php" name="URform" id="URform" class="card"
            onsubmit="return onFormSubmit()">

            <!-- Connection area -->
            <input type="hidden" name="webhook_url"
                value="<?= isset($_SESSION['webhook']) ? $_SESSION['webhook'] : "" ?>">
            <!--Car parfois le contenu de $_SESSION expire-->
            <input type="hidden" name="user_id" value="<?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "" ?>">
            <input type="hidden" name="pseudo" value="<?= isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : "" ?>">

            <fieldset id="connectField">
                <legend>Connexion Discord <span class="rouge">*</span></legend>
                <?php
                if ($connected) {
                    echo '<div>';
                    echo '<span class="who"><img id="username" src="' . $_SESSION['avatar_url'] . '" alt="">' . $_SESSION['username'] . '</span>';
                    echo '<input type="button" value="Déconnexion" id="deconnexion" class="btn btn--ghost" onclick="window.location.href=\'php/logout.php\'">';
                    echo '</div>';
                } else {
                    echo '<div><span class="who">Connectez-vous pour pouvoir envoyer votre présentation.</span>';
                    echo '<input type="button" value="Me connecter" id="connexion" class="btn btn--primary" onclick="window.location.href=\'php/get_authorization_code.php\'"></div>';
                }
                ?>
            </fieldset>


            <h2 class="section">Localisation</h2>

            <label>Code postal : <span class="rouge">*</span></label>
            <input type="text" id="codePostal" placeholder="75017, 1000, A1A 1A1, ..." required
                onchange="onCodePostalChange()">
            <!--Détermine pays/région/ville ci-dessous (voir js/postal_code_lookup.js) ; region/ville sont envoyés via les champs cachés plus bas, mêmes noms qu'avant.-->

            <div id="localisationResult">
                <p>Pays : <strong id="displayPays">--</strong></p>
                <p>Région : <strong id="displayRegion">--</strong></p>
                <p id="departementResult">Département : <strong id="displayDepartement">--</strong></p>
                <p>Ville :
                    <strong id="displayVille">--</strong>
                    <select id="villeSelect" style="display:none" onchange="onVilleSelectChange()"></select>
                    <input type="text" id="villeTexte" style="display:none" placeholder="Ville"
                        oninput="onVilleTexteChange()">
                </p>
            </div>

            <div id="paysVilleChoix" style="display:none">
                <label>Plusieurs correspondances possibles pour ce code postal, laquelle est la vôtre ?
                    <span class="rouge">*</span></label>
                <select id="paysVilleChoixSelect" onchange="onPaysVilleChoixChange()"></select>
            </div>

            <div id="paysManuel" style="display:none">
                <label class="rouge">Pays non déterminé automatiquement à partir de ce code postal, merci de
                    préciser :</label>
                <select id="paysManuelSelect" onchange="onPaysManuelChange()">
                    <option value="" selected>--Choisir--</option>
                    <option value="Belgique">Belgique</option>
                    <option value="Suisse">Suisse</option>
                    <option value="Luxembourg">Luxembourg</option>
                    <option value="Europe">Europe (autre)</option>
                </select>
            </div>

            <input type="hidden" name="region" id="region">
            <input type="hidden" name="ville" id="ville">
            <input type="hidden" name="departement" id="departement">


            <h2 class="section">À propos de vous</h2>

            <label>
                Âge : <span class="rouge">*</span>
                <input type="checkbox" id="checkAge" onclick="chgAgeDisplay()"> Indiquer une tranche d'âge
            </label>
            <input type="number" name="age" id="age" min="1" max="150" required placeholder="20 ans, 27 ans, ...">

            <select name="trancheAge" id="trancheAge" style="display: none">
                <option value="" selected>--Choisir--</option>

                <?php foreach ($tranches as $tranche) { ?>
                    <option value="<?= $tranche ?>">
                        <?= $tranche ?> ans
                    </option>
                <?php } ?>

            </select>


            <label>Ancienneté dans le JDR :</label>
            <input type="text" name="experience" placeholder="3 ans, initié, ..." />


            <label>Comment avez-vous connu le serveur : <span class="rouge">*</span></label>
            <input type="text" name="connaissance" placeholder="Association partenaire, groupe Facebook, ..."
                required />

            <label>Hobby :</label>
            <input type="text" name="hobby" id="hobby" placeholder="Lecture, jeux, ...">


            <h2 class="section">Jeu de rôle</h2>

            <label>MJ / PJ : <span class="rouge">*</span></label>
            <div id="checkboxesMJ" class="chips">
                <label class="chip"><input type="checkbox" name="MJ" id="MJ" value="MJ" required onclick="chgMjRequire()"> MJ</label>
                <label class="chip"><input type="checkbox" name="PJ" id="PJ" value="PJ" onclick="chgMjRequire()"> PJ</label>
            </div>

            <!-- Information sur nos systèmes jdr -->
            <label><img class="ico" src="img/info.png" alt="" title="Nos Jdr">Info : nos JDR 🎲</label>

            <select id="infoJDR"> <!--Juste là pour l'information, ne sera pas envoyé au serveur-->
                <option selected value="">Liste des JDR proposés sur le serveur</option>
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

            <label>JDR 🎲 : <span class="rouge">*</span></label>
            <input type="text" name="JDR" id="JDR" placeholder="Vos JDR préférés" required>


            <label>J'aime :</label>
            <input type="text" name="like" id="like" placeholder="Le JDR, lire, ...">

            <label>J'aime pas :</label>
            <input type="text" name="dislike" id="dislike" placeholder="Rien, les légumes, ...">

            <label>Disponibilités :</label>
            <input type="text" name="dispos" id="dispos" placeholder="Quelques soirs, toutes les nuits, ...">


            <h2 class="section">Divers</h2>

            <label>Secouriste (PSC, SST, ...) :</label>
            <input type="checkbox" name="secouriste">

            <label>Systèmes utilisés :</label>
            <div class="chips">
                <label class="chip"><input type="checkbox" name="win" id="win" value="win"> Windows</label>
                <label class="chip"><input type="checkbox" name="mac" id="mac" value="mac"> Mac</label>
                <label class="chip"><input type="checkbox" name="linux" id="linux" value="linux"> Linux</label>
                <label class="chip"><input type="checkbox" name="android" id="android" value="android"> Android</label>
            </div>

            <label>Jobs :</label>
            <input type="text" name="job" id="job" placeholder="">

            <label>Autre (votre OS par exemple) :</label>
            <input type="text" name="autre" id="autre" placeholder="">

            <label>Expression libre :</label>
            <textarea rows="3" name="expression" id="expression"></textarea>


            <label>Je veux être notifié des news autour du JDR</label>
            <input type="checkbox" name="news">

            <label>Je suis intéressé par du JDR grandeur nature</label>
            <input type="checkbox" name="gn">


            <div id="submitButtons" class="actions">
                <button type="reset" class="btn btn--ghost">Réinitialiser</button>
                <!--Bloque le bouton si on s'est pas connecté-->
                <button type="submit" name="submit" id="submit" class="btn btn--primary" <?= $connected ? '' : 'disabled' ?>>
                    <?= $connected ? 'Valider ✔' : 'Connectez-vous pour valider' ?>
                </button>
            </div>
        </form>

        <script src="js/record_form.js"></script>
    </main>

    <?php include('php/footer.html'); ?>
</body>

</html>
