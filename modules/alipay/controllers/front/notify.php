<?php
/**
 * @license MIT
 * @author blx90<zs.li@blx90.com>
 * @since 2014-07-14
 */
class AlipayNotifyModuleFrontController extends ModuleFrontController{
	
	var $params = null;
	
	public function init(){
		parent::init();
		
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
		
		if(!$this->module->verifyNotify())
			die($this->module->l('Sorry. System detected your request was dangerous!'));
			
		
	}
}