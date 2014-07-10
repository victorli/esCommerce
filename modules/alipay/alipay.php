<?php
/**
 * 2014 esCommerce extened PrestaShop
 * 
 * @license MIT
 * 
 * @author BLX90 Suzhou <zs.li@blx90.com>
 * @copyright 2014 BLX90
 * 
 */

if (!defined('_PS_VERSION_'))
	exit;
	
class Alipay extends PaymentModule{
	
	public function __construct(){
		$this->name = 	'alipay';
		$this->tab	=	'payments_gateways';
		$this->version	=	'0.1.0';
		$this->author	=	Module::AUTHOR_IS_BLX90;
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min'=>'1.5','max'=>'1.6');
		$this->bootstrap	=	true;
		
		parent::__construct();
		
		$this->displayName	=	$this->l('Alipay');
		$this->description	=	$this->l('Payment for Alipay.');
		
		$this->confirmUninstall	=	$this->l('Are you sure to remove this Alipay payment?');
		
		if(!Configuration::get('ALIPAY_NAME'))
			$this->warning	=	$this->l('No name provided');
	}
	
	public function install(){
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
		
		if(!parent::install() || 
			!$this->registerHook('payment') || 
			!$this->registerHook('paymentReturn') || 
			!Configuration::updateValue('ALIPAY_NAME','Alipay')
			)
			return false;
		return true;
	}
	
	public function uninstall(){
		if(!parent::uninstall() || 
			!Configuration::deleteByName('ALIPAY_NAME')
		)
			return false;
			
		return true;
	}
	
}