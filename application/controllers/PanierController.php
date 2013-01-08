<?php

/**
 * Gère le panier de l'utilisateur
 * @class: PanierController
 * @file: PanierController.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 23 nov. 2012
 * - Version initiale
 *
 **/

class PanierController extends SurcoucheZend_Controller_Action{
	
	/**
	 * Contient une instance de Zend_Session_Namespace qui référencie le panier de l'utilisateur
	 * @var: Object $panier
	 **/
	private $panier;
	
	/**
	 * Permet d'initialisé le controller panier
	 * @return: void
	 **/
	public function init(){
		// On récupère le namespace panier de zend session, il dure au maximum 1h
		$this->panier = new Zend_Session_Namespace('panier');
		$this->panier->setExpirationSeconds(3600);
		
		// S'il n'y a pas encore de tableau produits, on le crée
		if(!isset($this->panier->produits)){
			$this->panier->produits = array();
		}
	}
	
	/**
	 * Permet d'afficher un récapitulatif du panier actuel sous forme d'un bouton
	 * @return: void
	 **/
	public function recapitulatifAction(){
		$this->view->assign('nb_total_produit',$this->nbProduitsPanier());
	}
	
	/**
	 * Permet d'ajouter un produit au panier
	 * @return: void
	 **/
	public function ajouterAction(){
		// Tableau contenant les parametres à passer à l'url de redirection à la fin de l'action
		$param = array();
		
		// On test si on a un id de produit à ajouter
		if($this->getRequest()->getParam("id_produit")){
			$id_produit=intval($this->getRequest()->getParam("id_produit"));
			
			// On vérifie en bdd que le produit a ajouter existe et qu'il est disponible dans le catalogue et encore en stock
			$tproduit = new Application_Model_TProduit();
			$resultat = $tproduit->infoProduit($id_produit, true, false, true);
			if(count($resultat)==1){
				if(!isset($this->panier->produits[$id_produit]) 
						|| $this->panier->produits[$id_produit]['nb_produits_commande'] == null){
					$this->panier->produits[$id_produit]['nb_produits_commande']=1;
					$param['produit_ajouter']=$id_produit;
				}elseif(isset($this->panier->produits[$id_produit]) && 
						$this->panier->produits[$id_produit]['nb_produits_commande']+1<=intval($resultat[$id_produit]['stock_produit'])){
					$this->panier->produits[$id_produit]['nb_produits_commande']++;
					$param['produit_ajouter']=$id_produit;
				}elseif($this->panier->produits[$id_produit]['nb_produits_commande']+1>$resultat['stock_produit']){
					$this->panier->produits[$id_produit]['nb_produits_commande'] = intval($resultat[$id_produit]['stock_produit']);
					$param['non_dispo_stock']=true;
				}
			}else{
				if(isset($this->panier->produits[$id_produit])){
					unset($this->panier->produits[$id_produit]);
				}
				$param['erreur_ajout']=true;
			}
		}else{
			$param['erreur_ajout']=true;
		}
		
		// Redirection pour éviter les F5 ajoutant en boucle un produit
		$this->_helper->getHelper('Redirector')->gotoSimple('continuer', 'panier', 'default', $param);
	}
	
	/**
	 * Demande après un ajout si on veut commander ou continuer à acheter
	 * @return: void
	 **/
	public function continuerAction(){
		
		// on désactive le menu de gauche
		$this->disabledMenuLeft();
		
		// on ajoute un fichier css
		$this->ajoutCss('panier_continuer.css');
		
		// s'il y a eu une erreur lors de l'ajout
		if($this->getRequest()->getParam("erreur_ajout")){
			$this->view->assign("erreur_ajout", $this->getRequest()->getParam("erreur_ajout"));
		}elseif($this->getRequest()->getParam("produit_ajouter")){
			// si l'ajout c'est bien passé
			$id_produit = intval($this->getRequest()->getParam("produit_ajouter"));
			$tproduit = new Application_Model_TProduit();
			$produit = $tproduit->infoProduit($id_produit);
			$this->view->assign("produit_ajouter", $produit[$id_produit]["nom_produit"]);
		}elseif($this->getRequest()->getParam("non_dispo_stock")){
			// si l'ajout c'est bien passé
			$this->view->assign("non_dispo_stock", $this->getRequest()->getParam("non_dispo_stock"));
		}else{
			// si on vient pas de l'ajout d'un produit, on dit qu'il y a eu une erreur
			$this->view->assign("erreur_ajout", true);
		}
	}
	
