<?php

/**
 * Formulaire permettant de demander des informations sur le client pour l'envoie
 * @class: Application_Form_InformationsClient
 * @file: InformationsClient.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 27 nov. 2012
 * - Version initiale
 *
 **/

class Application_Form_InformationsClient extends Zend_Form {
	
	/**
	 * Contient l'url pour l'envoie du formulaire
	 * @var: string $url
	 **/
	private $url;
	
	/**
	 * Permet de savoir si on affiche ou non les erreurs
	 * @var: boolean $show_error
	 **/
	private $show_error;
	
	/**
	 * Texte pour le bouton submit
	 * @var: string $text_submit
	 **/
	private $text_submit;
	
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
		if(isset($params['show_error'])){
			$this->show_error = $params['show_error'];
		}else{
			$this->show_error = false;
		}
		if(isset($params['text_submit'])){
			$this->text_submit = $params['text_submit'];
		}else{
			$this->text_submit = "Valider";
		}
	
		parent::__construct($options);
	}
	
	
	/**
	 * Permet d'initialiser le formulaire demandant les informations sur le client
	 * @return: void
	 **/
	
	public function init(){
		// Décorateur pour les inputs de login et mdp
		$decorators_input = array(
				array('ViewHelper'),
				array('Label', array(
						'separator'=>' <span class="separator"><span class="informations">*</span> :</span>'
				)),
				array('HtmlTag', array('tag'=>"div"))
		);
		
		if(!$this->show_error){
			$decorators_input[]=array('Errors');
		}
		
		// Décorateur pour le formulaire en général
		$decorators_form = array(
				'FormElements',
				'Form'
		);
		
		
		// Parametrage du formulaire
		$this->setMethod('post');
		$this->setAction($this->url);
		$this->setAttrib('id', 'form-informations-client');
		
		//Desactivation des décorateurs par défaut et ajout du notre
		$this->clearDecorators();
		$this->addDecorators($decorators_form);
		
		// Création de l'input et label pour le nom
		$input_nom = new Zend_Form_Element_Text('nom');
		$input_nom->setLabel('Votre nom');
		$input_nom->setRequired(true);
		$input_nom->setAttrib('class', 'informations-clients');
		$input_nom->setDecorators($decorators_input);
		$input_nom->addFilter('StringTrim');
		$input_nom->setErrorMessages(array("Vous devez obligatoirement remplir ce champs."));
		
		// Création de l'input et label pour le prenom
		$input_prenom = new Zend_Form_Element_Text('prenom');
		$input_prenom->setLabel('Votre prenom');
		$input_prenom->setRequired(true);
		$input_prenom->setAttrib('class', 'informations-clients');
		$input_prenom->setDecorators($decorators_input);
		$input_prenom->addFilter('StringTrim');
		$input_prenom->setErrorMessages(array("Vous devez obligatoirement remplir ce champs."));
		
		// Création de l'input et label pour l'email
		$input_email = new Zend_Form_Element_Text('mail');
		$input_email->setLabel('Votre adresse mail');
		$input_email->setRequired(true);
		$input_email->setAttrib('class', 'informations-clients');
		$input_email->setDecorators($decorators_input);
		$input_email->addFilter('StringToLower');
		$input_email->addFilter('StringTrim');
		$input_email->addValidator('EmailAddress');
		$input_email->setErrorMessages(array("Vous devez obligatoirement remplir ce champs et que l'adresse mail soit correcte."));
		
		// Décorateur pour les inputs de login et mdp
		$decorators_input_submit = array(
				array('ViewHelper'),
				array('Description', array('tag'=>'div','placement'=>'prepend','class'=>'informations')),
				array('HtmlTag', array('tag'=>"div"))
		);
		
		// Création du bouton d'envoie du formulaire
		$input_submit = new Zend_Form_Element_Submit($this->text_submit);
		$input_submit->setDescription("* signifie que le champs doit être obligatoirement remplit.");
		$input_submit->setAttrib('class', 'informations-clients-submit');
		$input_submit->addDecorators($decorators_input_submit);
		$input_submit->removeDecorator('DtDdWrapper');
		
		// Ajout des éléments au formulaire
		$this->addElement($input_nom);
		$this->addElement($input_prenom);
		$this->addElement($input_email);
		$this->addElement($input_submit);
	}
}