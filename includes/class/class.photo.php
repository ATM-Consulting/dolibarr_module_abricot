<?php
 
/**
 * Version standardisee de la classe photo
 * 
 * date 2 mars 2011 - 10:41:35
 * 
 * @author maxime
 * @version 1.4.2.5
 * @copyright 2006
 * @subpackage Essential
 **/  
class TPhoto extends TObjetStd {
	function TPhoto(){
		$this->set_table(PHOTO_TABLE);
		$this->TChamps = array(); //tableau des champs é exploiter
		$this->add_champs("encadre", "type=entier;");
		$this->add_champs("", "type=float");
		$this->_init_vars("theme,titre,description,legende,source,fichier1,fichier2");
		$this->start();

		$this->TTheme=array();

		$this->xrata=1;
		$this->yrata=1;

		$this->width=1;
		// Définit si le retaillage est homotéthique (false) ou découpant (true)
		$this->set_in_place=false;
		
		// Définit si stockage dans des dossiers datés
		$this->stock_dating_folder=true;
	}

	function resizeImage($src,$rata=100, $size_concern="all", $dest="",$cadre=true){
  		// $size_concern="all","height","width"
        list($width, $height, $type) = getimagesize($src);
        
        $redim=false;
        
        if($width>$rata && $width>$height && $size_concern=="all"){
                        $p = $height / $width;
                        $newwidth = $rata;
                        $newheight = $p * $rata;
        }
        else if ($height>$rata && $height>$width && $size_concern=="all") {
                $p = $width / $height;
                        $newwidth = $p * $rata;
                        $newheight = $rata;
        }
        elseif($width>$rata && $size_concern=="width"){
                        $p = $height / $width;
                        $newwidth = $rata;
                        $newheight = $p * $rata;
        }
        else if ($height>$rata && $size_concern=="height") {
                $p = $width / $height;
                        $newwidth = $p * $rata;
                        $newheight = $rata;
        }
        else{
                        $newwidth = $width;
                        $newheight = $height;
        }
        
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        
        $kek=imagecolorallocate($thumb,255,255,255); 
        imagefill ( $thumb, 0, 0, $kek);
        
        if($dest==""){
                $img=$src;
        }
        else{
                $img=$dest;
        }
        
        if($type==1){
                $source= imagecreatefromgif($src);
                $img = substr($img,0,-4).".jpg";
                $redim=true;
        }
        elseif($type==3){
                $source= imagecreatefrompng($src);
                $img = substr($img,0,-4).".jpg";
                $redim=true;
        }
        else{
                $source= imagecreatefromjpeg($src);
                if($newwidth<$rata || $newheight<$rata){
                        $redim=true;
                }
                
        }

        imagecopyresampled ( $thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );
        

        if($this->stock_dating_folder){
	      $this->_test_and_cree_folders($img,'');
	    }

        if($newwidth!=$width || $newheight!=$height || $redim){
                imagejpeg($thumb,$img,80);
        }       
            
        if($cadre==true){
        	cadre_image($thumb,$rata,$newheight);
        }
        
        if($newwidth!=$width || $newheight!=$height || $redim){
                imagejpeg($thumb,$img,80);
        }
        imagedestroy($thumb);
                return basename($img);  
	}

