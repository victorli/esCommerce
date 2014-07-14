<?php
/**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 2014-07-14
 */
class AlipayValidationModuleFrontController extends ModuleFrontController{
	
	public function initContent(){
		parent::initContent();
		
	}
	
	public function postProcess(){
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || 
			$cart->id_address_delivery == 0 || 
			$cart->id_address_invoice == 0 || 
			!$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');
			
		$authorized = false;
		foreach (Module::getPaymentModules() as $module){
			if ($module['name'] == 'alipay'){
				$authorized = true;
				break;
			}
		}
		if (!$authorized)
			die($this->module->l('This payment method is not avaliable.','validation'));
			
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');
			
		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true,Cart::BOTH);
		
		$mailVars = array();
		//validate and add new order
		$this->module->validateOrder($cart->id,Configuration::get('PS_OS_ALIPAY'),$total,$this->module->displayName,NULL,$mailVars,(int)$currency->id,false,$customer->secure_key);
		
		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	}
}