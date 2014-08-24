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
		
		if(Tools::isSubmit('addSlide')){
			$output .= $this->headerHTML();
			$output .= $this->renderConfigForm();
		}else{ //list
			$output .= $this->renderConfigList();
			$output .= $this->renderItemList();
		}
		
		return $output;
	}
	
	protected function headerHTML(){
		if(Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name)
			return;
			
		$html = '<script type="text/javascript">
				$(function(){
					$("#barPosition").hide();
					
					$("#loader").change(function(){
						if($(this).val() == "bar"){
							$("#barPosition").show();
							$("#piePosition").hide();
						}else{
							$("#barPosition").hide();
							$("#piePosition").show();
						}
					});
				});
				</script>';
		
		return $html;
	}
	
	public function renderConfigForm(){
		$lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$loaderTypes = array(
			array(
				'id_option' => 	'pie',
				'name'		=>	$this->l('Pie')
			),
			array(
				'id_option'	=>	'bar',
				'name'		=>	$this->l('Bar')
			)
		);
		
		$hooks = array();
		$hs = Hook::getHooks(true);
		foreach($hs as $h){
			if(strcmp(substr($h['name'], 0, 7),'display') !== 0)
				continue;
			array_push($hooks, array('id_option' => $h['id_hook'], 'name' => $h['name']));
		}
		
		$barPos = array(
			array(
				'id_option' => 	'top',
				'name'		=>	$this->l('Top')
			),
			array(
				'id_option'	=>	'bottom',
				'name'		=>	$this->l('Bottom')
			)
		);
		
		$piePos = array(
			array(
				'id_option' => 	'rightTop',
				'name'		=>	$this->l('rightTop')
			),
			array(
				'id_option'	=>	'leftTop',
				'name'		=>	$this->l('leftTop')
			),
			array(
				'id_option' => 	'leftBottom',
				'name'		=>	$this->l('leftBottom')
			),
			array(
				'id_option'	=>	'rightBottom',
				'name'		=>	$this->l('rightBottom')
			)
		);
		
		$fields_form[0]['form'] = array(
				'legend' => array(
					'title' => 	$this->l('Slide Config Info'),
					'icon'	=>	'icon-cogs'
				),
				'input' => array(
					array(
						'type' 	=> 'text',
						'label'	=>	$this->l('Name'),
						'name'	=>	'name',
						//'lang'	=>	true,
						'required'=> true
					),
					array(
						'type'	=>	'text',
						'label'	=>	$this->l('Width'),
						'name'	=>	'width',
						'suffix'=>	'px',
						'required'=> true	
					),
					array(
						'type'	=>	'text',
						'label'	=>	$this->l('Height'),
						'name'	=>	'height',
						'suffix'=>	'px',
						'required'=> true	
					),
					array(
						'type'	=>	'select',
						'label'	=>	$this->l('Loader Type'),
						'name'	=>	'loader',
						'required'=> true,
						'options' => array(
							'query' => 	$loaderTypes,
							'id'	=>	'id_option',
							'name'	=>	'name'
						)	
					),
					array(
						'type'	=>	'select',
						'label'	=>	$this->l('Pie Position'),
						'name'	=>	'piePosition',
						'required'=> true,
						'options' => array(
							'query' => 	$piePos,
							'id'	=>	'id_option',
							'name'	=>	'name'
						)	
					),
					array(
						'type'	=>	'select',
						'label'	=>	$this->l('Bar Position'),
						'name'	=>	'barPosition',
						'required'=> true,
						'options' => array(
							'query' => 	$barPos,
							'id'	=>	'id_option',
							'name'	=>	'name'
						)	
					),
					array(
						'type'	=>	'text',
						'label'	=>	$this->l('Transation speed'),
						'name'	=>	'time',
						'suffix'=>	'ms',
						'required'=> true
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show navigation'),
						'name' => 'navigation',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show pagination'),
						'name' => 'pagination',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show thumbnails'),
						'name' => 'thumbnails',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
					array(
						'type'	=>	'select',
						'label'	=>	$this->l('Hook position'),
						'name'	=>	'id_hook',
						'required'=> true,
						'options' => array(
							'query' => 	$hooks,
							'id'	=>	'id_option',
							'name'	=>	'name'
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
		$helper->submit_action = 'submitSlide';
		
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
		
		return $helper->generateForm($fields_form);
		
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
									'href'=>AdminController::$currentIndex.'&configure='.$this->name.'&addSlide&token='.Tools::getAdminTokenLite('AdminModules'),
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
			'id_xslider' => array('title' => $this->l('Slider Name'),'callback' => 'getNameById', 'callback_object' => 'xSliderModel'),
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