	function resizeImage_XY($src,$img,$xrata=70, $yrata=32, $force_newheight=false){
		// $size_concern="all","height","width"
        list($width, $height, $type) = getimagesize($src);

        $redim=false;
        $newwidth=0;$newheight=0;
        $this->_get_newdimension($xrata, $yrata, $width, $height, $newwidth, $newheight);
                        
        $thumb = imagecreatetruecolor($xrata, $yrata);

		if($force_newheight){
		  $thumb = imagecreatetruecolor($xrata, $newheight);
		      }else{
		  $thumb = imagecreatetruecolor($xrata, $yrata);
		}
		              
		$kek=imagecolorallocate($thumb,255,255,255); 
		imagefill ( $thumb, 0, 0, $kek);
		
		if($type==1){
		      $source= imagecreatefromgif($src);
		      $img = substr($img,0,-4).".jpg";
		}
		elseif($type==3){
		      $source= imagecreatefrompng($src);
		      $img = substr($img,0,-4).".jpg";
		}
		else{
		      $source= imagecreatefromjpeg($src);
		      
		}
		if($force_newheight){
			$dst_y = 0;
		}else{
			$dst_y = ($yrata-$newheight) / 2;
		}
		$dst_x = ($xrata-$newwidth) / 2;
		
		imagecopyresampled ( $thumb, $source, $dst_x, $dst_y, 0, 0, $newwidth, $newheight, $width, $height );
		
		$photo = new TPhoto();
		if($photo->stock_dating_folder){
			$photo->_test_and_cree_folders($img,'');
		}
		
		imagejpeg($thumb,$img,80);
		imagedestroy($thumb);
		
		return $img;  
	}
  	function _test_and_cree_folders($fichier1="",$dir=""){
    	// patch correctif:
	    if(strlen($fichier1)>0 && (substr($fichier1, -1))=='/')$fichier1 .='dummy.file';
	
	    @ mkdir(dirname( $dir.$fichier1 ), 0777, true);
	    return true;
	}

	function charge_fichier1($file){
    // propriété permettant de stocker dans des dossiers datés
	    $prefix='';
	    if($this->stock_dating_folder){
	        $date_rep = date("Ymd");
	  		$prefix = $date_rep.'/';
	  		$this->_test_and_cree_folders($prefix,DIR_IMGORIGINE);
	  		//$prefix .= '_';
	    }
		
	    if(is_string($file)){
			$trans=array(" "=>"%20");
			$image_name = $prefix.date("Ymd_His")."_".basename($file);
			if(!copy(strtr($file,$trans),DIR_IMGORIGINE.$image_name))return false;
		} else {
			$image_name = $prefix.date("Ymd_His")."_"._url_format($file['name'], false, true);
			if(!copy($file['tmp_name'],DIR_IMGORIGINE.$image_name))return false;
		}
	
			$this->fichier1 = $image_name;
			return true;
			//$this->fichier1 = $this->retaille_image("normal", false, true); // on force l'image chargée é un certain format
			//$this->retaille_image("miniature");
	}

	function image_hosted($fichier, $w=null, $h=null) {
		$url = 'http://pub.batiactu.com/get-image-in.php?url_image='.$fichier;

		if($w!=null && $h!=null) $url.='&w='.$w.'&h='.$h;

		return file_get_contents($url);

	}	
 
	function retaille_image($mode="normal", $cadre=false, $force=false, $w=0, $h=0){
		/**
		 * Retaille l'image selon le mode choisi
		 * Normal : pas de retaillage
		 * Grand : 250 px max.
		 * Moyen : 175 px max.
		 * Petit : 100 px Max.
		 * miniature : 70px Max.
		 * cadre : ajoute un cadre ou non
		 * force : ... la recréation de l'image
		 * width et height si taille perso
		 * 15/11/2006 16:20:23 Alexis ALGOUD
		 **/

		$xrata = & $this->xrata;
		$yrata = & $this->yrata;

		if($mode=="grand" && $cadre){
			$dir = DIR_FILEIMG_GRANDC;
			$xrata=250;
			$yrata=250;
		}
		elseif($mode=="grand" && !$cadre){
			$dir = DIR_FILEIMG_GRAND;
			$xrata=250;
			$yrata=250;
		}
		elseif($mode=="tgrand" && $cadre){
			$dir = DIR_FILEIMG_TGRANDC;
			$xrata=300;
			$yrata=1000;
		}
		elseif($mode=="tgrand" && !$cadre){
			$dir = DIR_FILEIMG_TGRAND;
			$xrata=300;
			$yrata=1000;
		}
		elseif($mode=="moyen" && $cadre){
			$dir = DIR_FILEIMG_MOYENC;
			$xrata=175;
			$yrata=175;
		}
		elseif($mode=="moyen" && !$cadre){
			$dir = DIR_FILEIMG_MOYEN;
			$xrata=175;
			$yrata=175;
		}
		elseif($mode=="petit" && $cadre){
			$dir = DIR_FILEIMG_PETITC;
			$xrata=100;
			$yrata=100;
		}
		elseif($mode=="petit" && !$cadre){
			$dir = DIR_FILEIMG_PETIT;
			$xrata=100;
			$yrata=100;
		}
		elseif($mode=="diaporama" && !$cadre){
			$dir = DIR_FILEIMG_DIAPO;
			$xrata=400;
			$yrata=400;
		}
		elseif($mode=="diaporama" && $cadre){
			$dir = DIR_FILEIMG_DIAPOC;
			$xrata=400;
			$yrata=400;
		}
		elseif($mode=="miniature" && !$cadre){
			$dir = DIR_FILEIMG_MIN;
			$xrata=70;
			$yrata=52;
		}
		elseif($mode=="miniature" && $cadre){
			$dir = DIR_FILEIMG_MINC;
			$xrata=70;
			$yrata=52;
		}
		elseif($mode=="perso"){
			$dir = DIR_FILEIMG_UNIQUE;
			$xrata = $w;
			$yrata = $h;
			$pre_img =$w."-".$h."-";
		}
		elseif($cadre){
			$dir = DIR_FILEIMG_NORMALC;
			$xrata=640; // on considére que la taille de l'écran est suffissante pour une photo classé normal
			$yrata=480;
		}
		else{
			$dir = DIR_FILEIMG_NORMAL;
			$xrata=800;
			$yrata=800;
		}

		$img = $this->fichier1;
		if(isset($pre_img))$img = $pre_img.$img;
		return $this->_retaille_image($dir,$mode, $img, $xrata, $yrata, $cadre, $force);
	}

