<?php
// +-------------------------------------------------+
// ? 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: relations2.inc.php,v 1.12 2017-07-12 09:07:56 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// la taille d'un paquet de notices
$lot = SERIE_PAQUET_SIZE; // defini dans ./params.inc.php

// initialisation de la borne de d?part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_clean_relations_cat"], ENT_QUOTES, $charset)."</h2>";

$query = pmb_mysql_query("delete from notices_custom_values where notices_custom_champ not in (select idchamp from notices_custom)");
$affected = pmb_mysql_affected_rows();
$query = pmb_mysql_query("delete from expl_custom_values where expl_custom_champ not in (select idchamp from expl_custom)");
$affected = pmb_mysql_affected_rows();
$query = pmb_mysql_query("DELETE empr_custom_values FROM empr_custom_values LEFT JOIN empr ON id_empr=empr_custom_origine WHERE id_empr IS NULL ");
$affected = pmb_mysql_affected_rows();
$query = pmb_mysql_query("delete from empr_custom_values where empr_custom_champ not in (select idchamp from empr_custom)");
$affected = pmb_mysql_affected_rows();

$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_suppr_relations"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_relations_cat"], ENT_QUOTES, $charset);
// mise ? jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec, '', '3');
