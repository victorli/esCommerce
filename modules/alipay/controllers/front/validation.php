<?php
/**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 2014-07-14
 */
class AlipayValidationModuleFrontController extends ModuleFrontController{
	
	public $result = array();
	
	public function initContent(){
		parent::initContent();
	}
	
	public function postProcess(){
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || 
			$cart->id_address_delivery == 0 || 
			$cart->id_address_invoice == 0 || 
			!$this->module->active)
			$this->_checkAjaxRequest('error', 'redirect', 'index.php?controller=order&step=1');
			
		$authorized = false;
		foreach (Module::getPaymentModules() as $module){
			if ($module['name'] == 'alipay'){
				$authorized = true;
				break;
			}
		}
		if (!$authorized)
			$this->_checkAjaxRequest('error', 'die', $this->module->l('This payment method is not avaliable.','validation'));
			
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			$this->_checkAjaxRequest('error', 'redirect', 'index.php?controller=order&step=1');
			
		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true,Cart::BOTH);
		
		$mailVars = array();
		//validate and add new order
		$this->module->validateOrder($cart->id,Configuration::get('PS_OS_ALIPAY'),$total,$this->module->displayName,NULL,$mailVars,(int)$currency->id,false,$customer->secure_key);
		
		if(Tools::getValue('submit') == 'confirm-and-pay-later')
			$this->_checkAjaxRequest('success', 'redirect', 'index.php?controller=history');
		elseif (Tools::getValue('submit') == 'confirm-and-pay'){
			$params = array(
				'id_cart' => $cart->id,
				'id_order' =>$this->module->currentOrder,
				'key'	=> $customer->secure_key,
				'id_module' => $this->module->id	
			);
			$this->_checkAjaxRequest('success', 'jump',$this->context->link->getModuleLink('alipay','jump',$params));
		}
	}
	
	private function _checkAjaxRequest($code,$action,$msg){
		if($this->ajax){
			$this->result['code'] = $code;
			$this->result['action'] = $action;
			$this->result['msg'] = $msg;
		}else{
			if ($code == 'error' && $action == 'die')
				die($msg);
			else{
				Tools::redirect($msg);
			}
		}
	}
	
	public function displayAjax(){
		
		die(Tools::jsonEncode($this->result));
	}
}