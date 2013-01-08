<?php

/**
 * Model représentant la table commande
 * @class: Application_Model_TCommande
 * @file: TCommande.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 24 nov. 2012
 * - Version initiale
 *
 **/

class Application_Model_TCommande extends Zend_Db_Table_Abstract{
	/**
	 * Contient le nom de la table
	 * @var: string $_name
	 **/
	protected $_name = "commande";
	
	/**
	 * Contient le nom de la clé primaire
	 * @var: string $_primary
	 **/
	protected $_primary = "id_commande";
	
	/**
	 * Contient les références des clés étrangères aux autres tables
	 * @var: array $_referenceMap
	**/
	protected $_referenceMap = array('id_client' =>
			array('columns' => 'id_client',
					'refTableClass' => 'client',
					'refColumns' => 'id_client'));
	
	/**
	 * Permet de récupérer les informations sur les commandes
	 * @param: int|array $id_commande
	 * @param: boolean $just_no_delivered
	 * @param: boolean $just_request
	 * @param: boolean $format_array
	 * @param: array $array_tri
	 * @param: boolean $info_client
	 * @return: array|object
	 **/
	public function infoCommande($id_commande=null, $just_no_delivered=false,
			$just_request=false, $format_array=false, $array_tri=null, $info_client=true){
		// on prépare la requete
		$req=$this->select()->from('commande');
		
		// on vérifie si on veut une liste de commande ou tous
		if($id_commande!=null && !is_array($id_commande)){
			$id_commande = array(intval($id_commande));
		}elseif($id_commande!=null && is_array($id_commande)){
			$temp = array();
			foreach($id_commande as $val){
				$temp[] = intval($val);
			}
			$id_commande=$temp;
		}
		
		// Si on a besoin des informations sur le client, on effectuera la jointure
		if($info_client){
			$req->setIntegrityCheck(false)
			->join('client', 'commande.id_client=client.id_client');
		}
		
		// on ajoute le where à la requete
		if($id_commande!=null){
			$req->where('id_commande IN (?)', $id_commande);
		}
		
		// Si on veut juste les commandes non livrées
		if($just_no_delivered){
			$req->where('etat_livraison=?',0);
		}
		
		// Si on doit effectuer un orderby
		if($array_tri!=null && !empty($array_tri)){
			$param_order = array();
			foreach($array_tri as $colonne_tri => $ordre){
				if($colonne_tri=='client'){
					$tri = "client.nom_client";
				}elseif($colonne_tri=='date'){
					$tri = "commande.date_commande";
				}else{
					$tri = "commande.etat_livraison";
				}
				if($ordre===0){
					$param_order[] = $tri.' ASC';
				}else{
					$param_order[] = $tri.' DESC';
				}
			}
			$req = $req->order($param_order);
		}
		// si $just_request, on renverra la requete sql sans l'exécuter si false un tableau avec le résultat de la requete 
		if(!$just_request){
			//Recupération des résultat de la requete
			$commandes_resultat = $this->fetchAll($req);
			
			// si true, on formate le résultat sous forme d'un tableau
			if($format_array){
				// tableau pour formater le résultat sous forme de tableau et non pas d'objet
				$liste_commandes = array();
				
				// formatage du résultat sous la forme d'un tableau
				foreach($commandes_resultat as $commande){
					$liste_commandes[$commande->id_commande] = array();
					foreach($commande as $cle=>$val){
						$liste_commandes[$commande->id_commande][$cle] = $val;
					}
				}
			
				return $liste_commandes;
			}else{
				return $commandes_resultat;
			}
		}else{
			return $req;
		}
		
	}
	
	/**
	 * Permet de passer une commande comme livrée
	 * @param: int id_produit
	 * @return: boolean
	 **/
	public function envoieCommande($id_commande){
		if(!empty($id_commande) && $id_commande == intval($id_commande)){
			$commande = $this->find($id_commande)->current();
			if($commande!=null && $commande->etat_livraison==0){
				$commande->etat_livraison = 1;
				$id_modifie = $commande->save();
				if($id_modifie == $id_commande){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Permet d'ajouter une commande en bdd
	 * @param: int $id_client
	 * @param: float $montant_total
	 * @return: boolean|int
	 **/
	public function ajoutCommande($id_client, $montant_total){
		if($id_client!=intval($id_client)){
			return false;
		}
		// on effectue l'INSERT et on renvoie l'id de l'INSERT
		$new_commande = $this->createRow();
		$new_commande->id_client = intval($id_client);
		$new_commande->date_commande = date('Y-m-d');
		$new_commande->montant_total = floatval($montant_total);
		$new_commande->etat_livraison = 0;
		
		return $new_commande->save();
	}
}