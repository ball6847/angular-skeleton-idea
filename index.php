<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APPNAME', 'MyAPP');
define('APPPATH', __DIR__.'/');
define('APPURL', str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']));
define('NODE_BIN', '/home/ball6847/.nvm/versions/node/v0.12.0/bin/node');
define('COFFEE_BIN', NODE_BIN . ' ' . APPPATH.'node_modules/coffee-script/bin/coffee');
define('COFFEE_COMPILED_DIR', APPPATH.'.compiled/coffee/');
define('COFFEE_COMPILED_URL', APPURL.'.compiled/coffee/');

// ---------------------------------------------------

function coffeeToJS($file) {
    $basename = preg_replace("~\.coffee$~", '', $file);
    $saveTo = COFFEE_COMPILED_DIR.$basename.'.js';

    $output = shell_exec(COFFEE_BIN. ' -p '. escapeshellarg(APPPATH.$file));

    if ( ! is_dir(dirname($saveTo)) AND ! @mkdir(dirname($saveTo), 0777, true)) {
        return false;
    }

    file_put_contents($saveTo, $output);

    return '<script src="'.COFFEE_COMPILED_URL.$basename.'.js"></script>'. PHP_EOL;
    
}

// ---------------------------------------------------





?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Angular Dependencies Idea</title>
</head>
<body ng-app="<?php echo APPNAME; ?>">

    <div ng-controller="AcmeController">
        Hello, {{ username }}
    </div>

<script src="<?php echo APPURL; ?>bower_components/angular/angular.min.js"></script>

<?php

$modules = [];

foreach ((array)glob("modules/*/module.json") as $moduleJsonFile) {
    $module = json_decode(file_get_contents($moduleJsonFile));

    if ( ! isset($module->require) OR ! is_array($module->require)) {
        $module->require = [];
    }

    // we may need to resovle module's dependencies here

    echo '<script>angular.module('.json_encode($module->name).', '.json_encode($module->require).');</script>'.PHP_EOL;

    $modules[] = $module->name;
}

$modules = array_unique($modules);

// ---------------------------------------------------
// controllers
//

foreach ((array)glob("modules/*/controllers.coffee") as $file) {
    echo coffeeToJS($file);
}

?>

<script>
var app = angular.module(<?php echo json_encode(APPNAME); ?>, <?php echo json_encode($modules); ?>);
</script>

</body>
</html>
