<?php
// +-------------------------------------------------+
// ? 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: surligner.js.php,v 1.1.2.2 2018-01-10 16:10:53 jpermanne Exp $

session_start();
?>
terms=new Array('<?php echo (isset($_SESSION['surligner_tableau'])?$_SESSION['surligner_tableau']:''); ?>');
terms_litteraux=new Array('<?php echo (isset($_SESSION['surligner_tableau_l'])?$_SESSION['surligner_tableau_l']:''); ?>');
codes=new Array();
<?php echo (isset($_SESSION['surligner_codes'])?$_SESSION['surligner_codes']:''); ?>

function remplacer_carac(mot) {
	var x;	
	var chaine;
	var reg;				
	chaine=mot;
	<?php echo (isset($_SESSION['surligner_key_carac'])?$_SESSION['surligner_key_carac']:''); ?>		
	return(chaine);		
}

function trouver_mots_f(obj,mot,couleur,litteral,onoff) {
	var i;
	var chaine;
	if (obj.hasChildNodes()) {
		var childs=new Array();
		childs=obj.childNodes;
		
		if (litteral != 0) {
			mot=remplacer_carac(reverse_html_entities(mot));
		}
		
		for (i=0; i<childs.length; i++) {
			
			if (childs[i].nodeType==3) {
				if (litteral==0){
					chaine=childs[i].data.toLowerCase();
					chaine=remplacer_carac(chaine);
				} else {
					chaine=childs[i].data;
					chaine=remplacer_carac(chaine);
				}
				 
				var reg_mot = new RegExp(mot+' *','gi');	
				if (chaine.match(reg_mot)) {
					var elt_found = chaine.match(reg_mot);
					var chaine_display = childs[i].data;
					var reg = 0;
					for(var k=0;k<elt_found.length;k++){
						reg = chaine.indexOf(elt_found[k],reg); 
						if (onoff==1) {
							after_shave=chaine_display.substring(reg+elt_found[k].length);
							sp=document.createElement('span');
							if (couleur % 6!=0) {
								sp.className='text_search'+couleur;
							} else {
								sp.className='text_search0';
							}
							nmot=document.createTextNode(chaine_display.substring(reg,reg+elt_found[k].length));
							childs[i].data=chaine_display.substring(0,reg);
							sp.appendChild(nmot);
						
							if (after_shave) {
								var aftern=document.createTextNode(after_shave);
							} else var aftern='';
						
							if (i<childs.length-1) {
								obj.insertBefore(sp,childs[i+1]);
								if (aftern) { obj.insertBefore(aftern,childs[i+2]); }
							} else {
								obj.appendChild(sp);
								if (aftern) obj.appendChild(aftern);
							}
							chaine_display ='';
							i++;
						} else {
							obj.replaceChild(childs[i],obj);
						}
					}
				}
			} else if (childs[i].nodeType==1){
				trouver_mots_f(childs[i],mot,couleur,litteral,onoff);
			}
		}
	}
}
		
function rechercher(onoff) {
	obj=document.getElementById('res_first_page');
	if (!obj) {
		obj=document.getElementById('resultatrech_liste');
		if(obj) if (obj.getElementsByTagName('blockquote')[0]) {
			obj=obj.getElementsByTagName('blockquote')[0];
		}
	}
	if (obj) {
		if (terms_litteraux[0]!='')
		{
			for (var i=0; i<terms_litteraux.length; i++) {
				trouver_mots_f(obj,terms_litteraux[i],i+terms.length,1,onoff);			
			}
		}
		if (terms[0]!='')
		{
			for (var i=0; i<terms.length; i++) {
				trouver_mots_f(obj,terms[i],i,0,onoff);			
			}
		}
	}
}