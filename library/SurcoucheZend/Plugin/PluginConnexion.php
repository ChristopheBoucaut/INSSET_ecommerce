<?php

/**
 * Plugin permettant de gérer les évènements à appliquer pour la gestion de la connexion
 * durant le déroulement des controllers
 * @class: Application_Plugin_PluginConnexion
 * @file: PluginConnexion.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 20 nov. 2012
 * - Version initiale
 *
 **/
class SurcoucheZend_Plugin_PluginConnexion extends Zend_Controller_Plugin_Abstract {
	
	/**
	 * Fonction appliqué avant le lancement du controller désiré
	 * @param: Zend_Controller_Request_Abstract $request
	 * @return: void
	 **/
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$auth = Zend_Auth::getInstance();
		
		// Si on est pas connecté et qu'on est dans la partie admin et qu'on est pas sur la page de co ou de deco
		if(!$auth->hasIdentity()
				&& ($this->_request->getControllerName() == 'admin'
				&& ($this->_request->getActionName() != 'connexion' && $this->_request->getActionName() != 'deconnexion')
				)
		)
		{
			// On récupère l'action et controller désiré
			$need_action = $this->_request->getActionName();
			$need_controller = $this->_request->getControllerName();
			
			// On prépare l'url de redirection 
			$url = '/admin/connexion?need_action='.$need_action.'&need_controller='.$need_controller.'&need_connexion=1';
			
			// On instancie une aide redirector et on effectue la redirection
			$redirector = new Zend_Controller_Action_Helper_Redirector;
			$redirector->gotoUrlAndExit($url, array('exit' => true));
			
		}
	}
}

?>