<?php

class SurcoucheZend_Controller_Action extends Zend_Controller_Action{
	
	/**
	 * Méthode permettant d'ajouter des fichiers css au chargement du site
	 * @param:(string)$nom_fichier
	 * @return:void
	 **/
	public function ajoutCss($nom_fichier){
		if(Zend_Registry::isRegistered('css')){
			$css_temp = Zend_Registry::get('css');
		}else{
			$css_temp = array();
		}
		$css_temp[] = (string) $nom_fichier;
		Zend_Registry::set('css', $css_temp);
	}
	
	/**
	 * Méthode permettant d'ajouter des fichiers js au chargement du site
	 * @param:(string)$nom_fichier
	 * @return:void
	 **/
	public function ajoutJs($nom_fichier){
		if(Zend_Registry::isRegistered('js')){
			$js_temp = Zend_Registry::get('js');
		}else{
			$js_temp = array();
		}
		$js_temp[] = (string) $nom_fichier;
		Zend_Registry::set('js', $js_temp);
	}
	
	/**
	 * Permet de désactiver le header
	 * @return: void
	 **/
	public function disabledHeader(){
		Zend_Registry::set('showheader', true);
	}
	
	/**
	 * Permet d'activer le header
	 * @return: void
	 **/
	public function enabledHeader(){
		Zend_Registry::set('showheader', false);
	}
	
	/**
	 * Permet de désactiver le menu de gauche
	 * @return: void
	 **/
	public function disabledMenuLeft(){
		Zend_Registry::set('showmenu', true);
	}
	
	/**
	 * Permet d'activer le menu de gauche
	 * @return: void
	 **/
	public function enabledMenuLeft(){
		Zend_Registry::set('showmenu', false);
	}
	
	/**
	 * Permet de désactiver le footer
	 * @return: void
	 **/
	public function disabledFooter(){
		Zend_Registry::set('showfooter', true);
	}
	
	/**
	 * Permet d'activer le footer
	 * @return: void
	 **/
	public function enabledFooter(){
		Zend_Registry::set('showfooter', false);
	}
	
	/**
	 * Permet d'activer le div pour les popup
	 * @return: void
	 **/
	public function activePopup(){
		Zend_Registry::set('needpopup', true);
	}
	
	/**
	 * Permet de désactiver le div pour les popup
	 * @return: void
	 **/
	public function disablePopup(){
		Zend_Registry::set('needpopup', false);
	}
	
	/**
	 * Permet de savoir si on est sur une page admin ou non
	 * @return: boolean
	 **/
	public function onAdmin(){
		if(Zend_Controller_Front::getInstance()->getRequest()->getParam('controller')=="admin"){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Permet de savoir si on est connecté ou non
	 * @return: boolean
	 **/
	public function isConnected(){
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
			return true;
		}else{
			return false;
		}
	}
}