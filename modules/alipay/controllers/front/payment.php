<?php
/**
 * @license MIT
 * @author blx90 <zs.li@blx90.com>
 * @since 20140711
 */
class AlipayPaymentModuleFrontController extends ModuleFrontController{
	
	public $ssl = true;
	public $display_column_left = false;
	public $total = 0;
	public $nbProducts = 0;
	public $currency;
	public $secure_key = null;
	public $id_cart = 0;
	
	public function init(){
		parent::init();
		$this->display_column_left =false;
	}
	
	public function initContent(){
		parent::initContent();
		
		$this->context->smarty->assign(array(
			'nbProducts' => $this->nbProducts,
			'cust_currency' => $this->currency,
			//'currencies' => $this->module->getCurrency($cart->id_currency), //skip by chinese payment
			'total' => $this->total,
			'id_order' => $this->module->currentOrder,
			'id_cart' => $this->id_cart,
			'id_module' => $this->module->id,
			'key'	=> $this->secure_key
			));	
			
		$this->setTemplate('payment_confirm.tpl');
	}
	
	public function postProcess(){
		$cart = $this->context->cart;
		if(!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
			
		if($cart->id_customer == 0 || 
			$cart->id_address_delivery == 0 || 
			$cart->id_address_invoice == 0 || 
			!$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');
			
		$this->id_cart = $cart->id;
		if(empty($this->id_cart) || is_null($this->id_cart))
			Tools::redirect('index.php?controller=history');
		
		$this->nbProducts = $cart->nbProducts();
		$authorized = false;
		foreach (Module::getPaymentModules() as $module){
			if($module['name'] == $this->module->name){
				$authorized = true;
				break;
			}
		}
		
		if(!$authorized)
			die($this->module->l('This payment method is not avaliable.','payment'));
			
		$customer = new Customer($cart->id_customer);
		if(!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');
		$this->secure_key = $customer->secure_key;
			
		$this->currency = $this->context->currency;
		$this->total = (float)$cart->getOrderTotal(true,Cart::BOTH);
		
		$mailVars = array();
		$order = Order::getOrderByCartId($this->id_cart);
		if(Validate::isLoadedObject($order))
			Tools::redirect('index.php?controller=history');

		$this->module->validateOrder($this->id_cart,Configuration::get(Alipay::ALIPAY_ORDER_STATUS),$this->total,$this->module->displayName,NULL,$mailVars,(int)$this->currency->id,false,$this->secure_key);
	}
}