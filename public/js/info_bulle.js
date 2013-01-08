$(document).ready(function(){
	
	$('a.info-bulle').live('click', function(e){
		e.preventDefault();
		disabledLink($(this));
	})
	
	$('a.close-info-bulle').live('click', function(e){
		e.preventDefault();
		disabledLinkClose($(this));
	})
});

/**
 * Permet d'activer l'info-bulle
 * @param: Object(JQuery) lien_clique
 * @return: void
 **/
function disabledLink(lien_clique){
	var span = lien_clique.siblings('span');
	span.css('display','block');
	/*span.mouseleave(function(){$(this).css('display','none');});*/
}

/**
 * Permet de fermer l'info-bulle avec la croix
 * @param: Object(JQuery) croix
 * @return: void
 **/
function disabledLinkClose(croix){
	croix.parent('span.info-clients').css('display','none');
}