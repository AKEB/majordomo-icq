<?php
/**
* Russian language file for ICQ module
*
*/
$dictionary=array(
	/* general */
	'ABOUT' => 'О модуле',
	'ICQ_HELP' => 'Помощь',
	'ICQ_TOKEN'=>'Токен бота',
	'ICQ_STORAGE_PATH'=>'Путь к хранилищу',
	'ICQ_ADMIN'=>'Администратор',
	'ICQ_HISTORY'=>'История',
	'ICQ_HISTORY_LEVEL'=>'Приоритет истории',
	'ICQ_COMMANDS'=>'Команды',
	'ICQ_COMMAND'=>'Команда',
	'ICQ_PATTERNS'=>'Шаблоны',
	'ICQ_DOWNLOAD'=>'Загрузка',
	'ICQ_PLAY_VOICE'=>'Играть голос',
	'ICQ_DISABLE'=>'Запретить',
	'ICQ_ONLY_ADMIN'=>'Только для администраторов',
	'ICQ_ALL'=>'Для всех',
	'ICQ_ALL_NO_LIMIT' => 'Для всех (без ограничений)',
	'ICQ_SHOW_COMMAND'=>'Отображение команды',
	'ICQ_SHOW'=>'Показать',
	'ICQ_HIDE'=>'Скрыть',
	'ICQ_CONDITION'=>'Условие',
	'ICQ_EVENTS'=>'События',
	'ICQ_EVENT'=>'Событие',
	'ICQ_ENABLE'=>'Включить',
	'ICQ_EVENT_TEXT'=>'Текстовое сообщение',
	'ICQ_EVENT_IMAGE'=>'Изображение',
	'ICQ_EVENT_VOICE'=>'Голосовое сообщение',
	'ICQ_EVENT_AUDIO'=>'Аудио',
	'ICQ_EVENT_VIDEO'=>'Видео',
	'ICQ_EVENT_DOCUMENT'=>'Документ',
	'ICQ_EVENT_STICKER'=>'Стикер',
	'ICQ_EVENT_LOCATION'=>'Местоположение',
	'ICQ_COUNT_ROW'=>'Команд в строке',
	'ICQ_TIMEOUT'=>'Период long polling (сек)',
	'ICQ_UPDATE_USER_INFO'=>'Обновить информацию пользователей',
	'ICQ_PATH_CERT'=>'Путь к сертификату',
	/* about */

	/* help */
	'HELP_TOKEN'=>'Токен бота полученного от megabot вида \'001.3395960000.3147852325:745195177\'',
	'HELP_STORAGE'=>'Путь для сохранения файлов полученных от пользователя',
	'HELP_TIMEOUT'=>'Период ожидания новых сообщений в секундах',
	'HELP_USERID'=>'ICQ User ID',
	'HELP_NAME'=>'Имя пользователя',
	'HELP_MEMBER'=>'Связь с пользователем системы',
	'HELP_ADMIN'=>'Администратор',
	'HELP_HISTORY'=>'Отправка системной истории пользователю',
	'HELP_HISTORY_LEVEL'=>'Уровень важности для отправки (0 - отправка всей истории)',
	'HELP_COMMANDS'=>'Обработка команд полученных от пользователя',
	'HELP_PATTERNS'=>'Обработка сообщения пользователя как шаблона поведения',
	'HELP_DOWNLOAD'=>'Сохранение файлов отправляемых пользователем',
	'HELP_PLAY_VOICE'=>'Проигрывать голосовые сообщения от пользователя',
	'HELP_TITLE'=>'Имя команды (отображается на клавиатуре в ICQ клиенте)',
	'HELP_DESCRIPTION'=>'Описание команды',
	'HELP_ACCESS_CONTROL'=>'Ограничение доступа к команде',
	'HELP_COUNTROW'=>'Количество кнопок команд в одной строке на клавиатуре в ICQ клиенте'

	/* end module names */
);

foreach ($dictionary as $k=>$v) {
	if (!defined('LANG_'.$k)) {
		define('LANG_'.$k, $v);
	}
}

?>