<?php
/*
 
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

class TListviewTBS {
	
	function __construct( $id, $template='') {
		if(empty($template)) $template = COREROOT.'includes/class/html.list.tbs.php';	
			
		$this->id = $id;
		$this->template = $template;
		$this->typeRender = 'sql';
		
		$this->TTotalTmp=array();
		
		$this->TBind=array();
	}
	private function init(&$TParam) {
		
		if(!isset($TParam['hide']))$TParam['hide']=array();
		if(!isset($TParam['link']))$TParam['link']=array();
		if(!isset($TParam['subQuery']))$TParam['subQuery']=array();
		if(!isset($TParam['type']))$TParam['type']=array();
		if(!isset($TParam['orderby']['noOrder']))$TParam['orderby']['noOrder']=array();
		if(!isset($TParam['node']))$TParam['node']=array('main'=>'data', 'object'=>'dataline');
		
		if(!isset($TParam['liste']))$TParam['liste']=array();
		$TParam['liste'] = array_merge(array(
			'messageNothing'=>"Il n'y a aucun élément à afficher."
			,'picto_precedent'=>'&lt;'
			,'picto_suivant'=>'&gt;'
			,'order_down'=>'&#9660;'
			,'order_up'=>'&#9650;'
			,'noheader'=>0
			,'image'=>''
			,'titre'=>'Liste'
			,'orderDown'=>''
			,'orderUp'=>''
			,'id'=>$this->id
			,'picto_search'=>img_picto('Search', 'search.png')
			,'head_search'=>''
		),$TParam['liste']);
		
		if(!isset($TParam['limit']))$TParam['limit']=array();
		if(!empty($_REQUEST['TListTBS'][$this->id]['page'])) $TParam['limit']['page'] = $_REQUEST['TListTBS'][$this->id]['page'];
		
		$TParam['limit']=array_merge(array('page'=>1, 'nbLine'=>30), $TParam['limit']);
		
		if(!empty($_REQUEST['TListTBS'][$this->id]['orderBy'])) {
			/*
			 	$TParam['orderBy'] = array();	
				foreach($_REQUEST['TListTBS'][$this->id]['orderBy'] as $asc=>$field) {
					$TParam['orderBy'][$field]=$asc;
				}
			 */
			$TParam['orderBy'] = $_REQUEST['TListTBS'][$this->id]['orderBy']; 
		}
		
		
	//	print_r($TParam);
	}
	private function getSearchKey($key, &$TParam) {
				
		$prefixe='';	
		if(isset($TParam['search'][$key]['table']))$prefixe='`'.$TParam['search'][$key]['table'].'`.';
		
		if(isset($TParam['search'][$key]['field']))$key =$prefixe.'`'. $TParam['search'][$key]['field'] .'`';
		else $key =$prefixe.'`'. strtr($key,';','*').'`';
			
		return $key;
	} 
	private function getSearchValue($value) {
		$value = strtr(trim($value),';','*');	
			
		return $value;
	}
	private function search($sql,&$TParam) {
	
		if(!empty($_REQUEST['TListTBS'][$this->id]['search'])) {
			$sqlGROUPBY='';
			if(strpos($sql,'GROUP BY')!==false) {
				list($sql, $sqlGROUPBY) = explode('GROUP BY', $sql);
			}
			
			if(strpos($sql,'WHERE ')===false)$sql.=' WHERE 1 ';
			
			foreach($_REQUEST['TListTBS'][$this->id]['search'] as $key=>$value) {
				if(!empty($value)) {
					$sKey = $this->getSearchKey($key, $TParam);
					$sBindKey = strtr($sKey,array('.'=>'_' ,'`'=>''));
					
					if(isset($TParam['type'][$key]) && $TParam['type'][$key]==='date') {
						if(is_array($value)) {
							if(!empty($value['deb'])) {
								list($dd,$mm,$aaaa) = explode('/', $value['deb']);
								$valueDeb = date('Y-m-d', mktime(0,0,0,$mm,$dd,$aaaa));
								
								if(isset($this->TBind[$sBindKey.'_start'])) {
									$this->TBind[$sBindKey.'_start'] = $valueDeb;
								} 
								else  {
									$sql.=" AND ".$sKey." >= '".$valueDeb."'" ;
								}
							}
							if(!empty($value['fin'])) {
								list($dd,$mm,$aaaa) = explode('/', $value['fin']);
								$valueFin = date('Y-m-d', mktime(0,0,0,$mm,$dd,$aaaa));
								
								if(isset($this->TBind[$sBindKey.'_end'])) {
									$this->TBind[$sBindKey.'_end'] = $valueFin;
								} 
								else  {
									$sql.=" AND ".$sKey." <= '".$valueFin."'" ;	
								}
							}
							
						}	
						else {
							list($dd,$mm,$aaaa) = explode('/', $value);
							$value = date('Y-m-d', mktime(0,0,0,$mm,$dd,$aaaa)).'%';
							
							if(isset($this->TBind[$sBindKey])) {
								$this->TBind[$sBindKey] = $value.'%';
							} 
							else  {
							
								$sql.=" AND ".$sKey." LIKE '".$value."'" ;
							}	
						}
					}
					else {
						
						if(isset($this->TBind[$sBindKey])) {
							$this->TBind[$sBindKey] = '%'.$value.'%';
							
						} 
						else  {
							$value = $this->getSearchValue($value);
							$sql.=" AND ".$sKey." LIKE '%".addslashes($value)."%'" ;	
						}
						
					}
					
				}	
			}
			
			if($sqlGROUPBY!='')	$sql.=' GROUP BY '.$sqlGROUPBY;
			
		}
		return $sql;
	}
	public function render(&$db,$sql,$TParam=array(),$TBind=array()) {
		$this->typeRender = 'sql';
	//		print_r($TParam);
		$TEntete=array();
		$TChamps=array();	
		
		$this->init($TParam);
		
		if(!empty($TBind)) $this->TBind = $TBind;
		
		$sql = $this->search($sql,$TParam);
		$sql = $this->order_by($sql, $TParam);		
		
		$this->parse_sql($db, $TEntete, $TChamps, $TParam, $sql);	
		
		$TTotal=$this->get_total($TChamps, $TParam);
		
		return $this->renderList($TEntete, $TChamps,$TTotal, $TParam);
		
	}
	public function renderDataTableAjax(&$db,$sql,$TParam=array()) {
		$this->renderDataTableSQL($db, $sql, $TParam);
	}
	public function renderDataTableSQL(&$db,$sql,$TParam=array()) {
		$this->typeRender = 'dataTableAjax';
	//		print_r($TParam);
		$TEntete=array();
		$TChamps=array();	
		
		$this->init($TParam);
		
		$sql = $this->search($sql,$TParam);
		$sql = $this->order_by($sql, $TParam);		
		
		$this->parse_sql($db, $TEntete, $TChamps, $TParam, $sql);	
		$TTotal=$this->get_total($TChamps, $TParam);
		
		return $this->renderList($TEntete, $TChamps,$TTotal, $TParam);
		
	}
	public function renderXML(&$db,$xmlString, $TParam=array()) {
		$this->typeRender = 'xml';
		
		$TEntete=array();
		$TChamps=array();	
		
		$this->init($TParam);
		
		$this->parse_xml($db, $TEntete, $TChamps, $TParam,$xmlString);
		
		$TTotal=$this->get_total($TChamps, $TParam);
		
		return $this->renderList($TEntete, $TChamps,$TTotal, $TParam);
		
	}
	private function setSearch(&$TEntete, &$TParam) {
		if(empty($TParam['search'])) return array();
		
		$TSearch=array();
		$form=new TFormCore;
		foreach($TEntete as $key=>$libelle) {
			if(isset($TParam['search'][$key])) {
				$value = isset($_REQUEST['TListTBS'][$this->id]['search'][$key]) ? $_REQUEST['TListTBS'][$this->id]['search'][$key] : '';
				
				$typeRecherche = (is_array($TParam['search'][$key]) && isset($TParam['search'][$key]['recherche'])) ? $TParam['search'][$key]['recherche'] : $TParam['search'][$key];  
				
				if(is_array($typeRecherche)) {
					$typeRecherche = array(''=>' ') + $typeRecherche;
					$TSearch[$key]=$form->combo('','TListTBS['.$this->id.'][search]['.$key.']', $typeRecherche,$value);
				}
				else if($typeRecherche==='calendar') {
					$TSearch[$key]=$form->calendrier('','TListTBS['.$this->id.'][search]['.$key.']',$value,10,10);	
				}
				else if($typeRecherche==='calendars') {
					$TSearch[$key]=$form->calendrier('','TListTBS['.$this->id.'][search]['.$key.'][deb]',isset($value['deb'])?$value['deb']:'',10,10)
						.' '.$form->calendrier('','TListTBS['.$this->id.'][search]['.$key.'][fin]',isset($value['fin'])?$value['fin']:'',10,10);	
				}
				else if(is_string($typeRecherche)) {
					$TSearch[$key]=$TParam['search'][$key];	
				}
				else {
					$TSearch[$key]=$form->texte('','TListTBS['.$this->id.'][search]['.$key.']',$value,15,255);	
				}
					
			}
			else {
				$TSearch[$key]='';
			}
			
		}

		$search_button = ' <a href="#" onclick="TListTBS_submitSearch(this);" class="list-search-link">'.$TParam['liste']['picto_search'].'</a>';

		if(!empty($TParam['liste']['head_search'])) {
			$TParam['liste']['head_search'].=$search_button;
		}
		
		if(!empty($TParam['search']) && !empty($TSearch)) {
			$TSearch[$key].= $search_button;
		}
		
		return $TSearch;
	}

	/*
	 * Function analysant et totalisant une colonne
	 * Supporté : sum, average
	 */
	private function get_total(&$TChamps, &$TParam) {
		$TTotal=array();	
			
		if(!empty($TParam['math']) && !empty($TChamps[0])) {
			
			foreach($TChamps[0] as $field=>$value) {
				$TTotal[$field]='';	
			}
			
			foreach($TParam['math'] as $field=>$typeMath){

				if($typeMath=='average') {
					$TTotal[$field]=array_sum($this->TTotalTmp[$field]) / count($this->TTotalTmp[$field]);
				}
				else {
					$TTotal[$field]=array_sum($this->TTotalTmp[$field]);	
				}
								
			}
			
		
		}
		
		return $TTotal;
	}

	private function getJS(&$TParam) {
		$javaScript = '<script language="javascript">
		if(typeof(TListTBS_include)=="undefined") {
			document.write("<script type=\"text/javascript\" src=\"'.COREHTTP.'includes/js/list.tbs.js\"></scr");
	  		document.write("ipt>");
		}
		</script>';


		if($this->typeRender=='dataTable') {
			
			$javaScript.='<script language="javascript">
			
					if(typeof(TListTBS_dataTable_include)=="undefined") {
						var TListTBS_dataTable_include=true;	
						document.write("<script type=\"text/javascript\" src=\"'.COREHTTP.'includes/js/dataTable/js/jquery.dataTables.min.js\"></scr");
			  			document.write("ipt>");
						document.write("<link rel=\"stylesheet\" href=\"'.COREHTTP.'includes/js/dataTable/css/jquery.dataTables.css\" />");
					}
			
					$(document).ready(function () {
						$("#'.$this->id.'").wrap("<div id=\"'.$this->id.'_datatable_container\">");	
						$("#'.$this->id.'").dataTable({
					        "sPaginationType": "full_numbers"
					        ,"aLengthMenu": [['.$TParam['limit']['nbLine'].',-1], ['.$TParam['limit']['nbLine'].', "All"]]
					        ,"iDisplayLength" : '.$TParam['limit']['nbLine'].'
					    });
					}); 
			</script>';
			
			$TPagination=array();
		}
		elseif($this->typeRender=='dataTableAjax') {
			$javaScript.='<script language="javascript">
			
					if(typeof(TListTBS_dataTable_include)=="undefined") {
						var TListTBS_dataTable_include=true;
						document.write("<script type=\"text/javascript\" src=\"'.COREHTTP.'includes/js/dataTable/js/jquery.dataTables.min.js\"></scr");
			  			document.write("ipt>");
						document.write("<link rel=\"stylesheet\" href=\"'.COREHTTP.'includes/js/dataTable/css/jquery.dataTables.css\" />");
					}
			
					$(document).ready(function () {
						$("#'.$this->id.'").wrap("<div id=\"'.$this->id.'_datatable_container\">");	
						var oTable = $("#'.$this->id.'").dataTable({
					        "sPaginationType": "full_numbers"
					        ,"iDisplayLength" : '.$TParam['limit']['nbLine'].'
					        ,"sPaginationType": "full_numbers"
					        ,"oLanguage": {
								"sSearch": "Recherche globale :"
								/*,"sUrl": "media/language/de_DE.txt" pour depuis un fichier */
							}
					    });
						
						
						/* Ajoute du filtre via la recherche sur colonne */
					    $("#'.$this->id.' thead tr.barre-recherche td").each( function ( i ) {
					        $("select", this).change( function () {
					            oTable.fnFilter( $(this).val(), i );
					        } );
					        $("input", this).keyup( function () {
					            oTable.fnFilter( $(this).val(), i );
					        } );
							
					    } );
						
					}); 
			</script>';
		}
		
		return $javaScript;
	}
	private function renderList(&$TEntete, &$TChamps, &$TTotal, &$TParam) {
		$TBS = new TTemplateTBS;
		
		$javaScript = $this->getJS($TParam);
		
		if($this->typeRender!='dataTableAjax') {
			$TPagination=array(
				'pagination'=>array('pageSize'=>$TParam['limit']['nbLine'], 'pageNum'=>$TParam['limit']['page'], 'blockName'=>'champs', 'totalNB'=>count($TChamps))
			);
		}
		else {
			$TPagination=array();
		}
		
		
		$TSearch = $this->setSearch($TEntete, $TParam);
		
		return $TBS->render($this->template
			, array(
				'entete'=>$TEntete
				,'champs'=>$TChamps
				,'recherche'=>$TSearch
				,'total'=>$TTotal
			)
			, array(
				'liste'=>array_merge(array('id'=>$this->id, 'nb_columns'=>count($TEntete) ,'totalNB'=>count($TChamps), 'nbSearch'=>count($TSearch), 'haveTotal'=>(int)!empty($TTotal), 'havePage'=>(int)!empty($TPagination) ), $TParam['liste'])
			)
			, $TPagination
			, array()
		)
		.$javaScript;
	}
	public function renderDatatable(&$db, $TField, $TParam) {
		$this->typeRender = 'dataTable';
		// on conserve db pour le traitement ultérieur des subQuery
		$TEntete=array();
		$TChamps=array();	
		
		//$TParam['limit']['nbLine'] = 99999;
		
		$this->init($TParam);
		
		$this->parse_array($TEntete, $TChamps, $TParam,$TField);
		$TTotal=$this->get_total($TChamps, $TParam);
		return $this->renderList($TEntete, $TChamps, $TTotal,$TParam);
		
	}
	public function renderArray(&$db,$TField, $TParam=array()) {
		$this->typeRender = 'array';
		// on conserve db pour le traitement ultérieur des subQuery
		$TEntete=array();
		$TChamps=array();	
		
		$this->init($TParam);
		
		$this->parse_array($TEntete, $TChamps, $TParam,$TField);
		$TTotal=$this->get_total($TChamps, $TParam);
		return $this->renderList($TEntete, $TChamps,$TTotal, $TParam);
	}

	private function order_by($sql, &$TParam) {
		$first = true;	
		//	print_r($TParam['orderBy']);
		if(!empty($TParam['orderBy'])) {
			
			$sql.=' ORDER BY '; 
			foreach($TParam['orderBy'] as $field=>$order) {
				if(!$first) $sql.=',';
				
				if($order=='DESC')$TParam['liste']['orderDown'] = $field;
				else $TParam['liste']['orderUp'] = $field;
				
				if(strpos($field,'.')===false)	$sql.='`'.$field.'` '.$order;
				else $sql.=$field.' '.$order;
				
				$first=false;
			}
		}
		
		return $sql;
	}
	private function parse_xml(&$db, &$TEntete, &$TChamps, &$TParam, $xmlString) {
		$xml = simplexml_load_string($xmlString); 
		
		$first=true;
		foreach($xml->{$TParam['node']['main']}->{$TParam['node']['object']} as $node) {
			if($first) {
				$this->init_entete($TEntete, $TParam, $node);
				$first=false;
			}	
			
			$this->set_line($TChamps, $TParam, $node);
		}		

	}
	private function parse_array(&$TEntete, &$TChamps, &$TParam, $TField) {
		$first=true;
		
		$this->TTotalTmp=array();
		
		if(empty($TField)) return false;
		
		foreach($TField as $row) {
			if($first) {
				$this->init_entete($TEntete, $TParam, $row);
				$first=false;
			}	
			
			$this->set_line($TChamps, $TParam, $row);
		}		

	}
	
	private function init_entete(&$TEntete, &$TParam, $Tab) {
		foreach ($Tab as $field => $value) {
			if(!in_array($field,$TParam['hide'])) {
				$libelle = isset($TParam['title'][$field]) ? $TParam['title'][$field] : $field;
				$TEntete[$field] = array(
					'libelle'=>$libelle
					,'order'=>((in_array($field, $TParam['orderby']['noOrder']) || $this->typeRender != 'sql') ? 0 : 1)
					,'width'=>(!empty($TParam['size']['width'][$field]) ? $TParam['size']['width'][$field] : 'auto')
					,'text-align'=>(!empty($TParam['position']['text-align'][$field]) ? $TParam['position']['text-align'][$field] : 'auto')
				);
				  
			}
		}
		
		/*if(!empty($TParam['search']) && !empty($TEntete)) {
			$TEntete['actions']=array('libelle'=>'<!-- actions -->', 'order'=>0);
		}*/
		
	}
	private function set_line(&$TChamps, &$TParam, $currentLine) {
		
			$row=array(); $trans = array();
			foreach($currentLine as $field=>$value) {
				
				if(is_object($value)) {
					if(get_class($value)=='stdClass') {$value=print_r($value, true);}
					else $value=(string)$value;
				} 
				
				if(isset($TParam['subQuery'][$field])) {
					$dbSub = new Tdb;
					$dbSub->Execute( strtr($TParam['subQuery'][$field], array_merge( $trans, array('@val@'=>$value)  )) );
					$subResult = '';
					while($dbSub->Get_line()) {
						$subResult.= implode(', ',$dbSub->currentLine).'<br />';
					}
					$value=$subResult;
					$dbSub->close();
				}
				
				$trans['@'.$field.'@'] = $value;
				
				if(!empty($TParam['math'][$field])) {
					$this->TTotalTmp[$field][] = (double)strip_tags($value);
				}
				
				if(!in_array($field,$TParam['hide'])) {
					$row[$field]=$value;
					
					if(isset($TParam['link'][$field])) {
						if(empty($row[$field]) && $row[$field]!==0 && $row[$field]!=='0')$row[$field]='(vide)';
						$row[$field]= strtr( $TParam['link'][$field],  array_merge( $trans, array('@val@'=>$row[$field])  )) ;
					}
					
					if(isset($TParam['translate'][$field])) {
						$row[$field] = strtr( $row[$field] , $TParam['translate'][$field]);
					}
					
					if(isset($TParam['eval'][$field]) && in_array($field,array_keys($row))) {
						$strToEval = 'return '.strtr( $TParam['eval'][$field] ,  array_merge( $trans, array('@val@'=>$row[$field])  )).';';
						$row[$field] = eval($strToEval);
					}
					
					if(isset($TParam['type'][$field])) {
						if($TParam['type'][$field]=='date') { $row[$field] = date('d/m/Y', strtotime($row[$field])); }
						if($TParam['type'][$field]=='datetime') { $row[$field] = date('d/m/Y H:i:s', strtotime($row[$field])); }
						if($TParam['type'][$field]=='hour') { $row[$field] = date('H:i', strtotime($row[$field])); }
						if($TParam['type'][$field]=='money') { $row[$field] = '<div align="right">'.number_format((double)$row[$field],2,',',' ').'</div>'; }
						if($TParam['type'][$field]=='number') { $row[$field] = '<div align="right">'.number_format((double)$row[$field],2,',',' ').'</div>'; }
					}

				} 
				
				
			} 

			/*if(!empty($TParam['search']) && !empty($row)) {
				$row['actions']= '';
			}*/
		

			$TChamps[] = $row;	
	}
	
	private function parse_sql(&$db, &$TEntete, &$TChamps,&$TParam, $sql, $TBind=array()) {
		
		//$sql.=' LIMIT '.($TParam['limit']['page']*$TParam['limit']['nbLine']).','.$TParam['limit']['nbLine'];
		$this->TTotalTmp=array();
		
		$db->Execute($sql, $this->TBind);
		$first=true;
		while($db->Get_line()) {
			if($first) {
				$this->init_entete($TEntete, $TParam, $db->currentLine);
				$first = false;
			}
			
			$this->set_line($TChamps, $TParam, $db->currentLine);
			
		}
		
	}	
}
