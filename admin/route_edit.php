<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Context;
use \Bitrix\Main\Config\Option;
use \Ewp\Api\Tables\ApiTable;
use \Ewp\Api\Tables\RouteTable;
use \Ewp\Api\Main;

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

$request = Context::getCurrent()->getRequest();
$action = $request->get('action');

Loc::loadMessages(__FILE__);

global $APPLICATION;

function getControllerMethod($controller, $value)
{
	if (!class_exists($controller)) return 'Controller Not Found';

	$controller = new $controller;
	$arControllerMethods = $controller->getActions();

	ob_start();
	?>
	<select name="CONTROLLER_METHOD">
		<?foreach ($arControllerMethods as $method):?>
			<option value="<?=$method?>" <?=$value == $method ? ' selected' : ''?>><?=$method?></option>
		<?endforeach?>
	</select>
	<?
	return ob_get_clean();
}

function getControllerParams($controller, $method, $arParamValues = [])
{
	if (!class_exists($controller)) return;

	if (!is_array($arParamValues)) $arParamValues = [];

	$controller = new $controller;
	$arParams = $controller->getParams();
	$arParams = is_array($arParams) ? $arParams[$method] : [];

	ob_start();
	?>
	<table class="adm-detail-content-table">
		<tbody>
			<?if($arParams):?>
				<tr>
					<?foreach($arParams as $code => $arParam):?>
						<td width="40%" class="adm-detail-content-cell-l"><?=$arParam['NAME']?>:</td>
						<td width="60%" class="adm-detail-content-cell-r">
							<?
							switch ($arParam['TYPE']) {
								case 'select':
									?>
									<select name="PARAMS[<?=$code?>]">
										<?foreach($arParam['VALUES'] as $valueCode => $value):?>
											<option value="<?=$valueCode?>"<?=$arParamValues[$code] == $valueCode ? ' selected' : ''?>><?=$value?></option>
										<?endforeach?>
									</select>
									<?
									break;
								
								default:
									break;
							}
							?>
						</td>
					<?endforeach?>
				</tr>
			<?endif?>
		</tbody>
	</table>
	<?
	return ob_get_clean();
}

