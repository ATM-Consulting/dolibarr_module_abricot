<?php
  
/**
 * Classe gérant les réponses mail (récup de l'emploi)
 *
 * @version $Id$
 * @copyright 2006 
 */

class TReponseMail{
	/**
     * Constructor
     * @access protected
     */
	function TReponseMail($emailfrom="",$emailto="",$titre="",$corps=""){
		$this->emailfrom=$emailfrom;
		$this->emailto=$emailto;
		$this->titre=$titre;
		$this->corps=$corps;
		$this->emailtoBcc="";
		$this->reply_to='';
		$this->TPiece=array();
		$this->boundary = "_".md5 (uniqid (rand())); 
		
		$this->emailerror = defined('WEBMASTER_MAIL') ? WEBMASTER_MAIL : "webmaster@atm-consulting.com";
		
	}
	/**
	 * envoi la réponse ainsi générée 
	 * 20/09/2006 11:31:04 Alexis ALGOUD
	 * par défaut en ISO-8859-1
	 **/
	function send($html=true,$encoding='iso-8859-1'){
		$html = ($html == 'html')?true:$html;
		
		$headers="";
		$headers .= "From:".$this->emailfrom."\n";
		$headers .= "Message-ID: <".time().rand()."@".$_SERVER['SERVER_NAME'].">\n";
		$headers .= "X-Mailer: PHP v".phpversion()." \n";
		$headers .= "X-Sender: <".$this->emailfrom.">\n";
		$headers .= "X-auth-smtp-user: ".$this->emailerror." \n";
		$headers .= "X-abuse-contact: ".$this->emailerror." \n"; 
		if($this->reply_to==""){
			$this->reply_to = $this->emailfrom;
		}

		//empeche l'envoi de mail vers l'extérieur en préprod et en dev.		
		if((defined('ENV'))&& ( ENV =="DEV" || ENV =="PREPROD" ) ){
			$this->emailto = EMAIL_TO;
		}
		
		$headers .= "Reply-To: ".$this->reply_to." \n";
		$headers .= "Return-path: ".$this->reply_to." \n";
		//
		if($this->emailtoBcc!=''){
			$headers.="Bcc: ".$this->emailtoBcc."\n";
		}
		$headers .= "Date:" . date("D, d M Y H:i:s") . " \n";
		$headers .="MIME-Version: 1.0\n";
		
		if(count($this->TPiece)>0) {
			$headers .= "Content-type: multipart/mixed; boundary=\"".$this->boundary."\"\n\n";
			$headers .= "--".$this->boundary."\n";
			$headers .= "Content-Type: ".(($html)?"text/html":"text/plain")."; charset=".$encoding."\r\n\n";
			$headers .= $this->corps."\n\n";
			foreach($this->TPiece as $piece){
				$headers .= $piece."\n\n";
			}
			$headers .= "--" . $this->boundary . "--"; 
		}
		else {
			if ($html) $headers.= "Content-type: text/html; charset=\"".$encoding."\" \n";
			else $headers.= "Content-Type: text/plain; charset=\"".$encoding."\" \n";
			$headers.= "Content-Transfer-Encoding: 8bit \n";
			//die('count');
		}
		
		return mail($this->emailto,$this->titre,$this->corps,$headers, "-f".$this->emailerror);
	}
	
	
	public function add_piece_jointe($nom_fichier, $chemin_fichier, $type="application/pdf") {
		$fichier = file_get_contents($chemin_fichier);
		$fichier=chunk_split( base64_encode($fichier) );
		//$boundary = md5($fichier);
		//écriture de la pièce jointe
		$body = "--" .$this->boundary. "\n";
		$body .="Content-Type: $type; name=\"$nom_fichier\"\n";
		$body .="Content-Transfer-Encoding: base64\n";
		$body .="Content-Disposition: attachment; filename=\"$nom_fichier\"\n\n";
		$body .=$fichier;
		$piece = $body;
		$this->TPiece[]=$piece;
	
	}
	
	public function get_mime_type($file) {
		// our list of mime types
		$mime_types = array(
		"pdf"=>"application/pdf"
		,"exe"=>"application/octet-stream"
		,"zip"=>"application/zip"
		,"docx"=>"application/msword"
		,"doc"=>"application/msword"
		,"xls"=>"application/vnd.ms-excel"
		,"ppt"=>"application/vnd.ms-powerpoint"
		,"gif"=>"image/gif"
		,"png"=>"image/png"
		,"jpeg"=>"image/jpg"
		,"jpg"=>"image/jpg"
		,"mp3"=>"audio/mpeg"
		,"wav"=>"audio/x-wav"
		,"mpeg"=>"video/mpeg"
		,"mpg"=>"video/mpeg"
		,"mpe"=>"video/mpeg"
		,"mov"=>"video/quicktime"
		,"avi"=>"video/x-msvideo"
		,"3gp"=>"video/3gpp"
		,"css"=>"text/css"
		,"jsc"=>"application/javascript"
		
		,"js"=>"application/javascript"
		
		,"php"=>"text/html"
		
		,"htm"=>"text/html"
		
		,"html"=>"text/html"
		
		);
		
		
		
		$extension = strtolower(end(explode('.',$file)));
		
		
		if(!array_key_exists($extension,$mime_types)){
			return "application/octet-stream";
		}else{
			return $mime_types[$extension];
		}
		
	}

	
	public function preview(){
		
		echo '<hr>Preview de mail :';
		echo '<ul>';
		echo '<li>from : '.$this->emailfrom.'</li>';
		echo '    reply to : '.$this->reply_to.'</li>';
		echo '<li>to : '.$this->emailto.'</li>';
		echo '<li>email error : '.$this->emailerror.'</li>';
		echo '<li>titre : '.$this->titre.'</li>';
		echo '<li>corps : <br>================================================================================<br>';
		echo $this->corps;
		echo '<br>================================================================================<br>';
		echo '</li>';
		echo '</ul>';
		
	}
	
	
}  

