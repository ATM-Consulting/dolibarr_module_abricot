<?php
/*
 Copyright (C) 2003 Eric Moleon <eric.moleon@club-internet.fr>
 Copyright (C) 2003-2013 Alexis Algoud <azriel68@gmail.com>
 Copyright (C) 2013-2015 ATM Consulting <support@atm-consulting.fr>

 This program and all files within this directory and sub directory
 is free software: you can redistribute it and/or modify it under 
 the terms of the GNU General Public License as published by the 
 Free Software Foundation, either version 3 of the License, or any 
 later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

Class TFormCore {
var $type_aff='FORM'; //Type d'affichage du formulaire (FORM / VIEW )


//
var $js_validation=''; 


var $trans=array("\""=>'&quot;');
 /**
  * constructeur de la classe form
  * 
  * @return Form
  * @param pMethod String
  * @param pAction String
  * @param pName String
  * @desc constructeur de la classe form
  */
  
function __construct($pAction=null,$pName=null,$pMethod="POST",$pTransfert=FALSE,$plus=""){
// Modifié par AA 16/09/2004
// Je ne veux pas de cr�ation de formulaire syst�matique	
	if (!empty($pName)) {
	    echo $this->begin_form($pAction, $pName, $pMethod, $pTransfert, $plus);
	}
	
	// propriété de comparaison de string stricte si besoin!	
	$this->strict_string_compare = false;
}


function begin_form($pAction=null,$pName=null,$pMethod="POST",$pTransfert=FALSE,$plus="", $addToken = true) {
	
	$r='';
	if (!empty($pName)) {
	    $r.= '<form method="'.$pMethod.'"' ;
	    if ($pTransfert)
	      $r.=  ' ENCTYPE = "multipart/form-data"'; 
		if($plus)  $r.=  " $plus ";
	    
		if($pAction=='auto')$pAction=$_SERVER['PHP_SELF'];
		
	    $r.=  ' action="'.$pAction.'"';
	    $r.=  ' id="'.$pName.'"';
	    $r.=  ' name="'.$pName.'">';

	    if($addToken) $r.=  '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	}
	
	return $r;
}

function TForm_js($params=array()){
		
	global $action;
	global $obj;
	global $mode;
	
	$method = 'post';
	if(isset($params['method'])) $method = $params['method'];
	
	
	$form_action = (isset($params['form_action']))?$params['form_action']:"";
	
	// enctype="multipart/form-data" 
	$out = '<!-- TForm_js -->';
	$out .= '<form action="'.$form_action.'" method="'.$method.'" '; 
	
	if(@$params['multipart']==TRUE)$out .= ' enctype="multipart/form-data" ';
	
	$out .= ' name="formulaire" id="formulaire">'."\r\n";
  
	//$out .= '<input type="hidden" name="alerte[id]" value="'.$id_alerte.'">'."\r\n";
	
	//$out .= '<input type="hidden" name="p1" value="">'."\r\n";
	//$out .= '<input type="hidden" name="decal" value="">'."\r\n";
	
	$action = 'set_values';
	
	$out .= '<input type="hidden" name="debug" value="'.@DEBUG.'">'."\r\n";
	$out .= '<input type="hidden" name="action" value="'.$action.'">'."\r\n";
	$out .= '<input type="hidden" name="obj" value="'.$obj.'">'."\r\n";
	$out .= '<input type="hidden" name="mode" value="'.$mode.'">'."\r\n";
	
	if(isset($_REQUEST['url_go']) && $_REQUEST['url_go']!='') {
		$out .= '<input type="hidden" name="url_go" value="'.$_REQUEST['url_go'].'">'."\r\n";
	}
	
	$out .= '<!-- val hidden -->'."\r\n";
		
	$html_control_array = array('action'=>$action,'obj'=>$obj);
	if((isset($params['value']))&&(is_array($params['value']))){
		foreach($params['value'] as $key => $val){
			if(is_array($val)){
				foreach($val as $key_val => $val_val){
					$out .= '<input type="hidden" name="'.$key.'['.$key_val.']" value="'.$val_val.'">'."\r\n";
				}
			}
			else{
				$out .= '<input type="hidden" name="'.$key.'" value="'.$val.'">'."\r\n";
			}
		}
		$html_control_array = array_merge($html_control_array,$params['value']);
	}
	
	$out .= '<!-- getHtmlControl in form -->'.Tools::getHtmlControl($html_control_array);
	
	
	return $out;
}

function Set_typeaff($pType='edit'){
	
  $pType = strtolower($pType);  	
	
  if (($pType=='edit') || ($pType=='new'))
    $this->type_aff='edit';
  else
    $this->type_aff='view';  
}

function hidden($pName,$pVal,$plus=""){
  $field = '<input id="'.$pName.'" TYPE="HIDDEN" NAME="'.$pName.'" VALUE="'.$pVal.'" '.$plus.'> ';
  return $field;
} 

function hidden_js($array){
	$pName = "";
	if(isset($array['name']))$pName = $array['name'];
	
	$pVal = "";
	if(isset($array['value']))$pVal = $array['value'];
	
	$plus = "";
	if(isset($array['plus']))$plus = $array['plus'];
	
	
	$pId = "";
	if(isset($array['id']))$pId = $array['id'];
	if($pId == "")$pId = $pName;
	
	$pVal = htmlspecialchars($pVal,ENT_QUOTES);
	
	$class="";
	if((isset($array['required'])) && ($array['required']===TRUE)){
		$class .= ' required';
		
		if(isset($array['required_label']))$this->required_js($pName,$array['required_label'],TRUE,'text',$array);
		else $this->required_js($pName,'',TRUE,$type,$array);
		
	}
	
  	$field = '<input type="hidden" name="'.$pName.'" value="'.$pVal.'" id="'.$pId.'"  '.$plus.' class="'.$class.'">';
  	
  	
	
  	return $field;
} 


/*
 * fonction qui permet de récupérer un champ texte
 * 
 * @param array : pName : Nom, pVal : Valeur, pTaille : Taille ...
 * 
 */
 
public function text_js($array){
	global $viewStyle;
	
	$pLib = "";
	if(isset($array['label']))$pLib = $array['label'];
	
	$pName = "";
	if(isset($array['name']))$pName = $array['name'];

	//necessaire pour les validations JS de type equalTo 
	$pId = "";
	if(isset($array['id']))$pId = $array['id'];
	if($pId == "")$pId = $pName;

	$pVal = "";
	if(isset($array['value']))$pVal = $array['value'];

	$pTaille = "10";
	if(isset($array['size']))$pTaille = $array['size'];

	$pTailleMax = "0";
	if(isset($array['maxlength']))$pTailleMax = $array['maxlength'];

	//$plus = "";
	//if(isset($array['plus']))$pTaille = $array['plus'];

	$class = "text";
	if(isset($array['class']))$class = $array['class'];
	
	//EMAIL ...
	$type = '';
	if(isset($array['type']))$type = $array['type'];
	
	
	if((isset($array['required'])) && ($array['required']===TRUE)){
		$class .= ' required';
		
		if(isset($array['required_label']))$this->required_js($pName,$array['required_label'],TRUE,$type,$array);
		else $this->required_js($pName,'',TRUE,$type,$array);
		
		if($pLib!=''){
			
			$pLib .= " <span class=\"required\">*</span>";
			
		}
	}else	
	if((isset($array['required']))){
		
		//$class .= ' required';
		if(isset($array['required_label']))$this->required_js($pName,$array['required_label'],$array['required'],$type,$array);
		else $this->required_js($pName,'',$array['required'],$type,$array);
		
		if($pLib!=''){
			
			$pLib .= " <span class=\"required\">*</span>";
			
		}
	}
	
	
	if(isset($array['comment']))$pLib .= ' <span class="comment">'.$array['comment'].'</span>';
	
	
	
	$plus = '';
	if(isset($array['plus']))$plus = $array['plus'];
	
	// divs testés pour etre full compatible avec jquery :
	// $ret  = '<div id="div_'.$pId.'" class="text_js">';
	if(($viewStyle=='editable')||(@$array['viewStyle']=='editable')){
		$ret = $this->texte($pLib,$pName,$pVal,$pTaille,$pTailleMax,$plus,$class,$pId);
	}
	else $ret = '<span class="label_non_editable_textarea">'.$pLib.'</span><br>'.nl2br($pVal); ;
	
	//$ret .= '</div>';
	
	return($ret);
	
}



/**
 *	Override de la fonction classique de la class FormProject
 *  Show a combo list with projects qualified for a third party
 *
 *	@param	int		$socid      	Id third party (-1=all, 0=only projects not linked to a third party, id=projects not linked or linked to third party id)
 *	@param  int		$selected   	Id project preselected
 *	@param  string	$htmlname   	Nom de la zone html
 *	@param	int		$maxlength		Maximum length of label
 *	@param	int		$option_only	Option only
 *	@param	int		$show_empty		Add an empty line
 *	@return string         		    select or options if OK, void if KO
 */
function select_projects($socid=-1, $selected='', $htmlname='projectid', $maxlength=25, $option_only=0, $show_empty=1)
{
	global $user,$conf,$langs,$db;

	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

	$out='';

	if ($this->type_aff == 'view')
	{
		if ($selected > 0)
		{
			$project = new Project($db);
			$project->fetch($selected);
			
			return dol_trunc($project->ref,18).' - '.dol_trunc($project->title,$maxlength);
		}
		else 
		{
			return $out;			
		}
	}

	$hideunselectables = false;
	if (! empty($conf->global->PROJECT_HIDE_UNSELECTABLES)) $hideunselectables = true;

	$projectsListId = false;
	if (empty($user->rights->projet->all->lire))
	{
		$projectstatic=new Project($db);
		$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,1);
	}

	// Search all projects
	$sql = 'SELECT p.rowid, p.ref, p.title, p.fk_soc, p.fk_statut, p.public';
	$sql.= ' FROM '.MAIN_DB_PREFIX .'projet as p';
	$sql.= " WHERE p.entity = ".$conf->entity;
	if ($projectsListId !== false) $sql.= " AND p.rowid IN (".$projectsListId.")";
	if ($socid == 0) $sql.= " AND (p.fk_soc=0 OR p.fk_soc IS NULL)";
	if ($socid > 0)  $sql.= " AND (p.fk_soc=".$socid." OR p.fk_soc IS NULL)";
	$sql.= " ORDER BY p.ref ASC";

	dol_syslog(get_class($this)."::select_projects sql=".$sql,LOG_DEBUG);
	$resql=$db->query($sql);
	if ($resql)
	{
		if (empty($option_only)) {
			$out.= '<select class="flat" name="'.$htmlname.'">';
		}
		if (!empty($show_empty)) {
			$out.= '<option value="0">&nbsp;</option>';
		}
		$num = $db->num_rows($resql);
		$i = 0;
		if ($num)
		{
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				// If we ask to filter on a company and user has no permission to see all companies and project is linked to another company, we hide project.
				if ($socid > 0 && (empty($obj->fk_soc) || $obj->fk_soc == $socid) && ! $user->rights->societe->lire)
				{
					// Do nothing
				}
				else
				{
					$labeltoshow=dol_trunc($obj->ref,18);
					//if ($obj->public) $labeltoshow.=' ('.$langs->trans("SharedProject").')';
					//else $labeltoshow.=' ('.$langs->trans("Private").')';
					if (!empty($selected) && $selected == $obj->rowid && $obj->fk_statut > 0)
					{
						$out.= '<option value="'.$obj->rowid.'" selected="selected">'.$labeltoshow.' - '.dol_trunc($obj->title,$maxlength).'</option>';
					}
					else
					{
						$disabled=0;
						$labeltoshow.=' '.dol_trunc($obj->title,$maxlength);
						if (! $obj->fk_statut > 0)
						{
							$disabled=1;
							$labeltoshow.=' - '.$langs->trans("Draft");
						}
						if ($socid > 0 && (! empty($obj->fk_soc) && $obj->fk_soc != $socid))
						{
							$disabled=1;
							$labeltoshow.=' - '.$langs->trans("LinkedToAnotherCompany");
						}

						if ($hideunselectables && $disabled)
						{
							$resultat='';
						}
						else
						{
							$resultat='<option value="'.$obj->rowid.'"';
							if ($disabled) $resultat.=' disabled="disabled"';
							//if ($obj->public) $labeltoshow.=' ('.$langs->trans("Public").')';
							//else $labeltoshow.=' ('.$langs->trans("Private").')';
							$resultat.='>';
							$resultat.=$labeltoshow;
							$resultat.='</option>';
						}
						$out.= $resultat;
					}
				}
				$i++;
			}
		}
		if (empty($option_only)) {
			$out.= '</select>';
		}

		$db->free($resql);
		return $out;
	}
	else
	{
		dol_print_error($db);
		return '';
	}
}






