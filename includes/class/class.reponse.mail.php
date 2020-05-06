<?php
  
/*
 Copyright (C) 2006-2013 Alexis Algoud <azriel68@gmail.com>
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

class TReponseMail{
	/**
     * Constructor
     * @access protected
     */
	function TReponseMail($emailfrom="",$emailto="",$titre="",$corps=""){
		global $conf;
		
		$this->emailfrom=$emailfrom;
		$this->emailto=$emailto;
		$this->titre=$titre;
		$this->corps=$corps;
		$this->emailtoBcc="";
		$this->reply_to='';
		$this->TPiece=array();
		$this->boundary = "_".md5 (uniqid (rand())); 
		
		$this->emailerror = !empty($conf->global->MAIN_MAIL_ERRORS_TO) ? $conf->global->MAIN_MAIL_ERRORS_TO : $conf->global->MAIN_MAIL_EMAIL_FROM;
		
		$this->use_dolibarr_for_smtp = true;		
	}
	/**
	 * envoi la réponse ainsi générée 
	 * 20/09/2006 11:31:04 Alexis ALGOUD
	 * par défaut en ISO-8859-1
	 **/
	function send($html=true,$encoding='iso-8859-1'){
		global $conf;

		if(!empty($conf->global->ABRICOT_MAILS_FORMAT)) $encoding=$conf->global->ABRICOT_MAILS_FORMAT;

		if(!empty($conf->global->MAIN_DISABLE_ALL_MAILS)) return false; // désactivé globalement
		
		if($this->reply_to==""){
			$this->reply_to = $this->emailfrom;
		}

		//empeche l'envoi de mail vers l'extérieur en préprod et en dev.		
		if((defined('ENV'))&& ( ENV =="DEV" || ENV =="PREPROD" ) ){
			$this->emailto = EMAIL_TO;
		}

//var_dump($this->use_dolibarr_for_smtp , $conf->global->MAIN_MAIL_SENDMODE , $this->TPiece);exit;

		if($this->use_dolibarr_for_smtp && in_array($conf->global->MAIN_MAIL_SENDMODE, array('smtps', 'swiftmailer'))) {
			// Si la conf global indique du smtp et qu'il n'y a pas de pièce jointe, envoi via dolibarr
			dol_include_once('/core/class/CMailFile.class.php');
			if(class_exists('CMailFile')) {
				$TFilePath = $TMimeType = $TFileName = array();
				foreach($this->TPiece as &$piece) {
					$TFilePath[] = $piece['file'];
					$TMimeType[] = $piece['mimetype'];
					$TFileName[] = !empty($piece['name']) ? $piece['name'] : basename($piece['file']);
				}
																												//,$filepath,$mimetype,$filename
				$mail=new CMailFile($this->titre, $this->emailto, $this->emailfrom, $this->corps,$TFilePath,$TMimeType,$TFileName,'',$this->emailtoBcc,0,$html );
				$res = $mail->sendfile();
//exit('sendfile');				
				return $res;
				
			}
			
		}
		
		
		$html = ($html == 'html')?true:$html;
		
		$headers="";
		$headers .= "From:".$this->emailfrom."\n";
		$headers .= "Message-ID: <".time().rand()."@".$_SERVER['SERVER_NAME'].">\n";
		$headers .= "X-Mailer: PHP v".phpversion()." \n";
		$headers .= "X-Sender: <".$this->emailfrom.">\n";
		$headers .= "X-auth-smtp-user: ".$this->emailerror." \n";
		$headers .= "X-abuse-contact: ".$this->emailerror." \n"; 

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
			$body = "--".$this->boundary."\n";
			$body .= "Content-Type: ".(($html)?"text/html":"text/plain")."; charset=".$encoding."\r\n\n";
			$body .= $this->corps."\n\n";
			foreach($this->TPiece as $piece){
				$body .= $piece['data']."\n\n";
			}
			$body .= "--" . $this->boundary . "--"; 
		}
		else {
			if ($html) $headers.= "Content-type: text/html; charset=\"".$encoding."\" \n";
			else $headers.= "Content-Type: text/plain; charset=\"".$encoding."\" \n";
			$headers.= "Content-Transfer-Encoding: 8bit \n";
			$body = $this->corps;
			//die('count');
		}
		
		return mail($this->emailto,$this->titre,$body,$headers, "-f".$this->emailerror);
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
		$this->TPiece[]=array(
			'file'=>$chemin_fichier
			,'mimetype'=>$type
			,'data'=>$piece
			,'name'=>$nom_fichier
		);
	
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

