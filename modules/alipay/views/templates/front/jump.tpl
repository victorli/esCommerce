{*
/**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 2014
 */
 *}
{capture name=path}{l s='Order confirmation'}{/capture}

<h1 class="page-heading">{l s='Order confirmation'}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

{$HOOK_PAYMENT_RETURN}
{if $is_guest}
	<p>{l s='Your order ID is:'} <span class="bold">{$id_order_formatted}</span> . {l s='Your order ID has been sent via email.'}</p>
    <p class="cart_navigation exclusive">
	<a class="button-exclusive btn btn-default" href="{$link->getPageLink('guest-tracking', true, NULL, "id_order={$reference_order}&email={$email}")|escape:'html':'UTF-8'}" title="{l s='Follow my order'}"><i class="icon-chevron-left"></i>{l s='Follow my order'}</a>
    </p>
{else}
<p class="cart_navigation exclusive">
	<a class="button-exclusive btn btn-default" href="{$link->getPageLink('history', true)|escape:'html':'UTF-8'}" title="{l s='Back to orders'}"><i class="icon-chevron-left"></i>{l s='Back to orders'}</a>
</p>
{/if}

 <form action="{$action}_input_charset={$input_charset}" method="post" target="_blank" id="blx_alipay_jump_form">
 	{foreach from=$inputs key=key item=param}
 		<input type="hidden" name="{$key}" value="{$param}" />
 	{/foreach}
 	<button class="button btn default-button medium-button" type="submit" id="blx_go_alipay_btn">{l s='Pay'}</button>
 </form>