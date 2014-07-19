<?php
/**
 * @license MIT
 * @author blx90<zs.li@blx90.com>
 * @since 2014-07-14
 */
class AlipayReturnModuleFrontController extends ModuleFrontController{
	
	var $params = null;
	var $id_order = null;
	
	public function init(){
		parent::init();
		
		$this->ajax = true;
		
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
	}
	
	public function postProcess(){
		
		if(!$this->module->verifyNotify()){
			Alipay::Logger()->logError('Sync return verify failed. please check your sign or alipay verify.');
			die('fail');
		}
		
		foreach (array_keys($this->params) as $key)
			$this->params[$key] = Tools::getValue($key,NULL);

		//check and update order status
		$this->id_order = $this->params['out_trade_no'];
		$status = strtoupper($this->params['trade_status']);
		if($status != 'TRADE_FINISHED' && $status != 'TRADE_SUCCESS'){
			Alipay::Logger()->logInfo('The return trade status should between TRADE_FINISHED and TRADE_SUCCESS, but now is:'.$status);
			die('fail');
		}else{
			$order = new Order((int)$this->id_order);
			if(!Validate::isLoadedObject($order) || empty($this->id_order) || is_null($this->id_order)){
				Alipay::Logger()->logError('Invalid order id:'.$this->id_order);
				die('fail');
			}
		
			$os = 'BLX_OS_'.$status;
			$orderState = new OrderState((int)Configuration::get($os));
			if(!Validate::isLoadedObject($orderState)){
				Alipay::Logger()->logError('Unknown order status:'.$os);
				die('fail');
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