/*
 *	ajoute les valdiations javascript  
 *
 * 	messages: {
		required: "This field is required.",
		remote: "Please fix this field.",
		email: "Please enter a valid email address.",
		url: "Please enter a valid URL.",
		date: "Please enter a valid date.",
		dateISO: "Please enter a valid date (ISO).",
		number: "Please enter a valid number.",
		digits: "Please enter only digits.",
		creditcard: "Please enter a valid credit card number.",
		equalTo: "Please enter the same value again.",
		accept: "Please enter a value with a valid extension.",
		maxlength: $.validator.format("Please enter no more than {0} characters."),
		minlength: $.validator.format("Please enter at least {0} characters."),
		rangelength: $.validator.format("Please enter a value between {0} and {1} characters long."),
		range: $.validator.format("Please enter a value between {0} and {1}."),
		max: $.validator.format("Please enter a value less than or equal to {0}."),
		min: $.validator.format("Please enter a value greater than or equal to {0}.") 
 * 
 */

private function required_js($name,$lib = "",$required='TRUE',$type='text',$params = array()){
	if($lib=="")$lib = "Champ requis";
	$this->js_validation.= '// champ requis : '.$name." (id)\r\n";
	
	$messages = '';

    $formid = '';
	if(isset($params['formid']) && $params['formid']!=''){
		$formid = '#'.$params['formid'].' ';
	}
			
	switch((string)$required){
		/*case 'depends':
			// dependant ! 
			$this->js_validation.= '$(\''.$formid.'input[name="'.$name.'"]\').rules("add", { required: '.$params['depends'].', '."\r\n";
			$messages .= '					required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'", '."\r\n";
			break;*/
		case 'depends':
		    $name_clean = strtr($name,array('['=>'_',']'=>'_'));
			if(!isset($params['depends_label']) || empty($params['depends_label']))$params['depends_label']='Champs requis';
			//$messages .= '					depends_'.$name_clean.': "'.htmlspecialchars ($params['depends_label'],ENT_QUOTES) .'", '."\r\n";			
			$this->js_validation = '
			$.validator.addMethod(\'depends_'.$name_clean.'\', function(value, element, param) { 
			    '.$params['depends'].'  
			},"'.htmlspecialchars ($params['depends_label'],ENT_QUOTES) .'");
			'."\r\n".$this->js_validation;
			
			// dependant ! 
			$this->js_validation.= '$(\''.$formid.'input[name="'.$name.'"]\').rules("add", {  depends_'.$name_clean.': true, '."\r\n";
			//$messages .= '					required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'", '."\r\n";
			break;
			
		default:
			// requis 
			$this->js_validation.= '$(\''.$formid.'input[name="'.$name.'"]\').rules("add", { required: true, '."\r\n";
			$messages .= '					required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'" '."\r\n";
	}
	
		
	if($type=='email'){
		$this->js_validation.= ' email:true, '."\r\n";
		$messages .= ' 				'.(($messages!='')?',':'').'email: "Email invalide" '."\r\n";
	}


	if(isset($params['remote'])){
		$this->js_validation.= ' remote: "'.$params['remote'].'", '."\r\n";			
		//$messages .= ' 				remote: "invalide!!", '."\r\n";
		//$messages .= ' 				remote: jQuery.format("réponse serveur : {0}") , '."\r\n";
		$messages .= ' 				'.(($messages!='')?',':'').'remote: jQuery.format("ce '.$params['label'].' {0}, n\'est pas autorisé.")  '."\r\n";
	}
	
	if(isset($params['equalTo'])){
		//id de l'autre champ qui doit etre identique (pas le nom!)
		$this->js_validation.= ' equalTo:"#'.$params['equalTo'].'", '."\r\n";
		//$this->js_validation.= ' equalTo:"name=Treponse\[emailfrom\]", '."\r\n";
		$messages .= ' 				'.(($messages!='')?',':'').'equalTo: "confirmation erronée" '."\r\n";
	}
	
	
	if(isset($params['accept'])){
		if(empty($params['accept']))$params['accept']='jpg';
		if(!isset($params['accept_label']) || empty($params['accept_label']))$params['accept_label']='Extension non acceptée';
		$this->js_validation.= ' accept:"'.$params['accept'].'", '."\r\n";
		$messages .= ' 				'.(($messages!='')?',':'').'accept: "'.$params['accept_label'].'" '."\r\n";
	}
	
	if(isset($params['minlength'])){
		if(!isset($params['minlength_label']) || empty($params['minlength_label']))$params['minlength_label']='{0} caractères minimum';
		$this->js_validation.= ' minlength:'.$params['minlength'].', '."\r\n";
		$messages .= ' 				'.(($messages!='')?',':'').'minlength: "'.$params['minlength_label'].'" '."\r\n";
	}
	if(isset($params['digits'])){
		if(!isset($params['digits_label']) || empty($params['digits_label']))$params['digits_label']='Seulement les chiffres sont admis!';
		$this->js_validation.= ' digits:'.(((bool)$params['digits']===true)?'true':'"'.$params['digits'].'"').', '."\r\n";
		$messages .= ' 				'.(($messages!='')?',':'').'digits: "'.$params['digits_label'].'" '."\r\n";
	}
	//messages
	//if($messages!='') 
	$this->js_validation.= ' messages: {  '.$messages.' }';
		
	$this->js_validation.= '    });'."\r\n \r\n";
	
	if(isset($params['digits'])){
		$this->js_validation.= '$(\''.$formid.'input[name="'.$name.'"]\').ForceNumericOnly();'."\r\n \r\n";
	}
}


/*
function buttonset_js($id){
	$this->js_validation.= "//buttonset \r\n";
	$this->js_validation.= '$(\'input[name="'.$name.'"]\').rules("add", {
	$this->js_validation.= " \r\n";
	$this->js_validation.= " \r\n";
	
}
*/



/*
 * fonction qui permet de récupérer un champ texte
 * 
 * @param pLib : libellé
 * @param pName : Nom
 * @param pVal : Valeur
 * @param pTaille : Taille
 * 
 */
 
function texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default=''){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<label for=".$pName.">$pLib</label>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='view'){
  	$field='<input class="'.$class.'" type="text" id="'.$pName.'" name="'
  	.$pName.'" value="'.strtr($pVal,$this->trans).'" size="'.$pTaille.'" maxlength="'
  	.$pTailleMax.'" '.$plus.'>'."\n";  
  
 }
  else
    $field = ($pVal=='')?$default:strtr($pVal,$this->trans)." \n ";
//    $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}
/*
 * fonction qui permet de récupérer un champ texte
 * 
 * @param pLib : libellé
 * @param pName : Nom
 * @param pVal : Valeur
 * @param pTaille : Taille
 * 
 */
 
function number($pLib,$pName,$pVal,$pTaille,$step=1,$min=null,$max=null,$pTailleMax=0,$plus='',$class="text", $default=''){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<label for=".$pName.">$pLib</label>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='view'){
  	$field='<input class="'.$class.'" step="'.$step.'" type="number" id="'.$pName.'" name="'
  	.$pName.'" value="'.strtr($pVal,$this->trans).'" size="'.$pTaille.'" maxlength="'.$pTailleMax.'" '
  	.(!is_null($min) ? ' min="'.$min.'" ' :'')
  	.(!is_null($max) ? ' max="'.$max.'" ' :'')
  	.$plus.'>'."\n";  
  
 }
  else
    $field = ($pVal=='')?$default:strtr($pVal,$this->trans)." \n ";
//    $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}


function texte_search_predictive($pLib,$pNameId,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text",$source='annonce_emploi'){
  $lib="";
  $field="";
  if ($pLib!="") $lib   = '<label for="'.$pNameId.'"><b>$pLib</b></label>';
  
  if ($pTailleMax==0) $pTailleMax=$pTaille;
  
  $field = "<INPUT class='text_view' TYPE='TEXT' ID='$pNameId' NAME='$pNameId' VALUE=\"".strtr($pVal,$this->trans)."\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n ";
  
  if ($lib != '') $field = $lib." ".$field;

    $field .= '<script>

              $("#'.$pNameId.'").autocomplete({
                        source: "'.DIR_HTTP.'batisearch.php?get=search&t=&ttl=100&source='.$source.'
                       ,minLength: 2
                       ,select: function(event, ui) {
                                  //location.replace("?action=VIEW&id="+ui.item.value);
                                  return false; 
                                }
                            });
             </script>';
             
    return $field;
}

function doliCalendar($pName, $pVal) {
global $langs,$db;	
	
	
	if(is_int($pVal)) {
		$time = $pVal;	
	}
	else if(strpos($pVal,'-')!==false ) {
	  		$time = strtotime($pVal);
	}
	else {
	  		$time = Tools::get_time($pVal);
	}
	
	if ($this->type_aff!='view'){
		$formDoli=new Form($db);
		return $formDoli->select_date($time, $pName,0, 0, 0, "", 1, 0, 1);
	}
	else {
		
		return dol_print_date($time, $langs->trans('FormatDateShort')) ;
	}
	
}

function calendrier($pLib,$pName,$pVal,$pTaille=12,$pTailleMax=10,$plus='',$class='text',$format='d/m/Y'){
  /* jquery datepicker requis */
  
  $id = strtr($pName,array('['=>'_', ']'=>'_'));
  
  
  if(empty($pVal)) {
  	$dateValue='';
  }
  else if((is_numeric($pVal) && $pVal<=0) || substr($pVal,0,10)=='0000-00-00') {
  	$dateValue='';
  }
  elseif(strpos($pVal,'-')!==false || strpos($pVal,'/')!==false ) {
  		$dateValue = $pVal;
  }
  else {
  		$dateValue =  date($format,$pVal);
  }
  
  
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='view'){
    $field = '<input class="'.$class.' datepicker" TYPE="TEXT" id="'.$id.'" name="'.$pName.'" value="'.$dateValue.'" SIZE="'.$pTaille.'" MAXLENGTH="'.$pTailleMax.'" '.$plus.'> ';
  
    
    $field .= '<script type="text/javascript">
               $(function() {
  			        $( "#'.$id.'" ).datepicker({
  			        	 showAnim: ""
  			        	 ,constrainInput: true
  			        	 ,changeYear: true
  			        	 ,autoSize: false 
  			        	 ,dateFormat: "dd/mm/yy"
					});
  			    });
               </script>';
  }
  else {
  		$field = $dateValue;
  }
    


  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}


function timepicker($pLib,$pName,$pVal,$pTaille=12,$pTailleMax=10,$plus='',$class='text',$format='H:i', $minTime = '', $maxTime = ''){
  /* jquery datepicker requis */
  
  $id = strtr($pName,array('['=>'_', ']'=>'_'));
  
  
  if(empty($pVal)) {
  	$dateValue='';
  }
  elseif(strpos($pVal,':')!==false  ) {
  		$dateValue = $pVal;
  }
  else {
  		$dateValue =  date($format,$pVal);
  }
  
  
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='view'){
    $field = '<input class="'.$class.' datepicker" TYPE="TEXT" id="'.$id.'" name="'.$pName.'" value="'.$dateValue.'" SIZE="'.$pTaille.'" MAXLENGTH="'.$pTailleMax.'" '.$plus.'> ';
  
    
    $field .= '
	<script type="text/javascript">
			if(typeof(core_timepicker_already_included)=="undefined") {
					document.write(\'<script type="text/javascript" src="'.COREHTTP.'includes/js/timepicker/jquery.timepicker.min.js"></sc\'+\'ript>\');
					document.write(\'<link href="'.COREHTTP.'includes/js/timepicker/jquery.timepicker.css" rel="stylesheet" type="text/css" />\');
			}

			var core_timepicker_already_included=true;
					
            $(document).ready(function() {
		        $( "#'.$id.'" ).timepicker({
		        	 timeFormat: "'.$format.'"
		        	 ,setTime: '.strtotime(date('Y-m-d').' '.$dateValue).' 
		        	 ,step: 15
		        	 ,minTime: "'.$minTime.'"
		        	 ,maxTime: "'.$maxTime.'"
				});
		    });
	</script>';
  }
  else {
  		$field = $dateValue;
  }
    


  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function zonetexteXP($pLib,$pName,$pVal,$pTaille,$pHauteur=5,$plus='width="100%"',$class='text'){
 // installation de CKEDITOR obligatoire
 $class .= ' isCkeditor';
 $r = $this->zonetexte($pLib,$pName,$pVal,$pTaille,$pHauteur,$plus,$class);
	$custom_config = 'customConfig : \'./config_light.js\',';
 $r.='
  <SCRIPT LANGUAGE="javascript">
	CKEDITOR.replace( \''.$pName.'\',{
    '.$custom_config.'
    resize_enabled : false
    } );
	</SCRIPT>
'; 


  return $r;
}


/*
 * fonction qui permet de récupérer un champ d'upload de fichier
 * 
 * @param array : pName : Nom, pVal : Valeur, pTaille : Taille ...
 * 
 */
function file_js($array){
	//a jouter dans le core par la suite!
	require_once(DIR_INCLUDE."class/class.upload.php");
	
	//global $viewStyle;
	
	$pName = "userfile[]";
	//if(isset($array['name']))$pName = $array['name'];
	
	//l'id est necessaire pour la validation JS 
	$id = "";
	if(isset($array['id']))$id = $array['id'];
	else $id=$pName;
	
	//apres l'id et le name
	$pLib = "";
	if(isset($array['label']))$pLib = '<label class="label-file" for="'.$id.'">'.$array['label'].'</label>';
	
	$pVal = "";
	
	/*
	if(isset($array['value'])){
		$pVal = $array['value'];
		
		$pVal = str_replace('<br>',"\r\n",$pVal);
		
		$pVal = trim($pVal);
		
		$pVal = strip_tags($pVal);
	}	*/
	
	$pTaille = "10";
	if(isset($array['size']))$pTaille = $array['size'];
	
	$pTailleMax = "-1";
	if(isset($array['maxlength']))$pTailleMax = $array['maxlength'];
	
	$plus = "";
	//if(isset($array['plus']))$pTaille = $array['plus'];

	$class = "text";
	if(isset($array['class']))$class = $array['class'];	
	
	
	$this->fileUpload = new Upload();
	$this->fileUpload->Required = true;
	
	$date_dir = date('Ymd');
	
	//$this->fileUpload->DirUpload = DIR_UPLOAD.'xxx/'.$date_dir.'/';
	$this->fileUpload->FieldOptions= 'size="41"';
	
	
	if(isset($array['required'])&&($array['required']===TRUE)){
		$required = $array['required'];
		$this->required_file_js($id,$array['required_label'],$required='TRUE',$array);
		if($pLib!=''){
			
			$pLib .= " <span class=\"required\">*</span>";
			
		}
	}
	else
	if((isset($array['required']))&&($array['required']==='depends') && (isset($array['depends']))&&($array['depends']!='')){
		
		$class .= ' required';
		$required = $array['required'];
		if(isset($array['required_label']))$this->required_file_js($id,$array['required_label'],$required,$array);
		else $this->required_file_js($pName,'',$required,$type,$array);
		
		if($pLib!=''){
			
			$pLib .= " <span class=\"required\">*</span>";
			
		}
	}else
	if(isset($array['required'])){
		$required = $array['required'];
		if(isset($array['required_label']))$this->required_file_js($id,$array['required_label'],$required,$array);
		else $this->required_file_js($pName,'',$required,$type,$array);
		
		if($pLib!=''){
			
			$pLib .= " <span class=\"required\">*</span>";
			
		}
	}	
		
	$out = $this->fichier($pLib,$pName,$pVal,$pTaille,$pTailleMax,$plus,$class,$id);
	return(''.$out.'');
	
}



private function required_file_js($id,$lib = "",$required='TRUE',$params=array()){
	
	if($lib=="")$lib = "Champ requis";
	
	$message = '';
	
	$this->js_validation.= '// champ file requis : id : '.$id." (id)\r\n";
	//$this->js_validation.= '$(\'file[name="'.$name.'"]\').rules("add", {
	

	$formid = '';
	if(isset($params['formid']) && $params['formid']!=''){
		$formid = '#'.$params['formid'].' ';
	}
			
	switch((string)$required){
		case 'depends':
			$id_clean = strtr($id,array('['=>'_',']'=>'_'));
			if(!isset($params['depends_label']) || empty($params['depends_label']))$params['depends_label']='Extension non acceptée';
			//$message .= '					depends_'.$id_clean.': "'.htmlspecialchars ($params['depends_label'],ENT_QUOTES) .'", '."\r\n";			
			
			$this->js_validation = '
			$.validator.addMethod(\'depends_'.$id_clean.'\', function(value, element, param) { 
			    '.$params['depends'].'  
			},"'.htmlspecialchars ($params['depends_label'],ENT_QUOTES) .'");
			'."\r\n".$this->js_validation;
			
			// dependant ! 
			$this->js_validation.= '$(\''.$formid.'#'.$id.'\').rules("add", {  depends_'.$id_clean.': true, '."\r\n";
			//$message .= '					required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'", '."\r\n";
			break;
			
		case 'dummy_required':
			$this->js_validation.= '$(\''.$formid.'#'.$id.'\').rules("add", { '."\r\n";
			break;
			
		default:
			// requis 
			$this->js_validation.= '$(\''.$formid.'#'.$id.'\').rules("add", { required: true, '."\r\n";
			$message .= '					'.(($message!='')?',':'').'required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'" '."\r\n";
	}
	
	/*$this->js_validation.= '$(\'#'.$id.'\').rules("add", {
	   required: true, ';
	$message .= ' required: "'.htmlspecialchars ($lib,ENT_QUOTES).'",';
	*/
	
	if(isset($params['accept'])){
		//$this->js_validation.= ' accept:"'.$params['accept'].'", ';
		//$message .= ' accept:"Cette extension n\'est pas autorisée, seul les fichiers de type : <b>'.str_replace('|',', ',$params['accept']).'</b> sont acceptés", ';
		if(empty($params['accept']))$params['accept']='jpg';
		if(!isset($params['accept_label']) || empty($params['accept_label']))$params['accept_label']='Extension non acceptée';
		$this->js_validation.= ' accept:"'.$params['accept'].'", '."\r\n";
		$message .= ' 				'.(($message!='')?',':'').'accept: "'.$params['accept_label'].'" '."\r\n";
	}
	
	
	if(isset($params['filesize']) && $params['filesize']>0){
		if(strpos($this->js_validation,"addMethod('filesize',")===false){
			$this->js_validation = '
			$.validator.addMethod(\'filesize\', function(value, element, param) { 
			    // param = size (en bytes)  
			    // element = element to validate (<input>) 
			    // value = value of the element (file name)
			    //alert(element.id);
				//alert(element.files.length);
				if(element.files.length<1)return true;
				//alert(this.optional(element) || (element.files[0].size <= param));
				return this.optional(element) || (element.files[0].size <= param);
			});
			'."\r\n".$this->js_validation;
		}
		
		if(!isset($params['filesize_label']) || empty($params['filesize_label']))$params['filesize_label']='Le fichier est trop volumineux';		
		$this->js_validation.= ' filesize:"'.($params['filesize']*1024).'", '."\r\n";
		$message .= ' 				'.(($message!='')?',':'').'filesize: "'.$params['filesize_label'].'" '."\r\n";
	}
	 

		
	$this->js_validation.= ' messages: { '.$message.'  } ';
		 
	$this->js_validation.= '	});'."\r\n \r\n";
	
	// inutile sur les input file
	// $this->js_validation.= '$(\'input[name="'.$name.'"]\').buttonset();'."\r\n";

}



