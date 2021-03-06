<?php
// +-------------------------------------------------+
// ? 2002-2005 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: entites.class.php,v 1.57 2017-07-10 12:17:02 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once ($class_path.'/coordonnees.class.php');
require_once ($class_path.'/exercices.class.php');
require_once ($class_path.'/rent/rent_accounts.class.php');
require_once ($include_path.'/misc.inc.php');
require_once ($include_path.'/isbn.inc.php');
global $pmb_indexation_lang;
if($pmb_indexation_lang) {
	require_once ($include_path.'/marc_tables/'.$pmb_indexation_lang.'/empty_words');
}

class entites{
	
	
	public $id_entite = 0;				//Identifiant de l'entit?	
	public $type_entite = 0;			//Type de l'entit? (0=fournisseur, 1=biblioth?que)
	public $num_bibli = 0;				//Identifiant de la biblioth?que si Fournisseur, 0 sinon.
	public $raison_sociale = '';
	public $commentaires = '';
	public $siret = '';				//Num?ro de Siret				
	public $naf = '';					//Code naf
	public $rcs = '';					//Code registre du commerce
	public $tva = '';					//Num?ro de TVA intracommunautaire
	public $num_cp_client = '';		//Num?ro de compte chez le fournisseur
	public $num_cp_compta = 0;			//Num?ro de compte comptable (4)
	public $site_web = '';				//Url du site web de l'entit?
	public $logo = '';					//Url du logo de l'entit?
	public $autorisations = '';		//Autorisations d'acc?s ? l'entit?
	public $num_frais = 0;				//Identifiant des frais 
	public $num_paiement = 0;			//Identifiant du mode de paiement
	public $index_entite = '';			//Champ de recherche fulltext 

	 
	//Constructeur.	 
	public function __construct($id_entite= 0) {
		
		if ($id_entite) {
			$this->id_entite = $id_entite;
			$this->load();
		}
	}	
	
	
	// charge une entit? ? partir de la base.
	public function load(){
	
		global $dbh;
		
		$q = "select * from entites where id_entite = '".$this->id_entite."' ";
		$r = pmb_mysql_query($q, $dbh) ;
		$obj = pmb_mysql_fetch_object($r);
		$this->type_entite = $obj->type_entite;
		$this->num_bibli = $obj->num_bibli;		
		$this->raison_sociale = $obj->raison_sociale;
		$this->commentaires = $obj->commentaires;
		$this->siret = $obj->siret;
		$this->naf = $obj->naf;
		$this->rcs = $obj->rcs;
		$this->tva = $obj->tva;
		$this->num_cp_client = $obj->num_cp_client;
		$this->num_cp_compta = $obj->num_cp_compta;
		$this->site_web = $obj->site_web;
		$this->logo = $obj->logo;
		$this->autorisations = $obj->autorisations;
		$this->num_frais = $obj->num_frais;
		$this->num_paiement = $obj->num_paiement;		

	}

	
	// enregistre une entit? en base.
	public function save(){
		
		global $dbh;

		if( $this->raison_sociale == '' ) die ("Erreur de cr?ation entit?s");

		//Nettoyage des valeurs en entr?e
		$this->raison_sociale = clean_string($this->raison_sociale);
		$this->commentaires = clean_string($this->commentaires);
		$this->siret = clean_string($this->siret);
		$this->naf = clean_string($this->naf);
		$this->rcs = clean_string($this->rcs);
		$this->tva = clean_string($this->tva);
		$this->num_cp_client = clean_string($this->num_cp_client);
		$this->num_cp_compta = clean_string($this->num_cp_compta);
		$this->site_web = clean_string($this->site_web);
		$this->logo = clean_string($this->logo);

		if($this->id_entite) {
	
			$q = "update entites set type_entite = '".$this->type_entite."', num_bibli = '".$this->num_bibli."', raison_sociale = '".$this->raison_sociale."', commentaires = '".$this->commentaires."', ";
			$q.= "siret = '".$this->siret."', naf = '".$this->naf."', rcs = '".$this->rcs."', tva = '".$this->tva."', num_cp_client = '".$this->num_cp_client."', ";
			$q.= "num_cp_compta = '".$this->num_cp_compta."', site_web = '".$this->site_web."', logo = '".$this->logo."', autorisations = '".$this->autorisations."', ";
			$q.= "num_frais = '".$this->num_frais."', num_paiement = '".$this->num_paiement."', ";
			$q.= "index_entite = ' ".strip_empty_words($this->raison_sociale)." '";
			$q.= "where id_entite = '".$this->id_entite."' ";
			pmb_mysql_query($q, $dbh);

		} else {

			$q = "insert into entites set type_entite = '".$this->type_entite."', num_bibli = '".$this->num_bibli."', raison_sociale = '".$this->raison_sociale."', commentaires = '".$this->commentaires."', ";
			$q.= "siret = '".$this->siret."', naf = '".$this->naf."', rcs = '".$this->rcs."', tva = '".$this->tva."', num_cp_client = '".$this->num_cp_client."', ";
			$q.= "num_cp_compta = '".$this->num_cp_compta."', site_web = '".$this->site_web."', logo = '".$this->logo."' , autorisations = '".$this->autorisations."', ";
			$q.= "num_frais = '".$this->num_frais."', num_paiement = '".$this->num_paiement."', ";
			$q.= "index_entite = ' ".strip_empty_words($this->raison_sociale)." '";
			pmb_mysql_query($q, $dbh);
			$this->id_entite = pmb_mysql_insert_id($dbh);
			
		}

	}


