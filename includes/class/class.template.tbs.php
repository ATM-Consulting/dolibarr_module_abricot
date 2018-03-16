<?php
class TTemplateTBS {
	
	function __construct(){
		$this->TBS = new clsTinyButStrong;
		$this->TBS->NoErr = false;
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
			if($extension=='.odt' || $extension=='.docx' || $extension=='.ods' || $extension=='.xlsx') {	$TBS->PlugIn(OPENTBS_DELETE_COMMENTS); } 
			//$TBS->PlugIn(OPENTBS_DEBUG_XML_SHOW);
			if(!isset($TParam['outFile']))$TBS->Show(isset($TParam['TBS_OUTPUT']) ? $TParam['TBS_OUTPUT'] : OPENTBS_DOWNLOAD);
			else $TBS->Show(OPENTBS_FILE, $TParam['outFile']);
			
			if(($extension=='.odt' || $extension=='.docx' || $extension=='.ods' || $extension=='.xlsx') && !empty($TParam['convertToPDF']) && !empty($TParam['outFile'])) {
				$this->convertToPDF($TParam['outFile']);
				if(!empty($TParam['deleteSrc'])) unlink($TParam['outFile']);
			}
			
			return $TParam['outFile'];
		}
		else {
			
			if(($extension == '.html' || $extension == '.php') && !empty($TParam['outFile'])) {
				$res =$TBS->Show(TBS_NOTHING);
				file_put_contents($TParam['outFile'], $TBS->Source);
				
				if( !empty($TParam['convertToPDF']) ) {
					$this->convertToPDF($TParam['outFile'], $extension,$TParam);	
				}
				
				if(!empty($TParam['deleteSrc'])) unlink($TParam['outFile']);
				
				return $TParam['outFile'];
			}
			else {
				$TBS->Show( isset($TParam['TBS_OUTPUT']) ? $TParam['TBS_OUTPUT'] : TBS_NOTHING );
				return $TBS->Source;	
			}
		}
		
	}
	
	public static function extension($file) {
	/* extension d'un fichier */
		$ext = substr ($file, strrpos($file,'.'));
		return $ext;
	}
	
	public static function convertToPDF($file,$extension='',$TParam=array()) {
		global $conf;
		
		$infos = pathinfo($file);
		$filepath = $infos['dirname'];
		
		if(!empty($conf->global->ABRICOT_CONVERTPDF_USE_ONLINE_SERVICE)) {
			
			//print USE_ONLINE_SERVICE;				
			$postdata = http_build_query(
			    array(
			        'f1Data' => file_get_contents($file)
					,'f1'=>basename($file)
			    )
			    ,'','&', PHP_QUERY_RFC1738
			);
			
			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $postdata
			    )
			);
			
			$context  = stream_context_create($opts);
			//print USE_ONLINE_SERVICE;
			$result = file_get_contents($conf->global->ABRICOT_CONVERTPDF_USE_ONLINE_SERVICE, false, $context);
			//exit($result);
			$filePDF = $filepath.'/'.basename($result);
			
			copy(strtr($result, array(' '=>'%20')), $filePDF); 
			//exit($result.', '.$filePDF);
			return $filePDF;
		}	
		else {
			if($extension == '.html' || $extension == '.php') {
			
				$file_pdf = substr($infos['basename'], 0, strrpos( $infos['basename'], '.' ) ).'.pdf';
				$wkhtmltopdf = new Wkhtmltopdf(array(
						'path' => sys_get_temp_dir(),'margin-left'=>0,
				));
				
				if(!empty($TParam['wkOptions'])) $wkhtmltopdf->setOptions($TParam['wkOptions']);
				
		        $wkhtmltopdf->setTitle($infos['basename']);
				$wkhtmltopdf->setOrientation(Wkhtmltopdf::ORIENTATION_PORTRAIT); //TODO config
		        $wkhtmltopdf->setUrl($file);
				$wkhtmltopdf->_bin = !empty($conf->global->ABRICOT_WKHTMLTOPDF_CMD) ? $conf->global->ABRICOT_WKHTMLTOPDF_CMD : 'wkhtmltopdf';
		        $file = $wkhtmltopdf->output(Wkhtmltopdf::MODE_SAVE,$file_pdf);
			
				
				rename($file, $filepath.'/'.$file_pdf);
				
				return 1;
			}
			else {
				$cmd_print = empty($conf->global->ABRICOT_CONVERTPDF_CMD) ? 'libreoffice --invisible --norestore --headless --convert-to pdf --outdir' : $conf->global->ABRICOT_CONVERTPDF_CMD;	
			
				// Transformation en PDF
				$cmd = 'export HOME=/tmp'."\n";
				$cmd.= $cmd_print.' "'.$filepath.'" "'.$file.'"';
			
			}
			
			ob_start();
			system($cmd);
			$res = ob_get_clean();
			return $res;		
			
		}
	}
}

