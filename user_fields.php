<?
/**
 * Добавление пользовательского свойства
 */
$oUserTypeEntity    = new CUserTypeEntity();
 
$aUserFields    = array(
	'ENTITY_ID'         => 'USER',
	'FIELD_NAME'        => 'UF_COUPON',
	'USER_TYPE_ID'      => 'string',
	'SORT'              => 500,
	'MULTIPLE'          => 'N',
	'MANDATORY'         => 'N',
    'SHOW_FILTER'       => 'N',
    'SHOW_IN_LIST'      => '1',
    'EDIT_IN_LIST'      => '',
    'IS_SEARCHABLE'     => 'N',
    'SETTINGS'          => array(
        /* Значение по умолчанию */
        'DEFAULT_VALUE' => '',
        /* Размер поля ввода для отображения */
        'SIZE'          => '20',
		/* Количество строчек поля ввода */
        'ROWS'          => '1',
        /* Минимальная длина строки (0 - не проверять) */
        'MIN_LENGTH'    => '0',
		/* Максимальная длина строки (0 - не проверять) */
        'MAX_LENGTH'    => '0',
        /* Регулярное выражение для проверки */
        'REGEXP'        => '',
	),
	/* Подпись в форме редактирования */
    'EDIT_FORM_LABEL'   => array(
        'ru'    => 'Последний купон',
        'en'    => 'Coupon',
    ),
);
$iUserFieldId   = $oUserTypeEntity->Add( $aUserFields ); // int


$aUserFields    = array(
	'ENTITY_ID'         => 'USER',
	'FIELD_NAME'        => 'UF_COUPON_ACTIVE_TO',
	'USER_TYPE_ID'      => 'datetime',
	'SORT'              => 500,
	'MULTIPLE'          => 'N',
	'MANDATORY'         => 'N',
    'SHOW_FILTER'       => 'N',
    'SHOW_IN_LIST'      => '1',
    'EDIT_IN_LIST'      => '',
    'IS_SEARCHABLE'     => 'N',
    'SETTINGS'          => array(
	),
	/* Подпись в форме редактирования */
    'EDIT_FORM_LABEL'   => array(
        'ru'    => 'Купон активен до',
        'en'    => 'Coupon active to',
    ),
);
$iUserFieldId   = $oUserTypeEntity->Add( $aUserFields ); // int

