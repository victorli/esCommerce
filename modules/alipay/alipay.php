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
	
include_once(dirname(__FILE__).'/AlipayNotifyModel.php');
class Alipay extends PaymentModule{
	
	const ALIPAY_GATEWAY_NEW = 'https://mapi.alipay.com/gateway.do?';
	const ALIPAY_HTTPS_VERIFY_URL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	const ALIPAY_HTTP_VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
	
	const SIGN_TYPE = 'MD5';
	const PAY_WAY_PARTNER_TRADE = 'PARTNER';
	const PAY_WAY_DIRECT_PAY = 'DIRECT';
	const PAY_WAY_DIRECT_PAY_REFUND = 'REFUND';
	const INPUT_CHARSET = 'utf-8';
	
	const ASYNC_NOTIFY = 'notify';
	const SYNC_RETURN = 'return';
	
	public $orderStatus;
	
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
		
		$configs = Configuration::getMultiple(array('BLX_ALIPAY_ACCOUNT','BLX_ALIPAY_WAY','BLX_ALIPAY_CACERT','BLX_ALIPAY_PARTNER_ID','BLX_ALIPAY_SIGN_KEY','BLX_ALIPAY_SERVER_IP'));
		if (!empty($configs['BLX_ALIPAY_ACCOUNT']))
			$this->alipay_account = $configs['BLX_ALIPAY_ACCOUNT'];
		if(!empty($configs['BLX_ALIPAY_WAY']))
			$this->alipay_way = $configs['BLX_ALIPAY_WAY'];
		if (!empty($configs['BLX_ALIPAY_CACERT']))
			$this->alipay_cacert = $configs['BLX_ALIPAY_CACERT'];
		if (!empty($configs['BLX_ALIPAY_PARTNER_ID']))
			$this->alipay_partner_no = $configs['BLX_ALIPAY_PARTNER_ID'];
		if (!empty($configs['BLX_ALIPAY_SIGN_KET']))
			$this->alipay_sign_key = $configs['BLX_ALIPAY_SIGN_KEY'];
		if (!empty($configs['BLX_ALIPAY_SERVER_IP']))
			$this->alipay_server_ip = $configs['BLX_ALIPAY_SERVER_IP'];
		
		parent::__construct();
		
		$this->displayName	=	$this->l('Alipay');
		$this->description	=	$this->l('Payment for Alipay.');
		
		$this->confirmUninstall	=	$this->l('Are you sure to remove this Alipay payment?');
		
		if(!Configuration::get('BLX_ALIPAY_NAME'))
			$this->warning	=	$this->l('No name provided');
			