/*
 * fonction qui permet de récupérer un champ texte
 * 
 * @param array : pName : Nom, pVal : Valeur, pTaille : Taille ...
 * 
 */
 
function textarea_js($array){
	global $viewStyle;
	
	$pName = "";
	if(isset($array['name']))$pName = $array['name'];
	
	$id = "";
	if(isset($array['id']))$id = $array['id'];
	
	//apres l'id et le name
	$pLib = "";
	if(isset($array['label']))$pLib = '<label for="'.$id.'">'.$array['label'].'</label>';
	
	$ckeditor = false;
	if(isset($array['ckeditor']))$ckeditor = $array['ckeditor'];
	
	$pVal = "";
	if(isset($array['value'])){
		$pVal = $array['value'];
		if(!$ckeditor){
			$pVal = str_replace('<br>',"\r\n",$pVal);
			
			$pVal = trim($pVal);
			
			$pVal = strip_tags($pVal);
		}
	}
	
	$pTaille = "10";
	if(isset($array['size']))$pTaille = $array['size'];
	if(isset($array['cols']))$pTaille = $array['cols'];
	
	
	$pHauteur = 5;
	if(isset($array['rows']))$pHauteur = $array['rows'];
	
	$plus = "";
	if(isset($array['plus']))$pTaille = $array['plus'];
	
	
	$onChange = "";
	if(isset($array['onChange']))$onChange = $array['onChange'];
	
	if($onChange!=''){
		$plus .= ' onChange="'.$onChange.'" ';	
	}
	
	$onkeyup = "";
	if(isset($array['onkeyup']))$onkeyup = $array['onkeyup'];
	
	if($onkeyup!=''){
		$plus .= ' onkeyup="'.$onkeyup.'" ';	
	}
	
	$class = "text";
	if(isset($array['class']))$class = $array['class'];
	
    
	
	
	
	
	
	// champ requis
	if((isset($array['required']))&&($array['required']==TRUE)){
	
		$class .= ' required';
		
		if(isset($array['required_label']))$this->required_textarea_js($pName,$array['required_label'],TRUE,$array);
		else $this->required_textarea_js($pName,'',TRUE,$array);
		
		
		
		if($pLib!=''){
			
			$pLib .= " <span class=required>*</span>";
			
		}
	}
	
	
	if(($viewStyle=='editable')||(@$array['viewStyle']=='editable')){
		if($ckeditor){
			$out = $this->zonetexteXP($pLib,$pName,$pVal,$pTaille,$pHauteur,$plus,$class);
		}else{
			$out = $this->zonetexte($pLib,$pName,$pVal,$pTaille,$pHauteur,$plus,$class,$id);
		}
	}
	else $out = '<span class="label_non_editable_textarea">'.$pLib.'</span><br>'.nl2br($pVal); 
	
	
	
	return($out);
	
	
}




