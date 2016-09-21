var TListTBS_include = true;

function TListTBS_OrderDown(idListe, column) {
	var base_url = document.location.href;
	
	base_url = TListTBS_recup_form_param(idListe,base_url);
	base_url = TListTBS_removeParam(base_url,'TListTBS['+encodeURIComponent(idListe)+'][orderBy]');
	
	document.location.href=TListTBS_modifyUrl(base_url,"TListTBS["+encodeURIComponent(idListe)+"][orderBy]["+encodeURIComponent(column)+"]","DESC");
}
function TListTBS_OrderUp(idListe, column) {
	
	var base_url = document.location.href;
	
	base_url = TListTBS_recup_form_param(idListe,base_url);
	base_url = TListTBS_removeParam(base_url,'TListTBS['+encodeURIComponent(idListe)+'][orderBy]');
	
	document.location.href=TListTBS_modifyUrl(base_url,"TListTBS["+encodeURIComponent(idListe)+"][orderBy]["+encodeURIComponent(column)+"]","ASC");
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

function TListTBS_recup_form_param(idListe,base_url) {
	
	$('#'+idListe+' tr.barre-recherche [listviewtbs]').each(function(i,item) {
		
		base_url = TListTBS_modifyUrl(base_url, $(item).attr("name") , $(item).val());
		
	});
	
	return base_url;
}

function TListTBS_GoToPage(idListe,pageNumber){
	
	var base_url = document.location.href;
	
	base_url = TListTBS_recup_form_param(idListe,base_url);
	base_url =TListTBS_modifyUrl(base_url,"TListTBS["+encodeURIComponent(idListe)+"][page]",pageNumber);
	
	document.location.href=base_url;
}
function TListTBS_submitSearch(obj) {
	
	$(obj).closest('form').submit();
	//console.log($(obj).closest('form'));
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
