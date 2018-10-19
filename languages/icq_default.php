<?php
/**
* Default language file for ICQ module
*
*/
$dictionary=array(
	/* general */
	'ABOUT' => 'About',
	'ICQ_HELP' => 'Help',
	'ICQ_TOKEN'=>'Token bot',
	'ICQ_STORAGE_PATH'=>'Path download storage',
	'ICQ_ADMIN'=>'Administrator',
	'ICQ_HISTORY'=>'History',
	'ICQ_HISTORY_LEVEL'=>'History level',
	'ICQ_COMMANDS'=>'Commands',
	'ICQ_COMMAND'=>'Command',
	'ICQ_PATTERNS'=>'Patterns',
	'ICQ_DOWNLOAD'=>'Download',
	'ICQ_PLAY_VOICE'=>'Play',
	'ICQ_DISABLE'=>'Disable',
	'ICQ_ONLY_ADMIN'=>'Only administrators',
	'ICQ_ALL'=>'All',
	'ICQ_ALL_NO_LIMIT' => 'All (no limit)',
	'ICQ_SHOW_COMMAND'=>'Show command',
	'ICQ_SHOW'=>'Show',
	'ICQ_HIDE'=>'Hide',
	'ICQ_CONDITION'=>'Condition',
	'ICQ_EVENTS'=>'Events',
	'ICQ_EVENT'=>'Event',
	'ICQ_ENABLE'=>'Enable',
	'ICQ_EVENT_TEXT'=>'Text message',
	'ICQ_EVENT_IMAGE'=>'Image',
	'ICQ_EVENT_VOICE'=>'Voice',
	'ICQ_EVENT_AUDIO'=>'Audio',
	'ICQ_EVENT_VIDEO'=>'Video',
	'ICQ_EVENT_DOCUMENT'=>'Document',
	'ICQ_EVENT_STICKER'=>'Sticker',
	'ICQ_EVENT_LOCATION'=>'Location',
	'ICQ_COUNT_ROW'=>'Count commands on row',
	'ICQ_TIMEOUT'=>'Timeout long polling (sec)',
	'ICQ_UPDATE_USER_INFO'=>'Update user info',
	'ICQ_PATH_CERT'=>'Path to certificate',
	/* about */

	/* help */
	'HELP_TOKEN'=>'Token bot from megabot -> \'001.3395960000.3147852325:745195177\'',
	'HELP_STORAGE'=>'Path storage to save files from user',
	'HELP_TIMEOUT'=>'Timeout cycle in ms',
	'HELP_USERID'=>'ICQ User ID',
	'HELP_NAME'=>'Name user',
	'HELP_MEMBER'=>'Link to system user',
	'HELP_ADMIN'=>'Administrator',
	'HELP_HISTORY'=>'Send history to user',
	'HELP_HISTORY_LEVEL'=>'Level history to send(0 - send all history message)',
	'HELP_COMMANDS'=>'Process command from user',
	'HELP_PATTERNS'=>'Process patterns from user',
	'HELP_DOWNLOAD'=>'Download files to storage from user',
	'HELP_PLAY_VOICE'=>'Play voice from user',
	'HELP_TITLE'=>'Title command (view in keyboard icq client)',
	'HELP_DESCRIPTION'=>'Description command',
	'HELP_ACCESS_CONTROL'=>'Access control command',
	'HELP_COUNTROW'=>'Count commands on row'

	/* end module names */
);

foreach ($dictionary as $k=>$v) {
	if (!defined('LANG_'.$k)) {
		define('LANG_'.$k, $v);
	}
}

?>