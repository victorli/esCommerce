{**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 20140720
 *}
 <div id="blx_refund_dialog" class="modal hide fade" role="dialog">
 	<div class="modal-header">
    	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    	<h3 id="myModalLabel">{l s='Refund for:%s' sprintf=$id_order mod='alipay'}</h3>
 	</div>
 	<div class="modal-body">
 {if $message }
 	<p>{$message}</p>
 {else}
 	<p>
 		{if !$params}
 		<form class="form-horizontal">
 			<div class="control-group">
 				<label class="control-label" for="refund_fee">{l s="Refund fee"}</label>
 				<div class="controls">
 					<input type="text" id="refund_fee" placeholder="{l s='Lower or equal %s' sprintf=$total_fee mod='alipay'}"/>
 					<input type="hidden" id="total_fee" value="{$total_fee}">
 				</div>
 			</div>
 			<div class="control-group">
 				<label class="control-label" for="refund_reason">{l s='Refund reason' mod='alipay'}</label>
 				<div class="controls">
 					<input type="text" id="refund_reason" />
 				</div>
 			</div>
 		</form>
 		{else}
 		{l s='Are you sure?' mod='alipay'}
 		<form action="{$gateway}" method="post" target="_blank">
 			{foreach from=$params key=name item=value}
 				<input type="hidden" name="{$name}" value="{$value}">
 			{/foreach}
 			<button type="button" class="btn">{l s='Cancel' mod='alipay'}</button>
 			<button type="submit" class="btn btn-primary">{l s='Confirm' mod='alipay'}</button>
 		</form>
 		{/if}
 	</p>
 {/if}
 	</div>
</div>