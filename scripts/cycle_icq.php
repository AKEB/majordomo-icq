<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'icq/icq.class.php');
echo date("H:i:s") . " Running " . basename(__FILE__) . PHP_EOL;
$icq_module = new icq();
echo date("H:i:s") . " Init module " . PHP_EOL;
$icq_module->init();
$latest_check=0;

echo date("H:i:s") . " Start processCycle " . PHP_EOL;
while (1) {
	setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
	$icq_module->processCycle();
	
	if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
		exit;
	}
	sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
 
?>