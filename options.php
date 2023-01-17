<?
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Ewp\Api\Main;
use Bitrix\Main\Config\Option;

Loc::loadMessages(Application::getDocumentRoot().BX_ROOT.'/modules/main/options.php');
Loc::loadMessages(__FILE__);

$module_id = Main::getModuleId();

Loader::includeModule($module_id);
Loader::includeModule('iblock');

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$moduleRight = $APPLICATION->GetGroupRight($module_id);
if($moduleRight <= 'D') $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$moduleRightR = $moduleRight >= 'R';
$moduleRightW = $moduleRight >= 'W';

if (!$moduleRightR && !$moduleRightW) return;

$aTabs = array(
	array(
		'DIV' => $module_id.'_rights',
		'TAB' => Loc::getMessage('TAB_RIGHTS_TITLE'),
		'ICON' => 'main_settings',
		'TITLE' => Loc::getMessage('TAB_RIGHTS_TITLE_COMMON')
	),
);

function showOption($arOption)
{
	$val = (Option::get(Main::getModuleId(), $arOption['ID']));
	?>
	<div class="option">
		<h4 class="option-title">
			<label for="<?=htmlspecialcharsbx($arOption['ID'])?>"><?=$arOption['NAME']?></label>
		</h4>
		<div class="option-input-container">
			<?
				switch ($arOption['TYPE']) {
					case 'checkbox':
						?><input class="<?=$arOption['CLASS']?>" type="checkbox" name="<?=htmlspecialcharsbx($arOption['ID'])?>" id="<?=htmlspecialcharsbx($arOption['ID'])?>" value="Y"<?=($val=="Y") ? ' checked' : ''?>><?
						break;
					
					case 'text':
						?><input class="<?=$arOption['CLASS']?>" type="text" maxlength="255" name="<?=htmlspecialcharsbx($arOption['ID'])?>" id="<?=htmlspecialcharsbx($arOption['ID'])?>" value="<?=htmlspecialcharsbx($val)?>"><?
						break;
					
					case 'textarea':
						?><textarea class="<?=$arOption['CLASS']?>" rows="<?=$arOption['ROWS']?>" cols="<?=$arOption['COLS']?>" name="<?=htmlspecialcharsbx($arOption["ID"])?>" id="<?=htmlspecialcharsbx($arOption['ID'])?>"><?=htmlspecialcharsbx($val)?></textarea><?
						break;

					case 'selectbox':
						?><select class="<?=$arOption['CLASS']?>" name="<?=htmlspecialcharsbx($arOption['ID'])?>">
						<?foreach ($arOption['VALUES'] as $key => $value):?>
							<option value="<?=$key?>"<?=($val==$key) ? ' selected' : ''?>><?=htmlspecialcharsbx($value)?></option>
						<?endforeach?>
						</select><?
						break;
					
					default:
						break;
				}
		?>
		</div>
	</div>
	<?
}

$save = $request->getPost('save');

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults.$save) > 0 && $moduleRightW && check_bitrix_sessid())
{
	if(strlen($RestoreDefaults) > 0)
	{
		Option::delete($module_id);
	}
	else
	{
		foreach($arSettingsOptions as $arOption)
		{
			$val = $request->getPost($arOption['ID']);

			if($arOption['TYPE'] == "checkbox" && $val != "Y") $val="N";

			Option::set($module_id, $arOption['ID'], $val, $arOption['NAME']);
		}
	}

	ob_start();
	$Update = $Update.$Apply.$save;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	ob_end_clean();

	if (!$save)
	{
		if(strlen($request->get('back_url_settings')))
		{
			if((strlen($Apply) > 0) || (strlen($RestoreDefaults) > 0))
				LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($request->get('back_url_settings'))."&".$tabControl->ActiveTabParam());
			else
				LocalRedirect($request->get('back_url_settings'));
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
		}
	}
}
?>

<form class="form" method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
		<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php")?>

	<?$tabControl->Buttons()?>
		<input <?=(!$moduleRightW) ? 'disabled' : ''?> type="submit" name="Update" value="<?=Loc::getMessage("OPTIONS_SAVE")?>" title="<?=Loc::getMessage("OPTIONS_SAVE")?>" class="adm-btn-save">
		<input <?=(!$moduleRightW) ? 'disabled' : ''?> type="submit" name="RestoreDefaults" title="<?=Loc::getMessage("OPTIONS_RESTORE")?>" onclick="return confirm('<?=AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?=Loc::getMessage("OPTIONS_RESTORE")?>">
		<?=bitrix_sessid_post()?>
	<?$tabControl->End()?>
</form>