	/**
	 * Action permettant d'afficher le panier
	 * @return: void
	 **/
	public function afficherAction(){

		$this->ajoutCss("panier.css");
		
		// on désactive le menu de gauche
		$this->disabledMenuLeft();
		
		$modifs = $this->verifPanier();
		$this->view->assign('modifs', $modifs);
		if(count($this->panier->produits)>0){
			// on instancie TProduit pour récupérer toutes les informations sur les produits
			$tproduit = new Application_Model_TProduit();
			$info_produits = $tproduit->infoProduit(array_keys($this->panier->produits));
			foreach($info_produits as $id_produit => $produit){
				$info_produits[$id_produit]['nb_produits_commande'] = $this->panier->produits[$id_produit]['nb_produits_commande'];
			}
			
			// S'il y a des messages d'erreur après une modification du panier
			if($this->getRequest()->getParam('error_modif')){
				$error_modif = $this->getRequest()->getParam('error_modif');
			}else{
				$error_modif = array();
			}
			
			$url = $this->view->baseUrl('panier/modifier');
			
			// on instancie le formulaire d'affichage du panier
			$form_liste_panier = new Application_Form_ListePanier(array('produits'=>$info_produits, 'error_modif'=>$error_modif, 'url'=>$url));
			$this->view->assign('form_liste_panier',$form_liste_panier);
			$this->view->assign('prix_total',$this->prixProduitsPanier());
			$this->view->assign('nb_total',$this->nbProduitsPanier());
			
		}else{
			$this->view->assign('panier_vide', true);
		}
	}
	
	/**
	 * Action pour vider le panier
	 * @return: void
	 **/
	public function viderAction(){
		$this->viderPanier();
		// on redirige vers le panier
		$this->_helper->getHelper('Redirector')->gotoSimple('afficher', 'panier');
	}
	
