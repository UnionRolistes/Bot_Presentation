
// Déduit la région et propose des villes à partir du code postal saisi,
// pour limiter les erreurs de saisie (voir issue #83). Le champ région
// reste modifiable manuellement : en cas de doute (Belgique/Suisse/
// Luxembourg partagent le même format à 4 chiffres, impossible de les
// distinguer de façon fiable) on laisse l'utilisateur choisir lui-même,
// plutôt que de deviner au hasard.

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

// Renvoie le libellé de région (data/regions.xml) déduit du code postal,
// ou null si non déterminable (format inconnu, ou ambigu entre plusieurs
// pays comme Belgique/Suisse/Luxembourg).
function regionFromCodePostal(codePostal) {
	if (/^97\d{3}$/.test(codePostal)) {
		return DEPARTEMENT_TO_REGION[codePostal.substring(0, 3)] || null;
	}
	if (/^\d{5}$/.test(codePostal)) {
		return DEPARTEMENT_TO_REGION[codePostal.substring(0, 2)] || null;
	}
	// Québec/Canada : format alphanumérique A1A 1A1 (espace optionnel),
	// suffisamment distinctif pour être détecté sans ambiguïté.
	if (/^[A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d$/.test(codePostal)) {
		return 'Québec';
	}
	// Belgique, Suisse et Luxembourg utilisent tous un format à 4 chiffres
	// -- impossible de les distinguer de façon fiable à partir du seul
	// code postal, donc pas de détection automatique ici (l'utilisateur
	// choisit manuellement, comme avant cette fonctionnalité).
	return null;
}

// Interroge l'API adresse du gouvernement (gratuite, sans clé) pour
// récupérer la ou les communes correspondant à un code postal français.
// Renvoie un tableau vide en cas d'erreur réseau ou de code non français
// -- le champ ville reste alors éditable à la main comme avant.
async function villesFromCodePostal(codePostal) {
	if (!/^\d{5}$/.test(codePostal)) return [];
	try {
		const url = `https://api-adresse.data.gouv.fr/search/?q=${codePostal}&type=municipality&postcode=${codePostal}&limit=20`;
		const res = await fetch(url);
		if (!res.ok) return [];
		const data = await res.json();
		return [...new Set(data.features.map((f) => f.properties.city))];
	} catch (e) {
		return [];
	}
}

function selectRegion(regionLabel) {
	const select = document.getElementById('region');
	for (const option of select.options) {
		if (option.value === regionLabel) {
			select.value = regionLabel;
			return;
		}
	}
}

async function onCodePostalChange() {
	const codePostal = document.getElementById('codePostal').value.trim();
	if (codePostal === '') return;

	const region = regionFromCodePostal(codePostal);
	if (region) selectRegion(region);

	const villes = await villesFromCodePostal(codePostal);
	const datalist = document.getElementById('villesSuggestions');
	datalist.innerHTML = '';
	villes.forEach((ville) => {
		const option = document.createElement('option');
		option.value = ville;
		datalist.appendChild(option);
	});

	// Une seule ville possible pour ce code postal : on pré-remplit
	// directement. Plusieurs villes possibles (code postal "piégeux",
	// ex: 74120 -> Megève / Praz-sur-Arly / Demi-Quartier) : on laisse le
	// champ vide avec les suggestions dans la datalist, l'utilisateur
	// choisit plutôt qu'une valeur devinée au hasard.
	if (villes.length === 1) {
		document.getElementById('ville').value = villes[0];
	}
}
