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
	
	const PAY_WAY_PARTNER_TRADE = 'PARTNER';
	const PAY_WAY_DIRECT_PAY = 'DIRECT';
	
	public function __construct(){
		$this->name = 	'alipay';
		$this->tab	=	'payments_gateways';
		$this->version	=	'0.1.1';
		$this->author	=	Module::AUTHOR_IS_BLX90;
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min'=>'1.5','max'=>'1.6');
		$this->bootstrap	=	true;
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		
		$configs = Configuration::getMultiple(array('BLX_ALIPAY_WAY'));
		if(!empty($configs['BLX_ALIPAY_WAY']))
			$this->alipay_way = $configs['BLX_ALIPAY_WAY'];
		
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
			!Configuration::updateValue('BLX_ALIPAY_NAME','Alipay') ||
			!Configuration::updateValue('BLX_ALIPAY_WAY',self::PAY_WAY_PARTNER_TRADE)
			)
			return false;
		return true;
	}
	
	public function uninstall(){
		if(!parent::uninstall() || 
			!Configuration::deleteByName('BLX_ALIPAY_NAME') || 
			!Configuration::deleteByName('BLX_ALIPAY_WAY')
		)
			return false;
			
		return true;
	}
	
	public function getContent(){
		$output = null;
		
		if(Tools::isSubmit('submit'.$this->name)){
			$alipay_way = strval(Tools::getValue('ALIPAY_WAY'));
			if(!$alipay_way || empty($alipay_way) || !Validate::isGenericName($alipay_way))
				$output .= $this->displayError($this->l('Invalid pay way'));
			else{
				Configuration::updateValue('ALIPAY_WAY',$alipay_way);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		
		return $output.$this->displayForm();
	}
	
	public function displayForm(){
		$lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$way_options = array(
			array(
				'id_option' => self::PAY_WAY_PARTNER_TRADE,
				'name'		=> $this->l('Partner Trade')
			),
			array(
				'id_option' => self::PAY_WAY_DIRECT_PAY,
				'name'		=> $this->l('Direct Pay')
			)
		);
		
		$fields_form[0]['form']=array(
			'legend' => array('title' => $this->l('Setting')),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Alipay way'),
					'name'	=> 'BLX_ALIPAY_WAY',
					'required' => true,
					'options' => array(
						'query' => $way_options,
						'id'	=> 'id_option',
						'name'	=> 'name'
					)
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);
		
		$helper = new HelperForm();
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		
		$helper->default_form_language = $lang;
		$helper->allow_employee_form_lang = $lang;
		
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submit'.$this->name;
		
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
			),
			'back' => array(
				'desc' => $this->l('Back to list'),
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules')
			)
		);
		
		$helper->fields_value['BLX_ALIPAY_WAY'] = Configuration::get('BLX_ALIPAY_WAY');
		
		return $helper->generateForm($fields_form);
	}
	
	public function hookPayment($params){
		if(!$this->active)
			return;
			
		$this->context->smarty->assign(
			array(
				'alipay_module_name' => Configuration::get('BLX_ALIPAY_NAME'),
				'alipay_module_link' => $this->context->link->getModuleLink('alipay','display')
			)
		);
		
		return $this->display(__FILE__.'payment.tpl');
	}
	
	public function hookPaymentReturn($params){
		
	}
	
}