	/**
	 * Action permettant de modifier le panier
	 * @return: void
	 **/
	public function modifierAction(){
		// On récupère les modifications du panier
		$panier_modifie = $this->getRequest()->getParam('produits');
		
		// Tableau pour les éventuelles erreur lors de la modif
		$param = array('error_modif'=>array());
		
		// on récupère tous les id du panier
		$id_panier = array_keys($this->panier->produits);
		
		// On récupère les informations de la bdd pour les id du panier
		$tproduit = new Application_Model_TProduit();
		$resultat = $tproduit->infoProduit($id_panier, true, false, true, true, array('id_produit', 'stock_produit'));
		
		// on parcourt le panier
		foreach($id_panier as $id_produit){
			// si l'id existe dans le panier modifié, qu'il est pas en rupture de stock et que le stock le permet, on modifie la valeur
			if(isset($panier_modifie[$id_produit]) // on vérifie que le produit est tjrs présent dans le panier modifié
					&& is_numeric($panier_modifie[$id_produit]) // on vérifie qu'on a une valeur numérique
					&& isset($resultat[$id_produit]) // on vérifie qu'il reste au moins 1 produit en stock
					&& intval($resultat[$id_produit]['stock_produit'])>=intval($panier_modifie[$id_produit]) // on vérifie qu'on dépasse pas le stock dispo
					&& intval($panier_modifie[$id_produit])!=0 // on vérifie qu'on a pas mit 0 pour supprimer le produit du panier
					&& intval($panier_modifie[$id_produit])==$panier_modifie[$id_produit]){ // on vérifie qu'on a bien un int
				$this->panier->produits[$id_produit]['nb_produits_commande']=intval($panier_modifie[$id_produit]);
			
			// si l'id existe mais qu'on a renseigné une chaine
			}elseif(isset($panier_modifie[$id_produit]) && !is_numeric($panier_modifie[$id_produit])){ 
				$param['error_modif'][$id_produit]="Veuillez entrer un chiffre et non une chaine de caractères.";
				
			}elseif(!isset($panier_modifie[$id_produit]) // si pas dans le panier modifie (pas remplit)
					|| intval($panier_modifie[$id_produit])==0 // si demande 0 fois ce produit
					|| !isset($resultat[$id_produit])){ // si plus dispo en stock
				unset($this->panier->produits[$id_produit]); // on supprime du panier
				
			}elseif(intval($resultat[$id_produit]['stock_produit'])<=intval($panier_modifie[$id_produit])){ // et qu'on demande plus que le stock
				// on va mettre la maximum en stock
				$this->panier->produits[$id_produit]['nb_produits_commande']=$resultat[$id_produit]['stock_produit'];
				
			}elseif(intval($panier_modifie[$id_produit])!=$panier_modifie[$id_produit]){
				$param['error_modif'][$id_produit]="Veuillez entrer un chiffre sans virgule.";
			}
		}
		// on redirige vers le panier
		$this->_helper->getHelper('Redirector')->gotoSimple('afficher', 'panier', 'default', $param);
		
	}
	
