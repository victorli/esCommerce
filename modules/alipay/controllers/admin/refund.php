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
	
	public function init(){
		$this->ajax = true;
	}
	
	public function postProcess(){
		$this->id_order = Tools::getValue('id_order',0);
		$order = new Order((int)$this->id_order);
		
		if(!Validate::isLoadedObject($order)){
			$this->context->smarty->assign('message',$this->l('Order:'.$this->id_order.' is not exist.'));
		}else{
			$this->context->smarty->assing(array(
				'id_order' => $this->id_order,
				'trade_no' => '', //alipay transaction trade no
				'total_fee' => $this->order->getTotalPaid(),
				'hides' => array(
					'service' => '',
					'partner' => '',
					'_input_charset' => Alipay::INPUT_CHARSET,
					'sign_type'	=> Alipay::SIGN_TYPE,
					'sign' => '',
					'notify_url' => '',
					'seller_email' => Configuration::get('BLX_ALIPAY_ACCOUNT'),
					'seller_user_id' => Configuration::get('BLX_ALIPAY_PARTNER_ID'),
					'refund_date' => date('Y-m-d H:i:s'),
					'batch_no' => date('Y-m-d')."D".$this->id_order."T".date("H:i:s"),
					'batch_num' => 1,
					'detail_data' => ''
				)
			));
		}

	}
}