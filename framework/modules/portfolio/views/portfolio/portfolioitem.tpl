<div class="item">
	<h3><a href="{link action=show title=$record->sef_url}" title="{$record->title|escape:"htmlall"}">{$record->title}</a></h3>
	{permissions}
		<div class="item-actions">
			{if $permissions.edit == 1}
				{icon action=edit record=$record title="Edit `$record->title`"}
			{/if}
			{if $permissions.delete == 1}
				{icon action=delete record=$record title="Delete `$record->title`"}
			{/if}
		</div>
	{/permissions}
	{if $record->expTag|@count>0}
		<div class="tag">
			{'Tags'|gettext}:
			{foreach from=$record->expTag item=tag name=tags}
				<a href="{link action=showall_by_tags tag=$tag->sef_url}">{$tag->title}</a>{if $smarty.foreach.tags.last != 1},{/if}
			{/foreach}
		</div>
	{/if}
    <div class="bodycopy">
        {filedisplayer view="`$config.filedisplay`" files=$record->expFile record=$record is_listing=1}
        {if $config.usebody==1}
            <p>{$record->body|summarize:"html":"paralinks"}</p>
        {elseif $config.usebody==2}
        {else}
            {$record->body}
        {/if}
    </div>
    {permissions}
        {if $permissions.create == 1}
            {icon class="add addhere" action=edit rank=$record->rank+1 title="Add another here"|gettext  text="Add a portfolio piece here"|gettext}
        {/if}
    {/permissions}
    {clear}
</div>