	//supprime une entit? de la base
	public function delete($id_entite= 0) {
		
		global $dbh;

		if(!$id_entite) $id_entite = $this->id_entite; 	

		$q = "delete from entites where id_entite = '".$id_entite."' ";
		pmb_mysql_query($q, $dbh);

		$q = "delete from coordonnees where num_entite = '".$id_entite."' ";
		pmb_mysql_query($q, $dbh);
				
		$q = "delete from offres_remises where num_fournisseur = '".$id_entite."' ";
		pmb_mysql_query($q, $dbh);
		
		$q = "update abts_abts set fournisseur='0' where num_fournisseur = '".$id_entite."' ";
		pmb_mysql_query($q, $dbh);
				
	}


	//v?rifie l'existence d'une entit? en base ? partir de son identifiant
	public static function exists($id_entite= 0) {
		
		global $dbh;

		$q = "SELECT count(1) from entites where id_entite = '".$id_entite."' ";
		$r = pmb_mysql_query($q, $dbh);
		return pmb_mysql_result($r, 0, 0);
				
	}

	//v?rifie l'existence d'une entit? en base ? partir de sa raison sociale
	public static function exists_rs($raison_sociale= 0, $numero_bibli=0, $id_entite = 0) {
		//Contrainte ? appliquer :
		/*
		 * type= 1 -> etablissement
		 * type = 0 -> fournisseur
		 * Pas de fournisseur avec la m?me raison sociale que l'?tablissement 
		 * Pas deux fournisseurs avec la m?me raison sociale dans un ?tablissement
		 */
		global $dbh;
		
		$q = "select count(1) from entites where raison_sociale = '".$raison_sociale."' and num_bibli='".$numero_bibli."'";
		
		if($id_entite !== 0){
			$q.=" and id_entite != '".$id_entite."'";
		}
		$r = pmb_mysql_query($q, $dbh);
		return pmb_mysql_result($r, 0, 0);
				
	}

	
	//optimization de la table entites
	public function optimize() {
		
		global $dbh;
		
		$opt = pmb_mysql_query('OPTIMIZE TABLE entites', $dbh);
		return $opt;
				
	}

	
	//Retourne une requete pour liste des bibliotheques 
	//si user!=0 la requete est limitee aux bibliotheques accessibles par celui-ci  
	public static function list_biblio($user=0) {
		
		$q = "select * from entites where type_entite = '1' ";
		if ($user) $q.= "and autorisations like('% ".$user." %') ";
		$q.= "order by raison_sociale ";
		return $q;
				
	}
	
	//Retourne la liste des fournisseurs dans un ResultSet
	public static function list_fournisseurs($id_bibli=0, $debut=0, $nb_per_page=0, $aq=0) {
		
		global $dbh;
		
		$restrict = "type_entite = '0' ";
		if ($id_bibli) {
			$restrict.= "and num_bibli = '".$id_bibli."' ";
		}
		if(!$aq) {
			$q = "select * from entites where ".$restrict;
			$q.= "order by raison_sociale ";
		} else {
			$members=$aq->get_query_members("entites","raison_sociale","index_entite","id_entite",$restrict);
			$q = "select *, ".$members["select"]." as pert from entites where ".$members["where"]." ";
			if ($restrict) {
				$q.= "and ".$members["restrict"]." ";
			}
			$q.= "order by pert desc ";
		}  
		if ($debut) {
			$q.="limit ".$debut ;
			if ($nb_per_page) $q.= ",".$nb_per_page;
		}
		if($nb_per_page && !$debut){
			$q.= "limit 0,".$nb_per_page;
		}
		$r = pmb_mysql_query($q, $dbh);
		return $r;				
	}


