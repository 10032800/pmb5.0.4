<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_ui.class.php,v 1.12 2017-02-17 15:34:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path.'/templates/vedette/vedette_common.tpl.php');
require_once($class_path."/vedette/vedette_element.class.php");

class vedette_ui {
	
	
	/*** Attributes: ***/
	
	private $vedette_composee;
	
	/**
	 *
	 *
	 * @param int id_vedette_composee id de la vedette compos?e ? repr?senter
	 * 
	 * @return void
	 * @access public
	 */
	public function __construct(vedette_composee $vedette_composee) {
		$this->vedette_composee = $vedette_composee;
	}
	
	/**
	 *
	 *
	 * @param vedette_element vedette_element Factory : renvoie la classe vedette_element_ui qui va bien avec l'instance de vedette_element
	 * 
	 * @return void
	 * @access public
	 */
	public function get_vedette_element_ui($vedette_element) {
		return vedette_element::search_vedette_element_ui_class_name(get_class($vedette_element));
	}
	
	/**
	 * Renvoie le formulaire de la vedette compos?e
	 * 
	 * @param $property onto_common_property
	 * @param $restrictions onto_restriction
	 * @param $datas
	 * @param $instance_name
	 *
	 * @return string
	 * @access public
	 */
	public function get_form($property_name, $order, $instance_name, $property_type = "",$no_add_script=1){
		global $dbh,$base_path,$charset,$lang;
		global $vedette_tpl;
		
		$form_html = '';
		//TODO Retirer le style brut
		if(!$order && $no_add_script){
			$form_html.=$vedette_tpl['css'].$vedette_tpl['form_body_script'];
		}
		$form_html.=$vedette_tpl['form_body'];
		
		if (!count($this->vedette_composee->get_elements())) {
			$form_html = str_replace("!!vedette_composee_apercu!!", "", $form_html);
		} else {
			$form_html = str_replace("!!vedette_composee_apercu!!", htmlentities($this->vedette_composee->get_label(), ENT_QUOTES, $charset), $form_html);
		}
		$form_html = str_replace("!!vedette_composee_id!!", $this->vedette_composee->get_id(), $form_html);
		$form_html = str_replace("!!vedette_composee_type!!", $property_type, $form_html);
		$form_html = str_replace("!!vedette_composee_grammar!!", htmlentities($this->vedette_composee->get_config_filename(), ENT_QUOTES, $charset), $form_html);
		
		//les champs disponibles
		$available_fields_html='';
		$available_fields_scripts = "";
		$get_vedette_element_switchcases = "";
		
		foreach($this->vedette_composee->get_available_fields() as $key=>$available_field){
			$vedette_element_ui_class_name = vedette_element::search_vedette_element_ui_class_name($available_field["class_name"]);
			$js_class_name = $available_field["class_name"];
			$authid=0;
			if(isset($available_field['params'])){
				$authid=$available_field['params']['id_authority'];
				if (method_exists($vedette_element_ui_class_name, 'get_js_class_name')) {
					$js_class_name = $vedette_element_ui_class_name::get_js_class_name($available_field['params']);
				}
				$available_fields_scripts .= $vedette_element_ui_class_name::get_create_box_js($available_field['params']);
			}else {
 				$available_fields_scripts .= $vedette_element_ui_class_name::get_create_box_js();
			}
			$get_vedette_element_switchcases .= str_replace("!!vedette_type!!", $js_class_name, $vedette_tpl["vedette_composee_get_vedette_element_switchcase"]);
			
			$tmp_html=$vedette_tpl['vedette_composee_available_field'];
			$tmp_html = str_replace("!!available_field_id!!", $key, $tmp_html);
			$tmp_html = str_replace("!!available_field_type!!", $js_class_name, $tmp_html);
			$tmp_html = str_replace("!!authid!!", $authid, $tmp_html);
			
			$tmp_html=str_replace("!!vedette_composee_available_field_label!!", get_msg_to_display($available_field['name']), $tmp_html);
			$available_fields_html.=$tmp_html;
		}
		$form_html=str_replace("!!available_fields_scripts!!", $available_fields_scripts, $form_html);
		$form_html=str_replace("!!vedette_composee_available_fields!!", $available_fields_html, $form_html);
		$form_html=str_replace("!!get_vedette_element_switchcases!!", $get_vedette_element_switchcases, $form_html);
		
		//les zones de subdivision
		$subdivisions_html='';
		
		$tab_vedette_elements = array();
		
		//On parcourt les subdivisions
		foreach($this->vedette_composee->get_subdivisions() as $key=>$subdivision){
			$tmp_html = $vedette_tpl['vedette_composee_subdivision'];
			$tab_vedette_elements[$subdivision["order"]] = array();
			
			if (isset($subdivision["min"]) && $subdivision["min"]) $tmp_html = str_replace("!!vedette_composee_subdivision_cardmin!!", $subdivision["min"], $tmp_html);
			else $tmp_html = str_replace("!!vedette_composee_subdivision_cardmin!!", "", $tmp_html);
			if (isset($subdivision["max"]) && $subdivision["max"]) $tmp_html = str_replace("!!vedette_composee_subdivision_cardmax!!", $subdivision["max"], $tmp_html);
			else $tmp_html = str_replace("!!vedette_composee_subdivision_cardmax!!", "", $tmp_html);
			$tmp_html = str_replace("!!vedette_composee_subdivision_order!!", $subdivision["order"], $tmp_html);
			$elements_html='';
			if($elements=$this->vedette_composee->get_at_elements_subdivision($subdivision['code'])){
				
				// tableau pour la gestion de l'ordre ? l'int?rieur d'une subdivision
				$elements_order = array();
				
				// On parcourt les ?l?ments de la subdivision
				foreach($elements as $position=>$element){
					$current_element_html = $vedette_tpl['vedette_composee_element'];
					$elements_order[] = $position;
					
					$tab_vedette_elements[$subdivision["order"]][$position] = $element->get_isbd();
					$element_ui_class_name = vedette_element::search_vedette_element_ui_class_name(get_class($element));
					if($element->params){
						$current_element_html = str_replace("!!vedette_composee_element_form!!", $element_ui_class_name::get_form($element->params), $current_element_html);
						$autority_type = get_msg_to_display($element->params["label"]);
					}else{
						$current_element_html = str_replace("!!vedette_composee_element_form!!", $element_ui_class_name::get_form(), $current_element_html);
						$field_class_name = $this->vedette_composee->get_at_available_field_class_name(get_class($element));
						$autority_type = get_msg_to_display($field_class_name["name"]);
					}
					$current_element_html = str_replace("!!vedette_composee_element_order!!", $position, $current_element_html);
					$current_element_html = str_replace("!!vedette_composee_element_type!!", get_class($element), $current_element_html);
					$current_element_html = str_replace("!!vedette_element_rawlabel!!", $element->get_isbd(), $current_element_html);
					$current_element_html = str_replace("!!vedette_element_label!!", "[".$autority_type."] ".$element->get_isbd(), $current_element_html);
					$current_element_html = str_replace("!!vedette_element_id!!", $element->get_id(), $current_element_html);
					
					$elements_html.=$current_element_html;
				}
				$tmp_html = str_replace("!!vedette_composee_subdivision_elements!!", $elements_html, $tmp_html);
				$tmp_html = str_replace("!!elements_order!!", implode(",", $elements_order), $tmp_html);
			} else {
				$tmp_html = str_replace("!!vedette_composee_subdivision_elements!!", "", $tmp_html);
				$tmp_html = str_replace("!!elements_order!!", "", $tmp_html);
			}
			$tmp_html=str_replace("!!vedette_composee_subdivision_label!!", get_msg_to_display($subdivision['name']), $tmp_html);
			$tmp_html=str_replace("!!vedette_composee_subdivision_id!!", $subdivision['code'], $tmp_html);
			
			$subdivisions_html.=$tmp_html;
		}
		$form_html=str_replace("!!vedette_composee_subdivisions!!", $subdivisions_html, $form_html);
		$form_html=str_replace("!!caller!!", $instance_name, $form_html);
		$form_html=str_replace("!!vedette_composee_order!!", $order, $form_html);
		$form_html=str_replace("!!property_name!!", $property_name."_composed", $form_html);
		$form_html=str_replace("!!tab_vedette_elements!!", encoding_normalize::json_encode($tab_vedette_elements), $form_html);
		$form_html=str_replace("!!vedette_separator!!", htmlentities($this->vedette_composee->get_separator(), ENT_QUOTES), $form_html);
		
		return $form_html;
	}
	
	/**
	 * R?cup?re les ?l?ments du formulaire
	 *
	 * @return Array()
	 * @access public
	 */
	public function get_from_form(){
	
	}
	
	
	//inutile ?
	public function proceed() {
	
	}
}
