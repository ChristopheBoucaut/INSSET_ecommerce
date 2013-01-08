<?php

/**
 * Model représentant la table client
 * @class: Application_Model_TClient
 * @file: TClient.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 24 nov. 2012
 * - Version initiale
 *
 **/

class Application_Model_TClient extends Zend_Db_Table_Abstract{
	/**
	 * Contient le nom de la table
	 * @var: string $_name
	 **/
	protected $_name = "client";
	
	/**
	 * Contient le noms de la clé primaire
	 * @var: string $_primary
	 **/
	protected $_primary = "id_client";
	
	/**
	 * Permet de récupérer les informations sur les clients
	 * @param: int|array $id_client
	 * @param: boolean $format_array
	 * @param: boolean $just_request
	 * @return: object|array
	 **/
	public function infoClient($id_client=null, $format_array=true, $just_request=false){
		// on prépare la requete
		$req = $this->select()->from('client');
		
		// on vérifie si on veut une liste de client ou tous
		if($id_client!=null && !is_array($id_client)){
			$id_client = array(intval($id_client));
		}elseif($id_client!=null && is_array($id_client)){
			$temp = array();
			foreach($id_client as $val){
				$temp[] = intval($val);
			}
			$id_client=$temp;
		}
		
		// on ajoute le where à la requete
		if($id_client!=null){
			$req->where('id_client IN (?)', $id_client);
		}
		
		// on veut les resultats et pas juste la requete
		if(!$just_request){
			//Recupération des résultat de la requete
			$clients_resultat = $this->fetchAll($req);
			
			// on veut le resultat sous forme d'un tableau
			if($format_array){
				
				// tableau pour formater le résultat sous forme de tableau et non pas d'objet
				$liste_client = array();
			
				// formatage du résultat sous la forme d'un tableau
				foreach($clients_resultat as $client){
					$liste_client[$client->id_client] = array();
					foreach($client as $cle=>$val){
						$liste_produits[$client->id_client][$cle] = $val;
					}
				}
					
				return $liste_produits;
			}else{
				return $clients_resultat;
			}
		}else{
			return $req;
		}
	}
	
	/**
	 * Permet d'ajouter un client
	 * @param: string $adresse_mail
	 * @param: float $prix_commande
	 * @param: string $nom_client
	 * @param: string $prenom_client
	 * @return: boolean|int
	 **/
	public function ajoutClient($adresse_mail,$prix_commande,$nom_client,$prenom_client){
		// on test qu'on a bien une adresse mail
		$validator_mail = new Zend_Validate_EmailAddress();
		$validation_mail = $validator_mail->isValid($adresse_mail);
		
		// si on a une adresse mail
		if($validation_mail){
			// on vérifie que le client n'existe pas encore
			$req = $this->select()->from('client')->where('mail_client=(?)',$adresse_mail);
			$liste_clients = $this->fetchAll($req);
			if($liste_clients->count()!=0){
				// on effectue l'UPDATE et on renvoie l'id de l'UPDATE
				$client = $liste_clients->current();
				$client->nom_client = $nom_client;
				$client->prenom_client = $prenom_client;
				$client->prix_total_commande = $client->prix_total_commande + floatval($prix_commande);
				$client->date_derniere_commande = date('Y-m-d');
				$tab = $client->save();
				if(is_array($tab)){
					return $tab['id_client'];
				}else{
					return $tab;
				}
			}else{
				// on effectue l'INSERT et on renvoie l'id de l'INSERT
				$new_client = $this->createRow();
				$new_client->nom_client = $nom_client;
				$new_client->prenom_client = $prenom_client;
				$new_client->mail_client = $adresse_mail;
				$new_client->prix_total_commande = floatval($prix_commande);
				$new_client->date_derniere_commande = date('Y-m-d');
				$tab = $new_client->save();
				if(is_array($tab)){
					return $tab['id_client'];
				}else{
					return $tab;
				}
				
			}
		}else{
			return false;
		}
	}
}