private function required_textarea_js($name,$lib="",$required='TRUE',$array=array()){
	if(strpos($this->js_validation,"validator.setDefaults({ ignore: '' });")===false){
		$this->js_validation = '
		$.validator.setDefaults({ ignore: \'\' });
		'."\r\n".$this->js_validation;
	}  
	
	if($lib=="")$lib = "Champ requis";
	//$name = strtr($name,array('['=>'\\\\\\[',']'=>'\\\\\\]'));
	$this->js_validation.= '// champ requis : '.$name." (id)\r\n";
	$this->js_validation.= '$(\'textarea[name="'.$name.'"]\').rules("add", {
	   required: true, ';
	$message = '';
	if(isset($array['minlength'])){
		$this->js_validation.= ' minlength:'.$array['minlength'].', '."\r\n";
		if(isset($array['minlength_label']))$message = ' '.(($message!='')?',':'').'minlength: $.validator.format("'.$array['minlength_label'].'") ';
		else $message = ' '.(($message!='')?',':'').'minlength: $.validator.format("Entrez au moins {0} charactères.") ';
	}
	
	$this->js_validation.= '    messages: {  required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'"'.(($message!='')?',':'').'  '.$message.' } ';
		 
    
	$this->js_validation.= ' });'."\r\n \r\n";
	// inutile sur les input text
	// $this->js_validation.= '$(\'input[name="'.$name.'"]\').buttonset();'."\r\n";

}





/*
 * fonction qui permet de récupérer un textarea
 * 
 * @param pLib : libellé
 * @param pName : Nom
 * @param pVal : Valeur
 * @param pTaille : Taille
 * 
 */
function zonetexte($pLib,$pName,$pVal,$pTaille,$pHauteur=5,$plus='',$class='text',$pId='', $nl2br=false){
  $lib="";
  $field="";
  
  if($pId==''){
	$pId = $pName;
  }
  
  if ($pLib!=""){
    $lib   = "<b> $pLib </b>";
  }
  
  if ($this->type_aff!='view'){
  	$field = "<textarea class='$class' name=\"$pName\" id=\"$pId\" cols=\"$pTaille\" rows=\"$pHauteur\" $plus>$pVal</textarea>\n";
  }
  else{
  	$field = $pVal;
  }
  if($nl2br && $this->type_aff=='view') $field = nl2br($field);
//    $field = $pVal;
  if ($lib != ''){
    return $lib." ".$field;
  }
  else{
    return $field;
  }
}



function fichier($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class='text',$id=''){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  
  if($id=='')$id=$pName;
  
  if ($this->type_aff!='VIEW')
    $field = "<INPUT id='$id' class='$class' TYPE='FILE' NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' $plus>\n ";
  else
    $field = "<INPUT id='$id' class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n ";
//    $field = $pVal;
  if ($lib != '')
    return ''.$lib.' '.$field;
  else
    return $field;
}

function texteRO($pLib,$pName,$pVal,$pTaille,$pTailleMax=0, $plus=""){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='view')
      $field = "<INPUT class='text_readonly' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"".strtr($pVal,$this->trans)."\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' $plus>\n ";
  else
    $field = ($pVal=='')?$default:strtr($pVal,$this->trans)." \n ";
//      $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function filetexteRO($pFile,$pLib,$pName,$pVal,$pTaille,$pTailleMax=0, $plus=""){
global $app;
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  if ($this->type_aff!='VIEW'){
      $field = "<INPUT class='text_readonly' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax' $plus>\n ";
  }
  else if ($pVal=="") {
  		$field = "<b>Aucun fichier li�</b>";
           
  }
  else {
    	$field = "<a class=lienquit href=\"javascript:showPopup('../dlg/get_file.php','','$pFile',400,400);\">".$pVal."</a>";
  }
//      $field = $pVal;
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function memo($pLib,$pName,$pVal,$pLig,$pCol, $plus=""){
  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b><br>";
  if ($this->type_aff!='VIEW')
      $field ="<TEXTAREA Class='text' NAME='$pName' ROWS='$pLig' COLS='$pCol' $plus>$pVal</TEXTAREA>\n";
  else
      $field ="<TEXTAREA Class='text_view' NAME='$pName' READONLY TABINDEX=-1 ROWS='$pLig' COLS='$pCol'>$pVal</TEXTAREA>\n";
//      $field = nl2br($pVal);

  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}



/*
 * fonction qui permet de récupérer un champ texte
 * 
 * @param array : pName : Nom, pVal : Valeur, pTaille : Taille ...
 * 
 */
 
public function password_js($array){
	global $viewStyle;
	
	$pLib = "";
	if(isset($array['label']))$pLib = $array['label'];
	
	$pName = "";
	if(isset($array['name']))$pName = $array['name'];

	//necessaire pour les validations JS de type equalTo 
	$pId = "";
	if(isset($array['id']))$pId = $array['id'];
	if($pId == "")$pId = $pName;

	$pVal = "";
	if(isset($array['value']))$pVal = $array['value'];

	$pTaille = "10";
	if(isset($array['size']))$pTaille = $array['size'];

	$pTailleMax = "0";
	if(isset($array['maxlength']))$pTailleMax = $array['maxlength'];

	//$plus = "";
	//if(isset($array['plus']))$pTaille = $array['plus'];

	$class = "text";
	if(isset($array['class']))$class = $array['class'];
	
	//EMAIL ...
	$type = '';
	if(isset($array['type']))$type = $array['type'];
	
	
	if((isset($array['required']))&&($array['required']==TRUE)){
		
		$class .= ' required';
		
		if(isset($array['required_label']))$this->required_js($pName,$array['required_label'],TRUE,$type,$array);
		else $this->required_js($pName,'',TRUE,$type,$array);
		
		if($pLib!=''){
			
			$pLib .= " <span class=required>*</span>";
			
		}
	}
	
	
	
	
	if(isset($array['comment']))$pLib .= ' <span class="comment">'.$array['comment'].'</span>';
	
	
	
	$plus = '';
	
	// divs testés pour etre full compatible avec jquery :
	// $ret  = '<div id="div_'.$pId.'" class="text_js">';
	if($viewStyle=='editable')$ret = $this->password($pLib,$pName,$pVal,$pTaille,$pTailleMax,$plus,$class,$pId);
	else $ret = $pVal;
	
	//$ret .= '</div>';
	
	return($ret);
	
}


function password($pLib,$pName,$pVal,$pTaille,$pTailleMax=0, $plus="" ,$class="text",$pId=""){
  $lib="";
  $field="";

  if($pId=="")$pId=$pName;
  
  if ($pLib!=""){
    $lib   = "<label for=".$pId.">$pLib</label>";
  }
  if ($pTailleMax==0) 
     $pTailleMax=$pTaille;
  $field = '<input class="'.$class.'" TYPE="PASSWORD" id="'.$pId.'" NAME="'.$pName.'" VALUE="'.$pVal.'" SIZE="'.$pTaille.'" MAXLENGTH="'.$pTailleMax.'"  '.$plus.' />';
  return $lib." ".$field;
}




function combo_js($array){



	$pLib = "";
	if(isset($array['label']))$pLib = $array['label'];
	
	$pName = "";
	if(isset($array['name']))$pName = $array['name'];
	
	$plus = "";
	//if(isset($array['plus']))$plus = $array['plus'];
	// multiple
	if((isset($array['multiple']))&&($array['multiple']==TRUE)){
		$plus.=" multiple";
		$pName = $pName.'[]';
	}

	$pListe = array();
	if(isset($array['options']))$pListe = $array['options'];

	$pDefault = "";
	if(isset($array['default_value']))$pDefault = $array['default_value'];


// $pTaille=1,$onChange='',$plus='

	
	$pTaille = "1";
	if(isset($array['size']))$pTaille = $array['size'];
	
	$onChange = '';
	if(isset($array['onChange']))$onChange = $array['onChange'];
	
	
	
	$class = "text";
	if(isset($array['class']))$class = $array['class'];
	
	$id = "";
	if(isset($array['id']))$id = $array['id'];


	// champ requis
	if((isset($array['required']) && $array['required']==TRUE) || isset($array['custom_function'])){
	    if(isset($array['required']) && $array['required']==TRUE){
			$class .= ' required';
		}
				
		if(isset($array['required_label']) || isset($array['custom_function']))$this->required_combo_js($pName,$array);
		//else $this->required_combo_js($pName);
		
		if($pLib!=''){
			
			$pLib .= ' <span class="required">*</span>';
			
		}
	}
	
	
	
	// multiple
	if((isset($array['multiple']))&&($array['multiple']==TRUE)){
		$plus.=" multiple";
	}
	
	
	
	// $pListe['Mois...']=0;

	$out = $this->combo($pLib,$pName,$pListe,$pDefault,$pTaille,$onChange,$plus,$class,$id);
	
	return($out);
	

//pLib,$pName,$pListe,$pDefault,$pTaille=1,$onChange='',$plus=''

}

function combo($pLib,$pName,$pListe,$pDefault,$pTaille=1,$onChange='',$plus='',$class='flat',$id='',$multiple='false', $showEmpty=0){
// AA : 16/09/2004
// Ajout du onChange

	if(empty($pListe)) return '[Liste de valeur manquante]';

	$lib="";
	$field="";
		
    if($id=='')$id=$pName;
    
	if ($pLib!="")
	  //$lib   = "<b> $pLib </b>";
	 $lib   = "<label for=".$id.">$pLib</label>";
	
	$field = '<SELECT NAME="'.$pName.'"';
	
	$field.=' id="'.$id.'"';
  
	if ($onChange!='') {
	  $field.=" onChange=\"".$onChange."\"";
	}
	if ($multiple!='false') {
	  $field.=" multiple";
	}
	if($class!=''){
		$field.=' class="'.$class.'"';
	}
	
	if($pTaille!='1'){
		$field.=' SIZE="'.$pTaille.'" ';
	}
	
	if($plus!=''){
		$field.=" ".$plus;
	}
	
	
	$field.=">\n";
	$field.="<!-- options -->";
    if ($showEmpty)
    {
    	//echo 'test';
    	$textforempty=' ';
    	if (! empty($conf->use_javascript_ajax)) $textforempty='&nbsp;';	// If we use ajaxcombo, we need &nbsp; here to avoid to have an empty element that is too small.
        $valueofempty=-1;
		
		if($showEmpty<0){
			$valueofempty=$showEmpty;
		}
		
        $field.='<option value="'.($show_empty < 0 ? $show_empty : -1).'"'.( $pDefault==$valueofempty ?' selected':'').'>'.$textforempty.'</option>'."\n";     // id is -2 because -1 is already "do not contact"
    }
  	
  	$field.=$this->_combo_option($pListe, $pDefault);
	
  $field .="</SELECT>";
  
  if ($this->type_aff =='view'){	  
    if (isset($pListe["$pDefault"])){
    	if(is_array($pListe["$pDefault"])) {
  			$val = $pListe["$pDefault"]['label'];
  		}
		else {
			$val=$pListe["$pDefault"]; 
		}
	}
	else{
		$val="";
	} 
   // $field = "<INPUT class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$val\" SIZE='$pTaille'>\n ";
    $field = $val;
  }
 	
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}

function combo_sexy($pLib,$pName,$pListe,$pDefault,$pTaille=1,$onChange='',$plus='',$class='flat',$id='',$multiple='false', $showEmpty=0){
// AA : 16/09/2004
// Ajout du onChange
	global $conf;

	if(empty($pListe)) return '[Liste de valeur manquante]';

	$lib="";
	$field="";
		
    if($id=='')$id=$pName;
	
	$minLengthToAutocomplete=0;
	$tmpplugin=empty($conf->global->MAIN_USE_JQUERY_MULTISELECT)?constant('REQUIRE_JQUERY_MULTISELECT')?constant('REQUIRE_JQUERY_MULTISELECT'):'select2':$conf->global->MAIN_USE_JQUERY_MULTISELECT;
	$field.='<!-- JS CODE TO ENABLE '.$tmpplugin.' for id '.$id.' -->
			<script type="text/javascript">
				$(document).ready(function () {
					$(\''.(preg_match('/^\./',$id)?$id:'#'.$id).'\').'.$tmpplugin.'({
				    dir: \'ltr\',
					width: \'resolve\',		/* off or resolve */
					minimumInputLength: '.$minLengthToAutocomplete.'
				});
			});
		   </script>';
	
	$field .= $this->combo($pLib,$pName,$pListe,$pDefault,$pTaille,$onChange,$plus,$class,$id,$multiple,$showEmpty);
    return $field;
}

private function _combo_option($Tab, $pDefault) {
 $field ='';		

  foreach($Tab as $val=>$option) {	 
  			
		if(is_array($option) && !isset($option['label'])) {
			
			$field .='<optgroup label="'.$val.'">';
			$field .= $this->_combo_option($option, $pDefault);
			$field .='</optgroup>';
		}	
		else {
			$moreAttributs = '';
	  		if(is_array($option)) {
	  			$libelle = $option['label'];
				
				foreach($option as $k=>$v) {
					
					if($k!='label') {
						$moreAttributs.=' '.$k.'="'.addslashes($v).'"';	
					}
					
				}
				
	  		}
			else {
				$libelle = $option;
			}		
		
			$seleted = false;
			if (
			(is_array($pDefault) && !in_array($val,$pDefault))
			||  !is_array($pDefault) && (($val!=$pDefault && !$this->strict_string_compare) || ((string)$val!==(string)$pDefault && $this->strict_string_compare))
			){
		  	   $seleted=false;
			}
		  	else{
		  	  	$seleted=true;
			}
		
			 $field .= '<option value="'.$val.'" '.$moreAttributs.($seleted ? 'selected="selected"' : '').'>'.$libelle."</option>\n";

		}	
		
  }

	return $field; 		
}

private function required_combo_js($name,$params = array(),$default_value=""){
	
	$lib = (!isset($params['required_label']) || $params['required_label']=="")?"Champ requis":$params['required_label'];
	
	
	if(isset($params['custom_function'])){
		$name_clean = strtr($name,array('['=>'_',']'=>'_'));
		if(!isset($params['custom_function_label']) || empty($params['custom_function_label']))$params['custom_function_label']='Champs requis';
		//$messages .= '					depends_'.$name_clean.': "'.htmlspecialchars ($params['depends_label'],ENT_QUOTES) .'", '."\r\n";			
		$this->js_validation = '
		$.validator.addMethod(\'custom_function_'.$name_clean.'\', function(value, element, param) { 
		    '.$params['custom_function'].'  
		},"'.htmlspecialchars ($params['custom_function_label'],ENT_QUOTES) .'");
		'."\r\n".$this->js_validation;
		
		// dépendant !
		$this->js_validation.= '// required_combo_js : champ requis : '.$name." (id)\r\n"; 
		$this->js_validation.= '$(\'select[name="'.$name.'"]\').rules("add", {   custom_function_'.$name_clean.': true, '."\r\n";
		$this->js_validation.= ' messages: { required: "'.htmlspecialchars($lib,ENT_QUOTES) .'"  }  });'."\r\n \r\n";	
	}else{	
		$this->js_validation.= '// champ requis : '.$name." (id)\r\n";
		$this->js_validation.= '$(\'select[name="'.$name.'"]\').rules("add", {
		   required: true,
		   messages: {
		     required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'"
		   }
		});'."\r\n \r\n";
	}
	
	$this->js_validation.= " \r\n";
	
}


