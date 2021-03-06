<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: translation.tpl.php,v 1.2 2017-04-21 12:02:46 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

//*******************************************************************
// Définition des templates 
//*******************************************************************
$translation_tpl_form_javascript = "
<script type='text/javascript'>
	function translation_view(item) {
		var elt = document.getElementById(item);
		if (elt.style.display == 'none') elt.style.display = ''; else elt.style.display = 'none'; 
	}
</script>
";

$translation_tpl_form = "
<div class='row'>
	<label class='etiquette' for='!!field_name!!'>!!label!!</label>
	!!translation_button!!
</div>
<div class='row'>
	<input class='!!class_saisie!!' id='!!field_id!!' name='!!field_name!!' value='!!field_value!!' type='text'>!!translation_button_no_label!!
</div>
<!--	traductions-->
<div id='lang_!!field_id!!' class='!!class_form!!' style='!!style_form!!'>
	!!lang_list!!
</div>
";

$translation_tpl_line_form = "
	<div class='row'>
		<label class='etiquette'>!!libelle_lang!!</label>
	</div>
	<div class='row'>
		<input class='!!class_saisie!!' name='!!lang!!_!!field_name!!' value='!!text!!' type='text'>
	</div>
";
