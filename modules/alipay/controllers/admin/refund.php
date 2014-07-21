<?php
/**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 2014-07-19
 */

class AlipayRefundModuleAdminController extends ModuleAdminController{
	
	var $id_order = 0;
	var $total_fee = 0;
	var $message = null;
	var $refund_fee = 0.0;
	var $refund_reason = null;
	
	public function init(){
		$this->ajax = true;
	}
	
	public function postProcess(){
		$this->id_order = Tools::getValue('id_order',0);
		$order = new Order((int)$this->id_order);
		
		$this->refund_fee = Tools::getValue('refund_fee',false);
		$this->refund_reason = Tools::getValue('refund_reason',null);
		
		if(!Validate::isLoadedObject($order)){
			$this->message[] = $this->l('Order:'.$this->id_order.' is not exist.');
		}else {
			if(Tools::isSubmit('refund_fee') && Tools::isSubmit('refund_reason')){
				$this->total_fee = $order->getTotalPaid();
				if(!$this->refund_fee || !is_float($this->refund_fee) || $this->refund_fee <=0 || $this->refund_fee >= $this->total_fee)
					$this->message[] = $this->l('Refund fee must be greater than 0 and small or equal '.$this->total_fee);
				if(is_null($this->refund_reason) || strlen(trim($this->refund_reason)) == 0)
					$this->message[] = $this->l('Refund reason is empty.');
					
				if(is_null($this->message)){
					$params = $this->module->getRefundParam($this->id_order,$this->refund_fee,$this->refund_reason);
					if(!$params)
						$this->message[] = $this->l('Error to get refund params for post.');
				}
				
				if(is_null($this->message)){
					$this->context->smarty->assign('gateway',Alipay::ALIPAY_GATEWAY_NEW);
					$this->context->smarty->assign('params',$params);
				}
			}
			
		}
		
		if(!is_null($this->message))
			$this->context->smarty->assign('messages',$this->message);
	}
	
	public function displayAjax(){
		$this->smartyOutputContent($this->getTemplatePath().'refund.tpl');
	}
}