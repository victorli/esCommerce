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
		
		$this->tableConfig = 'xSliderConfig';
		$this->tableItem = 'xSliderItem';
		//configurations
		
		parent::__construct();
		
		$this->displayName = $this->l('XSlider');
		$this->description = $this->l('An awesome slder addon based on camera and you can hook all front-office hooks for home page and general site pages.');
		
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
	
	/**
	 * @return FileLoggerCore 
	 */
	public static function Logger(){
		$logger = new FileLogger();
		$logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_xslider.log');
		return $logger;
	}
	
	public function getContent(){
		
		$output = '';
		if(Tools::isSubmit('addSlider')){
			$output .= $this->renderConfigForm();
		}elseif(Tools::isSubmit('submitSlider')){
			$xslider = new xSliderModel();
			if(Tools::getValue('id_xslider') && (int)Tools::getValue('id_xslider') != 0)
				$xslider->id = (int)Tools::getValue('id_xslider');
			$xslider->name = Tools::getValue('name');
			$xslider->width = (int)Tools::getValue('width');
			$xslider->height = (int)Tools::getValue('height');
			$xslider->loader = Tools::getValue('loader');
			if($xslider->loader == 'pie')
				$xslider->piePosition = Tools::getValue('piePosition');
			else 
				$xslider->barPosition = Tools::getValue('barPosition');
			$xslider->time = (int)Tools::getValue('time');
			$xslider->navigation = (int)Tools::getValue('navigation');
			$xslider->pagination = (int)Tools::getValue('pagination');
			$xslider->thumbnails = (int)Tools::getValue('thumbnails');
			$xslider->id_hook = (int)Tools::getValue('id_hook');
			
			if($xslider->save(false,true)){
				$output .= $this->displayConfirmation($this->l('Add/Update slide successfully.'));
			}else{ 
				$output .= $this->displayError($this->l('Error to Add/Update slide.'));
			}
			
			$output .= $this->renderConfigForm($xslider->id_xslider).$this->renderItemList();
			
		}elseif(Tools::isSubmit('addSliderItem')){
			$output .= $this->renderItemForm();
		}elseif(Tools::isSubmit('submitSliderItem')){
			$xslider = new xSliderModel((int)Tools::getValue('id_xslider'));
			if($xslider instanceof ObjectModel){
				
				//Uploads image and save slider item
				$type = Tools::strtolower(Tools::substr(strrchr($_FILES['image']['name'], '.'), 1));
				$imagesize = @getimagesize($_FILES['image']['tmp_name']);
				if(isset($_FILES['image']) && 
				   isset($_FILES['image']['tmp_name']) &&
				   !empty($_FILES['image']['tmp_name']) && 
				   !empty($imagesize) && 
				   in_array(Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array('jpg','gif','jpeg','png')) &&
				   in_array($type, array('jpg','gif','jpeg','png'))){
					
				   	$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'ECX');
				   	$salt = sha1(microtime());
				   	
				   	if($error = ImageManager::validateUpload($_FILES['name']))
				   		$errors[] = $error;
				   	elseif((!$temp_name || !move_uploaded_file($_FILES['image']['tmp_name'], $temp_name)))
				   		$errors[] = $this->displayError($this->l('Something system error occurred.'));
				   	elseif(!ImageManager::resize($temp_name, dirname(__FILE__).'/images/'.$salt.'_'.$xslider->id.'_'.$_FILES['image']['name'],null,null,$type))
				   		$errors[] = $this->displayError($this->l('An error occurred during uploding the image.'));
				   	if(isset($temp_name))
				   		@unlink($temp_name);
				}
				if(count($errors)){
					$output .= implode('<br>', $errors) . $this->renderItemForm();
				}else{
					$data = array('id_xslider_item'=>(int)Tools::getValue('id_xslider_item'),
									'id_xslider'=>(int)Tools::getValue('id_xslider'),
									'image'=>$salt.'_'.(int)Tools::getValue('id_xslider').'_'.$_FILES['image']['name'],
									'description'=>Tools::getValue('description'),
									'link' => Tools::getValue('link'),
									'active'=>(int)Tools::getValue('active'));
					if(!xSliderModel::saveItem($data))
						$output .= $this->displayError($this->l('Error to save slider item for '.$xslider->id));
					else 
						$output .= $this->displayConfirmation($this->l('Add/Update slider item successfully.'));	
					
					$output .= $this->renderConfigForm($xslider->id);
				}
			}else{
				$output .= $this->displayConfirmation($this->l('Unknown xslider id:'.Tools::getValue('id_xslider')));
				$output .= $this->renderConfigList();
			}
		}elseif(Tools::isSubmit('delSlider') || Tools::isSubmit('submitBulkdelete'.$this->tableConfig)){//delete
			if(Tools::getValue('id_xslider') && is_numeric(Tools::getValue('id_xslider'))){
				if(xSliderModel::deleteByIds(Tools::getValue('id_xslider')))
					$output .= 	$this->displayConfirmation($this->l('Remove slide successfully.'));
				else 
					$output .= $this->displayError($this->l('Fail to remove slide.'));
			}else{
				$ids = Tools::getValue($this->tableConfig.'Box');
				if(!is_array($ids) || count($ids) < 1){
					$output .= $this->displayError($this->l('Please choose one item at least.'));
				}else{
					if(xSliderModel::deleteByIds($ids)){
						$output .= $this->displayConfirmation($this->l('Remove slide successfully.'));
					}else{
						$output .= $this->displayError($this->l('Fail to remove slide.'));
					}
				}
			}
			$output .= $this->renderConfigList();
			
		}elseif(Tools::isSubmit('submitFilterButton'.$this->tableConfig)){//filter
			$output .= $this->renderConfigList();
		}elseif(Tools::isSubmit('navigationSlider') || Tools::isSubmit('paginationSlider') || Tools::isSubmit('thumbnailsSlider')){
			$field = '';
			if(Tools::getIsset('navigationSlider')) $field = 'navigation';
			elseif(Tools::getIsset('paginationSlider')) $field = 'pagination';
			elseif(Tools::getIsset('thumbnailsSlider')) $field = 'thumbnails';
			$id_xslider = Tools::getValue('id_xslider');
			$enabled = Tools::getValue('enabled',1);
			
			if(xSliderModel::updateSlider(array($field=>!$enabled),'id_xslider='.$id_xslider)){
				$output .= $this->displayConfirmation($this->l('Update '.$field.' successfully.'));
			}else{
				$output .= $this->displayError($this->l('Fail to update '.$field));
			}
			
			$output .= $this->renderConfigList();			
		}else{ //default config list
			$output .= $this->renderConfigList();
		}
		
		return $output;
	}
	
	protected function headerHTML($lt='pie'){
		if(Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name)
			return;
			
		$html = '<script type="text/javascript">
				$(function(){
					var $barBox = $("#barPosition").parent().parent();
					var $pieBox = $("#piePosition").parent().parent();
				';
		if($lt == 'pie')						
			$html .= '$barBox.hide();';
		else 
			$html .= '$pieBox.hide();';
					
		$html .= '$("#loader").change(function(){
					if($(this).val() == "bar"){
						$barBox.show();
						$pieBox.hide();
					}else{
						$barBox.hide();
						$pieBox.show();
					}
				});
			});
			</script>';
		
		return $html;
	}
	
	public function renderConfigForm($id=null){
		$lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$form_id = 0;
		$id_xslider = $id;
		if(is_null($id_xslider))
			$id_xslider = Tools::getValue('id_xslider',null);
		
		$xslider = null;
		if(isset($id_xslider)){
			$xslider = xSliderModel::getSliderById($id_xslider);
			if(is_array($xslider) && count($xslider)>1)
				$form_id = $id_xslider;
			else 
				$xslider = null;
		}
		
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
					'title' => 	$this->l('Slide Config Form'),
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
		$helper->identifier = 'id_xslider';
		$helper->id = $form_id;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		if($id_xslider)
			$helper->currentIndex .= '&id_xslider='.$id_xslider;
		
		$helper->default_form_language = $lang;
		$helper->allow_employee_form_lang = $lang;
		
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submitSlider';
		
		/*$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
			),
			'back' => array(
				'desc' => $this->l('Back to list'),
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules')
			)
		);*/
		
		$helper->fields_value = $xslider;
		$lt = isset($xslider['loader']) ? $xslider['loader'] : 'pie';
		return $this->headerHTML($lt). $helper->generateForm($fields_form).$this->renderItemList();
		
	}
	
	public function renderConfigList(){
		
		$fields_list = array(
			'id_xslider' => array('title' => $this->l('ID'), 'align' => 'right'),
			'name'		=>	array('title' => $this->l('Name'), 'width' => 'auto'),
			'id_hook'	=>	array('title' => $this->l('Hook'), 'align'=>'center', 'callback' =>'getNameById', 'callback_object' => 'Hook'),
			'width'		=>	array('title' => $this->l('Width(px)'),	'align'=>'right', 'orderby'=>false),
			'height'	=>	array('title' => $this->l('Height(px)'), 'align'=>'right', 'orderby'=>false),
			'time'		=>	array('title' => $this->l('Time(ms)'),'align'=>'right', 'orderby'=>false),
			'loader'	=>	array('title' => $this->l('Loader'), 'align'=>'center'),
			'navigation'=>	array('title' => $this->l('Navigation'), 'class'=>'fixed-width-sm','active'=>'navigation','align'=>'center', 'type'=>'bool','orderby'=>false),
			'pagination'=>	array('title' => $this->l('Pagination'), 'class'=>'fixed-width-sm','active'=>'pagination','align'=>'center', 'type'=>'bool','orderby'=>false),
			'thumbnails'=>	array('title' => $this->l('Thumbnails'), 'class'=>'fixed-width-sm','active'=>'thumbnails','align'=>'center', 'type'=>'bool','orderby'=>false)	
		);
		
		$list = xSliderModel::getSliders($this->_getFilter(), Tools::getValue($this->tableConfig.'Orderby',null), Tools::getValue($this->tableConfig.'Orderway',null));
		
		$tpl_list_vars['icon'] = 'icon-list-ul';
		$tpl_list_vars['title'] = $this->l('Slide Config List');
		
		$tpl_delete_link_vars = array();
		
		$helper = new HelperList();
		$helper->module = $this;
		$helper->identifier = 'id_xslider';
		$helper->currentIndex = AdminController::$currentIndex;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->table = $this->tableConfig;
		$helper->listTotal = count($list);
		$helper->no_link = true;
		
		$helper->tpl_vars = $tpl_list_vars;
		$helper->tpl_delete_link_vars = $tpl_delete_link_vars;
		
		$helper->toolbar_btn = array('new_slide' => array(
									'href'=>AdminController::$currentIndex.'&configure='.$this->name.'&addSlider&token='.Tools::getAdminTokenLite('AdminModules'),
									'desc' => $this->l('Add new Slider'),
									'imgclass' => 'new'));
		$helper->bulk_actions = array('delete' => array(
								'text' => $this->l('Delete Selected'), 
								'confirm'=>$this->l('Are you sure to delete selected items?'),
								'icon'=>'icon-trash'));
		
		$helper->actions = array('edit','delete');
		
		return $helper->generateList($list,$fields_list);
	}
	
	public function renderItemForm(){
		
		$lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$id_xslider = Tools::getValue('id_xslider',null);
		if(!$id_xslider)
			return $this->displayError($this->l('id_xslider is empty.'));
		
		$id_xslider_item = Tools::getValue('id_xslider_item',null);

		$identifier = 'id_xslider_item';
		$form_id = $id_xslider_item;
		
		$fields_form[0]['form'] = array(
				'legend' => array(
					'title' => 	$this->l('Slide Item'),
					'icon'	=>	'icon-cogs'
				),
				'input' => array(
					array(
						'type' 	=> 'file',
						'label'	=>	$this->l('Select a picture'),
						'name'	=>	'image',
						'required'=> true,
						'desc' => $this->l(sprintf('Max image size %s', ini_get('upload_max_filesize')))
					),
					array(
						'type' => 'text',
						'label' => $this->l('Description'),
						'name'	=> 'description',
						'required' => false
					),
					array(
						'type' => 'text',
						'label' => $this->l('Link'),
						'name' => 'link',
						'required' => false
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Active'),
						'name' => 'active',
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
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'button'
				)
			);
			
		$helper = new HelperForm();
		$helper->module = $this;
		$helper->identifier = $identifier;
		$helper->id = $form_id;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&id_xslider='.$id_xslider;
		
		$helper->default_form_language = $lang;
		$helper->allow_employee_form_lang = $lang;
		
		$helper->title = $this->displayName;
		//$helper->show_toolbar = true;
		//$helper->toolbar_scroll = true;
		$helper->submit_action = 'submitSliderItem';
		$helper->fields_value = xSliderModel::getSliderItemById($id_xslider_item);
		
		return $helper->generateForm($fields_form);
	}
	
	public function renderItemList(){
		
		$id_xslider = Tools::getValue('id_xslider',null);
		if(!$id_xslider)
			return '';// output empty when added one new slider
		
		$fields_list = array(
			'id_xslider_item' => array('title' => $this->l('ID'), 'align' => 'right', 'class' => 'fixed-width-xs'),
			//'id_xslider' => array('title' => $this->l('Slider Name'),'callback' => 'getNameById', 'callback_object' => 'xSliderModel'),
			'image'		=>	array('title' => $this->l('Image'),'orderby'=>false),
			'link'		=>	array('title' => $this->l('Link'),'orderby'=>false),
			'description'		=>	array('title' => $this->l('Description'),	'align'=>'right', 'orderby'=>false),
			'active'	=>	array('title' => $this->l('Active'), 'align'=>'center', 'orderby'=>false, 'active'=>'status','type'=>'bool','class'=>'fixed-width-sm')
		);
		
		$list = xSliderModel::getSliderItems($id_xslider);
		
		$tpl_list_vars['icon'] = 'icon-list-ul';
		$tpl_list_vars['title'] = $this->l('Slider Items');
		
		$tpl_delete_link_vars = array();
		
		$helper = new HelperList();
		$helper->module = $this;
		$helper->identifier = 'id_xslider_item';
		$helper->currentIndex = AdminController::$currentIndex;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->table = $this->tableItem;
		$helper->listTotal = count($list);
		$helper->no_link = true;
		
		$helper->tpl_vars = $tpl_list_vars;
		$helper->tpl_delete_link_vars = $tpl_delete_link_vars;
		
		$helper->toolbar_btn = array('new_slider_item' => array(
									'href'=>AdminController::$currentIndex.'&configure='.$this->name.'&addSliderItem&id_xslider='.$id_xslider.'&token='.Tools::getAdminTokenLite('AdminModules'),
									'desc' => $this->l('Add new Slider item'),
									'imgclass' => 'new'));
		$helper->bulk_actions = array('delete' => array(
								'text' => $this->l('Delete Selected'), 
								'confirm'=>$this->l('Are you sure to delete selected items?'),
								'icon'=>'icon-trash'));
		
		$helper->actions = array('editItem','deleteItem');
		
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
	
	public function hookdisplayLeftColumn($params){
		
	}
	
	public function hookdisplayRightColumn($params){
		
	}
	
	public function hookdisplayFooter($params){
		
	}
	
	public function hookdisplayHome($params){
		
	}
	
	public function displayEditLink($token = null, $id, $name = null){
		$this->context->smarty->assign(array(
			'href' => Tools::safeOutput(AdminController::$currentIndex.'&configure='.$this->name.'&addSlider&id_xslider='.$id.'&token='.Tools::getAdminTokenLite('AdminModules')),
			'action' => $this->l('Edit','Helper'),
			'id' => $id
		));

		return $this->display(__FILE__,'/helper/list_action_edit.tpl');
	}
	
	public function displayEditItemLink($token = null, $id, $name = null){
		$this->context->smarty->assign(array(
			'href' => Tools::safeOutput(AdminController::$currentIndex.'&configure='.$this->name.'&addSliderItem&id_xslider_item='.$id.'&token='.Tools::getAdminTokenLite('AdminModules')),
			'action' => $this->l('Edit','Helper'),
			'id' => $id
		));

		return $this->display(__FILE__,'/helper/list_action_edit.tpl');
	}
	
	public function displayDeleteLink($token = null, $id, $name = null){
		$this->context->smarty->assign(array(
			'href' => Tools::safeOutput(AdminController::$currentIndex.'&configure='.$this->name.'&delSlider&id_xslider='.$id.'&token='.Tools::getAdminTokenLite('AdminModules')),
			'action' => $this->l('Edit','Helper'),
			'id' => $id
		));

		return $this->display(__FILE__,'/helper/list_action_delete.tpl');
	}
	
	public function displayDeleteItemLink($token = null, $id, $name = null){
		$this->context->smarty->assign(array(
			'href' => Tools::safeOutput(AdminController::$currentIndex.'&configure='.$this->name.'&delSliderItem&id_xslider_item='.$id.'&token='.Tools::getAdminTokenLite('AdminModules')),
			'action' => $this->l('Edit','Helper'),
			'id' => $id
		));

		return $this->display(__FILE__,'/helper/list_action_delete.tpl');
	}
	
	public function displayEnableLink($token, $id, $value, $active, $id_category = null, $id_product = null, $ajax = false)
	{
		$this->context->smarty->assign(array(
			'ajax' => $ajax,
			'enabled' => (bool)$value,
			'url_enable' => Tools::safeOutput(AdminController::$currentIndex.'&configure='.$this->name.'&'.$active.'Slider&id_xslider='.$id.'&token='.Tools::getAdminTokenLite('AdminModules').'&enabled='.$value),
		));
		return $this->display(__FILE__,'/helper/list_action_enable.tpl');
	}
	
	private function _getFilter($type='config'){
		$filter = array();
		$config_filter = array('id_xslider','name','id_hook','loader','width','height','time','navigation','pagination','thumbnails');
		$item_filter = array('id_xslider_item','id_xslider');
		if($type == 'config'){
			foreach($config_filter as $cf){
				$v = Tools::getValue($this->tableConfig.'Filter_'.$cf);
				if(Tools::getIsset($this->tableConfig.'Filter_'.$cf) && !empty($v))
					array_push($filter, 'x.'.$cf.'=\''.Tools::getValue($this->tableConfig.'Filter_'.$cf).'\'');
			}
		}else{
			foreach ($item_filter as $if){
				$v = Tools::getValue($this->tableItem.'Filter_'.$if);
				if(Tools::getIsset($this->tableItem.'Filter_'.$if) && !empty($v))
					array_push($filter, 'x.'.$if.'=\''.Tools::getValue($this->tableItem.'Filter_'.$if.'\''));
			}
		}
		
		if(count($filter))
			return implode(' AND ', $filter);
			
		return null;
	}

}