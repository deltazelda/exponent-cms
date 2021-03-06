{*
 * Copyright (c) 2004-2012 OIC Group, Inc.
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

{css unique="cal" link="`$smarty.const.PATH_RELATIVE`framework/modules-1/calendarmodule/assets/css/calendar.css"}
{literal}
	.viewweek {border: none;width:100%;list-style: none;margin: 0;padding: 0;}
	.viewweek dt {line-height: 2em; border-top: 1px solid;}
{/literal}
{/css}
 
<div class="calendarmodule cal-default"> 
    {if $moduletitle && !$config->hidemoduletitle}<h1>{$moduletitle}</h1>{/if}
    {if $config->moduledescription != ""}
        {$config->moduledescription}
    {/if}
	<h4 align="center">
	{if $totaldays == 1}
		<a href="{link module=calendarmodule action=viewmonth time=$start}">{'Events for'|gettext} {$start|format_date:"%B %e, %Y"}</a>
	{else}
		<a href="{link module=calendarmodule action=viewmonth time=$start}">{'Events for the next'|gettext} {$totaldays} {'days from'|gettext} {$start|format_date:"%B %e, %Y"}</a>
	{/if}
	</h4>
	<dl class="viewweek">
		{foreach from=$days item=events key=ts}
			{if $counts[$ts] != 0}
				<dt>
					<strong>
						<a class="itemtitle calendar_mngmntlink" href="{link module=calendarmodule action=viewday time=$ts}">{$ts|format_date:"%A, %b %e"}</a>
					</strong>
				</dt>
				{foreach from=$events item=event}
					{assign var=catid value=$event->category_id}
					<dd>
						<strong>
							<a class="itemtitle calendar_mngmntlink" href="{link module=calendarmodule action=view id=$event->id date_id=$event->eventdate->id}">{$event->title}</a>
						</strong>							
						<div>
							&#160;-&#160;
							{if $event->is_allday == 1}
								{'All Day'|gettext}
							{else}
								{if $event->eventstart != $event->eventend}
									{$event->eventstart|format_date:$smarty.const.DISPLAY_TIME_FORMAT} {'to'|gettext} {$event->eventend|format_date:$smarty.const.DISPLAY_TIME_FORMAT}
								{else}
									{$event->eventstart|format_date:$smarty.const.DISPLAY_TIME_FORMAT}
								{/if}
							{/if}
							{if $showdetail == 1}
								&#160;-&#160;{$event->body|summarize:"html":"paralinks"}
							{/if}
							{br}
						</div>
					</dd>
				{/foreach}
			{/if}
		{/foreach}
	</dl>
</div>