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

{if isset($xsliders)}
<script type="text/javascript">
	var numSliders = {$xsliders|count};
</script>	
{/if}

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

<script type="text/javascript">
{foreach from=$xsliders key=key item=slider}
	var xWidth_{$key} = {$xslider.$key.width};
	var xHeight_{$key} = {$xslider.$key.height};
	var xFx_{$key} = {$xslider.$key.fx};
	var xBD_{$key} = {$xslider.$key.barDirection};
	var xBP_{$key} = {$xslider.$key.barPosition};
	var xLoader_{$key} = {$xslider.$key.loader};
	var xNavigation_{$key} = {$xslider.$key.navigation};
	var xOL_{$key} = {$xslider.$key.overlayer};
	var xPagination_{$key} = {$xslider.$key.pagination};
	var xPP_{$key} = {$xslider.$key.playPause};
	var xPPn_{$key} = {$xslider.$key.piePosition};
	var xThumb_{$key} = {$xslider.$key.thumbnails};
	var xTime_{$key} = {$xslider.$key.time};
{/foreach}
{literal}
jQuery(function(){
	for(var i=0; i<numSliders; i++){
		$("#xslider-"+i).camera({
			height		: 	this['xHeight_'+i],
			fx			:	this['xFx_'+i],
			loader		:	this['xLoader_'+i],
			barPosition	:	this['xBP_'+i],
			barDirection:	this['xBD_'+i],
			navigation	:	this['xNavigation_'+i],
			overlayer	:	this['xOL_'+i],
			pagination	:	this['xPagination_'+i],
			playPause	:	this['xPP_'+i],
			piePosition	:	this['xPPn_'+i],
			thumbnails	:	this['xThumb_'+i],
			time		:	this['xTime_'+i]
		});	
	}
});
{/literal}
</script>