	//Compte le nb de fournisseurs pour une biblioth?que
	public static function getNbFournisseurs($id_bibli=0, $aq=0) {
		
		global $dbh;
		
		$restrict = '';
		if ($id_bibli) {
			$restrict.= "num_bibli = '".$id_bibli."' ";
		}
		
		if (!$aq) {
			$q = "select count(1) from entites where type_entite = '0' ";
			if ($restrict) $q.="and ".$restrict;
		} else {
			$q = $aq->get_query_count("entites","raison_sociale","index_entite", "id_entite", $restrict);
		}
		$r = pmb_mysql_query($q, $dbh);
		return pmb_mysql_result($r, 0, 0);
				
	}


	//Retourne la liste des offres de remises par type de produit pour un fournisseur dans un ResultSet
	public static function listOffres($id_fou=0) {
		
		global $dbh;
		
		$q = "select * from offres_remises, types_produits where num_fournisseur = '".$id_fou."' and id_produit = num_produit order by libelle ";
		$r = pmb_mysql_query($q, $dbh); 
		return $r;
				
	}


	//Retourne la liste des types de produits pour lesquels il n'y a pas d'offres pour un fournisseur (dans un ResultSet)
	public static function listNoOffres($id_fou=0) {
		
		global $dbh;
		
		$q = "select num_produit from offres_remises where num_fournisseur = '".$id_fou."' ";
		$r = pmb_mysql_query($q, $dbh);
		$c = pmb_mysql_num_rows($r);
		$a = array();
		while(($row = pmb_mysql_fetch_object($r))) {
			$a[] = "'".$row->num_produit."'";
		}
		$l = implode(" , ", $a );
		
		$q = "select id_produit, libelle from types_produits ";
		if ($c) $q.= "where id_produit not in (".$l.") order by libelle";
		$r = pmb_mysql_query($q, $dbh);
		return $r;
				
	}


