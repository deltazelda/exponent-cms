{*
 * Copyright (c) 2004-2011 OIC Group, Inc.
 *
 * This file is part of Exponent
 *
 * Exponent is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * GPL: http://www.gnu.org/licenses/gpl.txt
 *
 *}

{css unique="nav-manager1" link="`$smarty.const.YUI2_PATH`treeview/assets/skins/sam/treeview.css" corecss="panels"}

{/css}

{css unique="nav-manager2" link="`$smarty.const.YUI2_PATH`menu/assets/skins/sam/menu.css"}

{/css}

{css unique="nav-manager3" link="`$smarty.const.PATH_RELATIVE`framework/modules-1/navigationmodule/assets/css/nav-manager.css"}

{/css}

<div class="navigationmodule manager-hierarchy">

	<div class="form_header">
		<div class="info-header">
			<div class="related-actions">
				{help text="Get Help Managing the Menu Hierarchy" module="manage-all-pages"}
			</div>
			<h1>{$_TR.form_title}</h1>
		</div>
		<p>{$_TR.form_header}</p>
	</div>
	{permissions}
		{if $user->isAdmin()}
			<a class="add" href="{link action=add_section parent=0}">{$_TR.new_top_level}</a>
		{/if}
	{/permissions}
	<div id="navtree"><img src="{$smarty.const.ICON_RELATIVE}ajax-loader.gif">	<strong>Loading Navigation</strong></div>
</div>

{script yui3mods="1" unique="DDTreeNav"}
{literal} 


{/literal}
{/script}




