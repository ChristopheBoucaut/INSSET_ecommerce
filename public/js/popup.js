$(document).ready(function(){
	closePopup();
	lienPopup();
	
	$('.popup-content a').live('click', function(e){
		e.preventDefault();
		disabledLink($(this));
	})
	
	$('.popup-content form').live('submit', function(e){
		e.preventDefault();
		disabledForm($(this));
	})
});

function closePopup(){
	$('.popup .close-popup').click(function(e){
		e.preventDefault();
		$('.popup').css('display','none');
		$('.background-popup').css('display','none');
	})
}

function lienPopup(){
	$('a.popup-link').click(function(e){
		e.preventDefault();
		appelAjax($(this).attr('href'),'GET',{'ajax':'1'});
		$('.popup').css('display','block');
		$('.background-popup').css('display','block');
	});
}

/**
 * Permet de changer le comportement de tous les liens dans le menu et le contenu qui change en fonction d'ajax
 * @param: Object(JQuery) lien_clique
 * @return: void
 **/
function disabledLink(lien_clique){	
	var url = lien_clique.attr("href");
	/*
	 * Lance la requête ajax vers la page désirée lors du clique
	 */
	appelAjax(url, null, null);
}

/**
 * Permet de changer le comportement de tous les formulaire contenu dans la partie changeant en fonction d'ajax
 * @param: Object(JQuery) lien_clique
 * @return: void
 **/
function disabledForm(form_submit){
	/*
	 * On récupère l'informations sur la method et l'action pour l'envoie des données
	 */
	var action = $(form_submit).attr('action');
	var method = $(form_submit).attr('method');
	
	var str ="var param_form={'ajax':'1'";
	var i=0;
	/*
	 * On parcourt tous les éléments du formulaire et on créer une chaine de caractère pour créer un tableau de parametre
	 */
	$(form_submit).find('*:input').each(function(){
		if((($(this).attr('type')=="radio" || $(this).attr('type')=="checkbox") &&  $(this)[0].checked==true)
				|| ($(this).attr('type')!="radio" && $(this).attr('type')!="checkbox")){
			str=str+",";
			var name = $(this).attr('name');
			var val = $(this).val();
			if(name==""){
				str=str+"'"+i+"':'"+val+"'";
				i++;
			}else{
				str=str+"'"+name+"':'"+val+"'";
			}
		}
	});
	str=str+"}";
	/*
	 * Créer le tableau a passer en param pour la requete ajax
	 */
	eval(str);
	/*
	 * Lance la requête ajax vers la page désirée lors du clique
	 */
	appelAjax(action, method, param_form);
}

function appelAjax(url, type, data){
	if(type==null || type==""){
		type='GET';
	}
	if(typeof(data)!='object'){
		data=new Array();
	}
	
	$.ajax({
		type: type,
		url: url,
		data: data,
		dataType: 'json',
		success: function(data) {
			if(data.url){
				$(location).attr('href',data.url);
			}else{
				$('.popup .popup-content').html(data.content);
			}
			
		},
		error: function(){
			var div_error = $('<div>',{
				class:"erreur-chargement",
				html:"Erreur lors du chargement de la page. Contacter l'administrateur."});
			$('.popup .popup-content').empty();
			$('.popup .popup-content').append(div_error);
		}
	});
}