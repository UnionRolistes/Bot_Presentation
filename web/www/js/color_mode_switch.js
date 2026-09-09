/*UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
Ask a derogation at Contact.unionrolistes@gmail.com*/

// Bascule clair/sombre : même mécanisme que site.unionrolistes.fr
// (attribut data-theme="light" sur <html>, choix mémorisé sous la clé
// localStorage 'ur-theme', défaut = préférence système). Le thème initial
// est appliqué par un script inline dans <head> avant le chargement du CSS
// pour éviter un flash au chargement ; ici on ne gère que la bascule.
(function () {
	const KEY = 'ur-theme';

	function apply(theme) {
		if (theme === 'light') {
			document.documentElement.setAttribute('data-theme', 'light');
		} else {
			document.documentElement.removeAttribute('data-theme');
		}
	}

	// Conservé sous ce nom pour compatibilité avec d'éventuels appels externes.
	window.chgMode = function () {
		const next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
		apply(next);
		try {
			localStorage.setItem(KEY, next);
		} catch (e) {
			// stockage indisponible (navigation privée...) : la bascule reste
			// valable pour la page en cours, simplement pas mémorisée
		}
	};

	document.addEventListener('DOMContentLoaded', () => {
		const bouton = document.getElementById('themeToggle');
		if (bouton) bouton.addEventListener('click', window.chgMode);
	});
})();
