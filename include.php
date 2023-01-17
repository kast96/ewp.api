<?
CModule::AddAutoloadClasses(
	'ewp.api',
	array(
		'Ewp\\Api\\Main' => 'lib/main.php',
		'Ewp\\Api\\Token\\Base' => 'lib/token/base.php',
		'Ewp\\Api\\Token\\JWT' => 'lib/token/jwt.php',
		'Ewp\\Api\\Events\\EventHandlers' => 'lib/events/eventHandlers.php',
		'Ewp\\Api\\Events\\Functions' => 'lib/events/functions.php',
		'Ewp\\Api\\V1\\Pagination' => 'lib/v1/pagination.php',
		'Ewp\\Api\\V1\\Controllers\\UsersController' => 'lib/v1/controllers/usersController.php',
		'Ewp\\Api\\V1\\Controllers\\ProductsController' => 'lib/v1/controllers/productsController.php',
		'Ewp\\Api\\V1\\ActionFilter\\AuthenticationToken' => 'lib/v1/actionfilter/authenticationToken.php',
	)
);
?>