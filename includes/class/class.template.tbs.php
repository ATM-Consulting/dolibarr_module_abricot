<?php
class TTemplateTBS {
	
	function __construct(){
		$this->TBS = new clsTinyButStrong;
		$this->TBS->noerr = false;
		$this->TBS->Protect = false;
		
		$this->TExtensionOpenTBS=array('.odt','.docx','.xlsx','.ods');
	}
	
	function render($tpl, $TBlock=array(), $TField=array(), $TPagination=array(), $TParam=array()) {
			
		$TBS = &$this->TBS;
		$isOPENTBS_PLUGIN=false;			
		
		if(isset($TParam['option'])) {
			foreach($TParam['option'] as $k=>$v) {
				$TBS->SetOption($k,$v);
			}
		}
		
		if(is_file($tpl)) {
			$extension = $this->extension($tpl);
			if(in_array($extension, $this->TExtensionOpenTBS)) {
				$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
				$isOPENTBS_PLUGIN=true;
				
			}	
			
			$TBS->LoadTemplate($tpl , isset($TParam['charset']) ? $TParam['charset'] : 'utf-8' );	
		}
		else {
			$TBS->Source = $tpl;
		}
		
	
		
		if(!empty($TPagination)) {
			foreach ($TPagination as $nomPagination => $pagination) {
				//print $nomPagination; print_r($pagination);
				$pageSize = isset($pagination['pageSize']) ? $pagination['pageSize'] : 20 ;
				$pageNum = isset($pagination['pageNum']) ? $pagination['pageNum'] : 1 ;
				$totalNB = isset($pagination['totalNB']) ? $pagination['totalNB'] : -1 ;
				
				$blockName = $pagination['blockName'];
				
				if(isset($TBlock[$blockName])) {
						
					$TBS->PlugIn(TBS_BYPAGE,$pageSize,$pageNum);
					$TBS->MergeBlock($blockName, $TBlock[$blockName]);
					
					unset($TBlock[$blockName]);
				}
				
				$TBS->PlugIn(TBS_NAVBAR,$nomPagination, array(), $pageNum, $totalNB, $pageSize);
				
			}
			
		}
		
		
		
		
		if(!empty($TBlock)) {
			foreach($TBlock as $nameBlock=>$block) {
				
				$TBS->MergeBlock($nameBlock, $block);
				
			}
			
		}

		if(!empty($TField)) {
			foreach($TField as $nameBlock=>$block) {
				
				$TBS->MergeField($nameBlock, $block);
				
			}
		}
		
		if($isOPENTBS_PLUGIN) {
			if($extension==".odt" || $extension=='.docx') {	$TBS->PlugIn(OPENTBS_DELETE_COMMENTS); } 
			//$TBS->PlugIn(OPENTBS_DEBUG_XML_SHOW);
			if(!isset($TParam['outFile']))$TBS->Show(isset($TParam['TBS_OUTPUT']) ? $TParam['TBS_OUTPUT'] : OPENTBS_DOWNLOAD);
			else $TBS->Show(OPENTBS_FILE, $TParam['outFile']);
			
			if($extension == ".odt" && !empty($TParam['convertToPDF']) && !empty($TParam['outFile'])) {
				$this->convertToPDF($TParam['outFile']);
				if(!empty($TParam['deleteSrc'])) unlink($TParam['outFile']);
			}
			
			return $TParam['outFile'];
		}
		else {
			$TBS->Show( isset($TParam['TBS_OUTPUT']) ? $TParam['TBS_OUTPUT'] : TBS_NOTHING );
			return $TBS->Source;
		}
		
	}
	
	private function extension($file) {
	/* extension d'un fichier */
		$ext = substr ($file, strrpos($file,'.'));
		return $ext;
	}
	
	private function convertToPDF($file) {
		$infos = pathinfo($file);
		$filepath = $infos['dirname'];
		
		// Transformation en PDF
		$cmd = 'export HOME=/tmp'."\n";
		$cmd.= 'libreoffice --invisible --norestore --headless --convert-to pdf --outdir "'.$filepath.'" "'.$file.'"';
		ob_start();
		system($cmd);
		$res = ob_get_clean();
		return $res;
	}
}

