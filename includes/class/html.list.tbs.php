<table width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 2px;">
		<tr>
			[onshow;block=begin; when [liste.noheader]==0]
			<td class="nobordernopadding" width="40" align="left" valign="middle">
				[liste.image;magnet=img; strconv=no]
			</td>
			<td class="nobordernopadding"><div class="titre">[liste.titre; strconv=no]</div></td>
			[onshow;block=end]
			<td class="nobordernopadding" align="right" valign="middle">
				<div> 
					[onshow;block=div; when [liste.havePage]+-0 ]
				<!-- [onshow;block=div;when [pagination.last]+-1 ] -->
				    <a class="precedent" href="javascript:TListTBS_GoToPage('[liste.id]',[pagination.prev])"><!-- [pagination.prev;endpoint;magnet=a] -->[liste.picto_precedent;strconv=no]</a>
				    Page
				    <a class="page" href="javascript:TListTBS_GoToPage('[liste.id]',[pagination.page;navsize=15;navpos=centred])"> [pagination.page;block=a] </a>
				    <u>[pagination.page;block=u;currpage]</u>
				    <a class="suivant" href="javascript:TListTBS_GoToPage('[liste.id]',[pagination.next])"><!-- [pagination.last;endpoint;magnet=a] -->[liste.picto_suivant;strconv=no]</a>
				</div>
			</td>
		</tr>
</table>	

<table id="[liste.id]" class="liste" width="100%">
	<thead>
		<tr class="liste_titre">
			<th class="liste_titre">[entete.libelle;block=th;strconv=no] 
				<span>[onshow;block=span; when [entete.order]==1]<a href="javascript:TListTBS_OrderDown('[liste.id]','[entete.$;strconv=js]')">[liste.order_down;strconv=no]</a><a href="javascript:TListTBS_OrderUp('[liste.id]', '[entete.$;strconv=js]')">[liste.order_up;strconv=no]</a></span>
			</th>
		</tr>
		
		<tr class="liste_titre barre-recherche">[onshow;block=tr;when [liste.nbSearch]+-0]
			<td class="liste_titre">[recherche.val;block=td;strconv=no]</td>
		</tr>
	
	</thead>
	<tbody>
		<tr class="impair">
			<!-- [champs.$;block=tr;sub1] -->
			<td>[champs_sub1.val;block=td; strconv=no]</td>
		</tr>
		<tr class="pair">
			<!-- [champs.$;block=tr;sub1] -->
			<td>[champs_sub1.val;block=td; strconv=no]</td>
		</tr>
	</tbody>
	<tfoot>
		<tr class="liste_total">
			[onshow;block=tr; when [liste.haveTotal]+-0 ]
			<td align="right">[total.val;block=td;strconv=no;frm=0 000,00]</td>
		</tr>
	</tfoot>
	
</table>
<p align="center">
	[liste.messageNothing] [onshow; block=p; strconv=no; when [liste.totalNB]==0]
</p>
	