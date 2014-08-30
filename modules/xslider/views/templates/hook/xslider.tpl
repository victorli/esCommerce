{*
/*
* 2014 eCartx
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@ecartx.com so we can send you a copy immediately.
*
*  @author BLX90 <zs.li@blx90.com>
*  @copyright 2014 BLX90
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
*}

{foreach from=$xsliders key=key item=xslider}
	<div class="camera_wrap camera_azure_skin" id="xslider-{$xslider.id_xslider}">
	{foreach from=$xslider.items item=item}
		{if $item.href}
		<a href="{$item.href}" target="_blank">
		{/if}
		<div data-thumb="{$thumb_path}xslider_mini_{$item.image}" data-src="{$mod_path}images/{$item.image}">
        	<div class="camera_caption fadeFromBottom">
            	{$item.description}
            </div>
       	</div>
       	{if $item.href}
       	</a>
       	{/if}
    {/foreach}
	</div>
{/foreach}

{$js}
