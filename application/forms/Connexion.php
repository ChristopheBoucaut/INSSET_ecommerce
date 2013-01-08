<?php

/**
  * Formulaire de connexion
  * @class: Application_Form_Connexion
  * @file: Connexion.php
  * @author : Christophe BOUCAUT
  * @version: 1.0
  *
  * @changelogs :
  * Rev 1.0 du 7 nov. 2012
  * - Version initiale
  *
 **/

class Application_Form_Connexion extends Zend_Form {
	
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
	  * permet d'initialiser l'objet Connexion/Zend_Form
	  * @return: void
	 **/
	public function init(){
		
		// Décorateur pour les inputs de login et mdp
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
		
		// message d'erreur pour login et mdp
		$msg_error_text_required = "Ce champs doit obligatoirement être remplit.";
		
		
		// Parametrage du formulaire
		$this->setMethod('post');
		$this->setAction($this->url);
		$this->setAttrib('id', 'form-connexion');
		
		//Desactivation des décorateurs par défaut et ajout du notre
		$this->clearDecorators();
		$this->addDecorators($decorators_form);
		
		// Création de l'input et label pour le login
		$input_login = new Zend_Form_Element_Text('login');
		$input_login->setName("login");
		$input_login->setLabel('Votre identifiant de connexion');
		$input_login->setRequired(true);
		$input_login->setDecorators($decorators_input);
		$input_login->addValidator('NotEmpty');
		$input_login->setErrorMessages(array($msg_error_text_required));
		
		// Création de l'input et label pour le mot de passe
		$input_mdp = new Zend_Form_Element_Password('mdp');
		$input_mdp->setName("mdp");
		$input_mdp->setLabel('Votre mot de passe de connexion');
		$input_mdp->setRequired(true);
		$input_mdp->setDecorators($decorators_input);
		$input_mdp->addValidator('NotEmpty');
		$input_mdp->setErrorMessages(array($msg_error_text_required));
		
		// Création du captcha
		$baseUrl = new Zend_View_Helper_BaseUrl();
		$captcha = new Zend_Form_Element_Captcha('captcha', array(
    		'label' => "Veuillez recopier ce qui suis :",
    
			// paramétrage en reprenant les noms de méthodes vus précédemment
			'captcha' => array(
		        "captcha" => "Image",
		        "wordLen" => 5,
		        "font" => "./polices/godofwar.ttf",
				"height" => 100,
				"width" => 300,
				"fontSize" => 50,
				"imgDir" => "../var/captcha/",
				"imgUrl" => $baseUrl->baseUrl()."/../var/captcha/"
		    )
		));
		
		// Création du bouton d'envoie du formulaire
		$input_submit = new Zend_Form_Element_Submit('Connexion');
		$input_submit->removeDecorator('DtDdWrapper');
		
		// Ajout des éléments au formulaire
		$this->addElement($input_login);
		$this->addElement($input_mdp);
		$this->addElement($captcha);
		$this->addElement($input_submit);
		
	}
	
}


?>