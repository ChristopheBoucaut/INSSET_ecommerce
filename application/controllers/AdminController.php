<?php

/**
 * Controlleur gérant la partie administration du site
 * @class: AdminController
 * @file: AdminController.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 22 nov. 2012
 * - Version initiale
 *
 **/

class AdminController extends SurcoucheZend_Controller_Action
{

	/**
	 * Permet d'initialiser le controller
	 * @return: void
	 **/
	public function init()
	{
	}

	/**
	 * Action appelé par défaut si rien de précisé
	 * @return: void
	 **/
	public function indexAction()
	{
				
	}

	/**
	 * Action appelé pour la connexion
	 * @return: void
	 **/
	public function connexionAction(){
		// Si la connexion se passe bien et qu'on est en ajax on renverra du javascript avec l'url de redirection
		$ajax_redirection = array('url'=>$this->view->baseUrl('admin/commande').'?just_no_delivered=1');

		$ajax = $this->getRequest()->getParam('ajax');
		
		// On désactive l'affichage du menu de gauche
		$this->disabledMenuLeft();
		
		// On récupère les données de GET
		$data_get = $this->getRequest()->getQuery();
		
		// url pour l'action du formulaire
		$url = $this->view->baseUrl('admin/connexion');
		
		if(isset($data_get['need_action']) && isset($data_get['need_controller'])){
			$url .= "?need_action=".$data_get['need_action']."&need_controller=".$data_get['need_controller']."&need_connexion=1";
		}
		
		// instance du formulaire de connexion
		$form_connexion = new Application_Form_Connexion(array('url'=>$url));
			
		// On récupère instance de Zend_Auth
		$auth = Zend_Auth::getInstance();
			
		// Si on vient d'une redirection
		if(isset($data_get['need_connexion'])){
			$this->view->assign('need_connexion', true);
		}
			
		// Si on est déjà connecté, on est directement réenvoyé sur une page d'accueil
		if($auth->hasIdentity()){
			if($ajax=='1'){
				$this->_helper->layout->setLayout('ajax');
				$this->view->assign('ajax',$ajax_redirection);
				//exit;
			}else{
				$this->_helper->getHelper('Redirector')->gotoSimple('commande', 'admin','default',array('just_no_delivered'=>'1'));
			}
		}
		
		// affichage du formulaire lors de la première visite
		if(!$this->getRequest()->getPost()){
			$this->view->assign('form_connexion',$form_connexion);
		}else{
			// on récupère les données du formulaire
			$data = $this->getRequest()->getPost();
			
			// on formate et vérifie que les données sont correctes
			$validation = $form_connexion->isValid($data);
			
			// variable de test renseignant si la connexion à échouée ou non
			$test_connexion = false;
		
			// variable signalant si on doit afficher message erreur ou pas
			$error_connexion = false;
		
			// on effectue la connexion
			if($validation){
				$identifiant = trim((string)$data['login']);
				$mdp = trim((string)$data['mdp']);
				
				// Parametrage de l'adapteur
				$dbAdapter = new Zend_Auth_Adapter_DbTable(null, 'admin', 'login', 'mdp');
					
				// Chargement identifiant et mdp a tester
				$dbAdapter->setIdentity($identifiant);
				$dbAdapter->setCredential(md5($mdp));
					
				// Récupère l'authentification en passant en parametre l'adaptateur
				$resultat_connexion = $auth->authenticate($dbAdapter);
				
				// Si la connexion est réussie
				if($resultat_connexion->isValid()){
					$test_connexion = true;
		
					// Récupère l'id et login et mail
					$data = $dbAdapter->getResultRowObject(null, 'mdp');
		
					// Stocke id, login, mail dans zend_auth
					$auth->getStorage()->write($data);
		
					// Si l'utilisateur vient d'une autre page, on redirige une fois co a la page qu'il désirait
					if(isset($data_get['need_action']) && isset($data_get['need_controller'])){
						$this->_helper->getHelper('Redirector')->gotoSimple($data_get['need_action'], $data_get['need_controller']);
					}else{
						if($ajax=='1'){
							$this->_helper->layout->setLayout('ajax');
							var_dump($ajax_redirection);
							$this->view->assign('ajax',$ajax_redirection);
							//exit;
						}else{
							// si non, on le redirige vers une page par défaut
							$this->_helper->getHelper('Redirector')->gotoSimple('commande', 'admin','default',array('just_no_delivered'=>'1'));
						}
					}
				}else{
					$test_connexion = false;
				}
			}
		
			// Si la validation du formulaire a échouée ou si la connexion n'est pas bonne
			if(!$validation || !$test_connexion){
				$error_connexion = true;
				$form_connexion->populate($data);
				$this->view->assign('form_connexion',$form_connexion);
			}
		
			// permet d'afficher un message d'erreur
			$this->view->assign('error_connexion', $error_connexion);
		}
		
		if($ajax=='1'){
			// on charge le fichier css pour la connexion si appelée en ajax
			echo $this->view->headLink()->appendStylesheet($this->view->baseUrl()."/css/connexion_ajax.css");
		}
			
	}
		
