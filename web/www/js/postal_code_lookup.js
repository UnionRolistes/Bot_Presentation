
// Déduit pays/région/ville à partir du code postal saisi (voir issue #83) :
// remplace les anciens champs libres "Région"/"Ville" par un résultat
// affiché, pour limiter les erreurs de saisie. region/ville restent
// envoyés au serveur via des champs cachés (même noms qu'avant,
// create_presentation.php ne change pas).
//
// Cas non déterminable de façon fiable (Belgique/Suisse/Luxembourg
// partagent le même format à 4 chiffres) : un sélecteur de secours
// apparaît, plutôt que de deviner au hasard.

// Département (2 chiffres, ou 3 pour les DOM) -> région, mêmes libellés
// que data/regions.xml. Codé en dur plutôt que déduit d'une API : la
// région se déduit uniquement du département, pas besoin de réseau.
const DEPARTEMENT_TO_REGION = {
	'01': 'Auvergne-Rhône-Alpes', '02': 'Hauts-de-France', '03': 'Auvergne-Rhône-Alpes',
	'04': 'Provence-Alpes-Côte d\'Azur', '05': 'Provence-Alpes-Côte d\'Azur', '06': 'Provence-Alpes-Côte d\'Azur',
	'07': 'Auvergne-Rhône-Alpes', '08': 'Grand Est', '09': 'Occitanie', '10': 'Grand Est',
	'11': 'Occitanie', '12': 'Occitanie', '13': 'Provence-Alpes-Côte d\'Azur', '14': 'Normandie',
	'15': 'Auvergne-Rhône-Alpes', '16': 'Nouvelle-Aquitaine', '17': 'Nouvelle-Aquitaine',
	'18': 'Centre-Val de Loire', '19': 'Nouvelle-Aquitaine', '20': 'Corse',
	'21': 'Bourgogne-Franche-Comté', '22': 'Bretagne', '23': 'Nouvelle-Aquitaine',
	'24': 'Nouvelle-Aquitaine', '25': 'Bourgogne-Franche-Comté', '26': 'Auvergne-Rhône-Alpes',
	'27': 'Normandie', '28': 'Centre-Val de Loire', '29': 'Bretagne', '30': 'Occitanie',
	'31': 'Occitanie', '32': 'Occitanie', '33': 'Nouvelle-Aquitaine', '34': 'Occitanie',
	'35': 'Bretagne', '36': 'Centre-Val de Loire', '37': 'Centre-Val de Loire',
	'38': 'Auvergne-Rhône-Alpes', '39': 'Bourgogne-Franche-Comté', '40': 'Nouvelle-Aquitaine',
	'41': 'Centre-Val de Loire', '42': 'Auvergne-Rhône-Alpes', '43': 'Auvergne-Rhône-Alpes',
	'44': 'Pays de la Loire', '45': 'Centre-Val de Loire', '46': 'Occitanie',
	'47': 'Nouvelle-Aquitaine', '48': 'Occitanie', '49': 'Pays de la Loire', '50': 'Normandie',
	'51': 'Grand Est', '52': 'Grand Est', '53': 'Pays de la Loire', '54': 'Grand Est',
	'55': 'Grand Est', '56': 'Bretagne', '57': 'Grand Est', '58': 'Bourgogne-Franche-Comté',
	'59': 'Hauts-de-France', '60': 'Hauts-de-France', '61': 'Normandie', '62': 'Hauts-de-France',
	'63': 'Auvergne-Rhône-Alpes', '64': 'Nouvelle-Aquitaine', '65': 'Occitanie',
	'66': 'Occitanie', '67': 'Grand Est', '68': 'Grand Est', '69': 'Auvergne-Rhône-Alpes',
	'70': 'Bourgogne-Franche-Comté', '71': 'Bourgogne-Franche-Comté', '72': 'Pays de la Loire',
	'73': 'Auvergne-Rhône-Alpes', '74': 'Auvergne-Rhône-Alpes', '75': 'Île-de-France',
	'76': 'Normandie', '77': 'Île-de-France', '78': 'Île-de-France', '79': 'Nouvelle-Aquitaine',
	'80': 'Hauts-de-France', '81': 'Occitanie', '82': 'Occitanie',
	'83': 'Provence-Alpes-Côte d\'Azur', '84': 'Provence-Alpes-Côte d\'Azur', '85': 'Pays de la Loire',
	'86': 'Nouvelle-Aquitaine', '87': 'Nouvelle-Aquitaine', '88': 'Grand Est',
	'89': 'Bourgogne-Franche-Comté', '90': 'Bourgogne-Franche-Comté', '91': 'Île-de-France',
	'92': 'Île-de-France', '93': 'Île-de-France', '94': 'Île-de-France', '95': 'Île-de-France',
	'971': 'Guadeloupe', '972': 'Martinique', '973': 'Guyane', '974': 'La Réunion', '976': 'Mayotte',
};

function regionFromCodePostal(codePostal) {
	if (/^97\d{3}$/.test(codePostal)) {
		return DEPARTEMENT_TO_REGION[codePostal.substring(0, 3)] || null;
	}
	if (/^\d{5}$/.test(codePostal)) {
		return DEPARTEMENT_TO_REGION[codePostal.substring(0, 2)] || null;
	}
	return null;
}

function isCodePostalFrancais(codePostal) {
	return /^\d{5}$/.test(codePostal);
}

