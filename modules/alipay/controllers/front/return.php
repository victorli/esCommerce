<?php
/**
 * @license MIT
 * @author blx90<zs.li@blx90.com>
 * @since 2014-07-14
 */
class AlipayReturnModuleFrontController extends ModuleFrontController{
	
	var $params = null;
	var $id_order = null;
	var $message = null;
	var $status = null;
	
	public function init(){
		parent::init();
		
		$this->params = array(
			'is_success' => '',
			'sign_type' => '',
			'sign' => '',
			'out_trade_no' => '',
			'subject' => '',
			'payment_type' => '',
			'trade_no' => '',	
			'exterface' => '',
			'trade_status' => '',
			'notify_time' => '',
			'notify_type' => '',
			'notify_id' => '',
			'seller_email' => '',
			'buyer_email' => '',
			'seller_id' => '',
			'buyer_id' => '',
			'total_fee' => '',
			'body' => '',
			'extra_common_param' => '',
			'agent_user_id' => ''
		);
	}
	
	public function initContent(){
		parent::initContent();
		
		if(is_null($this->message)){
			$this->status = 'ok';
			$this->context->smarty->assign('total_paid',$this->params['total_fee']);
		}else{
			$this->status = 'error';
			$this->context->smarty->assign('message',$this->message);
		}	
		
		$this->setTemplate('payment_return.tpl');
		
	}
	
	public function postProcess(){
		
		if(!$this->module->verifyNotify()){
			Alipay::Logger()->logError('Sync return verify failed. please check your sign or alipay verify.');
			$this->message[] = $this->module->l('Sync return verify failed. please check your sign or alipay verify.');
			return;
		}
		
		foreach (array_keys($this->params) as $key)
			$this->params[$key] = Tools::getValue($key,NULL);

		//check and update order status
		$this->id_order = $this->params['out_trade_no'];
		$status = strtoupper($this->params['trade_status']);
		if($status != 'TRADE_FINISHED' && $status != 'TRADE_SUCCESS'){
			Alipay::Logger()->logInfo('The return trade status should between TRADE_FINISHED and TRADE_SUCCESS, but now is:'.$status);
			$this->message[] = $this->module->l('The return trade status should between TRADE_FINISHED and TRADE_SUCCESS, but now is:'.$status);
			return;
		}else{
			$order = new Order((int)$this->id_order);
			if(!Validate::isLoadedObject($order) || empty($this->id_order) || is_null($this->id_order)){
				Alipay::Logger()->logError('Invalid order id:'.$this->id_order);
				$this->message[] = $this->module->l('Invalid order id:'.$this->id_order);
				return;
			}
		
			$os = 'BLX_OS_'.$status;
			$orderState = new OrderState((int)Configuration::get($os));
			if(!Validate::isLoadedObject($orderState)){
				Alipay::Logger()->logError('Unknown order status:'.$os);
				$this->message[] = $this->module->l('Unknown order status:'.$os);
				return;
			}
			if($order->getCurrentState() != $orderState->id)	
				$order->setCurrentState($orderState->id);
		}
		
		//check and add return record
		$notify = new AlipayNotifyModel();
		foreach ($this->params as $key=>$value){
			$notify->$key = $value;
		}
		
		$notify->req_type = Alipay::SYNC_RETURN;
		$notify->add();
	}
}