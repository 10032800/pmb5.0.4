<?php
// +-------------------------------------------------+
// ? 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collstate_update.inc.php,v 1.2 2016-09-14 08:46:48 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");
require_once($class_path."/collstate.class.php");

$collstate = new collstate($id,$serial_id, $bulletin_id);
$collstate->update_from_form();
$view="collstate";
$location=$location_id;
include('./catalog/serials/serial_view.inc.php');

?>