		//initialize order status
		$this->orderStatus = array(
			'BLX_OS_CREATED'=>array('color'=>'Darkred','unremovable'=>1,'name'=>$this->l('Waiting to pay'),'send_email'=>true),
			'BLX_OS_WAIT_BUY_PAY'=>array('color'=>'Chocolate','unremovable'=>1,'name'=>$this->l('Waiting buyer to pay'),'send_email'=>true),
			'BLX_OS_TRADE_CLOSED'=>array('color'=>'LightSalmon','unremovable'=>1,'name'=>$this->l('Trade closed')),
			'BLX_OS_TRADE_SUCCESS'=>array('color'=>'LimeGreen','unremovable'=>1,'name'=>$this->l('Pay successful'),'invoice'=>true,'paid'=>true),
			'BLX_OS_TRADE_PENDING'=>array('color'=>'Olive','unremovable'=>1,'name'=>$this->l('Waiting saler to deposit')),
			'BLX_OS_TRADE_FINISHED'=>array('color'=>'Lime','unremovable'=>1,'name'=>$this->l('Trade finished'),'invoice'=>true,'send_email'=>true,'paid'=>true)
		);
	}
	
	private function _addOrderStates(){
		foreach ($this->orderStatus as $state=>$param){
			$orderState = new OrderState((int)Configuration::get($state));
			if(!Validate::isLoadedObject($orderState)){
				$orderState->color= $param['color'];
				$orderState->unremovable = isset($param['unremovable'])? $param['unremovable'] : true;
				$orderState->send_email = isset($param['send_email'])? $param['send_email'] : false;
				$orderState->invoice = isset($param['invoice'])? $param['invoice'] : false;
				$orderState->paid = isset($param['paid'])? $param['paid'] : false;
				$orderState->name = array();
				foreach (Language::getLanguages() as $lang)
					$orderState->name[$lang['id_lang']] = $param['name'];
				if(!$orderState->add())
					return false;
					
				if(!Configuration::updateValue($state,$orderState->id))
					return false;
			}
		}
		
		return true;
	}
	/**
	 * @return FileLoggerCore 
	 */
	public static function Logger(){
		$logger = new FileLogger();
		$logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_alipay.log');
		return $logger;
	}
	
	private function _removeOrderStatus(){
		foreach ($this->orderStatus as $state=>$param){
			if(!Configuration::deleteByName($state))
				return false;
		}
		return true;
	}
	
	public function install(){
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
		if(!parent::install() || 
			!$this->registerHook('payment') || 
			//!$this->registerHook('paymentReturn') || 
			!Configuration::updateValue('BLX_ALIPAY_NAME','Alipay') ||
			!Configuration::updateValue('BLX_ALIPAY_WAY',self::PAY_WAY_PARTNER_TRADE) || 
			!$this->_addOrderStates() || 
			!AlipayNotifyModel::createTables()
			)
			return false;
			
		return true;
	}
	
	public function uninstall(){
		if(!parent::uninstall() || 
			!Configuration::deleteByName('BLX_ALIPAY_NAME') || 
			!Configuration::deleteByName('BLX_ALIPAY_WAY') || 
			!Configuration::deleteByName('BLX_ALIPAY_CACERT') || 
			!Configuration::deleteByName('BLX_ALIPAY_ACCOUNT') || 
			!Configuration::deleteByName('BLX_ALIPAY_PARTNER_ID') || 
			!Configuration::deleteByName('BLX_ALIPAY_SIGN_KEY') || 
			!Configuration::deleteByName('BLX_ALIPAY_SERVER_IP') || 
			!$this->_removeOrderStatus() || 
			!AlipayNotifyModel::dropTables()
		)
			return false;
			
		return true;
	}
	
	public function getContent(){
		$output = null;
		
		if(Tools::isSubmit('submit'.$this->name)){
			$alipay_account = strval(trim(Tools::getValue('BLX_ALIPAY_ACCOUNT')));
			$alipay_partner_id = strval(trim(Tools::getValue('BLX_ALIPAY_PARTNER_ID')));
			$alipay_sign_key = strval(trim(Tools::getValue('BLX_ALIPAY_SIGN_KEY')));
			$alipay_way = strval(Tools::getValue('BLX_ALIPAY_WAY'));
			$server_ip = strval(trim(Tools::getValue('BLX_ALIPAY_SERVER_IP')));
			
			if(!$alipay_account || empty($alipay_account))
				$output .= $this->displayError($this->l('Invalid alipay account'))."<br/>";
			if(!$alipay_partner_id || empty($alipay_partner_id) || substr($alipay_partner_id, 0, 4) != '2088' || strlen($alipay_partner_id) != 16)
				$output .= $this->displayError($this->l('Partner id is a digital string start with:2088 and length 16.'))."<br/>";
			if(!$alipay_sign_key || empty($alipay_sign_key))
				$output .= $this->displayError($this->l('Invalid alipay sign key.'))."<br/>";
			if(!$alipay_way || empty($alipay_way))
				$output .= $this->displayError($this->l('Invalid pay way'));
			if(!$server_ip || empty($server_ip) || !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $server_ip))
				$output .= $this->displayError($this->l('Invalid ip address'));
			
			if(is_null($output)){	
				Configuration::updateValue('BLX_ALIPAY_ACCOUNT',$alipay_account);
				Configuration::updateValue('BLX_ALIPAY_PARTNER_ID',$alipay_partner_id);
				Configuration::updateValue('BLX_ALIPAY_SIGN_KEY',$alipay_sign_key);
				Configuration::updateValue('BLX_ALIPAY_WAY',$alipay_way);
				Configuration::updateValue('BLX_ALIPAY_SERVER_IP',$server_ip);
				
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		
		return $output.$this->displayForm();
	}
	
	public function checkCurrency($cart){
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);
		
		if(is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency']) {
					return true;
				}
		return false;
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
					'type' =>'text',
					'label' => $this->l('Alipay Account'),
					'name'  => 'BLX_ALIPAY_ACCOUNT',
					'size' => 50,
					'required' => true,
					'desc' => $this->l('Your alipay account applied from Alipay.')
					),
				array(
					'type' =>'text',
					'label' => $this->l('PID'),
					'name'  => 'BLX_ALIPAY_PARTNER_ID',
					'required' => true,
					'desc' => $this->l('Your partner id(PID) applied from Alipay.')
					),
				array(
					'type' =>'text',
					'label' => $this->l('Sign Key'),
					'name' => 'BLX_ALIPAY_SIGN_KEY',
					'required' => true,
					'desc' => $this->l('Your security key applied from Alipay.')
				),
				array(
					'type' =>'text',
					'label' => $this->l('Server IP'),
					'name' => 'BLX_ALIPAY_SERVER_IP',
					'required' => true,
					'desc' => $this->l('Your server\' ip address.')
				),
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
		$helper->fields_value['BLX_ALIPAY_ACCOUNT'] = Configuration::get('BLX_ALIPAY_ACCOUNT');
		$helper->fields_value['BLX_ALIPAY_PARTNER_ID'] = Configuration::get('BLX_ALIPAY_PARTNER_ID');
		$helper->fields_value['BLX_ALIPAY_SIGN_KEY'] = Configuration::get('BLX_ALIPAY_SIGN_KEY');
		$helper->fields_value['BLX_ALIPAY_SERVER_IP'] = Configuration::get('BLX_ALIPAY_SERVER_IP');
		
		return $helper->generateForm($fields_form);
	}
	
	public function hookPayment($params){
		if(!$this->active)
			return;
		
		if (!$this->checkCurrency($params['cart']))
			return;
			
		$this->context->smarty->assign(
			array(
				'module_name' => Configuration::get('BLX_ALIPAY_NAME'),
				'module_link' => $this->context->link->getModuleLink('alipay','payment'),
				'module_path' => $this->_path
			)
		);
		
		return $this->display(__FILE__,'payment.tpl');
	}
	
	public function getPaymentType($flag=null){
		if(is_null($flag))
			$flag = Configuration::get('BLX_ALIPAY_WAY');
			
		switch ($flag){
			case self::PAY_WAY_DIRECT_PAY: return "1";break;
			case self::PAY_WAY_PARTNER_TRADE: return "1";break;
		}
		return false;
	}
	
	public function getPaymentService($flag=null){
		if(is_null($flag))
			$flag = Configuration::get('BLX_ALIPAY_WAY');
		
		switch ($flag){
			case self::PAY_WAY_DIRECT_PAY: return "create_direct_pay_by_user";break;
			case self::PAY_WAY_PARTNER_TRADE: return "create_partner_trade_by_buyer";break;
			case self::PAY_WAY_DIRECT_PAY_REFUND: return "refund_fastpay_by_platform_pwd";break;
		}
		return false;
	}
	
	private function _createLinkString($param){
		$args = "";
		while(list($key,$val)=each($param))
			$args .=$key."=".$val."&";
		
		$args = substr($args, 0, count($args)-2);
		
		if(get_magic_quotes_gpc())
			$args = stripslashes($args);
		
		return $args;
	}
	
	private function _createLinkStringUrlencode($param){
		$args = "";
		while(list($key,$val)=each($param))
			$args .=$key."=".urlencode($val)."&";
		
		$args = substr($args, 0, count($args)-2);
		
		if(get_magic_quotes_gpc())
			$args = stripslashes($args);
		
		return $args;
	}
	
	private function _processParamsFilter($param){
		$param_filter = array();
		while(list($key,$val) = each($param))
			if ($key == 'sign' || $key == 'sign_key' || $val == '')
				continue;
			else 
				$param_filter[$key] = $param[$key];
				
		return $param_filter;
	}
	
	private function _processArgsSort($param){
		ksort($param);
		reset($param);
		
		return $param;
	}
	
	private function _processCharsetEncode($input,$_output_charset ,$_input_charset) {
		$output = "";
		if(!isset($_output_charset) )$_output_charset  = $_input_charset;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
	
	private function _processCharsetDecode($input,$_input_charset ,$_output_charset) {
		$output = "";
		if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset changes.");
		return $output;
	}
	
	private function _processMD5Sign($str){
		$str = $str . Configuration::get('BLX_ALIPAY_SIGN_KEY');
		return md5($str);
	}
	
	private function _processMD5Verify($str, $sign){
		$tmpSign = $this->_processMD5Sign($str);
		
		if ($tmpSign == $sign) {
			return true;
		}
		
		return false;
	}
	
	private function _buildRequestSign($param_sort){
		$str = $this->_createLinkString($param_sort);
		$sign = $this->_processMD5Sign($str);
		
		return $sign;
	}
	
	private function _buildRequestParam($param){
		$param_filter = $this->_processParamsFilter($param);
		$param_sort = $this->_processArgsSort($param_filter);
		
		$sign = $this->_buildRequestSign($param_sort);
		
		$param_sort['sign'] = $sign;
		$param_sort['sign_type'] = self::SIGN_TYPE;
		
		return $param_sort;
	}
	
	private function _processHttpRequestPost($url, $param, $input_charset=''){
		if (trim($input_charset) != '')
			$url = $url."_input_charset=".$input_charset;
			
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO,$this->alipay_cacert);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$param);
		$responseText = curl_exec($curl);
		curl_close($curl);
		
		return $responseText;
	}
	
	private function _processHttpRequestGet($url){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 );
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO,$this->alipay_cacert);
		$responseText = curl_exec($curl);
		curl_close($curl);
		
		return $responseText;
	}
	
	public function getRequestParam($id_order){
		$order = new Order((int)$id_order);
		$param = array(
			'action'=>self::ALIPAY_GATEWAY_NEW,
			'input_charset'=>self::INPUT_CHARSET,
			'inputs'=>array(
				'service'=>$this->getPaymentService(),
				'partner'=>Configuration::get('BLX_ALIPAY_PARTNER_ID'),
				'payment_type'=>$this->getPaymentType(),
				'notify_url'=>$this->context->link->getModuleLink('alipay','notify'),
				'return_url'=>$this->context->link->getModuleLink('alipay','return'),
				'seller_email'=>Configuration::get('BLX_ALIPAY_ACCOUNT'),
				'out_trade_no'=>$order->id_order,
				'subject'=>$order->name,
				'total_fee'=>$order->getOrdersTotalPaid(),
				'body'=>'BODY TEST',//order description
				'show_url'=>'',
				'anti_phishing_key'=>'',
				'exter_invoke_id'=>Configuration::get('BLX_ALIPAY_SERVER_IP'),
				'_input_charset'=>self::INPUT_CHARSET
			)
		);
		
		return $this->_buildRequestParam($param);
	}
	
	public function getRefundParam($id_order, $refund_fee=0, $refund_reason=''){
		$order = new Order((int)$id_order);
		$total_fee = $order->getTotalPaid();
		if($refund_fee <=0 || $refund_fee > $total_fee){
			self::Logger()->logWarning('Refund fee should be >0 and <='.$total_fee);
			return false;
		}
		
		$trade_no = AlipayNotifyModel::getAlipayTradeNo($id_order);
		if($trade_no == false){
			self::Logger()->logWarning('Unable to get the right trade_no by order id:'.$id_order);
			return false;
		}
		
		$param = array(
			'service' => $this->getPaymentService(Alipay::PAY_WAY_DIRECT_PAY_REFUND),
			'partner' => Configuration::get('BLX_ALIPAY_PARTNER_ID'),
			'_input_charset' => Alipay::INPUT_CHARSET,
			'notify_url' => $this->context->link->getModuleLink('alipay','notify'),
			'seller_email' => Configuration::get('BLX_ALIPAY_ACCOUNT'),
			'seller_user_id' => Configuration::get('BLX_ALIPAY_PARTNER_ID'),
			'refund_date' => date('Y-m-d H:i:s'),
			'batch_no' => date('Y-m-d')."D".$id_order."T".date("H:i:s"),
			'batch_num' => 1,
			'detail_data' => $trade_no."^".$refund_fee."^".$refund_reason
		);
		
		return $this->_buildRequestParam($param);
	}
	
	public function verifyNotify(){
		$param_filter = $this->_processParamsFilter($_POST);
		$param_sort = $this->_processArgsSort($param_filter);
		$prestr = $this->_createLinkString($param_sort);
		
		$isSign = $this->_processMD5Verify($prestr, Tools::getValue('sign'));
		if(!$isSign)
			return false;
			
		$vUrl = self::ALIPAY_HTTP_VERIFY_URL . 'partner=' . Configuration::get('BLX_ALIPAY_PARTNER_ID') . '&notify_id=' . Tools::getValue('notify_id');
		$resTxt = $this->_processHttpRequestGet($vUrl);
		
		if(preg_match('/true$/i', $resTxt))
			return true;
		return false;
	}
}