	/**
	 * Action demandant les informations sur le client avant de valider la commande
	 * @return: void
	 **/
	public function validerAction(){

		$this->ajoutCss("informations_client.css");
		
		// on désactive le menu de gauche
		$this->disabledMenuLeft();
		
		// on récupère le namespace contenant les informations sur le client
		$info_client = new Zend_Session_Namespace('info_client');
		$info_client->setExpirationSeconds(3600);
		// si on a déjà toutes les informations et qu'on ne demande pas à les modifier, on est rediriger
		if(isset($info_client->infos)
				&& isset($info_client->infos['nom'])
				&& isset($info_client->infos['prenom'])
				&& isset($info_client->infos['mail'])
				&& !$this->getRequest()->getParam('modifier')){
			$this->_helper->getHelper('Redirector')->gotoSimple('commander', 'panier');
		}
		
		// on récupère les données du formulaire
		$data_post = $this->getRequest()->getPost();
		
		// on prépare l'url pour l'action du formulaire
		if($this->getRequest()->getParam('modifier')){
			$params['url'] = $this->view->baseUrl('panier/valider')."?modifier=true";
		}else{
			$params['url'] = $this->view->baseUrl('panier/valider');
		}
		// on masque les erreurs si c'est la premiere fois qu'on arrive sur la page
		if(!$data_post){
			$params['show_error'] = true;
			if(isset($info_client->infos)){
				$data_post=$info_client->infos;
			}
		}
		if($this->getRequest()->getParam('modifier')){
			$params['text_submit'] = "Modifier";
		}else{
			$params['text_submit'] = "Continuer";
		}
		
		// on instancie le formulaire
		$form_informations_client = new Application_Form_InformationsClient($params);
		
		// on formate et vérifie que les données sont correctes
		$validation = $form_informations_client->isValid($data_post);
		
		if($this->getRequest()->getParam('modifier')){
			$this->view->assign('modifier',$this->getRequest()->getParam('modifier'));
		}
		
		// les données sont incorrectes, on réaffiche le formulaire
		if(!$validation){
			$this->view->assign('form_informations_client', $form_informations_client);
		}elseif($validation && $this->getRequest()->getParam('modifier')){
			$info_client->infos=$data_post;
			$this->view->assign('form_informations_client', $form_informations_client);
		}elseif($validation){
			$info_client->infos=$data_post;
			$this->_helper->getHelper('Redirector')->gotoSimple('commander', 'panier');
		}
	}
	
	
	/**
	 * Action permettant de passer la commande
	 * @return: void
	 **/
	public function commanderAction(){
		$this->ajoutCss('commander_panier.css');
		$this->disabledMenuLeft();
		
		// si le panier est vide, on redirige vers l'affichage du panier
		if(empty($this->panier->produits)){
			$this->_helper->getHelper('Redirector')->gotoSimple('afficher', 'panier');
		}
		// on récupère le namespace contenant les informations sur le client
		$info_client = new Zend_Session_Namespace('info_client');
		$info_client->setExpirationSeconds(3600);
		// Si les informations sont non renseignées, on redirige vers la demande d'information
		if(!isset($info_client->infos)){
			$this->_helper->getHelper('Redirector')->gotoSimple('valider', 'panier');
		}
		
		// On supprime les produits commandés en bdd
		$param_req = array();
		foreach($this->panier->produits as $id_produit=>$produit){
			$param_req[$id_produit] = $produit['nb_produits_commande'];
		}
		// on supprime en bdd les produits en bdd
		$tproduit = new Application_Model_TProduit();
		$suppression_bdd = $tproduit->validationCommande($param_req);
		
		// il y a bien eu des suppressions en bdd
		if(!empty($suppression_bdd)){
			// on met à jour le panier pour ce qui a été vraiment commandé
			foreach($this->panier->produits as $id_produit=>$produit){
				if(!array_key_exists($id_produit, $suppression_bdd)){
					unset($this->panier->produits[$id_produit]);
				}
			}
			//on ajoute le client
			$tclient = new Application_Model_TClient();
			$id_client = $tclient->ajoutClient($info_client->infos['mail'],$this->prixProduitsPanier(),$info_client->infos['nom'],$info_client->infos['prenom']);
			
			// si l'ajout c'est bien passé
			if(!empty($id_client)){				
				// on ajoute la commande
				$tcommande = new Application_Model_TCommande();
				$id_commande = $tcommande->ajoutCommande($id_client, $this->prixProduitsPanier());
				
				// si l'ajout de la commande est ok
				if(!empty($id_commande)){
					// on ajoute la relation entre commande-produit
					$tproduitscommande = new Application_Model_TProduitsCommande();
					$validation_ajout_produit_commande = $tproduitscommande->ajoutCommande($id_commande, $suppression_bdd);
					
					// si toutes les insertions sont ok
					if($validation_ajout_produit_commande){
						// on récupère les informations sur les produits
						$liste_produits = $tproduit->infoProduit(array_keys($suppression_bdd));
						
						// on envoie un mail au client et un au commerçant
						//on prépare le contenu du mail
						$content_mail = '<html>';
						$content_mail .= '<head></head><body>';
						$content_mail .= '<h2>Votre commande du '.date('Y-m-d').' est bien enregistrée.</h2>';
						$content_mail .= '<h3>Votre commande a un montant total de : '.$this->prixProduitsPanier().'&euro;</h3>';
						$content_mail .= '<div>Vos produits commandés :</div>';
						foreach($liste_produits as $id_produit=>$produit){
							$content_mail .= "<div>".$produit['nom_produit']." pour une quantité de ".$suppression_bdd[$id_produit]."</div>";
						}
						$content_mail .= '</body></html>';
						
						$mail = new Zend_Mail();
						$mail->setEncodingOfHeaders('quoted-printable');
						$mail->setFrom('c.boucautjj@laposte.net', 'De tout et de rien');
						$mail->addTo($info_client->infos['mail'], strtoupper($info_client->infos['nom'])." ".ucfirst($info_client->infos['prenom']));
						$mail->addTo('c.boucautjj@laposte.net');
						$mail->setSubject("Votre commande du ".date('Y-m-d')." vient d'être enregistrée.");
						$mail->setBodyHtml(utf8_decode($content_mail));		
						
						try{
							$mail->send();
							$this->view->assign('mail',true);
						}catch(Exception $e){
							$this->view->assign('error',true);
							echo "<h1>Erreur lors de l'envoie du mail ! Veuillez contacter l'administrateur !</h1>";
							echo "<pre>";
							var_dump($e);
							echo "</pre>";
							exit;
						}
					}else{
						$this->view->assign('error',true);
					}
				}else{
					$this->view->assign('error',true);
				}
				
			}else{
				$this->view->assign('error',true);
			}
			$this->viderPanier();
		}else{
			$this->view->assign('error',true);
		}

	}
	
	
	/**
	 * Permet de savoir le nombre de produits dans le panier. Si rien n'est précisé en argument, c'est le nom total de produits qui est renvoyé
	 * @param: int $cle
	 * @return: int
	 **/
	public function nbProduitsPanier($cle=null){
		$nb = 0;
		// total de tous les produits
		if($cle==null){
			foreach($this->panier->produits as $produit){
				$nb = $nb + $produit['nb_produits_commande'];
			}
		}else{
			// total pour un produit
			$cle = intval($cle);
			$nb = $this->panier->produits[$cle]['nb_produits_commande'];
		}
		return $nb;
	}
	
