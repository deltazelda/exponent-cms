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
 <div class="module store show event-registration">
     <h1>{$product->title}</h1>
     <div class="image">
         {if $product->expFile.images[0]->url == ""}
             {img src="`$smarty.const.ICON_RELATIVE`ecom/no-image.jpg"}
         {else}
             {img file=$product->expFile.images[0]->path square=200}
         {/if}
         {clear}
     </div>

     <div class="bd">
         {permissions level=$smarty.const.UILEVEL_PERMISSIONS}
         <div class="item-actions">
             {if $permissions.configure == 1 or $permissions.administrate == 1}
                 <a href="{link action=edit id=$product->id}" title="{"Edit this entry"|gettext}">
                     <img src="{$smarty.const.ICON_RELATIVE}edit.png" title="{"Edit this entry"|gettext}" alt="{"Edit this entry"|gettext}" />
                 </a>
                 {icon action=delete record=$product title="Delete this product"}
             {/if}
         </div>
         {/permissions}

         <div class="bodycopy">{$product->body}</div>
         <span class="date">
             <span class="label">Event Date: </span><span class="value">{$product->eventdate|date_format:"%A, %B %e, %Y"}</span>{br}
             <span class="label">Start Time: </span><span class="value">{$product->event_starttime|expdate:"g:i a"}</span>{br}
             <span class="label">End Time: </span><span class="value">{$product->event_endtime|expdate:"g:i a"}</span>{br}
             {br}
             <span class="label">Seats Available: </span><span class="value">{$product->spacesLeft()} of {$product->quantity}</span>{br}
             <span class="label">Registration Closes: </span><span class="value">{$product->signup_cutoff|expdate:"l, F j, Y, g:i a"}</span>{br}
         </span>
         <div class="price">{currency_symbol}{$product->price|number_format:2}</div>
        
         {if $product->isAvailable()}
            <a href="{link controller=cart action=addItem product_id=$product->id product_type=$product->product_type}" class="addtocart exp-ecom-link" rel="nofollow">
                Add to cart<span></span>
            </a>
         {else}
            <a href="#" class="addtocart exp-ecom-link">
                Registration is closed.<span></span>
            </a>
         {/if}
     </div>
     {clear}
 </div>

