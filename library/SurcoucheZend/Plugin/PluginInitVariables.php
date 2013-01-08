<?php

/**
 * Plugin permettant de gérer l'initialisation de certaines variables communes à tous
 * @class: SurcoucheZend_Plugin_PluginInitVariables
 * @file: PluginInitVariables.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 04 dec. 2012
 * - Version initiale
 *
 **/
class SurcoucheZend_Plugin_PluginInitVariables extends Zend_Controller_Plugin_Abstract {
	
	/**
	 * Fonction appliqué avant le lancement du controller désiré
	 * @param: Zend_Controller_Request_Abstract $request
	 * @return: void
	 **/
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$data = Zend_Controller_Front::getInstance()->getRequest()->getParams();
		
		if(isset($data['controller'])){
			$controller = strval($data['controller']);
		}
		if(isset($data['action'])){
			$action = strval($data['action']);
		}
		$o_baseurl = new Zend_View_Helper_BaseUrl();
		$baseUrl = $o_baseurl->baseUrl($controller.'/'.$action);
		
		// on récupère le baseUrl que l'on stocke dans le registry
		Zend_Registry::set('baseUrl', $baseUrl);
	}
}

?>