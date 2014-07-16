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
 <form action="{$link->getModuleLink('alipay','validation',[],true)|escape:'html':'UTF-8'}" method="post" id="blx_alipay_confirm_form">
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
                - {l s='The total amount of your order is' mod='alipay'}
                <span id="amount" class="price">{displayPrice price=$total}</span>
                {if $use_taxes == 1}
                    {l s='(tax incl.)' mod='alipay'}
                {/if}
            </p>
            <p>
                -
                {if $currencies|@count > 1}
                    {l s='We allow several currencies to be sent via alipay.' mod='alipay'}
                    <div class="form-group">
                        <label>{l s='Choose one of the following:' mod='alipay'}</label>
                        <select id="currency_payement" class="form-control" name="currency_payement">
                            {foreach from=$currencies item=currency}
                                <option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>
                                    {$currency.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                {else}
                    {l s='We allow the following currency to be sent via alipay:' mod='alipay'}&nbsp;<b>{$currencies.0.name}</b>
                    <input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
                {/if}
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
            type="submit" name="submit" value="confirm-and-pay-later">
                <span>{l s='Confirm order and Pay later' mod='alipay'}<i class="icon-chevron-right right"></i></span>
            </button>
       		
             <button
            class="button btn btn-default button-medium" 
            style="margin-right:5px;" 
            type="submit" name="submit" value="confirm-and-pay" onclick="javascript:$('form#blx_alipay_confirm_form').attr('target','_blank');">
                <span>{l s='Confirm order and Pay' mod='alipay'}<i class="icon-chevron-right right"></i></span>
            </button>
        </p>
 </form>
<!-- fancybox container triggered after 'confirm-and-pay' submit -->
<div style="display:none;" id="pay-confirm-modal">
<button class="button btn btn-default button-medium" onclick="javascript:window.location.href=index.php?controller=history;">{l s='Pay successfully.' mod='alipay'}</button>
<button class="button btn btn-default button-medium" onclick="javascript:$.fancybox.close();">{l s='Fail to pay' mod='alipay'}</button>
</div>
{/if}
<script type="text/javascript">
$(document).ready(function(e){
	$('button[type=submit]').click(function(e){
		if($(this).val() == 'confirm-add-pay'){
			$.fancybox($('#pay-confirm-modal'));
		}
	});
});
</script>