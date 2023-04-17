<?
CModule::AddAutoloadClasses(
	'ewp.api',
	array(
		'Ewp\\Api\\Main' => 'lib/main.php',
		'Ewp\\Api\\Tables\\ApiTable' => 'lib/tables/api.php',
		'Ewp\\Api\\Token\\Base' => 'lib/token/base.php',
		'Ewp\\Api\\Token\\JWT' => 'lib/token/jwt.php',
		'Ewp\\Api\\Events\\EventHandlers' => 'lib/events/eventHandlers.php',
		'Ewp\\Api\\Events\\Functions' => 'lib/events/functions.php',
		'Ewp\\Api\\Pagination' => 'lib/api/pagination.php',
		'Ewp\\Api\\Controller\\BaseController' => 'lib/api/controller/base/baseController.php',
		'Ewp\\Api\\Controller\\UsersController' => 'lib/api/controller/usersController.php',
		'Ewp\\Api\\Controller\\IblockController' => 'lib/api/controller/iblockController.php',
		'Ewp\\Api\\ActionFilter\\AuthenticationToken' => 'lib/api/actionfilter/authenticationToken.php',
	)
);
?>