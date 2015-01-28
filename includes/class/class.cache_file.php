<?php

/*
 Copyright (C) 2003-2013 Alexis Algoud <azriel68@gmail.com>

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

 
class TCacheFile{

  function TCacheFile(){
  // retourne le contenu correspondant
    $this->clef = 'id';
    $this->dir = ROOT.'tmp/_cache_file/';
    $this->to_log=false;
    $this->ttl = 60 * 60 ; // 60' par défaut
  }
  
  function get($hash) {
  		  $file = $this->dir.$hash.".data";
		  
		  $cpt=0;
		  while($cpt<50) {
		  	
			  @$f1 = file_get_contents($file);
			  
			  if($f1===false) {
			  	sleep(1);
			  	$cpt++;
			  }	
			  else {
			  	return unserialize($f1);
				
				break;	
			  }
		  }
		  
		  @unlink($this->dir.$hash.".data");
		  
		  return false;
  		  
  }
  function put($hash, $data) {
  		 
  		  $file = $this->dir.$hash.".data";	
  		  $f1=fopen($file,"w");
	      fputs($f1, serialize($data));
	      fclose($f1);
		  
		  return $file;
  }
  function sql_get(&$db, $sql) {
	/* rechercherche une requete SQL */
		$hash = md5($sql);
		
		if((int)rand(0,100)==0)$this->purge();	
	    $file = $this->dir.$hash.".sql_cache";
	    
	    if(!is_dir($this->dir)){
	      @mkdir($this->dir, 0777);
	    }
	    
	    if(is_file($file) && (filemtime($file)<( time() - $this->ttl )) ){
	      $Tab=$this->get_tab_by_sql($db, $sql);
	      if($Tab!==false)	@unlink($file);
	      else null; // echec de la réception du fichier, on conserve celui en place
	    }
	    
	    if(!is_file($file)){
	      if(!isset($Tab)) $Tab=$this->get_tab_by_sql($db, $sql);
	      $f1=fopen($file,"w");
	      fputs($f1, serialize($Tab));
	      fclose($f1);
	      
	      if($this->to_log){
	        @$f1 = fopen($this->dir."hash-".date("Ymd").".log","a");
	        @fputs($f1, $hash."\t".date("H:i:s d/m/Y")."\t".$sql."\r\n--------\r\n" );
	        @fclose($f1);
	      }
	      
	    }
	    else{
			@$Tab = unserialize(file_get_contents($file));
	    }
	  
	    return $Tab;
			
			
	}
	function get_tab_by_sql(&$db, $sql) {
		$Tab=array();
	
		if(is_string($db) && $db=='auto') $db=new Tdb;
	
		$db->Execute($sql);
		$THeader = array_keys($db->Get_lineHeader());	
		while($db->Get_line()){
   
		    $row=array();
		    foreach ($THeader as $key) {
		    	$row[$key]=$db->Get_field($key);
		    }
		    
		    $Tab[]=$row;
		}
		
		return $Tab;
	}
	
  function file_get($url){
  
  
  
    $hash = md5($url);

    $file = $this->dir.$hash.".file_cache";
    
    if(!is_dir($this->dir)){
      @mkdir($this->dir, 0777);
    }
    
    if(is_file($file) && (filemtime($file) < (time() - $this->ttl) ) ){
      $body=$this->get_file_by_url($url);
      
      
      if($body!==false)	@unlink($file);
      else null; // echec de la réception du fichier, on conserve celui en place
    }
    
	//die('FICHIER EN CACHE TTL'.$this->ttl);
	
    if(!is_file($file)){
      if(!isset($body)) $body=$this->get_file_by_url($url);
      $f1=fopen($file,"w");
      fputs($f1, $body);
      fclose($f1);
      
      if($this->to_log){
        @$f1 = fopen($this->dir."hash-".date("Ymd").".log","a");
        @fputs($f1, $hash."\t".date("H:i:s d/m/Y")."\t".$url."\r\n--------\r\n" );
        @fclose($f1);
      }
      
    }
    else{
    	
      $body = file_get_contents($file);
    }
  
    return $body;
  
  }

  function get_file_by_url($url){
    if((int)rand(0,100)==0)$this->purge();
    
    $body="";
    $body = @file_get_contents($url);
    
    return $body;
  }

  function purge(){
  
    if ($handle = opendir($this->dir)) {
       /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) {
            if($file[0]!='.'){
                if(filemtime($this->dir.$file)<( time() - ($this->ttl*10) )){ // suppression des fichier 10x plus vieux que le max (au cas où)
                  @unlink($this->dir.$file);
                }
            }
        }
    
        closedir($handle);
    }
  
    if($this->to_log){
        @$f1 = fopen($this->dir."hash-".date("Ymd").".log","a");
        @fputs($f1, "PURGE\t".date("H:i:s d/m/Y")."\r\n--------\r\n" );
        @fclose($f1);
    }
  }

}

