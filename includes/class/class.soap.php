<?php
class TSOAPCoreConnector {
	
	function __construct($soapWSDL, $login, $password) {
		$this->login  = $login;
		$this->password = $password;
		
		$this->soap = new SoapClient($soapWSDL);
	
	}
	public function call($function, $params=array()) {
		return $this->soap->{$function}($this->login, $this->password);
	}
	function getFonctionListe() {
		print '<pre>';	
		var_dump($this->soap->__getFunctions());
		print '</pre>';
	}
}


class TRESTCoreConnector {
	function __construct() {
			$this->typeOut='xml';
	}
	public function url($url) {
		$this->_url = $url;
		return $this;
	}
   public function get($pParams = array())
   {
      return $this->out($this->_launch($this->_makeUrl($pParams),
                            $this->_createContext('GET')));
   }
 
   public function create($pPostParams=array(), $pGetParams = array())
   {
      return $this->out($this->_launch($this->_makeUrl($pGetParams),
                            $this->_createContext('POST', $pPostParams)));
   }
    
   public function update($pContent = null, $pGetParams = array())
   {
      return $this->out($this->_launch($this->_makeUrl($pGetParams),
                            $this->_createContext('PUT', $pContent)));
   }
    
   public function delete($pContent = null, $pGetParams = array())
   {
      return $this->out($this->_launch($this->_makeUrl($pGetParams),
                            $this->_createContext('DELETE', $pContent)));
   }
  private function xml2array($xml) {
	  $arr = array();
	  foreach ($xml as $element) {
	    $tag = $element->getName();
	    $e = get_object_vars($element);
	    if (!empty($e)) {
	      $arr[$tag] = $element instanceof SimpleXMLElement ? $this->xml2array($element) : $e;
	    }
	    else {
	      $arr[$tag] = trim($element);
	    }
	  }
	  return $arr;
   }
   private function out($data) {
   		
		if($this->typeOut=='array') {
			$Tab=$this->xml2array(simplexml_load_string($data['content']));
			
			return $Tab;
		}
		else {
			return simplexml_load_string($data['content']);	
		}
		
	
   }    
	
   protected function _createContext($pMethod, $pContent = null)
   {
      $opts = array(
              'http'=>array(
                            'method'=>$pMethod,
                            'header'=>'Content-type: application/x-www-form-urlencoded',
                          )
      );
      if ($pContent !== null){
         if (is_array($pContent)){
            $pContent = http_build_query($pContent);
         }
         $opts['http']['content'] = $pContent;
      }
      return stream_context_create($opts);
   }
    
   protected function _makeUrl($pParams)
   {
      return $this->_url
             .(strpos($this->_url, '?') ? '' : '?')
             .http_build_query($pParams);
   }
    
   protected function _launch ($pUrl, $context)
   {
   	//print $pUrl;
      if (($stream = fopen($pUrl, 'r', false, $context)) !== false){
         $content = stream_get_contents($stream);
         $header = stream_get_meta_data($stream);
         fclose($stream);
         return array('content'=>$content, 'header'=>$header);
        }else{
         return false;
        }
   }
   
   function getFonctionListe() {
		$xml = simplexml_load_string( file_get_contents( $this->_url ) );
		
		?>
		<ul>
		<?php
		
		foreach($xml->api->children() as $module=>$more) {
			?><li><strong><?php echo $module."</strong> ". htmlentities($more->asXML()); ?></li><?php 
		}
		
		?>
		</ul>
		<?php
   }
   function asArray() {
   		$this->typeOut='array';
	
		return $this;
   }	
   function asXML() {
   		$this->typeOut='xml';
	
		return $this;
   }	
}