	/**
	 * Permet de déconnecter l'utilisateur
	 * @return: void
	 **/
	public function deconnexionAction(){
		// Instanciation de Zend_Auth
		$auth = Zend_Auth::getInstance();

		// Supprime la connexion de l'utilisateur
		$auth->clearIdentity();

		// Redirige à la page de connexion
		$this->_helper->getHelper('Redirector')->gotoSimple('index', 'index');
	}

	/**
	 * Permet d'afficher un bouton de déconnexion
	 * @return: void
	 **/
	public function boutondecoAction(){
			
	}

	/**
	 * Permet d'afficher la liste des produits avec un lien de modification
	 * @return: void
	 **/
	public function produitAction() {
		// on vient de supprimer un produit
		if($this->getRequest()->getParam('test_suppression')==1){
			$this->view->assign('validation_suppression',true);
		}elseif($this->getRequest()->getParam('test_suppression')!=null){
			$this->view->assign('error_suppression',true);
		}
		
		$this->ajoutCss('liste_produits_admin.css');
			
		// on récupère la requete sql pour récupérer tous les produits
		$tproduit = new Application_Model_TProduit();
		$req_produit = $tproduit->infoProduit(null, true, true);
			
		// on utiliser la pagination pour afficher seulement un maximum de 10 par pages
		$liste_produit = Zend_Paginator::factory($req_produit);
		$liste_produit->setCurrentPageNumber($this->getRequest()->getParam("page"));
		$liste_produit->setItemCountPerPage(10);
		$liste_produit->setPageRange(3);
		$this->view->assign('produits', $liste_produit);
	}
	
	/**
	 * Permet de supprimer un produit
	 * @return: void
	 **/
	public function supprimerproduitAction(){
		$id_produit = $this->getRequest()->getParam('id_produit');
		$tproduit = new Application_Model_TProduit();
		$test_suppression = $tproduit->disabledProduit($id_produit);
		$this->_helper->getHelper('Redirector')->gotoSimple('produit', 'admin', 'default', array('test_suppression'=>$test_suppression));
	}
	
