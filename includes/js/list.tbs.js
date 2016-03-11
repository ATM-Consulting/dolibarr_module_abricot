var TListTBS_include = true;

function TListTBS_OrderDown(idListe, column) {
	url = TListTBS_removeParam(document.location.href,'TListTBS['+encodeURIComponent(idListe)+'][orderBy]');
	document.location.href=TListTBS_modifyUrl(url,"TListTBS["+encodeURIComponent(idListe)+"][orderBy]["+encodeURIComponent(column)+"]","DESC");
}
function TListTBS_OrderUp(idListe, column) {
	url = TListTBS_removeParam(document.location.href,'TListTBS['+encodeURIComponent(idListe)+'][orderBy]');
	document.location.href=TListTBS_modifyUrl(url,"TListTBS["+encodeURIComponent(idListe)+"][orderBy]["+encodeURIComponent(column)+"]","ASC");
}
function TListTBS_modifyUrl(strURL,paramName,paramNewValue){
	    if (strURL.indexOf(paramName+'=')!=-1){
        	
                var strFirstPart=strURL.substring(0,strURL.indexOf(paramName+'=',0))+paramName+'=';
                var strLastPart="";
                if (strURL.indexOf('&',strFirstPart.length-1)>0)
                      strLastPart=strURL.substring(strURL.indexOf('&',strFirstPart.length-1),strURL.length);
              		  strURL=strFirstPart+paramNewValue+strLastPart;
                }
        else{
                if (strURL.search('=')!=-1) // permet de verifier s'il y a dej� des param�tres dans l'URL
                        strURL+='&'+paramName+'='+paramNewValue;
                else
                        strURL+='?'+paramName+'='+paramNewValue;
        }
        
        return strURL;
}
function TListTBS_removeParam(strURL, paramMask) {
	var cpt=0;
	var url = '';
	
	 while(strURL.indexOf(paramMask)!=-1 && cpt++ <50){
	 	var strFirstPart= strURL.substring(0,strURL.indexOf(paramMask)-1);
	 	
	 	var strLastPart='';
	 	if (strURL.indexOf('&',strFirstPart.length+1)>0) {
	 		strLastPart = strURL.substring(strURL.indexOf('&',strFirstPart.length+1),strURL.length);	
	 	}
	 		
		url = strFirstPart+strLastPart;
	 	
	 }
	 
	 if(url=='')url = strURL;
	 
	 return url;
}
function TListTBS_GoToPage(idListe,pageNumber){
	document.location.href=TListTBS_modifyUrl(document.location.href,"TListTBS["+encodeURIComponent(idListe)+"][page]",pageNumber);
}
function TListTBS_submitSearch(obj) {
	$parent = $(obj).parent();
//alert($parent.prop('tagName'));
	var cpt=0;
	while($parent.prop('tagName').toLowerCase()!='form' && cpt<30) {
		//alert($parent.prop('tagName'));
		$parent = $parent.parent(); 
		cpt++;
	}

	$parent.get(0).submit();
	
	//alert(.html());	
}
function TListTBS_downloadAs(mode,url,token,session_name) {
	$('div#listTBSdAS').remove();
	
	$('body').append('<div id="listTBSdAS" style="display:none;"><iframe id="listTBSdAS_iframe" src=""></iframe></div>');
	$('div#listTBSdAS').append('<form action="'+url+'" method="post" target="listTBSdAS_iframe"></form>');
	$('div#listTBSdAS form').append('<input type="hidden" name="mode" value="'+mode+'" />');
	$('div#listTBSdAS form').append('<input type="hidden" name="token" value="'+token+'" />');
	$('div#listTBSdAS form').append('<input type="hidden" name="session_name" value="'+session_name+'" />');
	
	
    $('div#listTBSdAS form').submit();
}
function postToIframe(data,url,target){
 
    
}

$(document).ready(function() {
	$('tr.barre-recherche input').keypress(function(e) {
    if(e.which == 13) {
       
       var id_list = $(this).closest('table').attr('id');
       
       $('#'+id_list+' .list-search-link').click();
       
    }
});
});