	function _retaille_image($dir,$mode, $img, $xrata, $yrata, $cadre, $force){
		if((!is_file($dir.$img)) || ($force)) {
			/**
			 * L'image n'existe pas donc on la crée
			 * 15/11/2006 16:25:17 Alexis ALGOUD
			 **/

			if(!is_dir($dir)){
				mkdir($dir,0777,true);
			}

			$dir_fichier_used = DIR_FILEIMG_NORMAL.$this->fichier1;

			if(!is_file($dir_fichier_used)) {
				$dir_fichier_used = DIR_FILEIMG_NORMAL."defaut.jpg";
			}

			list($width, $height, $type) = getimagesize($dir_fichier_used);

			$this->_get_newdimension($xrata, $yrata, $width, $height, $newwidth, $newheight);
			$this->height=$newheight;
			$this->width=$newwidth;

			if($mode=="miniature" || $this->set_in_place==true){
				$thumb = imagecreatetruecolor($xrata, $yrata);
			}
			else{
				$thumb = imagecreatetruecolor($newwidth, $newheight);
			}


			$kek=imagecolorallocate($thumb,255,255,255);
			imagefill ( $thumb, 0, 0, $kek);

			if($type==1){
				$source= imagecreatefromgif($dir_fichier_used);
				$img = substr($img,0,-4).".jpg";
			}
			elseif($type==3){
				$source= imagecreatefrompng($dir_fichier_used);
				$img = substr($img,0,-4).".jpg";
			}
			else{
				$source= imagecreatefromjpeg($dir_fichier_used);
				//$img = $this->fichier1;
			}

			if($mode=="miniature" || $this->set_in_place==true){
					
				$src_x = 0;
				$src_y = 0;
				$new_yrata=0;
				$new_xrata=0;
				
				$this->_get_dimension_for_in_place($src_x, $src_y, $new_xrata, $new_yrata, $width, $height, $xrata, $yrata);


				//imagecopyresampled(resource, resource , int   dst_x  , int   dst_y  , int   src_x  , int   src_y  , int   dst_w  , int   dst_h  , int   src_w  , int   src_h  )
				imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $xrata, $yrata, $new_xrata, $new_yrata);
			}
			else{
				imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			}

			if($cadre && $this->set_in_place){
				$this->cadre_image($thumb, $xrata, $yrata);
			}
			elseif($cadre) {
				$this->cadre_image($thumb, $newwidth, $newheight);
			}


			//if(isset($pre_img))$img = $pre_img.$img;

			imagejpeg($thumb,$dir.$img,80);

			return $img;
		}
		else{


			list($width, $height, $type) = getimagesize($dir.$img);
			$this->height=$height;
			$this->width=$width;

			return $img;
		}

	}
		 
	function _fill_image_transparent($img){
	
		if($img!=""){
			list($width, $height, $type) = getimagesize($img);
			$thumb = imagecreatetruecolor($width, 250);
						
			$source= imagecreatefromjpeg($img);
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $width, $height);				
		
			$kek=imagecolorallocatealpha($thumb,56,34,21,50); 
			imagefilledrectangle  ($thumb, 305, 0, $width-1, $height-1, $kek);	
			
			$kek2=imagecolorallocate($thumb,200,200,200); 
			
			$w=$width;
			$h=250;
			
			imageline($thumb, 0, 0, $w-1, 0, $kek2);
			imageline($thumb, $w-1, 0, $w-1, $h-1, $kek2);
			imageline($thumb, $w-1, $h-1, 0, $h-1, $kek2);
			imageline($thumb, 0, $h-1, 0, 0, $kek2);
		
			$img_final = "tr-".basename($img);
			imagejpeg($thumb,DIR_FILEIMG_UNIQUE.$img_final,80);
			
			
			
			return DIR_HTTPIMG_UNIQUE.$img_final;
		
		}
		else{
			return "";
		}
	
	}
		
	function _get_dimension_for_in_place(&$src_x, &$src_y, &$new_xrata, &$new_yrata, $width, $height, $xrata, $yrata){
		/*
		 * Dimension avec rognage homotéthique
		 */	
		//cas 1 : w = 200 ; h = 140
		//cas 2 : w = 200 ; h = 450
		//cas 3 : 400x319
		if(($width==$xrata && $height==$yrata) || ($height==.75*$width)) {
			$new_xrata=$width;
			$new_yrata=$height;
		}
		else{

			if($width>$height){ // cas 1
				$new_xrata = $height * 4 / 3; //w=187 | w=425?!
				$new_yrata= $height; //h=140
					
			}
			else { //cas 2
				$new_yrata = $width * 3 / 4; // h=150
				$new_xrata=$width; //w=200


			}

			if($new_xrata>$width ){
				$coef = $width / $new_xrata; // 0.94
			}
			elseif($new_yrata>$height){
				$coef = $height / $new_yrata;
			}
			else{
				$coef = 1;
			}

			$new_xrata = $new_xrata * $coef; //400
			$new_yrata = $new_yrata * $coef; //300

			$src_x = abs($width - $new_xrata) / 2; //src_x = 200-187 /2 = 8

			$src_y = abs($height - $new_yrata) / 2; //src_y = 450-150 / 2 = 150
			
		}
	}

	function cadre_image(&$thumb, $w, $h){
		$kek2=imagecolorallocate($thumb,200,200,200);
		imageline($thumb, 0, 0, $w-1, 0, $kek2);
		imageline($thumb, $w-1, 0, $w-1, $h-1, $kek2);
		imageline($thumb, $w-1, $h-1, 0, $h-1, $kek2);
		imageline($thumb, 0, $h-1, 0, 0, $kek2);
	}

	function _get_newdimension($xrata, $yrata, $width, $height, &$newwidth, &$newheight){
		/**
		 * Calcul les nouvelles dimension de l'image é partir de bornes maximum 
		 * 15/11/2006 16:39:22 Alexis ALGOUD
		 **/
		if($width>$xrata && $width>$height){
			$p = $height / $width;
			$newwidth = $xrata;
			$newheight = $p * $xrata;
		}
		else if ($width>$xrata) {
			$p = $height / $width;
			$newwidth = $xrata;
			$newheight = $p * $xrata;
		}
		else if ($height>$yrata) {
			$p = $width / $height;
			$newwidth = $p * $yrata;
			$newheight = $yrata;
		}
		else{
			$newwidth = $width;
			$newheight = $height;
		}

		if($newwidth>$xrata || $newheight>$yrata){
			$this->_get_newdimension($xrata, $yrata, $newwidth, $newheight, $newwidth, $newheight);
		}
	}
}