	/**
	 * Permet d'ajouter un produit
	 * @return: void
	 **/
	public function ajoutproduitAction(){
		$this->ajoutCss('ajout_produit.css');
		
		// Url pour l'action du formulaire
		$url=$this->view->baseUrl('admin/ajoutproduit');
		if($this->getRequest()->getParam('id_produit')){
			$url .= "?id_produit=".$this->getRequest()->getParam('id_produit');
		}
		
		// Parametre pour le formulaire
		$param_form = array('url'=>$url);
		
		//Instanciation du formulaire
		$form_ajout_produit = new Application_Form_AjoutProduit($param_form);
		
		// Instancation du model représentant les produits pour insérer/modifier/récupérer info sur produits
		$tproduit = new Application_Model_TProduit();
		
		// On affiche le formulaire si on vient pour la première fois
		if(!$this->getRequest()->getPost() && !$this->getRequest()->getParam('id_produit')){
			$this->view->assign('form_ajout_produit', $form_ajout_produit);
		}else{
			$id_produit_modif = $this->getRequest()->getParam('id_produit');
			// si on veut modifier un produit et qu'on a pas d'informations a passer on pré-remplit
			if(!$this->getRequest()->getPost() && $this->getRequest()->getParam('id_produit')){
				$data_post = $tproduit->infoProduit($id_produit_modif)[$id_produit_modif];
			}else{
				// si on vient d'envoyer des informations et qu'on veut pas modifier
				$data_post = $this->getRequest()->getPost();
			}
			
			// on formate et vérifie que les données sont correctes
			$validation = $form_ajout_produit->isValid($data_post);
			
			// si les données envoyées par le formulaire sont valides
			if($validation){
				$nom_produit=$data_post['nom_produit'];
				$description_produit=$data_post['description_produit'];
				$prix_produit=$data_post['prix_produit'];
				$stock_produit=$data_post['stock_produit'];
				
				// on veut modifier et on a eu acces au formulaire pour modifier
				if($this->getRequest()->getPost() && $this->getRequest()->getParam('id_produit')){
					$validation_ajout = $tproduit->ajoutProduit($nom_produit, $description_produit, $prix_produit, $stock_produit,$id_produit_modif);
				}elseif($this->getRequest()->getPost()){
					// on fait juste un nouveau produit
					$validation_ajout = $tproduit->ajoutProduit($nom_produit, $description_produit, $prix_produit, $stock_produit);
				}else{
					// on vient d'arriver pour modifier
					$validation_ajout="modification";
				}
				
				// validation ok et on ne vient pas tout juste d'arriver sur la page pour modifier
				if($validation_ajout && $validation_ajout!='modification'){
					$this->_helper->getHelper('Redirector')->gotoSimple('produit', 'admin', 'default', array('validation_ajout'=>true));
				}else{
					// on réaffiche le formulaire pré-remplit
					$this->view->assign('form_ajout_produit', $form_ajout_produit);
					if($validation_ajout!='modification'){
						// erreur ajout
						$this->view->assign('validation_ajout', true);
					}
				}
			}else{
				$this->view->assign('form_ajout_produit', $form_ajout_produit);
			}
		}
	}
	
	/**
	 * Action permettant l'affichage de la liste des clients
	 * @return: void
	 **/
	public function clientAction(){
		$this->ajoutCss('liste_client_admin.css');
		
		// on récupère la requete sql pour récupérer tous les clients
		$tclient = new Application_Model_TClient();
		$req_client = $tclient->infoClient(null, false, true);
		
		// on utiliser la pagination pour afficher seulement un maximum de 4 par pages
		$liste_clients = Zend_Paginator::factory($req_client);
		$liste_clients->setCurrentPageNumber($this->getRequest()->getParam("page"));
		$liste_clients->setItemCountPerPage(4);
		$liste_clients->setPageRange(3);
		$this->view->assign('clients', $liste_clients);
	}
	
