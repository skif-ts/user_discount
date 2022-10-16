<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<div class="get_discount">

	<form action="<?=POST_FORM_ACTION_URI?>" method="POST">
		<?=bitrix_sessid_post()?>

		<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
		<input type="hidden" name="action" value="get_discount">
		<input class="submit_but" type="submit" name="submit" value="Получить скидку">
	</form>

	<div class="ok_text"></div>
	<div class="err_text"></div>

</div>
	
<br/><br/><br/><br/>
<div class="check_discount">
	<form action="<?=POST_FORM_ACTION_URI?>" method="POST">
		<?=bitrix_sessid_post()?>
		
		<input class="coupon" type="text" name="code" value="" required >
		<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
		<input type="hidden" name="action" value="check_discount">
		<input class="submit_but" type="submit" name="submit" value="Проверить код">
	</form>

	<div class="ok_text"></div>
	<div class="err_text"></div>
</form>
</div>