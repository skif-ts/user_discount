<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
 
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Sale;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

class CUserDiscount extends CBitrixComponent implements Controllerable
{
	
	private $userId = 0;
	
	const DISCOUNT_PREFIX = "Скидка: ";
	const DISCOUNT_SUFIX = "";
	const TIME_FOR_NEW_COUPON = 3600; // sec 1 hour 
	const TIME_FOR_ACTIVEDATE_COUPON = 10800; // sec 3600*3, 3 hour 
	
	/**
	 * @return array
	 */
	public function configureActions()
	{
		$this->_checkModules();
		$this->_checkUser();
		
		return [
			'get_discount' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						array(ActionFilter\HttpMethod::METHOD_POST)
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => []
			],
			'check_discount' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						array(ActionFilter\HttpMethod::METHOD_POST)
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => []
			],			
			
		];
	}
	
    /**
     * Проверка наличия модулей требуемых для работы компонента
     * @return bool
     * @throws Exception
     */
    private function _checkModules() {
        if ( 
			!Loader::includeModule('sale')
        ) {
            throw new \Exception('Не загружены модули необходимые для работы модуля');
        }

        return true;
    }
    private function _checkUser() {
		global $USER;
        if ($USER->GetID() > 0){
            $this->userId = $USER->GetID();
        }
        return true;
    }
 
	function executeComponent(){
		$this->_checkModules();
		$this->_checkUser();
		
		if($this->userId){
			$this->includeComponentTemplate();
			//return parent::executeComponent();
		}else{
			echo "Необходимо зарегистрироваться на сайте";
		}
		
	}
 
	/**
	 * @return array
	 */
	public function get_discountAction(){
		$ret_coupon = [];
		$arCoupon = $this->getCouponByUserID(true, true);
		if(!empty($arCoupon) && isset($arCoupon["DISCOUNT_ID"])){
			$arDiscount = $this->getDiscountByCoupon($arCoupon["COUPON"]);
			
			if(isset($arDiscount["arDiscount"]["SHORT_DESCRIPTION_STRUCTURE"]["VALUE"]) && $arDiscount["arDiscount"]["SHORT_DESCRIPTION_STRUCTURE"]["VALUE"] > 0){
				$ret_coupon = [
					'code' => $arCoupon["COUPON"],
					'discount' => $arDiscount["arDiscount"]["SHORT_DESCRIPTION_STRUCTURE"]["VALUE"],
				];
			}
		}
		
		if(empty($ret_coupon)){
			$DISCOUNT_VALUE = rand(1, 50);
			$arDiscount = $this->set_discount($DISCOUNT_VALUE);
			
			if(isset($arDiscount["COUPON"])){
				$ret_coupon = [
					'code' => $arDiscount["COUPON"],
					'discount' => $DISCOUNT_VALUE,
				];
				
			}
		}
		return $ret_coupon;
	}

	/**
	 * @param string $coupon
	 * @return array
	 */
	public function check_discountAction($coupon = ""){
		$succes_ret = false;
		$ret_coupon = [];
		$err_code = 1;
		$arCoupon = $this->checkCoupon($coupon);
		if($arCoupon["result"]){
			$arDiscount = $this->getDiscountByCoupon($arCoupon["arCoupon"]["COUPON"]);
			if(isset($arDiscount["arDiscount"]["SHORT_DESCRIPTION_STRUCTURE"]["VALUE"]) && $arDiscount["arDiscount"]["SHORT_DESCRIPTION_STRUCTURE"]["VALUE"] > 0){
				$ret_coupon = [
					'err_code' => "0",
					'discount' => $arDiscount["arDiscount"]["SHORT_DESCRIPTION_STRUCTURE"]["VALUE"],
					'arCoupon' => $arCoupon,
					'coupon' => $coupon,
				];
				$succes_ret = true;
				$err_code = 0;
			}else{
				$err_code = 2;
			}
		}
		
		if(!$succes_ret){
			$ret_coupon = [
				'err_code' => $err_code,
				'err' => "No discount",
				'discount' => 0,
				'arCoupon' => $arCoupon,
				'coupon' => $coupon,
			];
		}
		return $ret_coupon;
	}	
	
	
	public function getDiscountByCoupon($coupon){
		$couponUserId = false;
		$arCoupon = false;
		$arDiscount = false;
		$isCoupon = false;
		
		$isThisUser = false;
		$isActive = false;
		$isActiveDate = false;
		$isHasDiscount = false;
		
		$arCoupon = \Bitrix\Sale\DiscountCouponsManager::getData($coupon); //coupon - номер купона
		if($arCoupon) $isCoupon = true;
		
		$couponUserId = \Bitrix\Sale\Internals\DiscountCouponTable::getList(array(
			'select' => ["USER_ID"], 
			'filter' => ["COUPON" => $coupon,]
		))->fetch();
		if($couponUserId["USER_ID"]) $couponUserId = $couponUserId["USER_ID"];

		if($couponUserId == $this->userId){
			if(
				$arCoupon["ACTIVE"] == "Y" && 
				$arCoupon["SAVED"] == "N" && 
				$arCoupon["DISCOUNT_ACTIVE"] == "Y"  
			){
				$isActive = true;
			}
			
			$dtFrom = $arCoupon["DISCOUNT_ACTIVE_FROM"]->getTimestamp();
			$dtTo = $arCoupon["DISCOUNT_ACTIVE_TO"]->getTimestamp();
			
			$curDate = time();
			if($curDate >= $dtFrom && $curDate <= $dtTo){
				$isActiveDate = true;
			}
			
			
			$isThisUser = true;
			
			$arDiscount = \Bitrix\Sale\Internals\DiscountTable::getList(
				[
					'filter' => [
						'ID' => $arCoupon['DISCOUNT_ID']
					]
				]
			)->fetch();	
			if($arDiscount) $isHasDiscount = true;
		}
		
		$res = [
			"isCoupon" => $isCoupon,
			"isThisUser" => $isThisUser,
			"isActive" => $isActive,
			"isActiveDate" => $isActiveDate,
			"isHasDiscount" => $isHasDiscount,
			"userId" => $couponUserId,
			"arCoupon" => $arCoupon,
			"arDiscount" => $arDiscount,
			
			"dtFrom" => $dtFrom,
			"dtTo" => $dtTo,
			"curDate" => $curDate,
		];
		return $res;
	}	
	
	public function set_discount($DISCOUNT_VALUE){
		$res = false;
		$dateFrom = new \Bitrix\Main\Type\DateTime();
		$dateTo = new \Bitrix\Main\Type\DateTime();
		//$dateTo->add("3 hours"); //TIME_FOR_ACTIVEDATE_COUPON
		$dateTo->add("T".self::TIME_FOR_ACTIVEDATE_COUPON . "S"); //3 hours 
		
		$name = self::DISCOUNT_PREFIX . $DISCOUNT_VALUE . "%";
		if(self::DISCOUNT_SUFIX ) $name .= ", " . self::DISCOUNT_SUFIX;

		$arDiscountFields = [
			"LID" => SITE_ID,
			"SITE_ID" => SITE_ID,
			"NAME"=> $name, // "Скидка ".$DISCOUNT_VALUE."%",
			"DISCOUNT_VALUE" => $DISCOUNT_VALUE,
			"DISCOUNT_TYPE" => "P",
			"ACTIVE" => "Y", 
			'ACTIVE_FROM' => $dateFrom,
			'ACTIVE_TO' => $dateTo,
		   
			"CURRENCY" => "RUB",
			"USER_GROUPS" => [1],
		   
			"ACTIONS" => [
				"CLASS_ID" => "CondGroup",
				"DATA" => [
					"All" => "AND"
				],
				
				"CHILDREN" => [
					[
						"CLASS_ID" => "ActSaleBsktGrp",
						"DATA" => [
							"Type" => "Discount", //
							"Value" => $DISCOUNT_VALUE, //
							"Unit" => "Perc",
							"Max" => 0,
							"All" => "AND", //
							"True" => "True",
						],
						"CHILDREN" => [
						]
					],
				],
			],

			"CONDITIONS" =>  [
				'CLASS_ID' => 'CondGroup',
				'DATA' => [
				 'All' => 'AND',
				 'True' => 'True',
				],
				'CHILDREN' => [
				],  
			],
		   
			"COUPON_ADD" => "Y",
			"COUPON_COUNT" => "Y",
			"COUPON" =>  [
				'TYPE' => 'O',
				'ACTIVE_FROM' => $dateFrom,
				'ACTIVE_TO' => $dateTo,
			],

		];
		$iDiscountNumber = \CSaleDiscount::Add($arDiscountFields);
		if(IntVal($iDiscountNumber) > 0){

			$codeCoupon = CatalogGenerateCoupon(); //Генерация купона
			$arCouponFields = [
				"DISCOUNT_ID" => $iDiscountNumber,
				"COUPON" => $codeCoupon,
				"ACTIVE" => "Y",
				'ACTIVE_FROM' => $dateFrom,
				'ACTIVE_TO' => $dateTo,
				'USER_ID' => $this->userId,
				
				"TYPE" => \Bitrix\Sale\Internals\DiscountCouponTable::TYPE_ONE_ORDER,
				"MAX_USE" => 1,	
			];
			
			$dd = \Bitrix\Sale\Internals\DiscountCouponTable::add($arCouponFields); //Создаем купон для этого правила
		   \Bitrix\Sale\Internals\DiscountGroupTable::updateByDiscount($iDiscountNumber, [2], "Y", true);//Обновить параметры для группы пользователей с ID = 2, только тогда скидка появляется в списке скидок в админке
		   
			// добавим данные юзеру
			global $USER_FIELD_MANAGER;
			$USER_FIELD_MANAGER->Update( 'USER', $this->userId, array(
				'UF_COUPON'  => $codeCoupon,
				'UF_COUPON_ACTIVE_TO'  => $dateTo,
			) ); 
		   
			$res = [
				"DISCOUNT_ID" => $iDiscountNumber,
				"COUPON" => $codeCoupon,
				"ACTIVE" => "Y",
				'ACTIVE_FROM' => $dateFrom,
				'ACTIVE_TO' => $dateTo,
				'USER_ID' => $this->userId,
				'DISCOUNT_NAME' => $name,
				'DISCOUNT_VALUE' => $DISCOUNT_VALUE,
			];
			
		}

		return $res;
	}
	
	public function getCouponByUserID($forseOneHour = false, $forseDeleteOld = false){
		$arCoupon = [];
		
		$filter = [
			"USER_ID" => $this->userId, 
			"ACTIVE" => 'Y',
			"<=ACTIVE_FROM" => ConvertTimeStamp(false, "FULL"),
			">=ACTIVE_TO"   => ConvertTimeStamp(false, "FULL"),
		];
		
		$couponIterator = \Bitrix\Sale\Internals\DiscountCouponTable::getList(array(
			'select' => ["*"], //array('ID', 'DISCOUNT_ID'),
			'filter' => $filter
		));
		while ($coupon = $couponIterator->fetch()){
			if($forseOneHour){
				$curDate = time();
				
				$dtFrom = $coupon["ACTIVE_FROM"]->getTimestamp();
				$dtTo = $dtFrom + self::TIME_FOR_NEW_COUPON;
				if($curDate >= $dtFrom && $curDate <= $dtTo){
					$arCoupon = $coupon;
				}else{
					if($forseDeleteOld){
						$this->delete_discount($coupon["DISCOUNT_ID"]);
					}
				}
			}else{
				$arCoupon = $coupon;
			}
		}
		
		return $arCoupon;
	}	
	
	private function delete_discount($DISCOUNT_ID){
		$result = \CSaleDiscount::Delete($DISCOUNT_ID);
		return true;
	}	
	
	public function checkCoupon($coupon, $forseOneHour = false){
		//проверяем, есть ли актуальный купон
		// активный
		// активная дата +3, +1
		// текущий юзер
		// не погашен
		
		$couponUserId = false;
		$arCoupon = false;
		$arDiscount = false;
		$isCoupon = false;
		
		$isThisUser = false;
		$isActive = false;
		$isActiveDate = false;
		$isHasDiscount = false;
		
		$arCoupon = \Bitrix\Sale\DiscountCouponsManager::getData($coupon); //coupon - номер купона
		if($arCoupon) $isCoupon = true;
		
		$couponUserId = \Bitrix\Sale\Internals\DiscountCouponTable::getList(array(
			'select' => ["USER_ID"], 
			'filter' => ["COUPON" => $coupon,]
		))->fetch();
		if($couponUserId["USER_ID"]) $couponUserId = $couponUserId["USER_ID"];

		if($couponUserId == $this->userId){
			$isThisUser = true;
			
			if(
				$arCoupon["ACTIVE"] == "Y" && 
				$arCoupon["SAVED"] == "N" 
				&& $arCoupon["DISCOUNT_ACTIVE"] == "Y"  
			){
				$isActive = true;
			}
			
			if($arCoupon["DISCOUNT_ACTIVE_FROM"]){
				$dtFrom = $arCoupon["DISCOUNT_ACTIVE_FROM"]->getTimestamp();
			}else{
				$dtFrom = time();
			}
			
			if($arCoupon["DISCOUNT_ACTIVE_TO"]){
				$dtTo = $arCoupon["DISCOUNT_ACTIVE_TO"]->getTimestamp();
			}else{
				$dtTo = time();
				$dtTo = $dtFrom + self::TIME_FOR_ACTIVEDATE_COUPON;
			}
			
			$curDate = time();
			if($curDate >= $dtFrom && $curDate <= $dtTo){
				$isActiveDate = true;
				
				if($forseOneHour){
					$isActiveDate = false;
					$dtTo = $dtFrom + self::TIME_FOR_NEW_COUPON;
					if($curDate >= $dtFrom && $curDate <= $dtTo){
						$isActiveDate = true;
					}
				}
			}
		}
		
		$res = [
		"couponUserId" => $couponUserId,
		"userId" => $this->userId,
		
			"isCoupon" => $isCoupon,
			"isThisUser" => $isThisUser,
			"isActive" => $isActive,
			"isActiveDate" => $isActiveDate,
			
			"arCoupon" => $arCoupon,
			"result" => $isCoupon && $isThisUser && $isActive && $isActiveDate,
			
			"dtFrom" => $dtFrom,
			"dtTo" => $dtTo,
		];
		return $res;
	}
}
