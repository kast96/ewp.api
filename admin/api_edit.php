<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Context;
use \Bitrix\Main\Config\Option;
use \Ewp\Api\Tables\ApiTable;
use \Ewp\Api\Main;

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

$request = Context::getCurrent()->getRequest();

Loc::loadMessages(__FILE__);

global $APPLICATION;

if (!Loader::includeSharewareModule('ewp.api'))
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('EWP_API_API_EDIT_REQUIRE_MODULE')
	]);
}
else
{
	$aTabs = [
		[
			'DIV' => 'ewp_api_edit_params',
			'TAB' => Loc::getMessage('EWP_API_API_EDIT_PARAMS_TAB_TITLE'),
			'ICON' => 'main_settings',
			'TITLE' => Loc::getMessage('EWP_API_API_EDIT_PARAMS_TAB_TITLE')
		],
	];
	$tabControl = new \CAdminTabControl('tabControl', $aTabs);

	$ID = intval($request->get('ID'));
	$message = null;

	//Обработка формы
	if ($REQUEST_METHOD == 'POST' && ($save || $apply) && check_bitrix_sessid())
	{
		$apiTable = new ApiTable();

		$arFields = [
			'ACTIVE' => $request->getPost('ACTIVE') == 'Y' ? 'Y' : 'N',
			'NAME' => $request->getPost('NAME'),
			'PATH' => $request->getPost('PATH'),
		];

		if($ID > 0)
		{
			$result = $apiTable->update($ID, $arFields);
		}
		else
		{
			$ID = $routeTable->add($arFields)->getid();
			$result = $ID > 0;
		}

		if($result)
		{
			if ($apply)
			{
				LocalRedirect('/bitrix/admin/ewp_api_edit.php?ID='.$ID.'&mess=ok&lang='.LANG.'&'.$tabControl->ActiveTabParam());
			}
			else
			{
				LocalRedirect('/bitrix/admin/ewp_api_list.php?lang='.LANG);
			}
		}
		else
		{
			if($e = $APPLICATION->GetException())
			{
				$message = new CAdminMessage(GetMessage('EWP_API_API_EDIT_SAVE_ERROR'), $e);
			}
		}
	}

	//Выборка данных
	if ($ID)
	{
		$arValues = ApiTable::getByid($ID)->fetch();
	}


	$APPLICATION->SetTitle(($ID > 0 ? Loc::getMessage('EWP_API_API_EDIT_TITLE_EDIT', ['#ID#' => $ID]) : Loc::getMessage('EWP_API_API_EDIT_TITLE_ADD')));
	
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

	$aMenu = [
		[
			'TEXT' => Loc::getMessage('EWP_API_API_EDIT_BTN_LIST'),
			'LINK' => 'ewp_api_list.php?lang='.LANG,
			'ICON' => 'btn_list',
		]
	];
	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	?>
	<form method="POST" action="<?=$APPLICATION->GetCurPage()?>" enctype="multipart/form-data" name="ewp_form">
		<?=bitrix_sessid_post()?>
		<?$tabControl->Begin()?>

		<?$tabControl->BeginNextTab()?>
		<tr>
			<?$value = $request->getPost('ACTIVE') ?: $arValues['ACTIVE']?>
			<td width="40%"><?=Loc::getMessage('EWP_API_API_EDIT_ACTIVE')?>:</td>
			<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?=$value == 'Y' ? ' checked' : ''?>></td>
		</tr>
		<tr>
			<?$value = $request->getPost('NAME') ?: $arValues['NAME']?>
			<td width="40%"><?=Loc::getMessage('EWP_API_API_EDIT_NAME')?>:</td>
			<td width="60%"><input type="text" name="NAME" value="<?=$value?>"></td>
		</tr>
		<tr>
			<?$value = $request->getPost('PATH') ?: $arValues['PATH']?>
			<td width="40%"><?=Loc::getMessage('EWP_API_API_EDIT_PATH')?>:</td>
			<td width="60%"><b><?=Option::get(Main::getModuleId(), 'API_PATH')?></b>&nbsp;<input type="text" name="PATH" value="<?=$value?>" placeholder="/v1"></td>
		</tr>

		<?$tabControl->Buttons([
			'back_url' => 'ewp_api_list.php?lang='.LANG,
		])?>
		<input type="hidden" name="lang" value="<?=LANG?>">
		<?if($ID > 0):?>
			<input type="hidden" name="ID" value="<?=$ID?>">
		<?endif?>
		<?$tabControl->End()?>
	</form>
	<?

	$tabControl->ShowWarnings('ewp_form', $message);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';