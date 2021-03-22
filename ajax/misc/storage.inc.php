<?php
// +-------------------------------------------------+
// � 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: storage.inc.php,v 1.3.2.1 2017-10-18 13:20:38 tsamson Exp $

require_once($class_path."/storages/storages.class.php");

switch($sub){
	case "upload" :
		$storage = storages::get_storage_class($id);
		if($storage){
			$filenames = $storage->upload_process();
		}
		if(!empty($filenames)){
			switch($type){
				case 'collection' :
					require_once($class_path."/cms/cms_collections.class.php");
					$collection = new cms_collection($id_collection);
					for($i=0 ; $i<count($filenames) ; $i++){
						print $collection->add_document($storage->get_uploaded_fileinfos($filenames[$i]),true,$from);
					}
					break;
			}
		}
		break;
	default :
		switch($action){
			case "get_params_form" :
				$storages = new storages();
				print $storages->get_params_form($class_name,$id);
				break;
		}
		break;
}