	//Retourne la liste des actes d'un type pour une biblioth?que dans un ResultSet
	public static function listActes($id_bibli, $type_acte, $statut='-1', $debut=0, $nb_per_page=0, $aq=0, $user_input='', $tri='', $id_exercice=0) {
		
		global $dbh;
		
		if ($statut == '-1') {		
			$filtre = '';
		} elseif ($statut == 32) {
			$filtre = "and ((actes.statut & 32) = 32) ";
		} else {
			$filtre = "and ((actes.statut & 32) = 0) and ((actes.statut & ".$statut.") = '".$statut."') ";
		}
		
		if ($id_exercice) {
			$filtre .="and actes.num_exercice=".$id_exercice;
		}
		
		$order="";
		if(trim($tri)){			
			if(substr($tri,0,1)=="-"){
				$order=" ORDER BY ".substr($tri,1)." DESC ";
			}else{
				$order=" ORDER BY ".$tri." ";
			}
		}	
			
		if(!$aq) {
			$q = "SELECT date_ech_calc, raison_sociale, actes2.numero as num_acte_parent, actes.* 
					FROM (actes 
					LEFT JOIN (SELECT MIN((DATE_FORMAT(date_ech, '%Y%m%d'))) AS date_ech_calc, num_acte FROM lignes_actes WHERE (('2' & statut) = '0') GROUP BY num_acte) dl ON dl.num_acte=actes.id_acte)
					LEFT JOIN entites ON entites.id_entite=actes.num_fournisseur 
					LEFT JOIN liens_actes ON num_acte_lie=actes.id_acte 
					LEFT JOIN actes actes2 ON actes2.id_acte=liens_actes.num_acte 
					WHERE actes.num_entite = '".$id_bibli."' ";
			$q.= "AND actes.type_acte = '".$type_acte."' ".$filtre." ";
			if(trim($order)){
				$q.=$order;
			} else{
				$q.= "ORDER BY actes.numero DESC ";
			}
			$q.= "limit ".$debut ;
			if ($nb_per_page) $q.= ",".$nb_per_page;
			
		} else {
	
			$isbn = '';
			$t_codes = array();
			
			if ($user_input!=='') {
				if (isEAN($user_input)) {
					// la saisie est un EAN -> on tente de le formater en ISBN
					$isbn = EANtoISBN($user_input);
					// si ?chec, on prend l'EAN comme il vient
					if($isbn) {
						$t_codes[] = $isbn;
						$t_codes[] = formatISBN($isbn,10);
					}
				} elseif (isISBN($user_input)) {
					// si la saisie est un ISBN
					$isbn = formatISBN($user_input);
					if($isbn) { 
						$t_codes[] = $isbn ;
						$t_codes[] = formatISBN($isbn,13);
					}
				} elseif (isISSN($user_input)) {
					$t_codes[] = $user_input ;
				} 
			}
			
			if (count($t_codes)) {

				$q = "SELECT distinct(actes.id_acte), actes.*, date_ech_calc, actes2.numero as num_acte_parent, raison_sociale 
					FROM (actes left join lignes_actes on num_acte=id_acte 
					LEFT JOIN (SELECT MIN((DATE_FORMAT(date_ech, '%Y%m%d'))) AS date_ech_calc, num_acte FROM lignes_actes WHERE (('2' & statut) = '0') GROUP BY num_acte) dl ON dl.num_acte=actes.id_acte)
					LEFT JOIN entites ON entites.id_entite=actes.num_fournisseur 
					LEFT JOIN liens_actes ON num_acte_lie=actes.id_acte 
					LEFT JOIN actes actes2 ON actes2.id_acte=liens_actes.num_acte ";
				$q.= "WHERE ( actes.num_entite='".$id_bibli."' and actes.type_acte='".$type_acte."' ".$filtre." ) ";
				$q.= "and ('0' ";
				foreach ($t_codes as $v) {
					$q.= "or lignes_actes.code like '%".$v."%' ";
				}
				$q.=") ";
				if(trim($order)){
					$q.=$order;
				} else{
					$q.= "order by actes.date_ech asc, actes.numero asc";
				}
				$q.=" limit ".$debut.",".$nb_per_page." ";
				
			} else {

				$members_actes = $aq->get_query_members("actes","actes.numero","actes.index_acte", "actes.id_acte");
				$members_lignes = $aq->get_query_members("lignes_actes","lignes_actes.code","lignes_actes.index_ligne", "lignes_actes.id_ligne");
				$q = "select distinct(actes.id_acte), actes.*, date_ech_calc, actes2.numero as num_acte_parent, raison_sociale, max(".$members_actes["select"]."+".$members_lignes["select"].") as pert 
						from (actes left join lignes_actes on num_acte=id_acte 
						LEFT JOIN (SELECT MIN((DATE_FORMAT(date_ech, '%Y%m%d'))) AS date_ech_calc, num_acte FROM lignes_actes WHERE (('2' & statut) = '0') GROUP BY num_acte) dl ON dl.num_acte=actes.id_acte)
						LEFT JOIN entites ON entites.id_entite=actes.num_fournisseur 
						LEFT JOIN liens_actes ON num_acte_lie=actes.id_acte 
						LEFT JOIN actes actes2 ON actes2.id_acte=liens_actes.num_acte ";
				$q.= "where actes.num_entite='".$id_bibli."' and actes.type_acte='".$type_acte."' ".$filtre." ";
				$q.= "and (".$members_actes["where"]." or ".$members_lignes["where"].") ";
				$q.= "group by actes.id_acte ";
				if(trim($order)){
					$q.=$order;
				} else{
					$q.= "order by pert desc";
				}
				$q.=" limit ".$debut.",".$nb_per_page." ";
			}
		}  
		$r = pmb_mysql_query($q, $dbh);		
		return $r;				
	}


	//Compte le nb d'acte d'un type pour une biblioth?que
	public static function getNbActes($id_bibli, $type_acte, $statut='-1', $aq=0, $user_input='', $id_exercice=0) {
		
		global $dbh;
		
		if ($statut == '-1') {		
			$filtre = '';
		} elseif ($statut == 32) {
			$filtre = "and ((actes.statut & 32) = 32) ";
		} else {
			$filtre = "and ((actes.statut & 32) = 0) and ((actes.statut & ".$statut.") = '".$statut."') ";
		}
		
		if ($id_exercice) {
			$filtre .="and num_exercice=".$id_exercice;
		}

		
		if (!$aq) {
			$q = "select count(1) from actes where num_entite = '".$id_bibli."' ";
			$q.= "and type_acte = '".$type_acte."' ".$filtre." "; 
		} else {

			$isbn = '';
			$t_codes = array();
			
			if ($user_input!=='') {
				if (isEAN($user_input)) {
					// la saisie est un EAN -> on tente de le formater en ISBN
					$isbn = EANtoISBN($user_input);
					if($isbn) {
						$t_codes[] = $isbn;
						$t_codes[] = formatISBN($isbn,10);
					}
				} elseif (isISBN($user_input)) {
					// si la saisie est un ISBN
					$isbn = formatISBN($user_input);
					if($isbn) { 
						$t_codes[] = $isbn ;
						$t_codes[] = formatISBN($isbn,13);
					}
				} elseif (isISSN($user_input)) {
					$t_codes[] = $user_input ;
				}
			}
			
			if (count($t_codes)) {

				$q = "select count(distinct(id_acte)) from actes left join lignes_actes on num_acte=id_acte ";
				$q.= "where ( num_entite='".$id_bibli."' and type_acte='".$type_acte."' ".$filtre." ) ";
				$q.= "and ('0' ";
				foreach ($t_codes as $v) {
					$q.= "or code like '%".$v."%' ";
				}
				$q.=") ";
				
			} else {
			
				$members_actes = $aq->get_query_members("actes","numero","index_acte", "id_acte");
				$members_lignes = $aq->get_query_members("lignes_actes","code","index_ligne", "id_ligne");
				$q = "select count(distinct(id_acte)) from actes left join lignes_actes on num_acte=id_acte ";
				$q.= "where ( num_entite='".$id_bibli."' and type_acte='".$type_acte."' ".$filtre." ) ";
				$q.= "and (".$members_actes["where"]." or ".$members_lignes["where"].") ";
				
			}
		}
		$r = pmb_mysql_query($q, $dbh);
		return pmb_mysql_result($r, 0, 0); 
				
	}


