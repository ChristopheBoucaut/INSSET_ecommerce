$(document).ready(function(){
	
	/*
	 * On récupère le code html pour générer les input des boutons radios
	 */
	$('.input-radio>label').each(function(){
		if($(this).children('input#sens-0').length){
			$(this).children('input#sens-0').attr('checked','checked');
			first_radio = $(this).html();
		}else if($(this).children('input#sens-1').length){
			second_radio = $(this).html();
		}
	});
	
	majRadio($('#type_tri').val());
	
	/*
	 * Si le select change, on va changer le text des boutons en dessous
	 */
	$('#type_tri').change(function(){
		majRadio($(this).val());
	})
});

function majRadio(str){
	if(str == 'prix'){
		$('.input-radio>label').each(function(){
			if($(this).children('input#sens-0').length){
				$(this).html(first_radio + 'Moins cher');
			}else if($(this).children('input#sens-1').length){
				$(this).html(second_radio + 'Plus cher');
			}
		});
	}else if(str == 'nom'){
		$('.input-radio>label').each(function(){
			if($(this).children('input#sens-0').length){
				$(this).html(first_radio + 'A -> Z');
			}else if($(this).children('input#sens-1').length){
				$(this).html(second_radio + 'Z -> A');
			}
		});
	}else if(str == 'disponibilite'){
		$('.input-radio>label').each(function(){
			if($(this).children('input#sens-0').length){
				$(this).html(first_radio + 'Le - en stock');
			}else if($(this).children('input#sens-1').length){
				$(this).html(second_radio + 'Le + en stock');
			}
		});
	}
}