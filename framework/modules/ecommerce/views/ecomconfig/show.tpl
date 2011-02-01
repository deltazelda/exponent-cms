{*
 * Copyright (c) 2007-2008 OIC Group, Inc.
 * Written and Designed by Adam Kessler
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

{*permissions level=$smarty.const.UILEVEL_NORMAL}
    {if $permissions.show == 1*}
        <div class="module storeadmin show">
	        <h1>{$moduletitle|default:"Ecommerce Administration"}</h1>
	        <ul>
	            <li><a href="{link action=options}">Manage Product Options</a></li>
	            <li><a href="{link action=groupdiscounts}">Group Discounts</a></li>
	            <li><a href="{link controller=billing action=showall_calculators}">Billing Methods</a></li>
	        </ul>
        </div>
    {*/if*}
{*/permissions*}