	//Compte le nb de coordonn?es pour une entit?
	public static function count_coordonnees($id_entite=0) {
		
		global $dbh;
		$q = "select count(1) from coordonnees where num_entite = '".$id_entite."' ";
		$r = pmb_mysql_query($q, $dbh); 
		return pmb_mysql_result($r, 0, 0);
		
	}


	//Retourne un resultset contenant les coordonn?es d'une entit?
	//Si type_entite=1, retourne l'adresse principale (de facturation)
	//Si type_entite=2, retourne l'adresse de livraison 
	//Si type_entite=0, retourne les autres coordonn?es
	//Si type_entite=-1, retourne toutes les coordonn?es
	public static function get_coordonnees($id_entite=0, $type_coord=0, $debut=0, $nb_per_page=0) {
		
		global $dbh;
		//if (!$id_entite) $id_entite = $this->id_entite;
		$q = "select * from coordonnees where num_entite = '".$id_entite."' ";
		if($type_coord != '-1') $q.= "and type_coord = '".$type_coord."' "; 
		if ($debut) {
			$q.="limit ".$debut ;
			if($nb_per_page) $q.= ",".$nb_per_page;
		}
		$r = pmb_mysql_query($q, $dbh);
		return $r;
		
	}


	//Compte le nb d'exercices pour une entit?	
	public static function has_exercices($id_entite=0, $statut='-1') {
		
		global $dbh;
		$q = "select count(1) from exercices where num_entite = '".$id_entite."' ";
		if($statut != '-1') $q.= "and statut = '".$statut."' ";		
		$r = pmb_mysql_query($q, $dbh); 
		return pmb_mysql_result($r, 0, 0);
		
	}


	//Compte le nb de budgets pour une entit?	
	public static function has_budgets($id_entite=0) {
		
		global $dbh;
		$q = "select count(1) from budgets where num_entite = '".$id_entite."' ";		
		$r = pmb_mysql_query($q, $dbh); 
		return pmb_mysql_result($r, 0, 0);
		
	}
	

	//Retourne les budgets actifs pour une entit? sous forme de Resultset 
	public static function listBudgetsActifs($id_entite=0) {
		
		global $dbh;
		
		$q = "select id_budget, libelle from budgets where num_entite = '".$id_entite."' and statut = '1' ";		
		$r = pmb_mysql_query($q, $dbh); 
		return $r;
		
	}


