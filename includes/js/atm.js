function getYScroll(){
 var y=0;
 if (document.body)y = document.body.scrollTop;
 return y;
}
function page_scroll_by(y){
	self.scrollBy(0,y);
}

function OpenForm(strUrl){
        window.document.location.href=strUrl;
}
function TListview_OrderBy(tblname,orderColumnOld,orderTypOld,orderColumnNew){
        //orderColumnOld : nom de la dernière colonne à partir de laquelle on a trié
        //orderTypOld : l'ancien ordre de tri
        //orderColumnNew : nom de la colonne sur laquelle on veut trier
        var orderTypNew='A';
        //var strURL=new String(document.location.href);
        if (orderColumnOld>"" && orderColumnNew>"" && orderColumnOld==orderColumnNew && orderTypOld=='A')
                orderTypNew='D';
        //modification de l'URL afin de passer les param�tres de tri
        document.location.href=modifyUrl(modifyUrl(modifyUrl(document.location.href,"orderColumn",orderColumnNew),"orderTyp",orderTypNew),"tblname",tblname);
}
function TListviewUrl_OrderBy(url,tblname,orderColumnOld,orderTypOld,orderColumnNew){
	    // idem précédente sauf url fournie
        var orderTypNew='A';
        if (orderColumnOld>"" && orderColumnNew>"" && orderColumnOld==orderColumnNew && orderTypOld=='A')
                orderTypNew='D';
        
		document.location.href=modifyUrl(modifyUrl(modifyUrl(url,"orderColumn",orderColumnNew),"orderTyp",orderTypNew),"tblname",tblname);
}
function modifyUrl(strURL,paramName,paramNewValue){
        if (strURL.search(paramName+'=')!=-1){
                //on récupère la première partie de l'url
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
function showPopup(strUrl,strFormName,strClickField,w,h){
        var strAdresse="";
		strAdresse+=strUrl;
		
		if((strFormName!='')||(strClickField!='')){
			strAdresse+="?";
		}
		
        if(strFormName!=''){
			strAdresse+="FORM="+strFormName;
		}
    var l=strClickField.length;
    if (strClickField != '' && l>0){
        var param ='', j= 0, k=0;
        while (j<l){
            var i = strClickField.indexOf(';',j);
            if (i==-1) i=l;
			
			if((param!='')||(strFormName!='')){
				param+='&';
			}
			param +='p'+k+'='+strClickField.substring(j,i);
            j=i+1;
            k++;
        }
        strAdresse+=param;
    }

        if(!w)w=400;
        if(!h)h=700;


        window.open(strAdresse,strFormName, "left=10, top=10, width="+w+", height="+h+", resizable=yes,dependent=yes,scrollbars=yes");
}
function LinkForm(strNomForm,strFormData){

    with (window.opener.document){
        for (var i=0;i<forms.length;i++){
            if (forms[i].name==strNomForm){
                                for (var j=0;j<forms[i].elements.length;j++){
                    //on cherche si un champ du formulaire se trouve dans la liste affich�e
                     var iIndex=strFormData.indexOf(forms[i].elements[j].name+'=',0);
                    if ((iIndex)>-1){
                        iIndex+=(forms[i].elements[j].name).length+1;
                        if (strFormData.indexOf(';',iIndex)>-1){
                                var strValue=strFormData.substring(iIndex,strFormData.indexOf(';',iIndex));
                                forms[i].elements[j].value=strValue;
                        }
                    }
            }
        }
    }
  }
  window.opener=null;
  self.close();
}

function TDBListview_PreviousPage(pageNumber,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber>0){
                pageNumber--;
               
                document.location.href=modifyUrl(modifyUrl(document.location.href,"pageNumber",pageNumber),"tblname",tblname);
                }
}

function TDBListview_NextPage(pageNumber,nbPage,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber<nbPage){
                pageNumber++;
                
                document.location.href=modifyUrl(modifyUrl(document.location.href,"pageNumber",pageNumber),"tblname",tblname);
        }
}

function TDBListview_GoToPage(pageNumber,nbPage,tblname){
	if(tblname==null){
		tblname=".";
	}
        if (pageNumber<=nbPage && pageNumber>=0){
                
                document.location.href=modifyUrl(modifyUrl(document.location.href,"pageNumber",pageNumber),"tblname",tblname);
        }
}


function TDBListviewUrl_PreviousPage(url,pageNumber,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber>0){
                pageNumber--;
               
                document.location.href=modifyUrl(modifyUrl(url,"pageNumber",pageNumber),"tblname",tblname);
                }
}

function TDBListviewUrl_NextPage(url,pageNumber,nbPage,tblname) {

	if(tblname==null){
		tblname=".";
	}

        if (pageNumber<nbPage){
                pageNumber++;
                
                document.location.href=modifyUrl(modifyUrl(url,"pageNumber",pageNumber),"tblname",tblname);
        }
}

function TDBListviewUrl_GoToPage(url,pageNumber,nbPage,tblname){
	if(tblname==null){
		tblname=".";
	}
        if (pageNumber<=nbPage && pageNumber>=0){
                
                document.location.href=modifyUrl(modifyUrl(url,"pageNumber",pageNumber),"tblname",tblname);
        }
}
function LinkForm2(strNomForm,strFormData){

   with (window.opener.document){
        for (var i=0;i<forms.length;i++){
            if (forms[i].name==strNomForm){
                for (var j=0;j<forms[i].elements.length;j++){
                    //on cherche si un champ du formulaire se trouve dans la liste affichée
                    var iIndex=strFormData.indexOf(forms[i].elements[j].name+'=',0);
                    if ((iIndex)>-1){
                        iIndex+=(forms[i].elements[j].name).length+1;
                        if (strFormData.indexOf(';',iIndex)>-1){
                                var strValue=strFormData.substring(iIndex,strFormData.indexOf(';',iIndex));
                                forms[i].elements[j].value=strValue;
                        }
                    }
                }
				forms[i].submit();
        	}
    	}
		
    }
  
 
  window.opener=null;
  self.close();
}
