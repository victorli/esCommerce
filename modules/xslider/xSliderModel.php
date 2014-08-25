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
class xSliderModel extends ObjectModel{
	
	public $id_xslider;
	
	public $name;
	public $width;
	public $height;
	public $fx = 'random';
	public $barDirection = 'leftToRight'; 
	public $barPosition = 'bottom'; //top or bottom
	public $loader = 'pie';//or bar
	public $navigation = true;
	public $overlayer = true;
	public $pagination = true;
	public $playPause = true;
	public $piePosition = 'rightTop'; //'rightTop', 'leftTop', 'leftBottom', 'rightBottom'
	public $thumbnails = false;
	public $time = 700;
	//distinct by hook position
	public $id_hook;
	
	public $id_shop;
	public $id_shop_group;
	public $date_add;
	public $date_upd;
	
	public $items = array();
	
	public static $definition = array(
		'table' => 'xslider_config',
		'primary' => 'id_xslider',
		'fields' => array(
			'name'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'width'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'height'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'fx'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'barDirection'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
			'barPosition'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
			'loader'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 16),
			'navigation'	=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true, 'size' => 8),
			'overlayer'		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true, 'size' => 8),
			'pagination'	=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true, 'size' => 8),
			'playPause'		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true, 'size' => 8),
			'piePosition'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
			'thumbnails'	=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true, 'size' => 8),
			'time'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'id_hook'		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'id_shop' 		=> 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_shop_group' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'date_add' 		=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' 		=> 	array('type' => self::TYPE_DATE, 'validate' => 'isDate')
		)
	);
	
	public function __construct($id = null, $id_lang = null, $id_shop = null){
		parent::__construct($id, $id_lang, $id_shop);
		
		if($id){
			$sql = new DbQuery();
			$sql->from('xslider_items','x');
			$sql->where('x.'.$this->def['primary'].'='.(int)$id);
			
			$this->items = Db::getInstance()->executeS($sql);
		}
	}
	
	public static function createTables(){
		return self::_createConfigTable() && self::_createSlideTable();
	}
	
	private static function _createConfigTable(){
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'xslider_config` (
				`id_xslider`  int NOT NULL AUTO_INCREMENT ,
				`name`	varchar(255) NOT NULL,
				`width`  mediumint NOT NULL ,
				`height`  mediumint NOT NULL ,
				`fx`  varchar(255) NOT NULL ,
				`barDirection`  varchar(32) NOT NULL ,
				`barPosition`  varchar(32) NOT NULL ,
				`loader`  varchar(16) NOT NULL ,
				`navigation`  tinyint(1) NOT NULL ,
				`overlayer`  tinyint(1) NOT NULL DEFAULT 1 ,
				`pagination`  tinyint(1) NOT NULL ,
				`playPause`  tinyint(1) NOT NULL ,
				`piePosition`  varchar(32) NOT NULL ,
				`thumbnails`  tinyint(1) NOT NULL DEFAULT 0 ,
				`time`  int NOT NULL ,
				`id_hook`  int NOT NULL ,
				`id_shop`  int NOT NULL ,
				`id_shop_group`  int NOT NULL ,
				`date_add`  datetime NOT NULL ,
				`date_upd`  timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY(`id_xslider`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARACTER SET=utf8;';
		
		return Db::getInstance()->execute($sql);
	}
	
	private static function _createSlideTable(){
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'xslider_items` (
				`id_xslider_item`  int NOT NULL AUTO_INCREMENT ,
				`id_xslider`  int NOT NULL ,
				`image`  varchar(255) NOT NULL ,
				`link`  varchar(255) NULL ,
				`description`  varchar(255) NOT NULL ,
				`active`  tinyint(1) NOT NULL DEFAULT 1,
				`date_upd`  timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY(`id_xslider_item`)
				)
				ENGINE='._MYSQL_ENGINE_.'
				DEFAULT CHARACTER SET=utf8;';
		
		return Db::getInstance()->execute($sql);
	}
	
	public static function dropTable(){
		$sql = "DROP TABLE IF EXISTS "._DB_PREFIX_."xslider_config,"._DB_PREFIX_."xslider_items";
		
		return Db::getInstance()->execute($sql);
	}
	
	public function add($autodate=true,$null_values=false){
		$this->id_shop = ($this->id_shop) ? $this->id_shop : Context::getContext()->shop->id;
		$this->id_shop_group = ($this->id_shop_group) ? $this->id_shop_group : Context::getContext()->shop->id_shop_group;
		
		return parent::add($autodate,false);
	}
	
	public function saveItems(){
		if(!isset($this->id_xslider) || !is_numeric($this->id_xslider))
			throw new PrestaShopException("id_xslider is empty or invalid.");
			
		if(is_array($this->items)){
			return Db::getInstance()->insert('xslider_items', $this->items,false,true,Db::REPLACE);
		}
		
		return false;
	}
	
	public static function getNameById($id_slider){
		if(!isset($id_slider))
			throw new PrestaShopException("id_xslider is empty or invalid.");
		
		$sql = new DbQueryCore();
		$sql->select('x.name');
		$sql->from('xslider_config','x');
		$sql->where('x.id_slider='.(int)$id_slider);
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}
	
	public static function getSliderById($id){
		$sql = new DbQueryCore();
		$sql->from('xslider_config','x');
		$sql->where('x.id_xslider='.$id);
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
	}
	
	public static function getSliders($filter=null, $orderBy='id_xslider', $orderWay='DESC'){
		$sql = new DBQuery();
		$sql->from('xslider_config','x');
		if(isset($filter))
			$sql->where($filter);
		$sql->orderBy('x.'.(isset($orderBy)? $orderBy : 'id_xslider').' '.(isset($orderWay) ? $orderWay : 'DESC'));
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
	
	public static function getSliderItems($id_xslider=null){
		$sql = new DBQuery();
		$sql->from('xslider_items','x');
		if (isset($id_xslider) && is_numeric($id_xslider))
			$sql->where('x.id_xslider='.(int)$id_xslider);
		$sql->orderBy('x.id_xslider DESC');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
	
	public static function deleteByIds($ids){
		if (is_array($ids)){
			$where = 'id_xslider in('.implode(',', $ids).')';
		}else{
			$where = 'id_xslider = '.$ids;
		}
		
		//clear xslider_config
		if(Db::getInstance()->delete('xslider_config', $where)){
			if(Db::getInstance()->delete('xslider_items', $where)){
				return true;
			}else{
				return false;
			}
		}
		
		return false;
	}
}