	//Retourne un Resultset contenant les rubriques finales des budgets d'une entite en fonction des droits de l'utilisateur courant si per_user=TRUE 
	//modification de la recherche : on retourne les rubriques finales, mais on cherche dans toutes les rubriques
	public static function listRubriquesFinales($id_entite=0, $id_exer, $per_user=FALSE, $debut=0, $nb_per_page=0, $elt_query=''){
		
		global $dbh;
		
		//on cherche toutes les rubriques correspondant ? la recherche
		$q = "select rubriques.id_rubrique from budgets, rubriques ";
		$q.= "where budgets.statut = '1' and budgets.num_entite = '".$id_entite."'  and budgets.num_exercice = '".$id_exer."' and rubriques.num_budget = budgets.id_budget ";		
		if(trim($elt_query)){
			$q.="and rubriques.libelle like '".addslashes(str_replace('*','%',$elt_query))."' ";
		}
		$r = pmb_mysql_query($q, $dbh);

		//on liste toutes les rubriques finales correspondantes
		$array_rubriques_finales = array();
		if (pmb_mysql_num_rows($r)) {
			while ($row = pmb_mysql_fetch_object($r)) {
				$rub_finales = entites::findRubriquesFinales($row->id_rubrique);
				if (count($rub_finales)) {
					foreach ($rub_finales as $id_rub_finale) {
						if ((!count($array_rubriques_finales)) || (!in_array($id_rub_finale,$array_rubriques_finales))) {
							$array_rubriques_finales[] = $id_rub_finale;
						}
					}
				}
			}
		}

		//on retourne le recordset des rubriques finales
		$q = "select budgets.libelle as lib_bud, budgets.type_budget, budgets.montant_global, budgets.seuil_alerte, rubriques.* from budgets, rubriques left join rubriques as rubriques2 on rubriques.id_rubrique=rubriques2.num_parent ";
		$q.= "where budgets.statut = '1' and budgets.num_entite = '".$id_entite."'  and budgets.num_exercice = '".$id_exer."' and rubriques.num_budget = budgets.id_budget and rubriques2.num_parent is NULL ";
		$q.= "and rubriques.id_rubrique in (".implode(",",$array_rubriques_finales).") ";
		if($per_user) {

			//R?cup?ration de l'utilisateur
		 	$requete_user = "SELECT userid FROM users where username='".SESSlogin."' limit 1 ";
			$res_user = pmb_mysql_query($requete_user, $dbh);
			$row_user=pmb_mysql_fetch_row($res_user);
			$user_userid=$row_user[0];

		$q.= "and rubriques.autorisations like('% ".$user_userid." %') ";			
		}
		$q.= "order by budgets.libelle, rubriques.id_rubrique ";
		
		if ($debut) {
			$q.="limit ".$debut ;
			if($nb_per_page) $q.= ",".$nb_per_page;
		} else {
			if($nb_per_page) $q.= "limit ".$nb_per_page;
		}
		
		$r = pmb_mysql_query($q, $dbh); 
		return $r;
		
	}

	//Trouve de fa?on r?cursive toutes les rubriques finales d'une rubrique particuli?re
	public static function findRubriquesFinales($id_rubrique){
		$liste_rubriques = array();
		
		$q = "select id_rubrique from rubriques where num_parent = ".$id_rubrique;
		$r = pmb_mysql_query($q, $dbh);
		if (pmb_mysql_num_rows($r)) {
			while ($row = pmb_mysql_fetch_object($r)) {
				$rub_enfants = entites::findRubriquesFinales($row->id_rubrique);
				if (count($rub_enfants)) {
					$liste_rubriques = array_merge($liste_rubriques,$rub_enfants);
				}
			}
		} else {
			$liste_rubriques[] = $id_rubrique;
		}
		
		return $liste_rubriques;
	}


	//Retourne le nombre de rubriques finales des budgets actifs d'une entite en fonction des droits de l'utilisateur courant si per_user=TRUE
	//modification de la recherche : on cherche dans toutes les rubriques 	
	public static function countRubriquesFinales($id_entite=0, $id_exer, $per_user=FALSE, $elt_query=''){
		
		global $dbh;
		
		$id_entite += 0;
		
		//on cherche toutes les rubriques correspondant ? la recherche
		$q = "select rubriques.id_rubrique from budgets, rubriques ";
		$q.= "where budgets.statut = '1' and budgets.num_entite = '".$id_entite."'  and budgets.num_exercice = '".$id_exer."' and rubriques.num_budget = budgets.id_budget ";
		if(trim($elt_query)){
			$q.="and rubriques.libelle like '".addslashes(str_replace('*','%',$elt_query))."' ";
		}
		$r = pmb_mysql_query($q, $dbh);
		
		//on liste toutes les rubriques finales correspondantes
		$array_rubriques_finales = array();
		if (pmb_mysql_num_rows($r)) {
			while ($row = pmb_mysql_fetch_object($r)) {
				$rub_finales = entites::findRubriquesFinales($row->id_rubrique);
				if (count($rub_finales)) {
					foreach ($rub_finales as $id_rub_finale) {
						if ((!count($array_rubriques_finales)) || (!in_array($id_rub_finale,$array_rubriques_finales))) {
							$array_rubriques_finales[] = $id_rub_finale;
						}
					}
				}
			}
		}
		
		//on retourne le recordset des rubriques finales
		
			
		$q = "select count(1) from budgets, rubriques ";
		$q.= "where budgets.statut = '1' and budgets.num_entite = '".$id_entite."' and budgets.num_exercice = '".$id_exer."' and rubriques.num_budget = budgets.id_budget ";
		if($per_user) {

			//R?cup?ration de l'utilisateur
		 	$requete_user = "SELECT userid FROM users where username='".SESSlogin."' limit 1 ";
			$res_user = pmb_mysql_query($requete_user, $dbh);
			$row_user=pmb_mysql_fetch_row($res_user);
			$user_userid=$row_user[0];

		$q.= "and rubriques.autorisations like('% ".$user_userid." %') ";			
		}
		if (count($array_rubriques_finales)) {
			$q.= "and rubriques.id_rubrique in (".implode(",",$array_rubriques_finales).") ";
		} else {
			$q.= "and rubriques.id_rubrique = 0 "; //pas de rubrique trouv?e
		}

		$r = pmb_mysql_query($q, $dbh); 
		return pmb_mysql_result($r, 0, 0);
		
	}	
	
		
	//Retourne les exercices courants d' une entit?	
	public static function getCurrentExercices($id_entite=0) {
		$q = "select id_exercice, libelle, statut from exercices where num_entite = '".$id_entite."' and (statut &  '".STA_EXE_ACT."') = '".STA_EXE_ACT."' ";
		$q.= "order by statut desc ";
		return $q;		
	}
	
		
	//Compte le nb de suggestions pour une entit?
	public static function has_suggestions($id_entite=0) {
		
		global $dbh;
		$q = "select count(1) from suggestions where num_entite = '".$id_entite."' ";		
		$r = pmb_mysql_query($q, $dbh); 
		return pmb_mysql_result($r, 0, 0);
		
	}
	
	
	//Compte le nb d'actes pour une entit?
	public static function has_actes($id_entite=0,$type_entite=0) {
		
		global $dbh;
		if ($type_entite) {
			$q = "select count(1) from actes where num_entite = '".$id_entite."' ";
			$r = pmb_mysql_query($q, $dbh);
		} else {
			$q = "select count(1) from actes where num_fournisseur = '".$id_entite."' ";
			$r = pmb_mysql_query($q, $dbh);
		}
		return pmb_mysql_result($r, 0, 0);
		
	}