/*
 * permet de récupérer un select avec le tableau passé en paramètre et la valeur par défaut cochée
 * 
 * @param $pLib Le libellé
 * @param $pname Le nom du paramètre
 * @param $pListe
 * @param $pDefault
 * @param $pTaille
 * @param $onChange
 * @param $plus
 *
 * @return Retourne le formulaire
 */
function comboOptGroup($pLib,$pName,$pListe,$pDefault,$pTaille=1,$onChange='',$plus='',$fct_determmine_group='',$val_def=0){

  $lib="";
  $field="";
  if ($pLib!="")
    $lib   = "<b> $pLib </b>";
  
  $field = "<SELECT NAME='$pName'";
  if ($onChange!='') {
      $field.=" onChange=\"$onChange\"";
  }
  if($plus!=''){
  		$field.=" ".$plus;
  }
  
  $field.=">\n";
  $label_group=false;
  $newlabel_group=false;
  while (list($val,$libelle) = each ($pListe))
  {
    //$val_def correspond � la valeur qui d�termine le label
    if($fct_determmine_group!=''){
      $newlabel_group = call_user_func($fct_determmine_group,$val,$val_def);   
    }
    if($newlabel_group !== false){
       if($label_group!==false) $field .= "</OPTGROUP>\n";
       $label_group = $newlabel_group;
       
       $field .= "<OPTGROUP label=\"$libelle\">\n";
    }else{
      if ($val!=$pDefault)
        $field .= "<OPTION VALUE=\"$val\">$libelle</OPTION>\n";
      else
        $field .= "<OPTION VALUE=\"$val\" SELECTED>$libelle</OPTION>\n";
  	}
  }
  if($label_group!==false) $field .= "</OPTGROUP>\n";
  $field .="</SELECT>";
 
  if ($this->type_aff =='VIEW'){
    if (array_key_exists($pDefault,$pListe)) $val=$pListe[$pDefault]; else $val="";  
    $field = "<INPUT class='text_view' TYPE='TEXT' READONLY TABINDEX=-1 NAME='$pName' VALUE=\"$val\" SIZE='$pTaille'>\n ";
  }
 
  if ($lib != '')
    return $lib." ".$field;
  else
    return $field;
}



/*
 * 
 * 
 * @param $pLib Le libellé
 * @param $pname Le nom du paramètre
 * @param $pListe
 * @param $pDefault
 *
 * @return Retourne le formulaire
 */
