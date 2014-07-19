<?php
/**
 * @license MIT
 * @author BLX90<zs.li@blx90.com>
 * @since 20140718
 */

class AlipayNotifyModel extends ObjectModel{
	
	public $id_alipay_notify;
	public $notify_time;
	public $notify_type;
	public $sign_type;
	public $sign;
	public $out_trade_no;
	public $subject;
	public $payment_type;
	public $trade_no;
	public $trade_status;
	public $gmt_create;
	public $gmt_payment;
	public $gmt_close;
	public $refund_status;
	public $gmt_refund;
	public $seller_email;
	public $buyer_email;
	public $seller_id;
	public $buyer_id;
	public $price;
	public $total_fee;
	public $quantity;
	public $body;
	public $discount;
	public $is_total_fee_adjust;
	public $use_coupon;
	public $extra_common_param;
	public $out_channel_type;
	public $out_channel_amount;
	public $out_channel_inst;
	public $business_scene;
	
	public $id_shop;
	public $id_shop_group;
	//public $id_lang;
	
	public $date_add;
	public $date_upd;
	
	public static $definition = array(
		'table' => 'alipay_notify',
		'primary' => 'id_alipay_notify',
		'fields' => array(
			'notify_time'		=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
			'notify_type'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'notify_id'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'sign_type'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
			'sign'				=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'out_trade_no'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 64),
			'subject'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 256),
			'payment_type'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 16),
			'trade_no'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 64),
			'trade_status'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 128),
			'gmt_create'		=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false),
			'gmt_payment'		=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false),
			'gmt_close'			=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false),
			'refund_status'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 128),
			'gmt_refund'		=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false),
			'seller_email'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 100),
			'buyer_email'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 100),
			'seller_id'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 30),
			'buyer_id'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 30),
			'price'				=>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => false),
			'total_fee'			=>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => false),
			'quantity'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
			'body'				=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => false, 'size' => 400),
			'discount'			=>	array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'is_total_fee_adjust'=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 1),
			'use_coupon'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 1),
			'extra_common_param'=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
			'out_channel_type'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),
			'out_channel_amount'=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255),	
			'out_channel_inst'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 128),
			'business_scene'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 128),
			'id_shop' 			=> 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_shop_group' 	=> 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			//'id_lang' 			=> 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
			//'date_add' 			=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			//'date_upd' 			=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			)
	
	);
	
	
	public function add($autodate=true, $null_values = false){
		$this->id_shop = ($this->id_shop) ? $this->id_shop : Context::getContext()->shop->id;
		$this->id_shop_group = ($this->id_shop_group) ? $this->id_shop_group : Context::getContext()->shop->id_shop_group;
		//$this->id_lang = ($this->id_lang) ? $this->id_lang : Context::getContext()->language->id;
		
		return parent::add($autodate,$null_values);
	}
	
	public function getHistory($id_order=0){
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'alipay_notify` ';
		$sql .= 'WHERE 1 '. Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
		if($id_order > 0)
		$sql .= ' AND out_trade_no='.$id_order;
		
		$result = Db::getInstance()->executeS($sql);
		
		if(!$result)
			return false;
			
		return $result;
	}
	
	public static function createTables(){
		return self::createNotifyTable();
	}
	
	public static function dropTables(){
		$sql = 'DROP TABLE 
				`'._DB_PREFIX_.'alipay_notify`';
		
		return Db::getInstance()->execute($sql);
	}
	
	public static function createNotifyTable(){
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alipay_notify`(
			`id_alipay_notify`	int unsigned not null auto_increment,
			`id_shop` int unsigned not null,
			`id_shop_group` int unsigned not null,
			`notify_time` datetime not null,
			`notify_type` varchar(255) not null,
			`notify_id` varchar(255) not null,
			`sign_type` varchar(32) not null,
			`sign`		varchar(255) not null,
			`out_trade_no` varchar(64),	
			`subject` varchar(256),		
			`payment_type` varchar(16),	
			`trade_no`	varchar(64),	
			`trade_status` varchar(128),
			`gmt_create` datetime,	
			`gmt_payment` datetime,
			`gmt_close`	datetime,
			`refund_status`	varchar(128),
			`gmt_refund` datetime,
			`seller_email`	varchar(100),
			`buyer_email`	varchar(100),
			`seller_id`	varchar(30),
			`buyer_id`	varchar(30),
			`price`		decimal(10,2),
			`total_fee`	decimal(10,2),
			`quantity`	int,
			`body`		varchar(400),
			`discount`	decimal(5,2),
			`is_total_fee_adjust` char(1),
			`use_coupon` char(1),
			`extra_common_param` varchar(255),
			`out_channel_type`	varchar(255),
			`out_channel_amount` varchar(255),
			`out_channel_inst` varchar(128),
			`business_scene` varchar(128),
			PRIMARY KEY(`id_alipay_notify`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
		
		return Db::getInstance()->execute($sql);
	}
}