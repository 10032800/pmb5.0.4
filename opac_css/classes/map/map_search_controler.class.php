<?php
// +-------------------------------------------------+
// ? 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: map_search_controler.class.php,v 1.10 2016-11-05 14:49:08 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
require_once($class_path."/map/map_hold.class.php");
require_once($class_path."/map/map_model.class.php");
require_once($class_path."/map/map_objects_controler.class.php");
require_once($class_path."/search.class.php");
require_once($class_path."/searcher.class.php");
require_once($class_path."/analyse_query.class.php");
require_once($include_path."/rec_history.inc.php");

/**
 * class map_search_controler
 * Controlleur de notre super dev
 */
class map_search_controler {

	/** Aggregations: */
	
	/** Compositions: */
	
	/*** Attributes: ***/
	
	/**
	 *
	 * @access protected
	 */
	protected $model;
	
	/**
	 *
	 * @access protected
	 */
	protected $mode;

	/**
	 * Constructeur.
	 *
	 * Il joue ?? aller chercher les infos utiles pour le mod?le (listes d'ids des
	 * objets li?s,...)
	 *
	 * @param map_hold map_hold Emprise courante de la carte
	
	 * @param int mode Mode de r?cup?ration des ?l?ments
	
	 * @return void
	 * @access public
	 */
	public function __construct($map_hold, $mode, $max_hold, $force_ajax=false, $cluster="true") {
		$this->editable = false;
		$this->ajax = $force_ajax;
  		$this->set_mode($mode);

  		$this->objects = array();
  		
  		$this->objects = $this->get_objects();
  		if(count($this->objects)){
  			$this->model = new map_model($map_hold, $this->objects,$max_hold,$cluster);
  			$this->model->set_mode("search");
  		}else{
  			//la recherche n'est pas encore enregistr?...
  			$this->ajax = true;
  		}
  		
  	} // end of member function __construct

  	/**
  	 * Modifie le mode
  	 *
  	 * @return void
  	 * @access public
  	 */
  	public function set_mode($mode) {
  		 
  		$this->mode = $mode;
  		
  	} // end of member function get_mode
  	
  	/**
  	 * Retourne le mode
  	 *
  	 * @return string
  	 * @access public
  	 */
  	public function get_mode() {
  	
  		return $this->mode;
  		
  	} // end of member function get_mode
  	
  	/**
  	 *
  	 *
  	 * @return void
  	 * @access public
  	 */
  	public function get_objects() {
  		global $dbh;
  		global $search;
  		global $opac_stemming_active;  	
  		global $user_query; 	
  		
  		$objects = array();
  		
  		$current_search = $this->get_mode();
  		
  		//	print $_SESSION["tab_result"];
  		$notices_ids=explode(",",$_SESSION["tab_result"]);
  		if(!count($notices_ids) || $notices_ids[0] == '') return $objects;
  		$objects[] = array(
  			'layer' => "record",
  			'ids' => $notices_ids
  		);
  		
  		$requete = "select distinct map_emprise_obj_num from map_emprises join notices_categories on map_emprises.map_emprise_obj_num = notices_categories.num_noeud where map_emprises.map_emprise_type=2 and notices_categories.notcateg_notice in (".implode(",",$notices_ids).")";
  		$result = pmb_mysql_query($requete,$dbh);
  		if(pmb_mysql_num_rows($result)){
  			$categ_ids = array();
  			while ($row = pmb_mysql_fetch_object($result)) {
  				$categ_ids[] = $row->map_emprise_obj_num;
  			}
  			$objects[] = array(
  				'layer' => "authority",
  				'type' => 2,
  				'ids' => $categ_ids
  			);
  		
  		}
  		return $objects;  		
  		 
  	} // end of member function get_objects

  	public function have_results(){
  		if(!$this->model){
  			return false;
  		}else{
  			return $this->model->have_results();
  		}
  	}
  	
  	public function get_holds_json_informations($indice){
  		global $dbh;
  		
		$json = array(); 	
  		if($this->model){
  			$json = $this->model->get_holds_informations($this->objects[$indice]['layer']);
  			return json_encode($json);
  		}
  	}
  	
  	public function get_json_informations(){
  		global $opac_url_base;
  		global $opac_map_base_layer_type;
  		global $opac_map_base_layer_params;
  		global $dbh;
  		
  		$layer_params = json_decode($opac_map_base_layer_params,true);
  		$baselayer =  "baseLayerType: dojox.geo.openlayers.BaseLayerType.".$opac_map_base_layer_type;
  		if(count($layer_params)){
  			if($layer_params['name']) $baselayer.=",baseLayerName:\"".$layer_params['name']."\"";
  			if($layer_params['url']) $baselayer.=",baseLayerUrl:\"".$layer_params['url']."\"";
  			if($layer_params['options']) $baselayer.=",baseLayerOptions:".json_encode($layer_params['options']);
  		}
  				
  		if ($this->ajax){
  			return "mode:\"search_result\", searchId: ".$this->mode.",".$baselayer.",layers_url: \"".$opac_url_base."ajax.php?module=ajax&categ=map&sub=search&action=get_layers\"";
  		}else if($this->model){
  			$json = array();
	  		$map_hold = $this->get_bounding_box();
	  		if($map_hold){
		  		$coords = $map_hold->get_coords();
		  		$json = array(
		  			'initialFit' => explode(',', map_objects_controler::get_coord_initialFit($coords)),
			  		'layers' => $this->model->get_json_informations(true, $pmb_url_base,false)
			  	);
	  		}else{
	  			$json = array(
	  				'initialFit' => array(0,0,0,0),
	  				'layers' => array(
	  					array(
	  						'type' => "record",
	  						'name' => "record",
	  						'holds' => array(),
	  						'ajax' => false
	  					)
	  				)	
	  			);
	  		}
	  		return json_encode($json);
  		}
  	}
  	
  	public function get_bounding_box(){
  		return $this->model->get_bounding_box();
  	}

} // end of map_search_controler