function checkbox($pLib,$pName,$pListe,$pDefault, $plus="", $enLigne=true){
  $lib   = "<b> $pLib </b>";
  $field ="<TABLE class='form' BORDER=0>\n";
  if($enLigne) $field.="<TR>\n";
  while (list ($val, $libelle) = each ($pListe))
  {
  	if(!$enLigne) $field.="<TR>\n";
    $field .= "<TD>$libelle</TD>";
    if ($val == $pDefault) 
       $checked = "CHECKED";
    else 
       $checked = " ";
    $field .= "<TD><INPUT TYPE='CHECKBOX' NAME='$pName' VALUE=\"$val\" "
                  . " $checked $plus> </TD>\n";
  	if(!$enLigne) $field.="\n</TR>\n";
  }
  if($enLigne) $field.="\n</TR>";
  $field .= "</TABLE>";
  return $lib." ".$field;
}
	
	
	function checkbox1_js($array){
		$pLib = "";
		if(isset($array['label']))$pLib = $array['label'];
		$pName = "";
		if(isset($array['name']))$pName = $array['name'];
		$pVal = array();
		if(isset($array['value']))$pVal = $array['value'];
		$checked = "false";
		if(isset($array['checked']))$checked = $array['checked'];
	
		// $pTaille=1,$onChange='',$plus='
		$class = "text";
		if(isset($array['class']))$class = $array['class'];
		
		$order = "case_after";
		if(isset($array['order']))$order = $array['order'];
		
		
		$id = "";
		if(isset($array['id']))$id = $array['id'];
		
		$plus = "";
		
		
		if(isset($array['onchange']))$plus .= ' OnChange="javascript:'.$array['onchange'].'";';
		if(isset($array['onclick']))$plus .= ' OnClick="javascript:'.$array['onclick'].'";';
		
		$out = $this->checkbox1($pLib,$pName,$pVal,$checked,$plus,$class,$id,$order);
		
		return($out);
		
		
	}
		
	/*
	 * checkbox1 retourne une case à cocher
	 * 
	 * @param $pLib Le libellé
	 * @param $pname Le nom du paramètre
	 * @param $pVal
	 *
	 * @return Retourne le formulaire
	 */
	function checkbox1($pLib,$pName,$pVal,$checked=false,$plus='',$class='',$id='',$order='case_after', $check_visu=array() ){
	  if($checked==true)$checkedVal="CHECKED";
	  else $checkedVal=" ";
	
	  if($id=='')$id = $pName;
	  
	  $field="";
	  
	  if ($this->type_aff =='view'){
				if($checked)$field='<span class="check">'. (isset($check_visu['yes']) ? $check_visu['yes'] : 'Oui') .'</span>';
				else $field='<span class="no-check">'. (isset($check_visu['no']) ? $check_visu['no'] : 'Non') .'</span>';
	  }
	  else {
	  		$field = "<INPUT TYPE='CHECKBOX' CLASS='$class' NAME='$pName' ID='$id' VALUE=\"$pVal\" $checkedVal $plus />\n";
	  }
	  if($order=='case_after')  return $pLib." ".$field;
	  else return $field.' '.$pLib;
	}
	

	
	
	public function radio_js($array){
		$pLib = "";
		if(isset($array['label']))$pLib = $array['label'];
		$pName = "";
		if(isset($array['name']))$pName = $array['name'];
		$pListe = array();
		if(isset($array['options']))$pListe = $array['options'];
		$pDefault = NULL;
		if(isset($array['default_value']))$pDefault = $array['default_value'];
	//	$onChange = '';
	//	if(isset($array['onChange']))$onChange = $array['onChange'];
		$class = "text";
		if(isset($array['class']))$class = $array['class'];
		
		$id = "";
		if(isset($array['id']))$id = $array['id'];
		
		$cpt_for_radiodiv = "";
		if(isset($array['cpt_for_radiodiv']))$cpt_for_radiodiv = $array['cpt_for_radiodiv'];
		
		$class_radiodiv = "";
		if(isset($array['class_radiodiv']))$class_radiodiv = $array['class_radiodiv'];
		
		// champ requis
		if((isset($array['required']))&&($array['required']==TRUE)){
			$class .= ' required';
			// NE FONCTIONNE PAS , PAS DEBUGGE !!!!!!
				if(isset($array['required_label']))$this->required_radio_js($pName,$array['required_label'],'',$array);
			else $this->required_radio_js($pName,'','',$array);
			//else $this->required_combo_js($pName);
			if($pLib!=''){
				$pLib .= " <span class=required>*</span>";
			}
		}
		
		
		
		
		$plus = "";
		if(isset($array['plus']))$plus = $array['plus'];
		
		if(isset($array['onchange']))$plus .= ' OnChange="javascript:'.$array['onchange'].'"';
		if(isset($array['onclick']))$plus .= ' OnClick="javascript:'.$array['onclick'].'"';
		
		$out = $this->radiodiv($pLib,$pName,$pListe,$pDefault, $plus,$class,$cpt_for_radiodiv);
		
		$out = '<div id="div_'.$id.'" class="'.$class_radiodiv.'">'.$out.'<div class="clear"></div></div>'."\r\n";
		
		if(isset($array['buttonset'])) $this->buttonset_radio_js('div_'.$id,$array);
		
		return $out;
	}


	private function buttonset_radio_js($name,$array=array()){
		$this->js_validation.= '$(\'div[id="'.$name.'"]\').buttonset();'."\r\n";
		if(isset($array['buttonset']) && $array['buttonset']==true){
			if(isset($array['name']) ){
				$this->js_validation.= 'var noIcon = {primary: \'ui-icon-radio-on\', secondary: null};'."\r\n";
				$this->js_validation.= 'var withIcon = {primary: \'ui-icon-radio-off\', secondary: null};'."\r\n";
				$this->js_validation.= '$(\'div[id="'.$name.'"] :radio\').click(function(e) {
				    $(\'div[id="'.$name.'"] :radio:not(:checked)\').button({icons: noIcon}).button(\'refresh\');
				    $(\'div[id="'.$name.'"] :radio:checked\').button({icons: withIcon}).button(\'refresh\');
				});'."\r\n";
				$this->js_validation.= '$(\'div[id="'.$name.'"] :radio:checked\').button({icons: withIcon});'."\r\n";
				$this->js_validation.= '$(\'div[id="'.$name.'"] :radio:not(:checked)\').button({icons: noIcon});'."\r\n";
			}
		}
	}

	private function required_radio_js($name,$lib = "",$default_value="",$params=array()){
		if($lib=="")$lib = "Champ requis";
		$messages='';
		
		if(isset($params['custom_function'])){
			$name_clean = strtr($name,array('['=>'_',']'=>'_'));
			if(!isset($params['custom_function_label']) || empty($params['custom_function_label']))$params['custom_function_label']='Champs requis';
			//$messages .= '					depends_'.$name_clean.': "'.htmlspecialchars ($params['depends_label'],ENT_QUOTES) .'", '."\r\n";			
			$this->js_validation = '
			$.validator.addMethod(\'custom_function_'.$name_clean.'\', function(value, element, param) { 
			    '.$params['custom_function'].'  
			},"'.htmlspecialchars ($params['custom_function_label'],ENT_QUOTES) .'");
			'."\r\n".$this->js_validation;
			
			// dependant !
			$this->js_validation.= '// required_radio_js : champ requis : '.$name." (id)\r\n"; 
			$this->js_validation.= '$(\':radio[name="'.$name.'"]\').rules("add", {  required: true, custom_function_'.$name_clean.': true, '."\r\n";			
		}else{
			$this->js_validation.= '// required_radio_js : champ requis : '.$name." (id)\r\n";
			$this->js_validation.= '$(\':radio[name="'.$name.'"]\').rules("add", { required: true, '."\r\n";
		}
				
		$messages .= '					'.(($messages!='')?',':'').'required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'" '."\r\n";
		
		$this->js_validation.= ' messages: { required: "'.htmlspecialchars ($lib,ENT_QUOTES) .'"  }  });'."\r\n \r\n";
	}
	
	function radiodiv($pLib,$pName,$pListe,$pDefault=NULL, $plus="",$class='',$cpt_for_radiodiv=1){
		//ui-state-default ui-corner-all ui-helper-cklearfix field
	    $lib   = '<p class="">'.$pLib.'</p>';
	    $field ="<!-- field jquery -->\n";
	    //$cpt_for_radiodiv=1;
	    while (list ($val, $libelle) = each ($pListe)){
	        
	        if ($val == $pDefault){
	            $checked = "CHECKED";
	        }
			else{
	            $checked = " ";
			}
			if(($pDefault===NULL)&&($cpt_for_radiodiv==1)){
				$checked = "CHECKED";
			}
			
	        $field .= '<input class="'.$class.'" type="radio" id="'.$pName.'_'.$cpt_for_radiodiv.'" name="'.$pName.'" value="'.$val.'" '." $checked $plus>";
			$field .= '<label for="'.$pName.'_'.$cpt_for_radiodiv.'">'.$libelle.'</label>';
			$cpt_for_radiodiv++;
	    }
	    $field .= "\r\n";
		//pas testé
	    //if ($this->type_aff =='VIEW')
	    //  $field = $pListe[$pDefault];  
	    return $lib." ".$field."\r\n";
	}


	
	function radio($pLib,$pName,$pListe,$pDefault, $plus="",$class='',$id='', $enligne = true){
	    $lib   = "<b> $pLib </b>";
	    $field ="<TABLE class='form' BORDER=0>\n";
		if($enligne == true) $field.="<TR>\n";
	    while (list ($val, $libelle) = each ($pListe)){
	    	if($enligne == false) $field.="<TR>\n";
	        $field .= "<TD>$libelle</TD>";
	        if ($val == $pDefault){
	            $checked = "CHECKED";
	        }
			else{
	            $checked = " ";
			}
	        $field .= "<TD><INPUT TYPE='RADIO' NAME='$pName' VALUE=\"$val\" ". " $checked $plus> </TD>\n";
			if($enligne == false)$field.="\n</TR>\n";
		}
		if($enligne == true)$field.="\n</TR>";
  		$field .= "</TABLE>";
	    if ($this->type_aff =='VIEW'){
	      $field = $pListe[$pDefault];
		}  
	    return $lib." ".$field;
	}




	function radio1($pLib,$pName,$pVal,$pDefault, $plus=""){
	        $field="";
			
			if ($pVal == $pDefault) 
	            $checked = "CHECKED";
	        else 
	            $checked = " ";
			
			if ($this->type_aff =='VIEW'){
				if($checked!=" ")$field="<img src=\"../images/croix.gif\" border=0>";
			}
			else{
				$field = "<INPUT TYPE='RADIO' NAME='$pName' VALUE=\"$pVal\" $checked $plus>\n";
			
			}
			
			
			return $pLib." ".$field;
	}
	function end() {
		print $this->end_form();
	}
	function end_form(){
	    return "</FORM>\n";
	}

	/*
	 * button_js retourne un bouton qui prend en compte le mode : popin ou bien 
	 * 
	 * 
	 */
	function button_js($array){
		global $viewStyle;
		
		$pLib = "";
		if(isset($array['label']))$pLib = $array['label'];
		
		$pName = "";
		if(isset($array['name']))$pName = $array['name'];
		
		$pId = "";
		if(isset($array['id']))$pId = $array['id'];
		if($pId == "")$pId = $pName;
		
		$pVal = "";
		if(isset($array['value']))$pVal = $array['value'];
	
		$pTaille = "10";
		if(isset($array['size']))$pTaille = $array['size'];
	
		$pTailleMax = "0";
		if(isset($array['maxlength']))$pTailleMax = $array['maxlength'];
	
		//$plus = "";
		//if(isset($array['plus']))$pTaille = $array['plus'];
	
		//$class = "btn_blue btn_blue20";
		$class="";
		if(isset($array['class']))$class = $array['class'];
		
		$action = '';
		if(isset($array['action']))$action = $array['action'];
		
		$from = '';
		if(isset($array['from']))$from = $array['from'];
		
		$url = '';
		if(isset($array['url']))$url = $array['url'];
		
		$type="button";
		if(isset($array['type']))$type = $array['type'];
		
		$btn_default="btn_blue";
		if(isset($array['btn_default']))$btn_default = $array['btn_default'];
		
		
		$btn = '';
		
		$popin_name = 'popin';
		if(isset($_REQUEST['popin_name']))$popin_name = $_REQUEST['popin_name'];
		
		
		global $mode;
		$btn = '<!-- mode:'.$mode.' -->';
		if(DEBUG!='')$btn = $mode.','.$action.'<br>';
		switch($mode){
			case 'popin':
				switch($action){
					case 'save': //type submit...
						if($viewStyle=='non-editable')return('');
						if($pVal=='')$pVal='Enregistrer';
						if($url==''){
							$btn .= '<input type="submit" class="'.$btn_default.' '.$class.'" name="'.$pName.'" value="'.$pVal.'">&nbsp;';
						}
						else{
							$btn .= ''; //PAS IMPLEMENTE
						}
						break;
					case 'del': //le pop in va sur l'url delete
						if($pVal=='')$pVal='Supprimer';
						if($url!=''){
							//la page se rafraichi sur elle meme, meme en popin car c'est l'action suivant qui fera le rafraichissement.
							if((substr($url,0,4)=='http')||($url{0}=='?')){
								$url = "document.location.href='".$url."&mode=popin'";
							}
							else{
								//$url = $url; => rien à ajouter!
							}
							$btn .= '<input type="button" class="'.$btn_default.' '.$class.'" name="'.$pName.'" value="'.$pVal.'" onClick="'.$url.'">&nbsp;';
						}
						break;
					case 'send':
						//if($viewStyle=='non-editable')return('');
						if($pVal=='')$pVal='Envoyer';
						//la page se rafraichi sur elle meme, meme en popin car c'est l'action suivant qui fera le rafraichissement.
						if($url==''){
							$btn .= '<input type="submit" class="'.$btn_default.' '.$class.'" name="'.$pName.'" value="'.$pVal.'">&nbsp;';
						}
						else{
							$btn .= ''; //PAS IMPLEMENTE
						}
						break;
					default:
					//case 'cancel':
					case 'close':
						if($pVal=='')$pVal='Fermer';
						if($url!=''){
							if((substr($url,0,4)=='http')||($url{0}=='?')){
								$url = "document.location.href='".$url."&mode=popin'";
							}
							else{
								$url = $url;
							}
						}else{
							$url = 'window.parent.closeDialog(\''.$popin_name.'\');';
						}
						$btn .= '<input type="button" class="btn_clair '.$class.'" name="'.$pName.'" value="'.$pVal.'" onClick="'.$url.'">&nbsp;';
						break;

				}
				break;
			default:
			case '':
				switch($action){
					case 'save': //type submit...
						if($viewStyle=='non-editable')return('');
						if($url==''){
							$btn .= '<input type="submit" name="'.$pName.'" value="'.$pVal.'" class="btn_fonce '.$class.'">&nbsp;';
						}
						break;
					case 'del': //la page va sur l'action delete
						if($url!=''){
							$btn .= '<input type="button" name="'.$pName.'" value="'.$pVal.'" class="'.$btn_default.' '.$class.'" onClick="document.location.href=\''.$url.'\'">&nbsp;';
						}
						break;
					case 'go_to_url': //la page va sur l'url
						if($url!=''){
							$btn .= '<input type="button" name="'.$pName.'" value="'.$pVal.'" class="'.$btn_default.' '.$class.'" onClick="document.location.href=\''.$url.'\'">&nbsp;';
						}
						break;
					case 'function': //la page va sur l'url
						if($url!=''){
							$btn .= '<input type="button" name="'.$pName.'" value="'.$pVal.'" class="'.$btn_default.' '.$class.'" onClick="'.$url.'">&nbsp;';
						}
						break;
					case 'close': //reviens à back !
						if(isset($_SERVER['HTTP_REFERER']))$from = $_SERVER['HTTP_REFERER'];
						if($pVal=='')$pVal='Annuler';
						if($from!='')$btn .= '<input type="button" name="Annuler" value="'.$pVal.'" class="btn_clair '.$class.'" onClick="document.location.href=\''.$from.'\'">&nbsp;';
						break;
				}
			break;
			
		}
		
		return $btn;
		
	}



	function btImg($pLib,$pName,$pImg,$plus=""){
	    $field = "<INPUT TYPE='IMAGE' NAME='$pName' src=\"$pImg\" border='0' alt=\"$pLib\" $plus>\n";  
	    return $field;
	}
	
	function btsubmit($pLib,$pName,$plus="", $class='button', $autoDisabled = false){
	    $field = "<INPUT class='".$class."' TYPE='SUBMIT' NAME='$pName' VALUE=\"$pLib\" ";
        
        if($autoDisabled && stripos($plus, 'onclick')===false) {
            $field.=' onclick="this.disabled=true" ';            
        }
        
        $field.=" $plus>\n";
	    return $field;
	}
	function bt($pLib,$pName,$plus=""){
	    $field = "<INPUT class='button' TYPE='BUTTON' NAME='$pName' VALUE='$pLib' $plus>\n";
	    return $field;
	}
	
	function btreset($pLib,$pName){
	    $field = "<INPUT class='button' TYPE='RESET' NAME='$pName' VALUE='$pLib'>\n";
	    return $field;
	}
	
	
	/*
	 * permet de récupérer un input avec un choix visuel d'une valeur entière
	 * 
	 * @param $pName 	Nom du champs (sans [] si tableau)
	 * @param $pMin 	Valeur minimale (entier)
	 * @param $pMax		Valeur maximale (entier)
	 * @param $pDefault	Valeur par défaut (entier)
	 * @param $pId		Entrer seulement si votre input doit être un tableau (name="TNote[$pId]")
	 * @param $pStep	Valeur de l'incrèment entre min et max
	 * @param $plusJs	Pour ajouter du JS
	 * @param $plusCss	Pour ajouter du CSS
	 * @param $trad		Pour ajouter une traduction à l'input (array de valeur dans l'ordre)
	 * @param $controleSaisie	Pour forcer ou non la saisie du champs (défaut : true);
	 *
	 * @return Retourne l'input au complet
	 */
	function radio_js_bloc_number($pName,$pMin,$pMax,$pDefault,$pId=null,$pStep=1,$plusJs=null,$plusCss=null,$trad=array(),$controleSaisie=true){
		// Exemple pour input en name tableau : name="TNote[1]" ...TNote[5]
		// obligatoire si on à un tablea dynamique avec plusieurs de ce type
		global $conf;
	    $field ="<!-- field jquery -->\n";
	    // Calcul affichage pour nombre élevé
	    
	    $nb_aff = ($pMax - $pMin)/$pStep;
		$must_split = ($nb_aff>10)?true:false;
		$nb_split = (($nb_aff/2)<10)?round($nb_aff/2):10;
		
		// Init var base
	   	$i=$pMin;
		$pName_unique=$pName;
		if($pDefault<$pMin || $pDefault>$pMax) $pDefault=null;
		if(isset($pId))
		{
			$pName_unique=$pName.'_'.$pId;
			$pName=$pName.'['.$pId.']';
		}
		if($this->type_aff=='view')
		{
			// Affichage view
			
			$field .= '<span class="radio_js_bloc_number '.$pName_unique.'">'.$pDefault.'</span>';
		} else {
			// Affichage edit/create
			$j=0;
			while ($i<=$pMax){
		        
		        if ($i == $pDefault){
		            $checked = "selected";
		        }
				else{
		            $checked = "";
				}
				$field .= '<span title="'.$trad[$j].'" class="radio_js_bloc_number '.$pName_unique.' '.$checked.'">'.$i.'</span>';
				
				if($must_split && $i%$nb_split==0)$field.='<br/>';
				$i++;
				$j++;
		    }
	        $field .= '<input type="hidden" id="'.$pName_unique.'" name="'.$pName.'" value="'.$pDefault.'" />';
			$field .= '
			<script type="text/javascript">
				$(document).ready(function(){
					$(".radio_js_bloc_number").tooltip();
					var error,same;
					$(".'.$pName_unique.'").on("click",function(){
						same=false;
						val = $(this).html();
						if($(this).hasClass("selected"))same=true;
						$(".'.$pName_unique.'").removeClass("selected");
						if(same)
						{
							$("#'.$pName_unique.'").val("");
						}else {
							$(this).addClass("selected");
							$("#'.$pName_unique.'").val(val);
						}
					});';
			
			if($controleSaisie)
			{	
				$field .= '$("#'.$pName_unique.'").closest("form").on("submit", function(){
						$("#'.$pName_unique.'").each(function(){
							if(this.value == "")
							{
								console.log("error"+this.value);
								$(this).closest("td").animate({
									backgroundColor:"#F78181"
								}, 500, function(){
									console.log("error"+this.value);
								})
								error=true;
							}
						});
						if(error)
						{
							error=false;
							$.jnotify("Vous devez saisir une note à chaque ligne !", "error");
							return false;
						}
					});';
			}
			$field .= '
					'.$plusJs.'
				});
			</script>';
			if(isset($plusCss)) $field .= '<style type="text/css">'.$plusCss.'</style>';
		}
		$field .= '
		<script type="text/javascript">
			$(document).ready(function(){
				<!-- Insertion du css une seule fois -->
				if (!$("link[href=\''.dol_buildpath('/abricot/includes/css/radio_js_number.css',1).'\']").length)
				{
	    			$(\'<link href="'.dol_buildpath('/abricot/includes/css/radio_js_number.css',1).'" rel="stylesheet">\').appendTo("head");
	    		}
			});
		</script>';
	    return $field;
	}


}

