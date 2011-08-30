{br}
<p>There are {$count} products that have non-unicode characters in it.</p>

 <div id="products">
	<table id="prods" class="exp-skin-table" style="width:95%">
	<thead>
		<tr>
			<th>Model</th>
			<th>Title</th>
			<th>Non-Unicode Field(s)</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$products item=listing name=listings}
		<tr class="{cycle values="odd,even"}">
			<td>{$listing.model|default:"N/A"}</td> 
			<td><a href={link controller=store action=showByTitle title=$listing.sef_url}>{$listing.title}</a></td>
			<td>{$listing.nonunicode}</td> 
			<td>
				{permissions level=$smarty.const.UILEVEL_NORMAL}
					{if $permissions.edit == 1}
						{icon img=edit.png action=edit id=$listing.id title="Edit `$listing.title`"}
					{/if}
				{/permissions}  
			</td>                   
		</tr>
		{/foreach}
	</tbody>
	</table>
	{br}
	<a href={link controller=store action=cleanNonUnicodeProducts} onclick="return confirm('Are you sure you want to clean all of the products shown above?');">Clean Data</a>
</div>