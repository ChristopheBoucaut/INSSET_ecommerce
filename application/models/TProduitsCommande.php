<?php

/**
 * Model représentant la table produits_commande
 * @class: Application_Model_TProduitsCommande
 * @file: TProduitsCommande.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 24 nov. 2012
 * - Version initiale
 *
 **/

class Application_Model_TProduitsCommande extends Zend_Db_Table_Abstract{
	/**
	 * Contient le nom de la table
	 * @var: string $_name
	 **/
	protected $_name = "produits_commande";
	
	/**
	 * Contient les noms des clés primaires
	 * @var: array $_primary
	 **/
	protected $_primary = array("id_produit", "id_commande");
	
	/**
	 * Contient les références des clés étrangères aux autres tables
	 * @var: array $_referenceMap
	**/
	protected $_referenceMap = array('produit' =>
			array('columns' => 'id_produit',
					'refTableClass' => 'produit',
					'refColumns' => 'id_produit'),
				
									'commande' =>
			array('columns' => 'id_commande',
					'refTableClass' => 'commande',
					'refColumns' => 'id_commande'));
	
	/**
	 * Permet d'ajouter les relations entre produits et commandes
	 * @param: int $id_commande
	 * @param: array $produits
	 * @return: boolean
	 **/
	 public function ajoutCommande($id_commande,$produits){
	 	$insert=0;
		if($id_commande!=intval($id_commande) || !is_array($produits)){
			return false;
		}
		
		foreach($produits as $id_produit=>$quantite){
			$new_commande_produit = $this->createRow();
			$new_commande_produit->id_commande=$id_commande;
			$new_commande_produit->id_produit=$id_produit;
			$new_commande_produit->quantite=$quantite;
			$validation_commande = $new_commande_produit->save();
			if(!empty($validation_commande)){
				$insert++;
			}
		}
		if(count($produits)==$insert){
			return true;
		}
	}
	
}