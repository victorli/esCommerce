{*
* 2014 esCommerce
* @copyright 2014 BLX90
* @author blx90<zs.li@blx90.com>
*}
{capture name=path}{l s='Alipay payment' mod='alipay'}{/capture}
<h1 class="page-heading">
	{l s='Order summary' mod='alipay'}
</h1>
{assign var="current_step" value="payment"}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <=0}
 <p class="alert alert-warning">
 	{l s='Your shopping cart is empty.' mod='alipay'}
 </p> 
{else}
 <form action="{$link->getModuleLink('alipay','jump',[],true)|escape:'html':'UTF-8'}" method="post" id="blx_alipay_confirm_form" target="_blank">
 		<div class="box cheque-box">
            <h3 class="page-subheading">
                {l s='Alipay payment.' mod='alipay'}
            </h3>
            <p class="cheque-indent">
                <strong class="dark">
                    {l s='You have chosen to pay by Alipay.' mod='alipay'} {l s='Here is a short summary of your order:' mod='alipay'}
                </strong>
            </p>
            <p>
            	<input type="hidden" name="id_order" value="{$id_order}" />
            	<input type="hidden" name="key" value="{$key}" />
            	<input type="hidden" name="id_cart" value="{$id_cart}" />
            	<input type="hidden" name="id_module" value="{$id_module}" />
                - {l s='The total amount of your order is' mod='alipay'}
                <span id="amount" class="price">{displayPrice price=$total}</span>
                {if $use_taxes == 1}
                    {l s='(tax incl.)' mod='alipay'}
                {/if}
            </p>
            <p>
                - {l s='We allow the following currency to be sent via alipay:' mod='alipay'}&nbsp;<b>{$cus_currency.name}</b>
            </p>
            <p>
                - {l s='Alipay account information will be displayed on the next page.' mod='alipay'}
                <br />
                - {l s='Please confirm your order by clicking "I confirm my order."' mod='alipay'}.
            </p>
        </div>
        <p class="cart_navigation clearfix" id="cart_navigation">
        	<a 
            class="button-exclusive btn btn-default" 
            href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
                <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='alipay'}
            </a>
           
            <button 
            class="button btn btn-default button-medium" 
            type="button" onclick="javascript:window.location.href='index.php?controller=history'">
                <span>{l s='Confirm order and Pay later' mod='alipay'}<i class="icon-chevron-right right"></i></span>
            </button>
       		
             <button
            class="button btn btn-default button-medium" 
            style="margin-right:5px;" 
            type="submit">
                <span>{l s='Confirm order and Pay' mod='alipay'}<i class="icon-chevron-right right"></i></span>
            </button>
        </p>
 </form>
<script type="text/javascript">var urlSplash = "{$link->getModuleLink('alipay','jump',[ajax:true],true)|escape:'html':'UTF-8'}";</script>
{literal}
<script type="text/javascript">
$(document).ready(function(){
	$.fancybox({href:urlSplash});
	$('button[type=submit]').click(function(){$.fancybox('#pay-confirm-modal'),{modal:true}});
});
{/literal}
</script>
{/if}