	/**
	 * Permet de connaitre le prix total du panier ou bien le prix total pour un type de produit si l'id est précisé
	 * @param: int $cle
	 * @return: float
	 **/
	public function prixProduitsPanier($cle=null){		
		$prix = 0;
		// prix total de tous les produits
		if($cle==null){
			// on instancie TProduit pour récupérer toutes les informations sur les produits
			$tproduit = new Application_Model_TProduit();
			$info_produits = $tproduit->infoProduit(array_keys($this->panier->produits));
			foreach($this->panier->produits as $id_produit=>$produit){
				$prix = $prix + ($produit['nb_produits_commande']*$info_produits[$id_produit]['prix_produit']);
			}
		}else{
			// total pour un produit
			$cle = intval($cle);
			// on instancie TProduit pour récupérer toutes les informations sur les produits
			$tproduit = new Application_Model_TProduit($cle);
			$prix = $this->panier->produits[$cle]['nb_produits_commande']*$info_produits[$cle]['prix_produit'];
		}
		return $prix;
	}
	
	/**
	 * Permet de mettre à jour le panier en fonction de la bdd (s'il y a plus de stock on supprime le produits du panier)
	 * @return: array
	 **/
	public function verifPanier(){
		// permet de savoir s'il y a eu une modif
		$modifs=array('suppression'=>false, 'modification'=>false);
		
		// on récupère tous les id du panier
		$id_panier = array_keys($this->panier->produits);
		
		// On récupère les informations de la bdd pour les id du panier
		$tproduit = new Application_Model_TProduit();
		$resultat = $tproduit->infoProduit($id_panier, true, false, true, true, array('id_produit', 'stock_produit'));
		
		// on vérifie l'ensemble du panier
		foreach($this->panier->produits as $id_produit => $nb_produits_commande){
			// s'il existe pas c'est que le stock est a présent a 0 ou qu'il n'est plus visible dans le catalogue
			if(!isset($resultat[$id_produit])){
				unset($this->panier->produits[$id_produit]);
				$modifs['suppression']=true;
			// si le stock a diminuer, on met a jour le panier
			}elseif($this->panier->produits[$id_produit]['nb_produits_commande']>$resultat[$id_produit]['stock_produit']){
				$this->panier->produits[$id_produit]['nb_produits_commande']=$resultat[$id_produit]['stock_produit'];
				$modifs['modification']=true;
			}
		}
		return $modifs;
	}
	
	/**
	 * Permet de vider le panier
	 * @return: void 
	 **/
	public function viderPanier(){
		$this->panier->produits = array();
	}
}