	//M?j des autorisations dans les rubriques lors de la m?j des autorisations dans les entit?s
	public function majAutorisations() {
		
		global $dbh;
				
			$q = "select id_budget from budgets where num_entite = '".$this->id_entite."' ";
			$r = pmb_mysql_query($q, $dbh);
			$nb = pmb_mysql_num_rows($r);
		
			if ($nb != '0') {			
				$liste= '';
				for ($i=0; $i<$nb; $i++) { 
					$row =pmb_mysql_fetch_row($r);
					$liste.= $row[0];
					if ($i<$nb-1) $liste.= ', ';
				}
			
			$q = "select id_rubrique, autorisations from rubriques where autorisations != '' and num_budget in (".$liste.") ";
			$r = pmb_mysql_query($q, $dbh); 
			$aut_entite = explode(' ',$this->autorisations);

			while(($row=pmb_mysql_fetch_object($r))) {
				
				$aut_rub = explode(' ',$row->autorisations);			
				$aut = array_intersect($aut_entite, $aut_rub);
				
				$q1 = "update rubriques set autorisations = '".' '.implode(' ',$aut).' '."' where id_rubrique = '".$row->id_rubrique."' ";
				pmb_mysql_query($q1, $dbh);
			}
		}
		
	}

	//Recuperation de l'etablissement session
	public static function getSessionBibliId() {
		global $deflt3bibli;
		if (!isset($_SESSION['id_bibli'])) $_SESSION['id_bibli'] = '';
		if (!$_SESSION['id_bibli'] && $deflt3bibli) {
			$_SESSION['id_bibli']=$deflt3bibli;
		}
		return $_SESSION['id_bibli'];
	}

	//Definition de l'etablissement session
	public static function setSessionBibliId($id_bibli) {
		$_SESSION['id_bibli']=$id_bibli;
		return;
	}
	
	
	//Retourne un selecteur html avec la liste des bibliotheques
	public static function getBibliHtmlSelect($user=FALSE, $selected=0, $sel_all=FALSE, $sel_attr=array()) {
		
		global $dbh,$msg,$charset;
		
		$sel='';
		$q = "select id_entite,raison_sociale from entites where type_entite = '1' ";
		if ($user) $q.= "and autorisations like('% ".$user." %') ";
		$q.= "order by raison_sociale ";
		$r = pmb_mysql_query($q, $dbh);
		$res = array();
		if ($sel_all) {
			$res[0]=$msg['acquisition_coord_all'];
		}
		while ($row = pmb_mysql_fetch_object($r)){
			$res[$row->id_entite] = $row->raison_sociale;
		}
		
		if (count($r)) {
			$sel="<select ";
			if (count($sel_attr)) {
				foreach($sel_attr as $attr=>$val) {
					$sel.="$attr='".$val."' ";
				}
			}
			$sel.=">";
			foreach($res as $id=>$val){
				$sel.="<option value='".$id."'";
				if($id==$selected) $sel.=" selected='selected' ";
				$sel.=" >";
				$sel.=htmlentities($val,ENT_QUOTES,$charset);
				$sel.="</option>";
			}
			$sel.='</select>';
		}
		return $sel;
	}
	
