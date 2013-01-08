<?php
/**
 * Formulaire affichant tout le panier
 * @class: Application_Form_ListePanier
 * @file: ListePanier.php
 *
 * @author: Christophe BOUCAUT
 * @version: 1.0
 *
 * @changelogs:
 * Rev 1.0 du 26 nov. 2012
 * - Version initiale
 *
 **/

class Application_Form_ListePanier extends Zend_Form {
	
	/**
	 * Contient la liste des produits du panier
	 * @var: array $produits
	 **/
	private $produits;
	
	/**
	 * Contient une liste des erreurs lors de la modification du panier
	 * @var: array $error_modif
	 **/
	private $error_modif;
	
	/**
	 * Contient l'url pour l'envoie du formulaire
	 * @var: string $url
	 **/
	private $url;
	
	/**
	 * Constructeur
	 * @param: array $params
	 * @param: array $options
	 * @return: Object
	 **/
	public function __construct($params, $options=null){
		// on ajoute la liste des produits du panier
		if(isset($params['produits'])){
			$this->produits = $params['produits'];
		}else{
			$this->produits = array();
		}
		
		// on ajoute les éventuelles erreurs en cas de modification du panier
		if(isset($params['error_modif'])){
			$this->error_modif = $params['error_modif'];
		}else{
			$this->error_modif = array();
		}
		
		if(isset($params['url'])){
			$this->url = $params['url'];
		}else{
			$this->url = "";
		}
		
		parent::__construct($options);
	}
	
	/**
	 * Permet d'initialiser le formulaire
	 * @return: void
	 **/
	public function init(){
		// Décorateur pour le formulaire en général
		$decorators_form = array(
				'FormElements',
				'Form'
		);
		
		// Parametrage du formulaire
		$this->setMethod('post');
		$this->setAction($this->url);
		$this->setAttrib('class', 'formulaire-affichage-panier');
		
		//Desactivation des décorateurs par défaut et ajout du notre
		$this->clearDecorators();
		$this->addDecorators($decorators_form);
		
		foreach($this->produits as $id_produit=>$produit){
			// Décorateur pour les inputs de login et mdp
			$decorators_input = array(
					array('ViewHelper'),
					array('Errors'),
					array('Label', array(
							'title'=>$produit['nom_produit'],
							'class'=>"label_input_produit",
							'separator'=>' <div class="prix-total-produit">( '.$produit['prix_produit']*$produit['nb_produits_commande'].'€ ) :</div>',
							'tag'=>'div'
					)),
					array('Description', array(
							'tag'=>"span",
							'class'=>"stock-produit"
					)),
					array('HtmlTag', array('tag'=>"div",
											'class'=>'div-non-submit'))
			);
			
			// Création de l'input et label pour chaque produit
			$input_produit = new Zend_Form_Element_Text(strval($id_produit));
			$input_produit->setBelongsTo("produits");
			$input_produit->setLabel(substr($produit['nom_produit'], 0, 40)."... - ".$produit['prix_produit'].'€/u');
			$input_produit->setValue($produit['nb_produits_commande']);
			$input_produit->setDescription("/".$produit['stock_produit']." produits en stock");
			$input_produit->setDecorators($decorators_input);
			$this->addElement($input_produit);
		}

		// Création du bouton d'envoie du formulaire
		$decorators_input_submit = array(
				array('ViewHelper'),
				array('Errors'),
				array('Description', array(
						'tag'=>"span",
						'class'=>"description-submit informations"
				)),
				array('HtmlTag', array('tag'=>"div"))
		);
		$input_submit_modifier = new Zend_Form_Element_Submit('Enregistrer');
		$input_submit_modifier->setDescription('Avant de passer votre commande, pensez à enregistrer les modifications.');
		$input_submit_modifier->setAttrib('class', 'submit');
		$input_submit_modifier->setDecorators($decorators_input_submit);
		
		// Ajout des éléments submit au formulaire
		$this->addElement($input_submit_modifier);
		
	}
	
}