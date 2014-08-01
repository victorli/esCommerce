<?php
/**
 * @license MIT
 * @author blx90<zs.li@blx90.com>
 * @since 2014-07-14
 */
class AlipayNotifyModuleFrontController extends ModuleFrontController{
	
	var $params = null;
	var $id_order = null;
	
	public function init(){
		parent::init();
		
		$this->ajax = true;
		
		$this->params = array(
			'notify_time' => '',
			'notify_type' => '',
			'notify_id' => '',
			'sign_type' => '',
			'sign' => '',
			'out_trade_no' => '',
			'subject' => '',
			'payment_type' => '',
			'trade_no' => '',
			'trade_status' => '',
			'gmt_create' => '',
			'gmt_payment' => '',
			'gmt_close' => '',
			'refund_status' => '',
			'gmt_refund' => '',
			'seller_email' => '',
			'buyer_email' => '',
			'seller_id' => '',
			'buyer_id' => '',
			'price' => '',
			'total_fee' => '',
			'quantity' => '',
			'body' => '',
			'discount' => '',
			'is_total_fee_adjust' => '',
			'use_coupon' => '',
			'extra_common_param' => '',
			'out_channel_type' => '',
			'out_channel_amount' => '',
			'out_channel_inst' => '',
			'business_scene' => ''
		);
	}
	
	public function initContent(){
		parent::initContent();
		
	}
	
	public function postProcess(){
		
		if(!$this->module->verifyNotify()){
			Alipay::Logger()->logError('Request verify failed. please check your sign or alipay verify.');
			die('fail');
		}
			
		foreach (array_keys($this->params) as $key){
			$this->params[$key] = Tools::getValue($key,NULL);
		}
		
		$this->id_order = $this->params['out_trade_no'];
		
		$this->_saveNotifyRecord();
		$this->_addOrderPayment();
		$this->_updateOrderStatus();
		
		die('success');
	}
	
	private function _addOrderPayment(){
		if(in_array(strtoupper($this->params['trade_status']), array('TRADE_SUCCESS','TRADE_FINISHED'))){
			$order = new Order((int)$this->id_order);
			
			if(!Validate::isLoadedObject($order) || empty($this->id_order) || is_null($this->id_order)){
				Alipay::Logger()->logError('Invalid order id:'.$this->id_order);
				die('fail');
			}
			
			$total_paid = $order->getTotalPaid();
			if((float)$order->total_paid == (float)$total_paid)
				return;
			if(!$order->addOrderPayment($this->params['total_fee'],null,$this->params['trade_no'],null,$this->params['gmt_payment'])){
				Alipay::Logger()->logError('Error to add order payment or order invoice for order:'.$this->id_order);
				die('fail');
			}
			if(((float)$total_paid + (float)$this->params['total_fee']) > (float($order->total_paid)))
				Alipay::Logger()->logWarning('Warning: order\'s total fee is '.$order->total_paid.',and has beed paid '.$total_paid.',but new total fee is '.$this->params['total_fee']);
		}
	}
	
	private function _updateOrderStatus(){
		$os = 'BLX_OS_';
		$as = strtoupper($this->params['trade_status']);
		switch ($as){
			case 'WAIT_BUY_PAY' :
			case 'TRADE_CLOSED' :
			case 'TRADE_SUCCESS' :
			case 'TRADE_PENDING' :
			case 'TRADE_FINISHED' : $os .= $as; break;
			default: $os = null;
		}
		
		if(is_null($os)){
			Alipay::Logger()->logError('Unknown order status from alipay:'.$as);
			die('fail');
		}
			
		$order = new Order((int)$this->id_order);
		if(!Validate::isLoadedObject($order) || empty($this->id_order) || is_null($this->id_order)){
			Alipay::Logger()->logError('Invalid order id:'.$this->id_order);
			die('fail');
		}

		$orderState = new OrderState((int)Configuration::get($os));
		if(!Validate::isLoadedObject($orderState)){
			Alipay::Logger()->logError('Unknown order status:'.$os);
			die('fail');
		}
			
		$current_order_state = $order->getCurrentOrderState();
		if($current_order_state->id != $orderState->id){
			$history = new OrderHistory();
			$history->id_order = $order->id;
			$history->id_employee = 0;
			
			$use_existings_payment = false;
			if(!$order->hasInvoice())
				$use_existings_payment = true;
				
			$history->changeIdOrderState((int)$orderState->id, $order, $use_existings_payment);
			
			if($orderState->send_email){
				$templateVars = array();
				if(!$history->addWithemail(true,$templateVars)){
					Alipay::Logger()->logError('Error to add order history with email.');
					die('fail');
				}
			}else{
				if(!$history->add()){
					Alipay::Logger()->logError('Error to add order history.');
					die('fail');
				}
			}
			
			// synchronizes quantities if needed..
			if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
			{
				foreach ($order->getProducts() as $product)
				{
					if (StockAvailable::dependsOnStock($product['product_id']))
						StockAvailable::synchronize($product['product_id'], (int)$product['id_shop']);
				}
			}
		}
		
		//$order->setCurrentState($orderState->id);
	}
	
	private function _saveNotifyRecord(){
		$notify = new AlipayNotifyModel();
		foreach ($this->params as $key=>$value){
			$notify->$key = $value;
		}
		
		$notify->req_type = Alipay::ASYNC_NOTIFY;
		$notify->add();
	}
}