	//Retourne un tableau (id_entite=>raison sociale) a partir d'un tableau d'id 
	//si id_bibli est pr?cis?, limite les resultats aux fournisseurs par bibliotheque
	public static function getRaisonSociale($tab=array(),$id_bibli=0) {
		
		global $dbh;
		
		$res=array();
		if(is_array($tab) && count($tab)) {
			$q ="select id_entite, raison_sociale from entites where id_entite in ('".implode("','", $tab)."') ";
			if($id_bibli) $q.= " and num_bibli='".$id_bibli."' ";
			$r = pmb_mysql_query($q,$dbh);
			while($row=pmb_mysql_fetch_object($r)) {
				$res[$row->id_entite]=$row->raison_sociale;
			}
		}
		return $res;
	}
	
	//Compte le nb d'abonnements pour une entit?
	public static function has_abonnements($id_entite=0) {
	
		global $dbh;
		$q = "select count(1) from abts_abts where fournisseur = '".$id_entite."' ";
		$r = pmb_mysql_query($q, $dbh);
		return pmb_mysql_result($r, 0, 0);
	
	}
	
	// Liste des ?tablissements autoris?s pour l'utilisateur
	static public function get_entities() {
	
		$entities = array();
		$query = entites::list_biblio(SESSuserid);
		$result = pmb_mysql_query($query);
		if($result) {
			while ($row = pmb_mysql_fetch_object($result)) {
				$entities[] = array(
						'id' => $row->id_entite,
						'label' => $row->raison_sociale
				);
			}
		}
		return $entities;
	}
	
	// Administration - Affiche la liste des etablissements
	static public function get_display_list_entities($entities = array(), $type = 'pricing_systems') {
	
		$parity=1;
		$display = "<table>";
		foreach ($entities as $entity) {
			if ($parity % 2) {
				$pair_impair = "even";
			} else {
				$pair_impair = "odd";
			}
			$parity += 1;
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./admin.php?categ=acquisition&sub=".$type."&id_entity=".$entity['id']."';\" ";
			$display .= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'><td><i>".$entity['label']."</i></td></tr>";
		}
		$display .= "</table>";
		return $display;
	}
	
	// Acquisition - Affiche la liste des etablissements
	static public function show_list_biblio($function_name = '', $class_name = '') {
		global $msg, $charset;
		global $categ, $sub;
		global $current_module;
	
		//Affiche de la liste des etablissements auxquels a acces l'utilisateur si > 1
		$entities = static::get_entities();
		if (count($entities) == 1) {
			static::setSessionBibliId($entities[0]['id']);
			if($class_name != '') {
				$instance = new $class_name(); 
				return $instance->$function_name($entities[0]['id']);
			} else {
				$function_name($entities[0]['id']);
				exit;
			}
		}
		$def_bibli=static::getSessionBibliId();
		foreach ($entities as $entity) {
			if($def_bibli == $entity['id']) {
				if($class_name != '') {
					$instance = new $class_name(); 
					return $instance->$function_name($def_bibli);
				} else {
					$function_name($def_bibli);
					exit;
				}
			}
		}
	
		$display = "<form class='form-".$current_module."' id='list_biblio_form' name='list_biblio_form' method='post' action=\"\" >";
		$display .= "<h3>".htmlentities($msg['acquisition_menu_chx_ent'], ENT_QUOTES, $charset)."</h3><div class='row'></div>";
		$display .= "<table>";
	
		$parity=1;
		foreach($entities as $entity) {
			if ($parity % 2) {
				$pair_impair = "even";
			} else {
				$pair_impair = "odd";
			}
			$parity += 1;
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" onmousedown=\"document.forms['list_biblio_form'].setAttribute('action','./acquisition.php?categ=".$categ."&sub=".$sub."&action=list&id_bibli=".$entity['id']."');document.forms['list_biblio_form'].submit(); \" ";
			$display .= "<tr class='".$pair_impair."' ".$tr_javascript." style='cursor: pointer'><td><i>".htmlentities($entity['label'], ENT_QUOTES, $charset)."</i></td></tr>";
		}
		$display .=" </table></form>";
		return $display;
	}
	
}
?>