if (!Loader::includeSharewareModule('ewp.api'))
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('EWP_API_ROUTE_EDIT_REQUIRE_MODULE')
	]);
}
else if ($action == 'getControllerMethod')
{
	$controller = $request->get('controller');
	$controllerMethod = $request->get('controllerMethod');
	$APPLICATION->RestartBuffer();
	?>
	<?=getControllerMethod($controller, $controllerMethod)?>
	<?
	return;
}
else if ($action == 'getControllerParams')
{
	$controller = $request->get('controller');
	$controllerMethod = $request->get('controllerMethod');
	$APPLICATION->RestartBuffer();
	?>
	<?=getControllerParams($controller, $controllerMethod)?>
	<?
	return;
}
else
{
	$API_ID = (int)$request->get('API_ID');
	$arApi = ApiTable::getList(['filter' => ['ID' => $API_ID], 'select' => ['ID', 'NAME', 'PATH']])->fetch();

	if (!$arApi['ID'])
	{
		\CAdminMessage::ShowMessage([
			'TYPE' => 'ERROR',
			'MESSAGE' => Loc::getMessage('EWP_API_ROUTE_EDIT_REQUIRE_API_ID')
		]);
	}
	else
	{
		$arControllers = Main::getControllers();

		$aTabs = [
			[
				'DIV' => 'ewp_api_edit_params',
				'TAB' => Loc::getMessage('EWP_API_ROUTE_EDIT_PARAMS_TAB_TITLE'),
				'ICON' => 'main_settings',
				'TITLE' => Loc::getMessage('EWP_API_ROUTE_EDIT_PARAMS_TAB_TITLE')
			],
		];
		$tabControl = new \CAdminTabControl('tabControl', $aTabs);
		$arMethods = ['GET', 'POST'];

		$ID = intval($request->get('ID'));
		$message = null;

		//Обработка формы
		if ($REQUEST_METHOD == 'POST' && ($save || $apply) && check_bitrix_sessid())
		{
			$routeTable = new RouteTable();

			$arFields = [
				'API_ID' => $request->getPost('API_ID'),
				'ACTIVE' => $request->getPost('ACTIVE') == 'Y' ? 'Y' : 'N',
				'NAME' => $request->getPost('NAME'),
				'PATH' => $request->getPost('PATH'),
				'METHOD' => serialize($request->getPost('METHOD')),
				'CONTROLLER' => $request->getPost('CONTROLLER'),
				'CONTROLLER_METHOD' => $request->getPost('CONTROLLER_METHOD'),
				'PARAMS' => serialize($request->getPost('PARAMS')),
			];

			if($ID > 0)
			{
				$result = $routeTable->update($ID, $arFields);
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
					LocalRedirect('/bitrix/admin/ewp_api_route_edit.php?API_ID='.$arApi['ID'].'&ID='.$ID.'&mess=ok&lang='.LANG.'&'.$tabControl->ActiveTabParam());
				}
				else
				{
					LocalRedirect('/bitrix/admin/ewp_api_list.php?API_ID='.$arApi['ID'].'&lang='.LANG);
				}
			}
			else
			{
				if($e = $APPLICATION->GetException())
				{
					$message = new CAdminMessage(GetMessage('EWP_API_ROUTE_EDIT_SAVE_ERROR'), $e);
				}
			}
		}

		//Выборка данных
		if ($ID)
		{
			$arValues = RouteTable::getByid($ID)->fetch();
		}


		$APPLICATION->SetTitle(($ID > 0 ? Loc::getMessage('EWP_API_ROUTE_EDIT_TITLE_EDIT', ['#ID#' => $ID]) : Loc::getMessage('EWP_API_ROUTE_EDIT_TITLE_ADD')));
		
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

		$aMenu = [
			[
				'TEXT' => Loc::getMessage('EWP_API_ROUTE_EDIT_BTN_LIST', ['#API_NAME#' => $arApi['NAME']]),
				'LINK' => 'ewp_api_route_list.php?API_ID='.$arApi['ID'].'&lang='.LANG,
				'ICON' => 'btn_list',
			]
		];
		$context = new CAdminContextMenu($aMenu);
		$context->Show();

		?>
		<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?API_ID=<?=$arApi['ID']?>" enctype="multipart/form-data" name="ewp_form">
			<?=bitrix_sessid_post()?>
			<?$tabControl->Begin()?>

			<?$tabControl->BeginNextTab()?>
			<tr>
				<?$value = $request->getPost('ACTIVE') ?: $arValues['ACTIVE']?>
				<td width="40%"><?=Loc::getMessage('EWP_API_ROUTE_EDIT_ACTIVE')?>:</td>
				<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?=$value == 'Y' ? ' checked' : ''?>></td>
			</tr>
			<tr>
				<?$value = $request->getPost('NAME') ?: $arValues['NAME']?>
				<td width="40%"><?=Loc::getMessage('EWP_API_ROUTE_EDIT_NAME')?>:</td>
				<td width="60%"><input type="text" name="NAME" value="<?=$value?>"></td>
			</tr>
			<tr>
				<?$value = $request->getPost('PATH') ?: $arValues['PATH']?>
				<td width="40%"><?=Loc::getMessage('EWP_API_ROUTE_EDIT_PATH')?>:</td>
				<td width="60%"><b><?=Option::get(Main::getModuleId(), 'API_PATH')?><?=$arApi['PATH']?></b>&nbsp;<input type="text" name="PATH" value="<?=$value?>"></td>
			</tr>
			<tr>
				<?$value = $request->getPost('METHOD') ?: unserialize($arValues['METHOD'])?>
				<td width="40%"><?=Loc::getMessage('EWP_API_ROUTE_EDIT_METHOD')?>:</td>
				<td width="60%">
					<select name="METHOD[]" multiple>
						<?foreach ($arMethods as $method):?>
							<option value="<?=$method?>" <?=$value && in_array($method, $value) ? ' selected' : ''?>><?=$method?></option>
						<?endforeach?>
					</select>
				</td>
			</tr>
			<tr>
				<?$controllerValue = $request->getPost('CONTROLLER') ?: $arValues['CONTROLLER']?>
				<td width="40%"><?=Loc::getMessage('EWP_API_ROUTE_EDIT_CONTROLLER')?>:</td>
				<td width="60%">
					<select class="js-controller" name="CONTROLLER">
						<?foreach ($arControllers as $arController):?>
							<option value="<?=$arController['CLASS']?>" <?=$controllerValue == $arController['CLASS'] ? ' selected' : ''?>><?=$arController['CLASS']?></option>
						<?endforeach?>
					</select>
				</td>
			</tr>
			<tr>
				<?$controllerMethodValue = $request->getPost('CONTROLLER_METHOD') ?: $arValues['CONTROLLER_METHOD'];?>
				<td width="40%"><?=Loc::getMessage('EWP_API_ROUTE_EDIT_CONTROLLER_METHOD')?>:</td>
				<td width="60%">
					<div class="js-controller-method">
						<?=getControllerMethod($controllerValue, $controllerMethodValue)?>
					</div>
				</td>
			</tr>
			<tr>
				<?$value = $request->getPost('PARAMS') ?: unserialize($arValues['PARAMS'])?>
				<td class="js-controller-params" colspan="2">
					<?=getControllerParams($controllerValue, $controllerMethodValue, $value)?>
				</td>
			</tr>

			<?$tabControl->Buttons([
				'back_url' => 'ewp_api_list.php?API_ID='.$arApi['ID'].'&lang='.LANG,
			])?>
			<input type="hidden" name="lang" value="<?=LANG?>">
			<?if($ID > 0):?>
				<input type="hidden" name="ID" value="<?=$ID?>">
			<?endif?>
			<?if($arApi['ID'] > 0):?>
				<input type="hidden" name="API_ID" value="<?=$arApi['ID']?>">
			<?endif?>
			<?$tabControl->End()?>
		</form>
		<?

		$tabControl->ShowWarnings('ewp_form', $message);
		?>
		<script>
			var controller = document.querySelector('.js-controller');
			var controllerMethod = document.querySelector('.js-controller-method');
			var controllerParams = document.querySelector('.js-controller-params');

			function getControllerMethod() {
				var select = controllerMethod.querySelector('select');

				var xhr = new XMLHttpRequest();
				xhr.open('GET', window.location.pathname+'?action=getControllerMethod&controller='+controller.value+'&controllerMethod='+(select ? select.value : ''));

				xhr.onreadystatechange = function() {
					if (xhr.readyState !== 4 || xhr.status !== 200) {
						return;
					}
					const response = xhr.response;
					controllerMethod.innerHTML = response;

					initControllerMethodChange();

					const e = new Event('change');
					controllerMethod.querySelector('select').dispatchEvent(e);
				}

				xhr.send();
			}

			function getControllerPrarms() {
				var select = controllerMethod.querySelector('select');

				var xhr = new XMLHttpRequest();
				xhr.open('GET', window.location.pathname+'?action=getControllerParams&controller='+controller.value+'&controllerMethod='+(select ? select.value : ''));

				xhr.onreadystatechange = function() {
					if (xhr.readyState !== 4 || xhr.status !== 200) {
						return;
					}
					const response = xhr.response;
					controllerParams.innerHTML = response;
				}

				xhr.send();
			}
			
			function initControllerChange() {
				controller.addEventListener('change', function() {
					getControllerMethod();
				});
			}

			function initControllerMethodChange() {
				var select = controllerMethod.querySelector('select');
				if (!select) return;
				select.addEventListener('change', function() {
					getControllerPrarms();
				});
			}

			initControllerChange();
			initControllerMethodChange();

			<?if(!$controllerMethodValue):?>
				getControllerMethod();
			<?endif?>
		</script>
		<?
	}
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';