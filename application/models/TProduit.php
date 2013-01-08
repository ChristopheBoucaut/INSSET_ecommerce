<?php

/**
 * Model représentant la table produit
 * @class: Application_Model_TProduit
 * @file: TProduit.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 24 nov. 2012
 * - Version initiale
 *
 **/

class Application_Model_TProduit extends Zend_Db_Table_Abstract{
	/**
	 * Contient le nom de la table
	 * @var: string $_name
	 **/
	protected $_name = "produit";
	
	/**
	 * Contient le nom de la clé primaire
	 * @var: string $_primary
	 **/
	protected $_primary = "id_produit";
	
	/**
	 * Permet de récupérer les produits de la bdd ou un si l'id est précisé
	 * @param: int|array $id
	 * @param: boolean $just_visible
	 * @param: boolean $just_request
	 * @param: boolean $in_stock
	 * @param: boolean $format_array
	 * @param: array $array_select
	 * @param: array $array_tri
	 * @return: array|Object|string
	 **/
	public function infoProduit($id=null, $just_visible=true, $just_request = false, $in_stock=false,
								$format_array = true, $array_select=null, $array_tri=null){
		//Requete pour récupérer les infos sur le produit
		//si on veut un champ/des champs en particluier
		if($array_select != null){
			if(!in_array('id_produit', $array_select)){
				$array_select[]='id_produit';
			}
			$req = $this->select()->from('produit', $array_select);
		}else{
			$req = $this->select()->from('produit');
		}
		
		if($id!=null && !is_array($id)){
			$id = array(intval($id));
		}elseif($id!=null && is_array($id)){
			$temp = array();
			foreach($id as $val){
				$temp[] = intval($val);
			}
			$id=$temp;
		}
		// si on a pas précisé d'id c'est que l'on veut tous les produits
		if($id!=null){
			$req = $req->where("id_produit IN (?)", $id);
		}
		
		// si true => juste les produits pour le catalogue, false, tous les produits
		if($just_visible){
			$req = $req->where("visibilite_catalogue = ?", $just_visible);
		}
		
		// si true => juste les produits encore en stock
		if($in_stock){
			$req = $req->where("stock_produit >= ?", $just_visible);
		}
		
		if($array_tri!=null && !empty($array_tri)){
			$param_order = array();
			foreach($array_tri as $colonne_tri => $ordre){
				if($ordre===0){
					$param_order[] = $colonne_tri.' ASC';
				}else{
					$param_order[] = $colonne_tri.' DESC';
				}
			}
			$req = $req->order($param_order);
		}
		
		// si $just_request, on renverra la requete sql sans l'exécuter si false un tableau avec le résultat de la requete 
		if(!$just_request){
			//Recupération des résultat de la requete
			$produits_resultat = $this->fetchAll($req);
			
			// si true, on formate le résultat sous forme d'un tableau
			if($format_array){
				// tableau pour formater le résultat sous forme de tableau et non pas d'objet
				$liste_produits = array();
				
				// formatage du résultat sous la forme d'un tableau
				foreach($produits_resultat as $produit){
					$liste_produits[$produit->id_produit] = array();
					foreach($produit as $cle=>$val){
						$liste_produits[$produit->id_produit][$cle] = $val;
					}
				}
			
				return $liste_produits;
			}else{
				return $produits_resultat;
			}
		}else{
			return $req;
		}
			
	}
	
	/**
	 * Permet de supprimer un produit de la liste des produits a afficher
	 * @param: int $id_produit
	 * @return: boolean
	 **/
	public function disabledProduit($id_produit){
		// on a fournit un id vide
		if(empty($id_produit)){
			return false;
		}
		// on a pas fournit un entier
		if($id_produit != intval($id_produit)){
			return false;
		}
		
		$produit = $this->find($id_produit)->current();
		if($produit!=null){
			$produit->visibilite_catalogue = 0;
			$id_modifie = $produit->save();
			if($id_modifie == $id_produit){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
	}
	
	/**
	 * Permet d'ajouter un produit
	 * @param: String $nom_produit
	 * @param: String $description_produit
	 * @param: Float $prix_produit
	 * @param: int $stock_produit
	 * @param: int $id_produit
	 * @return: int|boolean
	 **/
	public function ajoutProduit($nom_produit, $description_produit,$prix_produit,$stock_produit, $id_produit=null){
		// on vérifie que les parametres correspondents au types désirés
		if(trim($nom_produit)==""
				|| trim($description_produit)==""
				|| $prix_produit != floatval($prix_produit)
				|| $prix_produit<0
				|| $stock_produit != intval($stock_produit)
				|| $stock_produit<0){
			return false;
		}
		
		if($id_produit!= null && $id_produit == intval($id_produit)){
			// on effectue l'UPDATE et on renvoie l'id de l'UPDATE
			$new_produit = $this->find($id_produit)->current();
		}else{
			// on effectue l'INSERT et on renvoie l'id de l'INSERT
			$new_produit = $this->createRow();
		}
		$new_produit->nom_produit = $nom_produit;
		$new_produit->description_produit = $description_produit;
		$new_produit->prix_produit = preg_replace('/,/','.',$prix_produit);
		$new_produit->stock_produit = $stock_produit;
		$new_produit->visibilite_catalogue = 1;
		return $new_produit->save();
	}
	
	/**
	 * Permet de mettre à jour le panier et la bdd lors de la validation d'une commande
	 * @param: array $panier
	 * @return: array
	 **/
	public function validationCommande($panier){
		if(!is_array($panier)){
			$panier=array();
		}
		$tab_return=array();
		if(!empty($panier)){
			// on récupère les informations sur tous les produits du panier
			$liste_id_produits = array_keys($panier);
			$liste_produits = $this->infoProduit($liste_id_produits, true, false,false,false,array('stock_produit'));
			
			// on parcourt tous les résultats pour les mettre à jour
			foreach($liste_produits as $produit){
				if(isset($panier[$produit->id_produit])){
					if($panier[$produit->id_produit]<=$produit->stock_produit){
						$produit->stock_produit = $produit->stock_produit - $panier[$produit->id_produit];
						$tab_return[$produit->id_produit]=$panier[$produit->id_produit];
					}elseif($panier[$produit->id_produit]>$produit->stock_produit && $produit->stock_produit!=0){
						$tab_return[$produit->id_produit]=$produit->stock_produit;
						$produit->stock_produit = 0;
					}
					$produit->save();
				}
			}
		}
		return $tab_return;
	}
}