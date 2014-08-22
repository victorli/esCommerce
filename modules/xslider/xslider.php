<?php
/*
* 2014 eCartx
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@ecartx.com so we can send you a copy immediately.
*
*  @author BLX90 <zs.li@blx90.com>
*  @copyright 2014 BLX90
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_') && !defined('_ECX_VERSION_'))
	exit;

include_once(dirname(__FILE__).'/xSliderModel.php');	
class Xslider extends Module{
	
	public function __construct(){
		$this->name = 'xslider';
		$this->tab = 'front_office_features';
		$this->version = '1.1.0';
		$this->author = Module::AUTHOR_IS_BLX90;
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min'=>'1.6');
		$this->ecx_versions_compliancy = array('min'=>'1.1.0');
		$this->bootstrap = true;
		
		//configurations
		
		parent::__construct();
		
		$this->displayName = $this->l('XSlider');
		$this->description = $this->l('An awesome slder addon based on camera.');
		
		$this->confirmUninstall = $this->l('Are you sure to remove this awesome slider?');
		
		if(!Configuration::get('BLX_XSLIDER_NAME'))
			$this->warning = $this->l('No name provided.');
	}
	
	public function install(){
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
		if(!parent::install() ||
			!$this->registerHook('displayHeader') ||
			!$this->registerHook('displayTopColumn') || 
			//!$this->registerHook('actionShopDataDuplication') ||
			!Configuration::updateValue('BLX_XSLIDER_NAME','xSlider') ||
			!xSliderModel::createTables()
		){
			return false;
		}
		
		return true;
	}
	
	public function uninstall(){
		if(!parent::uninstall() || 
			!Configuration::deleteByName('BLX_XSLIDER_NAME') ||
			!xSliderModel::dropTable()
		)
		return false;
		
		return true;
	}
	
	public function getContent(){
		$output = '';
		
		$output .= $this->renderConfigList();
		$output .= $this->renderItemList();
		
		return $output;
	}
	
	public function renderConfigForm(){
		
	}
	
	public function renderConfigList(){
		
		$fields_list = array(
			'id_xslider' => array('title' => $this->l('ID'), 'align' => 'right', 'class' => 'fixed-width-xs'),
			'name'		=>	array('title' => $this->l('Name'), 'width' => 'auto'),
			'id_hook'	=>	array('title' => $this->l('Hook'), 'align'=>'center', 'callback' =>'getNameById', 'callback_object' => 'Hook'),
			'width'		=>	array('title' => $this->l('Width'),	'align'=>'right', 'orderby'=>false),
			'height'	=>	array('title' => $this->l('Height'), 'align'=>'right', 'orderby'=>false),
			'time'		=>	array('title' => $this->l('Time'),'align'=>'right', 'orderby'=>false),
			'loader'	=>	array('title' => $this->l('Loader'), 'align'=>'center'),
			'position'	=>	array('title' => $this->l('Position'), 'align'=>'center'),
			'pagination'=>	array('title' => $this->l('Pagination'), 'class'=>'fixed-width-sm','active'=>'status','align'=>'center', 'type'=>'bool','orderby'=>false),
			'thumbnails'=>	array('title' => $this->l('Thumbnails'), 'class'=>'fixed-width-sm','active'=>'status','align'=>'center', 'type'=>'bool','orderby'=>false)	
		);
		
		$list = xSliderModel::getSlides();
		
		$tpl_list_vars['icon'] = 'icon-list-ul';
		$tpl_list_vars['title'] = $this->l('Slide Config List');
		
		$tpl_delete_link_vars = array();
		
		$helper = new HelperList();
		$helper->tpl_vars = $tpl_list_vars;
		$helper->tpl_delete_link_vars = $tpl_delete_link_vars;
		
		$helper->toolbar_btn = array('new_slide' => array(
									'href'=>AdminController::$currentIndex.'&add_slide&token='.Tools::getAdminTokenLite('AdminModules'),
									'desc' => $this->l('Add new Slide'),
									'imgclass' => 'new'));
		$helper->bulk_actions = array('delete' => array(
									'text' => $this->l('Delete Selected'), 
									'confirm'=>$this->l('Are you sure to delete selected items?'),
									'icon'=>'icon-trash'));
		
		return $helper->generateList($list,$fields_list);
	}
	
	public function renderItemForm(){
		
	}
	
	public function renderItemList(){
		$fields_list = array(
			'id_xslider_item' => array('title' => $this->l('ID'), 'align' => 'right', 'class' => 'fixed-width-xs'),
			'id_xslider' => array('title' => $this->l('Slider Name'),'callback' => '', 'callback_object' => ''),
			'image'		=>	array('title' => $this->l('Image')),
			'link'		=>	array('title' => $this->l('Link')),
			'description'		=>	array('title' => $this->l('Width'),	'align'=>'right', 'orderby'=>false),
			'active'	=>	array('title' => $this->l('Height'), 'align'=>'center', 'orderby'=>false, 'active'=>'status','type'=>'bool','class'=>'fixed-width-sm')
		);
		
		$list = xSliderModel::getSlideItems();
		
		$tpl_list_vars['icon'] = 'icon-list-ul';
		$tpl_list_vars['title'] = $this->l('Slide Items List');
		
		$tpl_delete_link_vars = array();
		
		$helper = new HelperList();
		$helper->tpl_vars = $tpl_list_vars;
		$helper->tpl_delete_link_vars = $tpl_delete_link_vars;
		
		//$helper->toolbar_btn = array('new_slide' => array(
		//							'href'=>AdminController::$currentIndex.'&add_slide&token='.Tools::getAdminTokenLite('AdminModules'),
		//							'desc' => $this->l('Add new Slide'),
		//							'imgclass' => 'new'));
		$helper->bulk_actions = array('delete' => array(
									'text' => $this->l('Delete Selected'), 
									'confirm'=>$this->l('Are you sure to delete selected items?'),
									'icon'=>'icon-trash'));
		
		return $helper->generateList($list,$fields_list);
	}
	
	public function hookdisplayHeader($params){
		return;
	}
	
	public function hookdisplayTopColumn($params){
		return;
	}
	
	public function hookdisplayTop($params){
		return $this->hookdisplayTopColumn($params);
	}
}