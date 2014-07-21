{*
* 2014 BLX90
*
* NOTICE OF LICENSE

*  @author BLX90 <zs.li@blx90.com>
*  @copyright  2014 BLX90
*  @license    MIT
*}

{if $status == 'ok'}
<p>{l s='Your order on %s is confirmed.' sprintf=$shop_name mod='alipay'}
		<br /><br />
		{l s='Please note below:' mod='alipay'}
		<br /><br />- {l s='You paid an Amount of %s to this order.' sprintf=$total_paid mod='alipay'}
		<br /><br />- {l s='An email has been sent with this information.' mod='alipay'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as possible.' mod='alipay'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='alipay'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team. ' mod='alipay'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='alipay'} 
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team. ' mod='alipay'}</a>.
		{foreach from=$message item=msg}
		<br /><br />- {$msg}
		{/foreach}
	</p>
{/if}
