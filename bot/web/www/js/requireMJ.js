function chgMjRequire(){ //Force qu'au moins MJ ou PJ soit coché

	if(document.getElementById('PJ').checked){ //Si on a choisi PJ
		document.getElementById('MJ').required = false;//Alors MJ n'est plus obligatoire
        document.getElementById('PJ').required = true;
	}
    else{ //Et inversement. Un des 2 sera toujours obligatoire même si on s'amuse avec le bouton reset
        document.getElementById('PJ').required = false;
        document.getElementById('MJ').required = true;
    }
}