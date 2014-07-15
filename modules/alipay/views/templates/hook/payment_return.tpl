{*
*  @author BLX90<zs.li@blx90.com>
*  @copyright  2014 BLX90
*  @license    MIT
*}

{if $status == 'ok'}
<p>{l s='Your order on %s is created successfully.' sprintf=$shop_name mod='alipay'}
		<br /><br />
		{l s='Next is your order and payment info:' mod='alipay'}
		<br /><br />- {l s='Amount' mod='alipay'} <span class="price"> <strong>{$total_to_pay}</strong></span>
		<br /><br />- {l s='Name of account owner' mod='alipay'}  <strong>...</strong>
		<br /><br />- {l s='Include these details' mod='alipay'}  <strong>...</strong>
		<br /><br />- {l s='Payment' mod='alipay'}  <strong>{l s='Alipay Payment' mod='alipay'}</strong>
		<br /><br />- {l s='Payment way'  mod='alipay'} <strong>{$payment_way}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='alipay'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team. ' mod='alipay'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='alipay'} 
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team. ' mod='alipay'}</a>.
	</p>
{/if}
