<?php
/**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 2014
 */
class AlipayJumpModuleFrontController extends ModuleFrontController{
	
	public $ssl = true;
	public $id_cart;
	public $id_order;
	public $id_module;
	public $secure_key;
	public $reference;
	
	public function init(){
		parent::init();
		
		$this->id_cart = Tools::getValue('id_cart',0);
		$this->id_order = Tools::getValue('id_order',0);
		$this->secure_key = Tools::getValue('key',false);
		$this->id_module = Tools::getValue('id_module',0);
		
		$is_guest = false;
		
		if(Cart::isGuestCartByCartId($this->id_cart)){
			$is_guest = true;
			$redirectLink = 'index.php?controller=guest-tracking';
		}else{
			$redirectLink = 'index.php?controller=history';
		}
		
		$order = new Order((int)($this->id_order));
		if(!Validate::isLoadedObject($order) || !$this->secure_key || empty($this->secure_key))
			Tools::redirect($redirectLink.(Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
		if($is_guest){
			$customer = new Customer((int)$order->id_customer);
			$redirectLink .= '&id_order='.$order->reference.'&email='.urlencode($customer->email);
		}
		
		$this->reference = $order->reference;
		if($order->id_customer != $this->context->customer->id || $this->secure_key != $order->secure_key)
			Tools::redirect($redirectLink);
		$module = Module::getInstanceById((int)($this->id_module));
		if($order->module != $module->name)
			Tools::redirect($redirectLink);
	}
	
	public function initContent(){
		parent::initContent();
		
		$this->context->smarty->assign(array(
			'is_guest' => $this->context->customer->is_guest
		));
		
		if($this->context->customer->is_guest){
			$this->context->smarty->assign(array(
				'id_order' => $this->id_order,
				'reference_order' => $this->reference,
				'id_order_formatted' => sprintf('#%06d', $this->id_order),
				'email' => $this->context->customer->email
			));
			
			$this->context->customer->mylogout();
		}
		
		$module = Module::getInstanceById((int)($this->id_module));
		$reqParam = $module->getRequestParam($this->id_order);
		$this->context->smarty->assign($reqParam);
		
		$this->setTemplate('jump.tpl');
	}
}