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
	<div class="camera_wrap {$xslider.skin}" id="xslider-{$xslider.id_xslider}">
	{foreach from=$xslider.items item=item}
		<div data-thumb="{$thumb_path}xslider_mini_{$item.image}" data-src="{$mod_path}images/slides/{$item.image}" {if $item.link_type == 'image'}data-link="{$item.link}" data-target="_blank"{else}data-time=4500{/if}>
        	{if $item.link_type == 'image'}
        	<div class="camera_caption fadeFromBottom">
            	{$item.description}
            </div>
            {else}
            <iframe src="{$item.link}" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
            {/if}
       	</div>
    {/foreach}
	</div>
{/foreach}
{$js}
