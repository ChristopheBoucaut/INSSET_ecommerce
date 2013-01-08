<?php

/**
 * Formulaire permettant le tri des produits sur la page publique
 * @class: Application_Form_TriProduits
 * @file: TriProduits.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 29 nov. 2012
 * - Version initiale
 *
 **/

class Application_Form_TriProduits extends Zend_Form{
	
	/**
	 * Contient l'url pour l'envoie du formulaire
	 * @var: string $url
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
		// Décorateur pour le select
		$decorators_select = array(
				array('ViewHelper'),
				array('Errors'),
				array('Label',array('tag'=>'div')),
				array('HtmlTag', array('tag'=>"div"))
		);
		
		// Décorateur pour le radio
		$decorators_radio = array(
				array('ViewHelper'),
				array('Errors'),
				array('HtmlTag', array('tag'=>"div",'class'=>"input-radio"))
		);
		
		// Décorateur pour le formulaire en général
		$decorators_form = array(
				'FormElements',
				'Form'
		);
		
		
		// Parametrage du formulaire
		$this->setMethod('get');
		$this->setAction($this->url);
		$this->setAttrib('id', 'form-tri-produit');
		
		//Desactivation des décorateurs par défaut et ajout du notre
		$this->clearDecorators();
		$this->addDecorators($decorators_form);
		
		// Création du select avec les 3 façons de trier
		$select = new Zend_Form_Element_Select('type_tri');
		$select->setLabel('Trier par :');
		$param_select = array("nom"=>"Nom","prix"=>"Prix","disponibilite"=>"Disponibilité");
		$select->addMultiOptions($param_select);
		$select->setDecorators($decorators_select);
		
		// Création des deux boutons radio pour savoir dans quel sens les trier
		$radio = new Zend_Form_Element_Radio('sens');
		$param_radio = array("0"=>"","1"=>"");
		$radio->addMultiOptions($param_radio);
		$radio->setDecorators($decorators_radio);
		
		// Création du bouton d'envoie du formulaire
		$input_submit = new Zend_Form_Element_Submit('Trier');
		$input_submit->removeDecorator('DtDdWrapper');
		
		// Ajout des éléments au formulaire
		$this->addElement($select);
		$this->addElement($radio);
		$this->addElement($input_submit);
	}
}