	/**
	 * Action permettant l'affichage des commandes
	 * @return: void
	 **/
	public function commandeAction(){
		// tableau pour le tri de la requete
		$array_tri = array();
		
		// on ajoute le css
		$this->ajoutCss('liste_commande_admin.css');
		// on ajoute le js pour les info-bulles
		$this->ajoutJs('info_bulle.js');
		
		// on prépare les urls pour les filtres
		$url_base = $this->view->baseUrl('admin/commande');
		
		// on filtre sur le client
		if($this->getRequest()->getParam('filtre_client')!=null){
			if($this->getRequest()->getParam('filtre_client')=="0"){
				$this->view->assign('filtre_client_css', 'asc');
				$this->view->assign('url_client', $url_base.'?filtre_client=1');
				$array_tri['client']=0;
			}else{
				$this->view->assign('filtre_client_css', 'desc');
				$this->view->assign('url_client', $url_base.'?filtre_client=0');
				$array_tri['client']=1;
			}
		}else{
			$this->view->assign('url_client', $url_base.'?filtre_client=0');
		}
			
		// on filtre sur la date
		if($this->getRequest()->getParam('filtre_date')!=null){
			if($this->getRequest()->getParam('filtre_date')=="0"){
				$this->view->assign('filtre_date_css', 'asc');
				$this->view->assign('url_date', $url_base.'?filtre_date=1');
				$array_tri['date']=0;
			}else{
				$this->view->assign('filtre_date_css', 'desc');
				$this->view->assign('url_date', $url_base.'?filtre_date=0');
				$array_tri['date']=1;
			}
		}else{
			$this->view->assign('url_date', $url_base.'?filtre_date=0');
		}
			
		// on filtre sur la livraison
		if($this->getRequest()->getParam('filtre_etat')!=null){
			if($this->getRequest()->getParam('filtre_etat')=="0"){
				$this->view->assign('filtre_etat_css', 'asc');
				$this->view->assign('url_etat', $url_base.'?filtre_etat=1');
				$array_tri['etat']=0;
			}else{
				$this->view->assign('filtre_etat_css', 'desc');
				$this->view->assign('url_etat', $url_base.'?filtre_etat=0');
				$array_tri['etat']=1;
			}
		}else{
			$this->view->assign('url_etat', $url_base.'?filtre_etat=0');
		}
		
		// on récupère la requete sql pour récupérer toutes les commandes
		$tcommande = new Application_Model_TCommande();
		if($this->getRequest()->getParam('just_no_delivered')){
			$req_commande = $tcommande->infoCommande(null, true, true);
		}else{
			$req_commande = $tcommande->infoCommande(null, false, true,false,$array_tri);
		}
		
		// on utiliser la pagination pour afficher seulement un maximum de 10 par pages
		$liste_commandes = Zend_Paginator::factory($req_commande);
		$liste_commandes->setCurrentPageNumber($this->getRequest()->getParam("page"));
		$liste_commandes->setItemCountPerPage(10);
		$liste_commandes->setPageRange(3);
		$this->view->assign('commandes', $liste_commandes);
	}
	
	/**
	 * Action permettant d'envoyer une commande
	 * @return: void
	 **/
	public function envoyerAction(){
		// si on a bien un id de passé pour la commande
		if($this->getRequest()->getParam('id_commande')){
			$id_commande = $this->getRequest()->getParam('id_commande');
			// on modifie la bdd pour que la commande passe comme livrée
			$tcommande = new Application_Model_TCommande();
			$validation = $tcommande->envoieCommande($id_commande);
			
			// si la modif est ok en bdd
			if($validation){
				// on va récupérer l'adresse mail du client
				$client = $tcommande->infoCommande($id_commande,false,false,true);
				if(is_array($client) && !empty($client)){
					$client = $client[$id_commande];
					// envoie mail client
					
					$mail = new Zend_Mail();
					$mail->setEncodingOfHeaders('quoted-printable');
					$mail->setFrom('c.boucautjj@laposte.net', 'De tout et de rien');
					$mail->addTo($client['mail_client'], strtoupper($client['nom_client'])." ".ucfirst($client['prenom_client']));
					$mail->setSubject("Votre commande du ".$client['date_commande']." vient d'être envoyée.");
					$mail->setBodyHtml(utf8_decode('<html><head></head><body><h2>Bonjour '.strtoupper($client['nom_client'])." ".ucfirst($client['prenom_client']).'</h2><div>Votre commande sur le site De tout et de rien du '.$client['date_commande'].' a été envoyée.<br/>Vous devriez la recevoir sous peu de temps.</div><div>Merci d\'avoir acheté chez nous :).</div></body></html>'));
					
					try{
						$mail->send();
					}catch(Exception $e){
						echo "<h1>Erreur lors de l'envoie du mail ! Veuillez contacter l'administrateur !</h1>";
						echo "<pre>";
						var_dump($e);
						echo "</pre>";
						exit;
					}
				}
				
			}
			
			// on redirige vers la page des commandes
			$this->_helper->getHelper('Redirector')->gotoSimple('commande', 'admin','default',array());
		}else{
			$this->_helper->getHelper('Redirector')->gotoSimple('commande', 'admin','default',array());
		}
	}
}