// Québec/Canada : format alphanumérique A1A 1A1 (espace optionnel),
// suffisamment distinctif pour être détecté sans ambiguïté.
function isCodePostalQuebec(codePostal) {
	return /^[A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d$/.test(codePostal);
}

// Paris/Lyon/Marseille sont les 3 seules communes françaises divisées en
// arrondissements ayant chacun leur propre code postal : pour un même
// code postal, l'API renvoie parfois à la fois le nom générique de la
// ville ("Paris") et l'arrondissement précis ("Paris 1er
// Arrondissement") -- l'arrondissement est toujours plus précis (le code
// postal désigne déjà UN arrondissement en particulier), on le préfère
// plutôt que de traiter ça comme une ambiguïté entre 2 vraies communes
// différentes (contrairement à un code partagé par plusieurs communes
// distinctes, ex: 74120 -> Megève / Praz-sur-Arly / Demi-Quartier).
function preferArrondissement(villes) {
	const arrondissement = villes.find((v) => /^(Paris|Lyon|Marseille) \d+(er|e) Arrondissement$/i.test(v));
	if (arrondissement && villes.length > 1) {
		return [arrondissement];
	}
	return villes;
}

// Interroge l'API adresse du gouvernement (gratuite, sans clé) pour
// récupérer la ou les communes correspondant à un code postal français.
// Renvoie un tableau vide en cas d'erreur réseau.
async function villesFromCodePostal(codePostal) {
	if (!isCodePostalFrancais(codePostal)) return [];
	try {
		const url = `https://api-adresse.data.gouv.fr/search/?q=${codePostal}&type=municipality&postcode=${codePostal}&limit=20`;
		const res = await fetch(url);
		if (!res.ok) return [];
		const data = await res.json();
		const villes = [...new Set(data.features.map((f) => f.properties.city))];
		return preferArrondissement(villes);
	} catch (e) {
		return [];
	}
}

function setPays(label) {
	document.getElementById('displayPays').textContent = label || '--';
}

function setRegion(label) {
	document.getElementById('region').value = label || '';
	document.getElementById('displayRegion').textContent = label || '--';
}

function setVille(name) {
	document.getElementById('ville').value = name || '';
}

// La zone "Ville" change de forme selon ce qu'on peut déterminer :
// - 'display' : une seule ville possible (ou aucune donnée) -- texte simple
// - 'select'  : plusieurs communes partagent ce code postal -- l'utilisateur
//               choisit parmi les vraies options plutôt que de deviner
// - 'texte'   : pas de source fiable pour cette zone (étranger) -- saisie libre
function setVilleMode(mode) {
	document.getElementById('displayVille').style.display = mode === 'display' ? 'inline' : 'none';
	document.getElementById('villeSelect').style.display = mode === 'select' ? 'inline' : 'none';
	document.getElementById('villeTexte').style.display = mode === 'texte' ? 'inline' : 'none';
}

function resetLocalisation() {
	setPays(null);
	setRegion(null);
	setVille(null);
	setVilleMode('display');
	document.getElementById('displayVille').textContent = '--';
	document.getElementById('villeSelect').innerHTML = '';
	document.getElementById('villeTexte').value = '';
	document.getElementById('paysManuel').style.display = 'none';
	document.getElementById('paysManuelSelect').value = '';
}

async function onCodePostalChange() {
	const codePostal = document.getElementById('codePostal').value.trim();
	resetLocalisation();
	if (codePostal === '') return;

	if (isCodePostalFrancais(codePostal)) {
		setPays('France');
		setRegion(regionFromCodePostal(codePostal));

		const villes = await villesFromCodePostal(codePostal);
		if (villes.length === 1) {
			setVilleMode('display');
			document.getElementById('displayVille').textContent = villes[0];
			setVille(villes[0]);
		} else if (villes.length > 1) {
			setVilleMode('select');
			const select = document.getElementById('villeSelect');
			select.innerHTML =
				'<option value="" selected>--Choisir--</option>' +
				villes.map((v) => `<option value="${v}">${v}</option>`).join('');
		}
		// villes.length === 0 : aucune commune trouvée (erreur réseau ou code
		// inconnu) -- reste en mode 'display' avec "--", rien de plus à faire.
		return;
	}

	if (isCodePostalQuebec(codePostal)) {
		setPays('Québec');
		setRegion('Québec');
		setVilleMode('texte');
		return;
	}

	// Format non reconnu (souvent Belgique/Suisse/Luxembourg, tous à 4
	// chiffres -- impossible de les distinguer entre eux à partir du seul
	// code postal) : on demande le pays à la main plutôt que de deviner.
	document.getElementById('paysManuel').style.display = 'block';
	setVilleMode('texte');
}

function onPaysManuelChange() {
	const pays = document.getElementById('paysManuelSelect').value;
	setPays(pays || null);
	// regions.xml traite Belgique/Suisse/Luxembourg/Europe/Québec comme des
	// entrées de région à part entière (pas de subdivision en dessous) --
	// même valeur des deux côtés.
	setRegion(pays || null);
}

function onVilleSelectChange() {
	setVille(document.getElementById('villeSelect').value);
}

function onVilleTexteChange() {
	setVille(document.getElementById('villeTexte').value);
}

function onFormSubmit() {
	if (!document.getElementById('region').value) {
		alert('Merci de renseigner un code postal permettant de déterminer votre pays avant de valider (utilisez le sélecteur de secours si besoin).');
		return false;
	}
	alert('Présentation validée ! Envoi en cours');
	return true;
}

// record_form.js restaure la valeur de codePostal depuis le localStorage
// au chargement -- sans ça, le champ paraît rempli mais région/ville
// cachés restent vides tant qu'on n'a pas retouché le champ.
document.addEventListener('DOMContentLoaded', () => {
	if (document.getElementById('codePostal').value.trim() !== '') {
		onCodePostalChange();
	}
});
