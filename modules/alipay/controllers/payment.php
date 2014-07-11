<?php
/**
 * @author blx90 <zs.li@blx90.com>
 * @since 20140711
 */
class AlipayPaymentFrontController extends ModuleFrontController{
	
	public function initContent(){
		parent::initContent();
		
		$cart = $this->context->cart;
		if(!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		
		$this->context->smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency($cart->id_currency),
			'total' => $cart->getOrderTotal(true,Cart::BOTH)
			));	
			
		$this->setTemplate('payment_confirm.tpl');
	}
}