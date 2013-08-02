<?php

/**
  * Classe de lancement trop cool d'une application Zend Framework 1
  * 
  * @class: Bootstrap
  * @file: Bootstrap.php
  * @author : Christophe BOUCAUT
  * @version: 1.0
  *
  * @changelogs :
  * Rev 1.0 du 12 nov. 2012
  * - Version initiale
  *
  **/

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	/**
	  * Méthode permettant de lancer l'éxécution de l'application
	  * @return:void
	 **/
	public function run(){
		parent::run();
	}
	
	/**
	  * Initialiser le chargement de la libraire de surcouche de zend 
	  * @return: void
	 **/
	protected function _initLibraryFestival(){
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('SurcoucheZend_');
	}
	
	/**
	  * Initialiser une session avec le namespace pour la librairie
	  * @return: Zend_Session_Namespace $session
	 **/
	protected function _initSession(){
		$session = new Zend_Session_Namespace('SurcoucheZend', true);
		return $session;
	}
	
	/**
	  * Méthode d'initialisation de variables enregistrées dans Zend_Registry
	  * @return: void
	 **/
	public function _initRegistry(){
		// On définit que par défaut on ne veut pas que le menu soit caché
		Zend_Registry::set('showmenu', false);
		
		// On définit que par défaut on ne veut pas que le header soit caché
		Zend_Registry::set('showheader', false);
		
		// On définit que par défaut on ne veut pas que le footer soit caché
		Zend_Registry::set('showfooter', false);
		
		// On définit un tableau des fichiers css à ajouter pendant l'excécution des scripts
		Zend_Registry::set('css', array());
		
		// On définit un tableau des fichiers js à ajouter pendant l'excécution des scripts
		Zend_Registry::set('js', array());
		
		// On définit si on a besoin d'une popup
		Zend_Registry::set('needpopup', true);
	}
	
	/**
	  * Permet le chargement des plugins nécessaires au fonctionnement du site
	  * @return: void
	 **/
	public function _initPlugins(){
		$this->bootstrap('frontcontroller');
		$frontcontroller = $this->getResource('frontcontroller');
		
		// Plugin vérifiant la connexion au début de chaque controller
		$frontcontroller->registerPlugin(new SurcoucheZend_Plugin_PluginConnexion());
		// Plugin vérifiant si on fait un appel en ajax
		$frontcontroller->registerPlugin(new SurcoucheZend_Plugin_PluginAccesAjax());
		// Plugin pour init certaines variables a instancier une fois le bootstrap lancé ex : baseUrl
		$frontcontroller->registerPlugin(new SurcoucheZend_Plugin_PluginInitVariables());
	}
	
	/**
	 * Permet d'initialiser la config mail
	 * @return: void
	 **/
	public function _initZendMail(){
		$config = array(/*'auth' => 'login',
				'username' => 'christophe.boucaut@etud.u-picardie.fr',
				'password' => 'ecoleecole',*/
				'port'=>'25');
		
		//$config = array('name' => 'christophe.boucaut@etud.u-picardie.fr');
		$transport = new Zend_Mail_Transport_Smtp('oban.u-picardie.fr',$config);
		Zend_Mail::setDefaultTransport($transport);
	}

}

