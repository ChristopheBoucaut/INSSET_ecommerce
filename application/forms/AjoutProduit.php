<?php

/**
 * Formulaire pour l'ajout d'un produit
 * @class: Application_Form_AjoutProduit
 * @file: AjoutProduit.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 6 déc. 2012
 * - Version initiale
 *
 **/

class Application_Form_AjoutProduit extends Zend_Form{
	
	/**
	 * Url pour l'action du formulaire
	 * @var: String $url
	 **/
	private $url;
	
	
	/**
	 * Constructeur
	 * @param: array $params
	 * @param: array $options
	 * @return: Object
	 **/
	public function __construct($params, $options=null){
		if(isset($params['url'])){
			$this->url = $params['url'];
		}else{
			$this->url = "";
		}
	
		parent::__construct($options);
	}
	
	/**
	 * Permet d'initialiser le formulaire
	 * @return: void
	 **/
	public function init(){
		// Décorateur pour les inputs
		$decorators_input = array(
				array('ViewHelper'),
				array('Errors'),
				array('Label', array(
						'requiredSuffix'=>' *',
						'separator'=>' :'
				)),
				array('HtmlTag', array('tag'=>"div"))
		);
		
		// Décorateur pour le formulaire en général
		$decorators_form = array(
				'FormElements',
				'Form'
		);
		
		// message d'erreur pour champs manquant
		$msg_error_text_required = "Ce champs doit obligatoirement être remplit.";
		
		// message d'erreur pour champs numérique
		$msg_error_numerique = "Ce champs doit obligatoirement être numérique";
		
		// message d'erreur pour champs numérique en cas de nombre négatif
		$msg_error_numerique_unsigned = "Ce champs doit obligatoirement être d'une valeur positive ou nulle";
		
		// Parametrage du formulaire
		$this->setMethod('post');
		$this->setAction($this->url);
		$this->setAttrib('id', 'form-ajout-produit');
		
		//Desactivation des décorateurs par défaut et ajout du notre
		$this->clearDecorators();
		$this->addDecorators($decorators_form);
		
		// Création de l'input et label pour le nom du produit
		$input_nom = new Zend_Form_Element_Text('nom_produit');
		$input_nom->setLabel('Désignation du produit');
		$input_nom->setRequired(true);
		$input_nom->setDecorators($decorators_input);
		$input_nom->addValidator('NotEmpty');
		$input_nom->getValidator('NotEmpty')->setMessage($msg_error_text_required);
		
		// Création de l'input et label pour la description du produit
		$input_description = new Zend_Form_Element_Textarea('description_produit');
		$input_description->setLabel('Description du produit');
		$input_description->setRequired(true);
		$input_description->setDecorators($decorators_input);
		$input_description->addValidator('NotEmpty');
		$input_description->getValidator('NotEmpty')->setMessage($msg_error_text_required);
		
		// Création de l'input et label pour le prix du produit
		$prix_produit = new Zend_Form_Element_Text('prix_produit');
		$prix_produit->setLabel('Prix du produit');
		$prix_produit->setRequired(true);
		$prix_produit->setDecorators($decorators_input);
		$prix_produit->addValidator('NotEmpty');
		$prix_produit->getValidator('NotEmpty')->setMessage($msg_error_text_required);
		$prix_produit->addValidator('Float');
		$prix_produit->getValidator('Float')->setMessage($msg_error_numerique);
		$prix_produit->addValidator('Regex',false,array('pattern'=>"/^[[:digit:]]*(\.|\,){0,1}[[:digit:]]*$/"));
		$prix_produit->getValidator('Regex')->setMessage($msg_error_numerique_unsigned);
		$prix_produit->addFilter('PregReplace', array('match'=>'/\./','replace'=>','));
		
		// Création de l'input et label pour le stock du produit
		$stock_produit = new Zend_Form_Element_Text('stock_produit');
		$stock_produit->setLabel('Stock du produit');
		$stock_produit->setRequired(true);
		$stock_produit->setDecorators($decorators_input);
		$stock_produit->addValidator('NotEmpty');
		$stock_produit->getValidator('NotEmpty')->setMessage($msg_error_text_required);
		$stock_produit->addValidator('Int');
		$stock_produit->getValidator('Int')->setMessage($msg_error_numerique);
		$stock_produit->addValidator('Regex',false,array('pattern'=>"/^[[:digit:]]*$/"));
		$stock_produit->getValidator('Regex')->setMessage($msg_error_numerique_unsigned);
		
		// Création du bouton d'envoie du formulaire
		$input_submit = new Zend_Form_Element_Submit('Ajouter');
		$input_submit->removeDecorator('DtDdWrapper');
		
		// Ajout des éléments au formulaire
		$this->addElement($input_nom);
		$this->addElement($input_description);
		$this->addElement($prix_produit);
		$this->addElement($stock_produit);
		$this->addElement($input_submit);
	}
	
}