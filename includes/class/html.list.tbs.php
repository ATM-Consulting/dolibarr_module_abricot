<table width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 2px;">
		<tr>
			[onshow;block=begin; when [liste.noheader]==0]
			<td class="nobordernopadding" width="40" align="left" valign="middle">
				[liste.image;magnet=img; strconv=no]
			</td>
			<td class="nobordernopadding"><div class="titre">[liste.titre; strconv=no]</div></td>
			[onshow;block=end]
			<td class="nobordernopadding" align="right" valign="middle">
				<div class="pagination">
					[onshow;block=div; when [liste.havePage]+-0 ]
					<!-- [onshow;block=div;when [pagination.last]+-1 ] -->
					<ul style="display: inline-block; list-style: outside none none;">
						<li class="pagination" style="display: inline-block;"><a class="paginationprevious" href="javascript:TListTBS_GoToPage('[liste.id]',[pagination.prev])"><!-- [pagination.prev;endpoint;magnet=li] --> [liste.picto_precedent;strconv=no] </a></li>
						<li class="pagination" style="display: inline-block;"><a class="page" href="javascript:TListTBS_GoToPage('[liste.id]',[pagination.page;navsize=15;navpos=centred])"> [pagination.page;block=li] </a></li>
						<li class="pagination" style="display: inline-block;"><span class="active"> [pagination.page;block=li;currpage] </span></li>
						<li class="pagination" style="display: inline-block;"><a class="paginationnext" href="javascript:TListTBS_GoToPage('[liste.id]',[pagination.next])"><!-- [pagination.last;endpoint;magnet=li] --> [liste.picto_suivant;strconv=no] </a></li>
					</ul>
				</div>
			</td>
		</tr>
</table>

<table id="[liste.id]" class="liste" width="100%">
	<thead>
		<tr class="liste_titre barre-recherche-head">
			<td colspan="[liste.nb_columns]">[liste.head_search;strconv=no;magnet=tr]</td>
		</tr>
		<tr class="liste_titre barre-recherche">[onshow;block=tr;when [liste.nbSearch]+-0]
			<td class="liste_titre">[recherche.val;block=td;strconv=no]</td>
		</tr>
		<tr class="liste_titre">
			<th style="width:[entete.width;];text-align:[entete.text-align]" class="liste_titre">[entete.libelle;block=th;strconv=no]
				<span class="nowrap">[onshow;block=span; when [entete.order]==1]<a href="javascript:TListTBS_OrderDown('[liste.id]','[entete.$;strconv=js]')">[liste.order_down;strconv=no]</a><a href="javascript:TListTBS_OrderUp('[liste.id]', '[entete.$;strconv=js]')">[liste.order_up;strconv=no]</a></span>
				[entete.more;strconv=no;]
			</th>
		</tr>



	</thead>
	<tbody>
		<tr class="impair">
			<!-- [champs.$;block=tr;sub1] -->
			<td field="[champs_sub1.$]">[champs_sub1.val;block=td; strconv=no]</td>
		</tr>
		<tr class="pair">
			<!-- [champs.$;block=tr;sub1] -->
			<td field="[champs_sub1.$]">[champs_sub1.val;block=td; strconv=no]</td>
		</tr>
	</tbody>
	<tfoot>
		<tr class="liste_total">
			[onshow;block=tr; when [liste.haveTotal]+-0 ]
			<td field="[total.$]">[total.val;block=td;strconv=no;frm=0 000,00]</td>
		</tr>
	</tfoot>

</table>

[onshow;block=begin; when [liste.useBottomPagination]==1]
<table width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 2px;">
		<tr>
			<td id="pagination_bottom" class="nobordernopadding" align="right" valign="middle">
				<script type="text/javascript">
					if (typeof $ != "undefined")
					{
						$(function() {
							var tbs_pagination = $('div.pagination').clone();
							$('#pagination_bottom').append(tbs_pagination);
						});
					}
				</script>
			</td>
		</tr>
</table>
[onshow;block=end]

<div class="tabsAction">
	[onshow;block=div; when [liste.haveExport]+-0 ]
	<a href="javascript:;" onclick="TListTBS_downloadAs(this, '[export.mode]','[export.url]','[export.token]','[export.session_name]');" class="butAction">[export.label;block=a;]</a>
</div>
<p align="center">
	[liste.messageNothing;strconv=no] [onshow; block=p;  when [liste.totalNB]==0]
</p>