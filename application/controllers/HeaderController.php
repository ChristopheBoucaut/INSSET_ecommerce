<?php

class HeaderController extends SurcoucheZend_Controller_Action{
	
	public function afficherAction(){
		if($this->isConnected()){
			$this->view->assign('last_header', "deconnexion");
		}else{
			$this->view->assign('last_header', "recap_panier");
		}
	}
}

?>