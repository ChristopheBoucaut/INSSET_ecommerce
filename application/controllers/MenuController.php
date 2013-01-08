<?php

/**
 * Permet de gérer l'affichage du menu gauche du site
 * @class: MenuController
 * @file: MenuController.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 22 nov. 2012
 * - Version initiale
 *
 **/

class MenuController extends SurcoucheZend_Controller_Action{
	
	/**
	 * Permet d'afficher le menu d'admin ou public en fonction du controller demandé
	 * @return: void
	 **/
	public function afficherAction(){
		$type_menu;
		if($this->onAdmin()){
			$type_menu = "admin";
		}else{
			$type_menu = "public";
		}
		$this->view->assign('type_menu',$type_menu);
	}
	
	/**
	 * Permet d'afficher le menu d'admin
	 * @return: void
	 **/
	public function adminAction(){
		// on charge le fichier css pour le menu
		echo $this->view->headLink()->appendStylesheet($this->view->baseUrl()."/css/menu_admin.css");
	}
	
	/**
	 * Permet d'afficher le menu public
	 * @return: void
	 **/
	public function publicAction(){
		echo $this->view->headScript()->appendFile($this->view->baseUrl().'/js/tri_produits.js');
		
		// on charge le fichier css pour le menu
		echo $this->view->headLink()->appendStylesheet($this->view->baseUrl()."/css/menu_public.css");
		
		$url = $this->view->baseUrl("index/index");
		$form_tri = new Application_Form_TriProduits(array('url'=>$url));
		$this->view->assign('form_tri', $form_tri);
	}
}