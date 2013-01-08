<?php

/**
 * Plugin permettant de gérer l'appel ajax des controllers du site
 * durant le déroulement des controllers
 * @class: Application_Plugin_PluginAccesAjax
 * @file: PluginAccesAjax.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 21 nov. 2012
 * - Version initiale
 *
 **/
class SurcoucheZend_Plugin_PluginAccesAjax extends Zend_Controller_Plugin_Abstract {
	
	/**
	 * Fonction appliqué avant le lancement du controller désiré
	 * @param: Zend_Controller_Request_Abstract $request
	 * @return: void
	 **/
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		// on récupère les données en paramètre
		$data = $this->_request->getParams();
		
		if(isset($data["ajax"]) && intval($data)==1){
			$resource_layout = new Zend_Application_Resource_Layout();
			$resource_layout->getLayout()->setLayout("ajax");
		}
	}
}

?>