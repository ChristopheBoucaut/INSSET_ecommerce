<?php

class IndexController extends SurcoucheZend_Controller_Action
{

	/**
	 * Liste des parametres acceptable pour le tri des produits
	 * @var: 
	 **/
	private $actions_autorisees_tri = array('nom','prix','disponibilite');
	
    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * action appelée par défaut
     * @return: void
     **/
    public function indexAction()
    {
    	// on ajoute le fichier css pour la mise en page des produits
    	$this->ajoutCss("index.css");
    	
    	$type_tri = strval($this->getRequest()->getParam('type_tri'));
    	$sens_tri = intval($this->getRequest()->getParam('sens'));
    	// On récupère les éventuels paramètres de tri
    	if($type_tri==""
    			|| ($sens_tri !== 0 && $sens_tri !== 1)
    			|| !in_array($type_tri,$this->actions_autorisees_tri)){
    		$type_tri = 'nom';
    		$sens_tri = 0;
    	}
    	
    	// on prépare le tableau a passer en argument pour le tri
    	if($type_tri == 'nom'){
    		$param_order = array('nom_produit'=>$sens_tri);
    	}elseif($type_tri == 'prix'){
    		$param_order = array('prix_produit'=>$sens_tri);
    	}elseif($type_tri == 'disponibilite'){
    		$param_order = array('stock_produit'=>$sens_tri);
    	}

    	// on récupère la requete sql pour récupérer tous les produits
        $tproduit = new Application_Model_TProduit();
        $req_produit = $tproduit->infoProduit(null, true, true,false,true,null,$param_order);
        
        // on utiliser la pagination pour afficher seulement un maximum de 9 par pages
        $liste_produit = Zend_Paginator::factory($req_produit);
        $liste_produit->setCurrentPageNumber($this->getRequest()->getParam("page"));
        $liste_produit->setItemCountPerPage(6);
        $liste_produit->setPageRange(3);
        $this->view->assign('produits', $liste_produit);
    }

}

