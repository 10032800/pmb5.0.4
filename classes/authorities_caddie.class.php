<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authorities_caddie.class.php,v 1.8.2.4 2017-12-04 15:28:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// définition de la classe de gestion des paniers

require_once ($class_path."/caddie_root.class.php");
require_once ($include_path."/templates/authorities_cart.tpl.php");
require_once ($include_path."/templates/cart.tpl.php");
require_once($class_path."/autoloader.class.php");

class authorities_caddie extends caddie_root {
	// propriétés
	public $idcaddie ;
	public $type = ''			;	// Type de panier (AUTHORS = auteurs, CATEGORIES = categories, PUBLISHERS = éditeurs,...)
	public static $table_name = 'authorities_caddie';
	public static $field_name = 'idcaddie';
	public static $table_content_name = 'authorities_caddie_content';
	public static $field_content_name = 'caddie_id';
	
	// ---------------------------------------------------------------
	//		caddie($id) : constructeur
	// ---------------------------------------------------------------
	public function __construct($caddie_id=0) {
		$this->idcaddie = $caddie_id+0;
		$this->getData();
	}
	
	// ---------------------------------------------------------------
	//		getData() : récupération infos caddie
	// ---------------------------------------------------------------
	protected function getData() {
		global $dbh;
		parent::getData();
		$this->type = '';
		if($this->idcaddie) {
			$requete = "SELECT * FROM authorities_caddie WHERE idcaddie='$this->idcaddie' ";
			$result = @pmb_mysql_query($requete, $dbh);
			if(pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);
				pmb_mysql_free_result($result);
				$this->idcaddie = $temp->idcaddie;
				$this->type = $temp->type;
				$this->name = $temp->name;
				$this->comment = $temp->comment;
				$this->autorisations = $temp->autorisations;
				$this->classementGen = $temp->caddie_classement;
				$this->acces_rapide = $temp->acces_rapide;
				$this->creation_user_name = $temp->creation_user_name;
				$this->creation_date = $temp->creation_date;
			
				//liaisons
				
			}
			$this->compte_items();
		}
	}
	
	protected function get_template_form() {
		global $cart_form;
		return $cart_form;
	}
	
	protected function get_warning_delete() {
		global $msg;
		
		$message_delete_warning = $msg["caddie_used_in_warning"];
		foreach ($this->liaisons as $type => $values){
			if(count($values)){
				switch ($type){
					default://On ne doit pas passer par là
						break;//On sort aussi du foreach
				}
			}
		}
		$message_delete_warning .= "\\n";
		return $message_delete_warning;
	}
	
	// formulaire
	public function get_form($form_action="", $form_cancel="") {
		global $msg, $charset;
		global $liaison_tpl;
		global $current_print;
		
		$form = parent::get_form($form_action, $form_cancel);
		if ($this->get_idcaddie()) {
			$type = "caddie_de_".$this->type;
			$form = str_replace('!!cart_type!!', $msg[$type], $form);
			$info_liaisons = $this->get_links_form();
			$message_delete_warning = "";
			if($info_liaisons){
				$liaison_tpl=str_replace("<!-- info_liaisons -->",$info_liaisons,$liaison_tpl);
				$form = str_replace('<!-- liaisons -->', $liaison_tpl, $form);
				$message_delete_warning = $this->get_warning_delete();
			}
			$button_delete = "<input type='button' class='bouton' value=' ".$msg['supprimer']." ' onClick=\"javascript:confirmation_delete(".$this->get_idcaddie().",'".htmlentities(addslashes($this->name),ENT_QUOTES, $charset)."')\" />";
			$form = str_replace('!!button_delete!!', $button_delete, $form);
			$form .= confirmation_delete("./autorites.php?categ=caddie&action=del_cart&idcaddie=",$message_delete_warning);
		} else {
			$select_cart="
				<select name='cart_type'>
					<option value='MIXED' ".($this->type == 'MIXED' ? "selected='selected'" : "").">".$msg['caddie_de_MIXED']."</option>
					<option value='AUTHORS' ".($this->type == 'AUTHORS' ? "selected='selected'" : "").">".$msg['caddie_de_AUTHORS']."</option>
					<option value='CATEGORIES' ".($this->type == 'CATEGORIES' ? "selected='selected'" : "").">".$msg['caddie_de_CATEGORIES']."</option>
					<option value='PUBLISHERS' ".($this->type == 'PUBLISHERS' ? "selected='selected'" : "").">".$msg['caddie_de_PUBLISHERS']."</option>
					<option value='COLLECTIONS' ".($this->type == 'COLLECTIONS' ? "selected='selected'" : "").">".$msg['caddie_de_COLLECTIONS']."</option>
					<option value='SUBCOLLECTIONS' ".($this->type == 'SUBCOLLECTIONS' ? "selected='selected'" : "").">".$msg['caddie_de_SUBCOLLECTIONS']."</option>
					<option value='SERIES' ".($this->type == 'SERIES' ? "selected='selected'" : "").">".$msg['caddie_de_SERIES']."</option>
					<option value='TITRES_UNIFORMES' ".($this->type == 'TITRES_UNIFORMES' ? "selected='selected'" : "").">".$msg['caddie_de_TITRES_UNIFORMES']."</option>
					<option value='INDEXINT' ".($this->type == 'INDEXINT' ? "selected='selected'" : "").">".$msg['caddie_de_INDEXINT']."</option>
					<option value='CONCEPTS' ".($this->type == 'CONCEPTS' ? "selected='selected'" : "").">".$msg['caddie_de_CONCEPTS']."</option>
				</select>
				<input type='hidden' name='current_print' value='$current_print'/>";
			$form=str_replace('!!cart_type!!', $select_cart, $form);
			$form = str_replace('!!button_delete!!', '', $form);
		}
		return $form;
	}
	
	// Liaisons pour le panier
	protected function get_links_form() {
		global $msg, $charset;
		global $dsi_active;
			
		$links_form = "";
		$end = false;
		foreach ( $this->liaisons as $type => $values ) {
			if (count ( $values )) {
				$links_form .= "<br>";
				switch ($type) {
					default : // On ne doit pas passer par là
						$links_form = "";
						//break 2; // On sort aussi du foreach
						$end = true;
						break;
				}
				if($end) break;
				foreach ( $values as $infos ) {
					$links_form .= str_replace ( array (
							"!!id!!",
							"!!name!!"
					), array (
							$infos ["id"],
							htmlentities ( $infos ["lib"], ENT_QUOTES, $charset )
					), $link );
				}
				$links_form .= "</div>";
			}
		}
		return $links_form;
	}
	
	public function set_properties_from_form() {
		global $cart_type;
		global $classementGen_authorities_caddie;

		parent::set_properties_from_form();
		if(!$this->idcaddie) {
			$this->type = $cart_type;
		}
		$this->classementGen = stripslashes($classementGen_authorities_caddie);
	}
	
	protected static function get_order_cart_list() {
		return " order by type, name, comment ";
	}
	
	static public function get_cart_data($temp) {
		global $dbh;
		
		$nb_item = 0 ;
		$nb_item_pointe = 0 ;
		$rqt_nb_item="select count(1) from authorities_caddie_content where caddie_id='".$temp->idcaddie."' ";
		$nb_item = pmb_mysql_result(pmb_mysql_query($rqt_nb_item, $dbh), 0, 0);
		$rqt_nb_item_pointe = "select count(1) from authorities_caddie_content where caddie_id='".$temp->idcaddie."' and (flag is not null and flag!='') ";
		$nb_item_pointe = pmb_mysql_result(pmb_mysql_query($rqt_nb_item_pointe, $dbh), 0, 0);
		
		return array(
				'idcaddie' => $temp->idcaddie,
				'name' => $temp->name,
				'type' => $temp->type,
				'comment' => $temp->comment,
				'autorisations' => $temp->autorisations,
				'caddie_classement' => $temp->caddie_classement,
				'nb_item' => $nb_item,
				'nb_item_pointe' => $nb_item_pointe,
		);
	}
	
	// liste des paniers disponibles
	static public function get_cart_list($restriction_panier="",$acces_rapide = 0) {
		$caddies = array_merge(
				parent::get_cart_list($restriction_panier, $acces_rapide),
				parent::get_cart_list("MIXED", $acces_rapide)
		);
		//Dédoublonnage 
		return array_map("unserialize", array_unique(array_map("serialize", $caddies)));
	}
	
	// création d'un panier vide
	public function create_cart() {
		$requete = "insert into authorities_caddie set name='".addslashes($this->name)."', type='".$this->type."', comment='".addslashes($this->comment)."', autorisations='".$this->autorisations."', caddie_classement='".addslashes($this->classementGen)."', acces_rapide='".$this->acces_rapide."' ";
		$user = $this->get_info_user();
		if(count($user)) {
			$requete .= ", creation_user_name='".addslashes($user->name)."', creation_date='".date("Y-m-d H:i:s")."'";
		}
		pmb_mysql_query($requete);
		$this->idcaddie = pmb_mysql_insert_id();
		$this->compte_items();
	}
	
	// sauvegarde du panier
	public function save_cart() {
		$query = "update authorities_caddie set name='".addslashes($this->name)."', comment='".addslashes($this->comment)."', autorisations='".$this->autorisations."', caddie_classement='".addslashes($this->classementGen)."', acces_rapide='".$this->acces_rapide."' where ".static::get_field_name()."='".$this->get_idcaddie()."'";
		$result = pmb_mysql_query($query);
		return true;
	}

	// ajout d'un item
	public function add_item($item=0, $object_type="AUTHORS") {
		if (!$item) return CADDIE_ITEM_NULL ;
		
		// les objets sont cohérents
		if ($object_type==$this->type || $this->type == "MIXED") {
			$requete_compte = "select count(1) from authorities_caddie_content where caddie_id='".$this->get_idcaddie()."' AND object_id='".$item."' ";
			$result_compte = @pmb_mysql_query($requete_compte);
			$deja_item=pmb_mysql_result($result_compte, 0, 0);
			if (!$deja_item) {
				$requete= "insert into authorities_caddie_content set caddie_id='".$this->get_idcaddie()."', object_id='".$item."' ";
				$result = @pmb_mysql_query($requete);
			}
		}
	}
	
	public function del_item_base($item=0,$forcage=array()) {
		if (!$item) return CADDIE_ITEM_NULL ;
		
		$authority = new authority($item);
		$object_instance = $authority->get_object_instance();
		
		switch ($authority->get_type_object()) {
			case AUT_TABLE_INDEX_CONCEPT :
			case AUT_TABLE_CONCEPT :
				global $class_path;
								
				$autoloader = new autoloader();
				$autoloader->add_register("onto_class",true);
				
				$onto_store_config = array(
						/* db */
						'db_name' => DATA_BASE,
						'db_user' => USER_NAME,
						'db_pwd' => USER_PASS,
						'db_host' => SQL_SERVER,
						/* store */
						'store_name' => 'ontology',
						/* stop after 100 errors */
						'max_errors' => 100,
						'store_strip_mb_comp_str' => 0
				);
				$data_store_config = array(
						/* db */
						'db_name' => DATA_BASE,
						'db_user' => USER_NAME,
						'db_pwd' => USER_PASS,
						'db_host' => SQL_SERVER,
						/* store */
						'store_name' => 'rdfstore',
						/* stop after 100 errors */
						'max_errors' => 100,
						'store_strip_mb_comp_str' => 0
				);				
				$tab_namespaces = array(
						"skos"	=> "http://www.w3.org/2004/02/skos/core#",
						"dc"	=> "http://purl.org/dc/elements/1.1",
						"dct"	=> "http://purl.org/dc/terms/",
						"owl"	=> "http://www.w3.org/2002/07/owl#",
						"rdf"	=> "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
						"rdfs"	=> "http://www.w3.org/2000/01/rdf-schema#",
						"xsd"	=> "http://www.w3.org/2001/XMLSchema#",
						"pmb"	=> "http://www.pmbservices.fr/ontology#"
				);
				
				$params = new stdClass();
				$params->action = 'delete_from_cart';
				$params->categ = 'concepts';
				$params->sub = 'concept';
				$params->id = $object_instance->get_id();
				
				$onto_ui = new onto_ui($class_path."/rdf/skos_pmb.rdf", "arc2", $onto_store_config, "arc2", $data_store_config,$tab_namespaces,'http://www.w3.org/2004/02/skos/core#prefLabel',$params);
				$response = $onto_ui->proceed();
				
				if (count($response)) {
					return CADDIE_ITEM_AUT_USED;
				} else {
					return CADDIE_ITEM_SUPPR_BASE_OK;
				}
				break;
			
			case AUT_TABLE_AUTHPERSO :
				//TODO : à revoir quand on implémentera les paniers d'autorités perso				
				$authperso = new authperso(0, $object_instance->id);
				if ($authperso->delete() === false) {
					return CADDIE_ITEM_SUPPR_BASE_OK;
				} else  {
					return CADDIE_ITEM_AUT_USED;
				}
				break;
			default :
				if ($object_instance->delete() === false) {
					return CADDIE_ITEM_SUPPR_BASE_OK;
				} else  {
					return CADDIE_ITEM_AUT_USED;
				}
				break;
		}	
		/* Appeler la methode delete pour chacun des types d'autorités
		 * Faire attention au retour de chacune des méthodes pour retourner la bonne constante : CADDIE_ITEM_SUPPR_BASE_OK - CADDIE_ITEM_AUTHORITY_USED - CADDIE_ITEM_OK
		 */
		return CADDIE_ITEM_OK ;
	}
	
	// suppression d'un item de tous les caddies
	public function del_item_all_caddies($item, $type) {
		$requete_suppr = "delete from authorities_caddie_content where object_id='".$item."'";
		$result_suppr = pmb_mysql_query($requete_suppr);
	}

	public function del_item_flag() {
		global $dbh;
		$requete = "delete FROM authorities_caddie_content where caddie_id='".$this->idcaddie."' and (flag is not null and flag!='') ";
		$result = @pmb_mysql_query($requete, $dbh);
		$this->compte_items();
	}

	public function del_item_no_flag() {
		global $dbh;
		$requete = "delete FROM authorities_caddie_content where caddie_id='".$this->idcaddie."' and (flag is null or flag='') ";
		$result = @pmb_mysql_query($requete, $dbh);
		$this->compte_items();
	}

	public function pointe_item($item=0) {
		global $dbh;
		$requete = "update authorities_caddie_content set flag='1' where caddie_id='".$this->idcaddie."' and object_id='".$item."' ";
		$result = @pmb_mysql_query($requete, $dbh);
		$this->compte_items();
		return CADDIE_ITEM_OK ;
	}
	
	// suppression d'un panier
	public function delete() {
		parent::delete();
	}

	// get_cart() : ouvre un panier et récupère le contenu
	public function get_cart($flag="") {
		global $dbh;
		$cart_list=array();
		switch ($flag) {
			case "FLAG" :
				$requete = "SELECT * FROM authorities_caddie_content where caddie_id='".$this->idcaddie."' and (flag is not null and flag!='') ";
				break ;
			case "NOFLAG" :
				$requete = "SELECT * FROM authorities_caddie_content where caddie_id='".$this->idcaddie."' and (flag is null or flag='') ";
				break ;
			case "ALL" :
			default :
				$requete = "SELECT * FROM authorities_caddie_content where caddie_id='".$this->idcaddie."' ";
				break ;
			}
		$result = @pmb_mysql_query($requete, $dbh);
		if(pmb_mysql_num_rows($result)) {
			while ($temp = pmb_mysql_fetch_object($result)) {
				$cart_list[] = $temp->object_id;
			}
		} 
		return $cart_list;
	}

	// compte_items 
	public function compte_items() {
		parent::compte_items();
	}

	static public function show_actions($id_caddie = 0, $type_caddie = '') {
		global $msg,$cart_action_selector,$cart_action_selector_line;
		
		//Le tableau des actions possibles
		$array_actions = array();
		$array_actions[] = array('msg' => $msg["caddie_menu_action_suppr_panier"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=supprpanier&action=choix_quoi&object_type=NOTI&idcaddie='.$id_caddie.'&item=0');
		//$array_actions[] = array('msg' => $msg["caddie_menu_action_transfert"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=transfert&action=transfert&object_type=NOTI&idcaddie='.$id_caddie.'&item=');
		$array_actions[] = array('msg' => $msg["caddie_menu_action_edition"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=edition&action=choix_quoi&object_type=NOTI&idcaddie='.$id_caddie.'&item=0');
		//$array_actions[] = array('msg' => $msg["caddie_menu_action_export"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=export&action=choix_quoi&object_type=NOTI&idcaddie='.$id_caddie.'&item=0');
		$array_actions[] = array('msg' => $msg["caddie_menu_action_selection"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=selection&action=&object_type=NOTI&idcaddie='.$id_caddie.'&item=0');
		//$array_actions[] = array('msg' => $msg["caddie_menu_action_suppr_base"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=supprbase&action=choix_quoi&object_type=NOTI&idcaddie='.$id_caddie.'&item=0');
		$array_actions[] = array('msg' => $msg["caddie_menu_action_reindex"], 'location' => './autorites.php?categ=caddie&sub=action&quelle=reindex&action=choix_quoi&object_type=NOTI&idcaddie='.$id_caddie.'&item=0');
		//On crée les lignes du menu
		$lines = '';
		foreach($array_actions as $item_action){
			$tmp_line = str_replace('!!cart_action_selector_line_location!!',$item_action['location'],$cart_action_selector_line);
			$tmp_line = str_replace('!!cart_action_selector_line_msg!!',$item_action['msg'],$tmp_line);
			$lines.= $tmp_line;
		}
		
		//On récupère le template
		$to_show = str_replace('!!cart_action_selector_lines!!',$lines,$cart_action_selector);
		
		return $to_show;
	}
	
	protected function replace_in_action_query($query, $by) {
// 		$final_query=str_replace("CADDIE(MIXED)",$by,$final_query);
		$final_query = preg_replace("/CADDIE\(((.*,)?AUTHORS(,[^\)]*)?|(.*,)?CATEGORIES(,[^\)]*)?|(.*,)?PUBLISHERS(,[^\)]*)?|(.*,)?COLLECTIONS(,[^\)]*)?|(.*,)?SUBCOLLECTIONS(,[^\)]*)?|(.*,)?SERIES(,[^\)]*)?|(.*,)?TITRES_UNIFORMES(,[^\)]*)?|(.*,)?INDEXINT(,[^\)]*)?|(.*,)?CONCEPTS(,[^\)]*)?)\)/", $by, $query);
		return $final_query;
	}
	
	protected function get_edition_template_form() {
		global $cart_choix_quoi_edition;
		return $cart_choix_quoi_edition;
	}
	
	public function get_edition_form($action="", $action_cancel="") {
		global $msg;
		
		if(!$action) $action = "./autorites/caddie/action/edit.php?idcaddie=".$this->get_idcaddie();
		if(!$action_cancel) $action_cancel = "./autorites.php?categ=caddie&sub=action&quelle=edition&action=&idcaddie=0" ;
		$form = parent::get_edition_form($action, $action_cancel);
		$form = str_replace('<!-- !!boutons_supp!! -->', '', $form);
		$form = str_replace('<!-- notice_template -->', '', $form);
		return $form ;
	}
	
	private function generate_authority($authority){
		global $include_path;
		$template_path = $include_path.'/templates/authorities/list/'.$authority->get_string_type_object().'.html';
		if(file_exists($include_path.'/templates/authorities/list/'.$authority->get_string_type_object().'_subst.html')){
			$template_path = $include_path.'/templates/authorities/list/'.$authority->get_string_type_object().'_subst.html';
		}
		if(file_exists($template_path)){
			$h2o = new H2o($template_path);
			$context = array('list_element' => $authority);
			return $h2o->render($context);
		}
		return '';
	}
	
	// affichage du contenu complet d'un caddie
	public function aff_cart_objects($url_base="./autorites.php?categ=caddie&sub=gestion&quoi=panier&idcaddie=0", $no_del=false,$rec_history=0, $no_point=false ) {
		global $msg, $begin_result_liste;
		global $nbr_lignes, $page, $nb_per_page_search ;
		
		// nombre de références par pages
		if ($nb_per_page_search != "") $nb_per_page = $nb_per_page_search ;
		else $nb_per_page = 10;
		
		// on récupére le nombre de lignes
		if(!$nbr_lignes) {
			$requete = "SELECT count(1) FROM authorities_caddie_content where caddie_id='".$this->get_idcaddie()."' ";
			$res = pmb_mysql_query($requete);
			$nbr_lignes = pmb_mysql_result($res, 0, 0);
		}
		
		if(!$page) $page=1;
		$debut =($page-1)*$nb_per_page;
		
		//Calcul des variables pour la suppression d'items
		$modulo = $nbr_lignes%$nb_per_page;
		if($modulo == 1){
			$page_suppr = (!$page ? 1 : $page-1);
		} else {
			$page_suppr = $page;
		}
		$nb_after_suppr = ($nbr_lignes ? $nbr_lignes-1 : 0);
		
		if($nbr_lignes) {
			$requete = "SELECT object_id, flag FROM authorities_caddie_content where caddie_id='".$this->get_idcaddie()."'";
			$requete.= " LIMIT $debut,$nb_per_page ";
		} else {
			print $msg[399];
			return;
		}
		
		$liste=array();
		$result = @pmb_mysql_query($requete);
		if ($result) {
			if(pmb_mysql_num_rows($result)) {
				while ($temp = pmb_mysql_fetch_object($result)) {
					$liste[] = array('object_id' => $temp->object_id, 'flag' => $temp->flag ) ;
				}
			}
		}
		if(!sizeof($liste) || !is_array($liste)) {
			print $msg[399];
			return;
		} else {
			print $this->get_js_script_cart_objects('autorites');
			print $begin_result_liste;
			print authorities_caddie::show_actions($this->get_idcaddie());
			while(list($cle, $object) = each($liste)) {
				$authority = new authority($object['object_id']);
				if (!$no_del) {
					$lien_suppr_cart = "<a href='$url_base&action=del_item&item=".$object['object_id']."&page=$page_suppr&nbr_lignes=$nb_after_suppr&nb_per_page=$nb_per_page'><img src='./images/basket_empty_20x20.gif' alt='basket' title=\"".$msg['caddie_icone_suppr_elt']."\" /></a>";
					$authority->set_icon_del_in_cart($lien_suppr_cart);
				}
				if (!$no_point) {
					if ($object['flag']) $marque_flag ="<img src='images/depointer.png' id='caddie_".$this->get_idcaddie()."_item_".$object['object_id']."' title=\"".$msg['caddie_item_depointer']."\" onClick='del_pointage_item(".$this->get_idcaddie().",".$object['object_id'].");' style='cursor: pointer'/>" ;
					else $marque_flag ="<img src='images/pointer.png' id='caddie_".$this->get_idcaddie()."_item_".$object['object_id']."' title=\"".$msg['caddie_item_pointer']."\" onClick='add_pointage_item(".$this->get_idcaddie().",".$object['object_id'].");' style='cursor: pointer'/>" ;
				} else {
					if ($object['flag']) $marque_flag ="<img src='images/tick.gif'/>" ;
					else $marque_flag ="" ;
				}
				$authority->set_icon_pointe_in_cart($marque_flag);
				print $this->generate_authority($authority);
			}
			print "<br />".aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true);
		}
		return;
	}
	
	public function aff_cart_titre() {
		global $msg;
	
		$link = "";
		return "
			<div class='titre-panier'>
				<h3>
					<a href='".$link."'>".$this->name.($this->comment ? " - ".$this->comment : "")."</a> <i><small>(".$msg["caddie_de_".$this->type].")</small></i>
				</h3>
			</div>";
	}
	
	protected function get_choix_quoi_template_form() {
		global $authorities_cart_choix_quoi;
		return $authorities_cart_choix_quoi;
	}
	
	public function reindex_from_list($liste=array()) {
		global $msg;
		
		$pb=new progress_bar($msg['caddie_situation_reindex_encours'],count($liste),5);
		while(list($cle, $object) = each($liste)) {
			$authority = new authority($object);
			$indexation_authority = indexations_collection::get_indexation($authority->get_type_object());
			$indexation_authority->maj($object);
			$pb->progress();
		}
		$pb->hide();
	}
	
	public function del_items_base_from_list($liste=array()) {
		$res_aff_suppr_base = '';
		
		foreach ($liste as $object) {
			if ($this->del_item_base($object)==CADDIE_ITEM_SUPPR_BASE_OK) {
				$this->del_item_all_caddies($object, $this->type) ;
			} else {
				$authority = new authority($object);
				$res_aff_suppr_base .= $this->generate_authority($authority);
			}
		}
		return $res_aff_suppr_base;
	}
	
	protected function write_header_tableau($worksheet) {
		global $msg;
		global $entete_bloc;
		
// 		$worksheet->write_string(1,0,$msg["caddie_mess_edition_".$entete_bloc]);
		$worksheet->write_string(2,0,$msg['caddie_action_marque']);
		$worksheet->write_string(2,1,$msg[1601]);
		$worksheet->write_string(2,2, $msg['cms_authority_format_data_isbd']);
	}
	
	protected function write_content_tableau($worksheet) {
		$list = $this->get_tab_list();
		$debligne_excel = 4;
		while(list($cle, $object) = each($list)) {
			$authority_instance = new authority($object['object_id']);
			if ($object['flag']) $worksheet->write_string(($cle+$debligne_excel),0,"X");
			$worksheet->write_string(($cle+$debligne_excel),1,$authority_instance->get_id());
			$worksheet->write_string(($cle+$debligne_excel),2,$authority_instance->get_isbd());
		}
	}
	
	protected function get_display_header_tableauhtml() {
		global $msg;
		global $entete_bloc;
		
		$display = '';
// 		$display .= "<h3>".$msg["caddie_mess_edition_".$entete_bloc]."</h3>";
		$display .= "\n<tr>";
		$display .= "<th align='left'>".$msg['caddie_action_marque']."</th>";
		$display .= "<th align='left'>ID</th>";
		$display .= "<th align='left'>ISBD</th>";
		$display .= "</tr>";
		return $display;
	}
	
	protected function get_display_content_tableauhtml() {
		$list = $this->get_tab_list();
		
		$display = '';
		while(list($cle, $object) = each($list)) {
			$authority_instance = new authority($object['object_id']);
			$display .= "<tr>";
			if ($object['flag']) $display .= "<td align='center'>X</td>";
			else $display .= "<td align='center'></td>";
			$display .= "<td align='center'>".$authority_instance->get_id()."</td>";
			$display .= "<td>".$authority_instance->get_isbd()."</td>";
			$display .= "</tr>";
		}
		return $display;
	}
	
	public function get_idcaddie() {
		return $this->idcaddie;
	}
	
	public static function get_type_from_const($const) {
		switch($const){
			case AUT_TABLE_AUTHORS :
				return "AUTHORS";
			case AUT_TABLE_PUBLISHERS :
				return "PUBLISHERS";
			case AUT_TABLE_COLLECTIONS :
				return "COLLECTIONS";
			case AUT_TABLE_SUB_COLLECTIONS :
				return "SUBCOLLECTIONS";
			case AUT_TABLE_SERIES :
				return "SERIES";
			case AUT_TABLE_INDEXINT :
				return "INDEXINT";
			case AUT_TABLE_TITRES_UNIFORMES :
				return "TITRES_UNIFORMES";
			case AUT_TABLE_CATEG :
				return "CATEGORIES";
			case AUT_TABLE_CONCEPT :
				return "CONCEPTS";
		}
	}
} // fin de déclaration de la classe caddie