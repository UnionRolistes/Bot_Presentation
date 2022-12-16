
function chgAgeDisplay(){ //Change la zone de saisie d'age en menu déroulant pour les tranches d'âge

	if(document.getElementById('checkAge').checked){ //On affiche les tranches

		document.getElementById('age').value=""; //Pour pas que le texte rentré avant de cocher soit compté à l'envoi
		document.getElementById('age').style.display="none";
		document.getElementById('age').required = false;

		document.getElementById('trancheAge').style.display="initial";
		document.getElementById('trancheAge').required = true;
	}
	else{ //On réaffiche l'age

		document.getElementById('trancheAge').value="";
		document.getElementById('trancheAge').style.display="none";
		document.getElementById('trancheAge').required = false;

		document.getElementById('age').style.display="initial";
		document.getElementById('age').required = true;
	}	
}
