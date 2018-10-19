<?php
/**
 * ICQ Bot 
 * @package project
 * @author Vadim Babajanyan <akeb@akeb.ru>
 * @copyright (c)
 */
//
//
class icq extends module {
	private $last_update_id=0;
	private $fetchUrl = '';

	private $baseURL = 'https://botapi.icq.net/';

	function __construct() {
		$this->name = "icq";
		$this->title = "ICQ";
		$this->module_category = "<#LANG_SECTION_APPLICATIONS#>";
		$this->checkInstalled();
		
		$this->getConfig();
	}
	/**
	* saveParams
	*
	* Saving module parameters
	*
	* @access public
	*/
	function saveParams($data=0) {
		$p = array();
		if(IsSet($this->id)) {
			$p["id"] = $this->id;
		}
		if(IsSet($this->view_mode)) {
			$p["view_mode"] = $this->view_mode;
		}
		if(IsSet($this->edit_mode)) {
			$p["edit_mode"] = $this->edit_mode;
		}
		if(IsSet($this->tab)) {
			$p["tab"] = $this->tab;
		}
		return parent::saveParams($p);
	}
	/**
	* getParams
	*
	* Getting module parameters from query string
	*
	* @access public
	*/
	function getParams() {
		global $id;
		global $mode;
		global $view_mode;
		global $edit_mode;
		global $tab;
		if(isset($id)) {
			$this->id = $id;
		}
		if(isset($mode)) {
			$this->mode = $mode;
		}
		if(isset($view_mode)) {
			$this->view_mode = $view_mode;
		}
		if(isset($edit_mode)) {
			$this->edit_mode = $edit_mode;
		}
		if(isset($tab)) {
			$this->tab = $tab;
		}
	}
	/**
	* Run
	*
	* Description
	*
	* @access public
	*/
	function run() {
		global $session;
		$out = array();
		if($this->action == 'admin') {
			$this->admin($out);
		} else {
			$this->usual($out);
		}
		if(IsSet($this->owner->action)) {
			$out['PARENT_ACTION'] = $this->owner->action;
		}
		if(IsSet($this->owner->name)) {
			$out['PARENT_NAME'] = $this->owner->name;
		}
		$out['VIEW_MODE'] = $this->view_mode;
		$out['EDIT_MODE'] = $this->edit_mode;
		$out['MODE'] = $this->mode;
		$out['ACTION'] = $this->action;
		$out['DATA_SOURCE'] = $this->data_source;
		$out['TAB'] = $this->tab;
		if($this->single_rec) {
			$out['SINGLE_REC'] = 1;
		}
		$this->data = $out;
		$p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
		$this->result = $p->result;
	}
	
	function debug($content) {
		if($this->config['ICQ_DEBUG']) $this->log(print_r($content,true));
	}
	
	function log($message) {
		//echo $message . "\n";
		// DEBUG MESSAGE LOG
		if(!is_dir(ROOT . 'cms/debmes')) {
			mkdir(ROOT . 'cms/debmes', 0777);
		}
		$today_file = ROOT . 'cms/debmes/log_' . date('Y-m-d') . '-icq.php.txt';
		$data = date("H:i:s")." " . $message . "\n";
		file_put_contents($today_file, $data, FILE_APPEND | LOCK_EX);
	}
	/**
	* BackEnd
	*
	* Module backend
	*
	* @access public
	*/
	function admin(&$out) {
		$this->getConfig();
		
		if ((time() - intval(gg('cycle_icqRun'))) < $this->config['ICQ_TIMEOUT'] * 2 ) {
			$out['CYCLERUN'] = 1;
		} else {
			$out['CYCLERUN'] = 0;
		}
		
		global $getlogicq;
		global $filter;
		global $limit;
		global $atype;
		if($getlogicq) {
			
			header("HTTP/1.0: 200 OK\n");
			header('Content-Type: text/html; charset=utf-8');
			//$limit = 50;
			// Find last midifed
			$filename = ROOT . 'cms/debmes/log_*-icq.php.txt';
			foreach(glob($filename) as $file) {
				$LastModified[] = filemtime($file);
				$FileName[] = $file;
			}
			
			$files = array_multisort($LastModified, SORT_NUMERIC, SORT_ASC, $FileName);
			$lastIndex = count($LastModified) - 1;
			// Open file
			$data = LoadFile($FileName[$lastIndex]);
			$lines = explode("\n", $data);
			$lines = array_reverse($lines);
			$res_lines = array();
			$total = count($lines);
			$added = 0;
			for($i = 0; $i < $total; $i++) {
				if(trim($lines[$i]) == '') continue;
				if($filter && preg_match('/' . preg_quote($filter) . '/is', $lines[$i])) {
					$res_lines[] = $lines[$i];
					$added++;
				} elseif(!$filter) {
					$res_lines[] = $lines[$i];
					$added++;
				}
				if($added >= $limit) break;
			}
			echo implode("<br/>", $res_lines);
			exit;
		}

		global $sendMessage;
		if ($sendMessage) {
			header("HTTP/1.0: 200 OK\n");
			header('Content-Type: text/html; charset=utf-8');
			global $user;
			global $text;
			$res = $this->sendMessageToUser($user,$text);
			echo "Ok";
			exit;
		}
		$out['ICQ_TOKEN'] = $this->config['ICQ_TOKEN'];
		$out['ICQ_STORAGE'] = $this->config['ICQ_STORAGE'];
		$out['ICQ_COUNT_ROW'] = $this->config['ICQ_COUNT_ROW'];
		$out['ICQ_TIMEOUT'] = $this->config['ICQ_TIMEOUT'];
		if(!$out['ICQ_COUNT_ROW']) $out['ICQ_COUNT_ROW'] = 3;
		if(!$out['ICQ_TIMEOUT']) $out['ICQ_TIMEOUT'] = 30;
		if($out['ICQ_TIMEOUT']>600) $out['ICQ_TIMEOUT'] = 30;
		$out['ICQ_DEBUG'] = $this->config['ICQ_DEBUG'];
		$out['ICQ_test'] = $this->data_source . "_" . $this->view_mode . "_" . $this->tab;
		
		if($this->data_source == 'icq' || $this->data_source == '') {
			if($this->view_mode == 'update_settings') {
				global $icq_token;
				$this->config['ICQ_TOKEN'] = $icq_token;
				global $icq_storage;
				$this->config['ICQ_STORAGE'] = $icq_storage;
				global $icq_count_row;
				$this->config['ICQ_COUNT_ROW'] = $icq_count_row;
				global $icq_timeout;
				$this->config['ICQ_TIMEOUT'] = $icq_timeout;
				if($this->config['ICQ_TIMEOUT']>600) $this->config['ICQ_TIMEOUT'] = 30;
				global $icq_debug;
				$this->config['ICQ_DEBUG'] = $icq_debug;
				$this->saveConfig();
				$this->log("Save config");
				setGlobal('cycle_icq','restart');
				$this->log("Init cycle restart");
				$this->redirect("?tab=".$this->tab);
			}
			if($this->view_mode == 'user_edit') {
				$this->edit_user($out, $this->id);
			}
			if($this->view_mode == 'cmd_edit') {
				$this->edit_cmd($out, $this->id);
			}
			if($this->view_mode == 'event_edit') {
				$this->edit_event($out, $this->id);
			}
			if($this->view_mode == 'user_delete') {
				$this->delete_user($this->id);
				$this->redirect("?");
			}
			if($this->view_mode == 'cmd_delete') {
				$this->delete_cmd($this->id);
				$this->redirect("?tab=cmd");
			}
			if($this->view_mode == 'event_delete') {
				$this->delete_event($this->id);
				$this->redirect("?tab=events");
			}
			if ($this->view_mode=='export_command') {
				$this->export_command($out, $this->id);
			}
			if ($this->view_mode=='import_command') {
				$this->import_command($out);
			}
			if ($this->view_mode=='export_event') {
				$this->export_event($out, $this->id);
			}
			if ($this->view_mode=='import_event') {
				$this->import_event($out);
			}
			if($this->view_mode == '' || $this->view_mode == 'search_ms') {
				if($this->tab == 'cmd') {
					$this->icq_cmd($out);
				} else if($this->tab == 'events') {
					$this->icq_events($out);
				} else if($this->tab == 'log') {
					$this->icq_log($out);
				} else {
					$this->icq_users($out);
				}
			}
		}
		global $update_user_info;
		if ($update_user_info) {
			$this->log("Update user info");
			$users = $this->getUsers("");
			foreach($users as $user) {
				$this->updateInfo($user);
			}
			$this->redirect("?");
		}
	}
	/**
	* Edit/add
	*
	* @access public
	*/
	function edit_user(&$out, $id) {
		require(DIR_MODULES . $this->name . '/user_edit.inc.php');
	}
	function edit_cmd(&$out, $id) {
		require(DIR_MODULES . $this->name . '/cmd_edit.inc.php');
	}
	function edit_event(&$out, $id) {
		require(DIR_MODULES . $this->name . '/event_edit.inc.php');
	}
	
	/**
	* Export/import
	*
	* @access public
	*/
	function removeBOM($data) {
		if (0 === strpos(bin2hex($data), 'efbbbf')) {
			return substr($data, 3);
		}
	}

	function export_command(&$out, $id) {
		$command=SQLSelectOne("SELECT * FROM icq_cmd WHERE ID='".(int)$id."'");
		unset($command['ID']);
		$data=json_encode($command);
		$filename="Command_ICQ_".urlencode($command['TITLE']);
		$ext = "txt";
		$mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA')
			? 'application/octetstream' : 'application/octet-stream';
		header('Content-Type: ' . $mime_type);
		if (PMA_USR_BROWSER_AGENT == 'IE')
		{
			header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			print $data;
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			print $data;
		}
	exit;
	}
	function import_command(&$out) {
		global $file;
		global $overwrite;
		$data=LoadFile($file);
		$command=json_decode($this->removeBOM($data), true);
		if (is_array($command)) {
			$rec=SQLSelectOne("SELECT * FROM icq_cmd WHERE TITLE='". DBSafe($command["TITLE"]) . "'");
			if ($rec['ID'])
			{
				if ($overwrite)
				{
					$command{'ID'} = $rec['ID'];
					SQLUpdate("icq_cmd", $command); // update
				}
				else
				{
					$command["TITLE"] .= "_copy";
					SQLInsert("icq_cmd", $command); // adding new record
				}
			}	
			else
				SQLInsert("icq_cmd", $command); // adding new record
		}
		$this->redirect("?tab=cmd");
 	}
	function export_event(&$out, $id) {
		$event=SQLSelectOne("SELECT * FROM icq_event WHERE ID='".(int)$id."'");
		unset($event['ID']);
		$data=json_encode($event);
		$filename="Event_ICQ_".urlencode($event['TITLE']);
		$ext = "txt";
		$mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA')
			? 'application/octetstream' : 'application/octet-stream';
		header('Content-Type: ' . $mime_type);
		if (PMA_USR_BROWSER_AGENT == 'IE')
		{
			header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			print $data;
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			print $data;
		}
	exit;
	}
	function import_event(&$out) {
		global $file;
		global $overwrite;
		$data=LoadFile($file);
		$event=json_decode($this->removeBOM($data), true);
		if (is_array($event)) {
			$rec=SQLSelectOne("SELECT * FROM icq_event WHERE TITLE='". DBSafe($event["TITLE"]) . "'");
			if ($rec['ID'])
			{
				if ($overwrite)
				{
					$event{'ID'} = $rec['ID'];
					SQLUpdate("icq_event", $event); // update
				}
				else
				{
					$event["TITLE"] .= "_copy";
					SQLInsert("icq_event", $event); // adding new record
				}
			}	
			else
				SQLInsert("icq_event", $event); // adding new record
		}
		$this->redirect("?tab=events");
	}
	/**
	* Delete user
	*
	* @access public
	*/
	function delete_user($id) {
		$rec = SQLSelectOne("SELECT * FROM icq_user WHERE ID='$id'");
		// some action for related tables
		SQLExec("DELETE FROM icq_user WHERE ID='" . $rec['ID'] . "'");
		SQLExec("DELETE FROM icq_user_cmd WHERE USER_ID='" . $rec['ID'] . "'");
	}
	function delete_cmd($id) {
		$rec = SQLSelectOne("SELECT * FROM icq_cmd WHERE ID='$id'");
		// some action for related tables
		SQLExec("DELETE FROM icq_cmd WHERE ID='" . $rec['ID'] . "'");
		SQLExec("DELETE FROM icq_user_cmd WHERE CMD_ID='" . $rec['ID'] . "'");
	}
	function delete_event($id) {
		$rec = SQLSelectOne("SELECT * FROM icq_event WHERE ID='$id'");
		// some action for related tables
		SQLExec("DELETE FROM icq_event WHERE ID='" . $rec['ID'] . "'");
	}
	function icq_users(&$out) {
		require(DIR_MODULES . $this->name . '/icq_users.inc.php');
	}
	function icq_log(&$out) {
		require(DIR_MODULES . $this->name . '/icq_log.inc.php');
	}
	function icq_cmd(&$out) {
		require(DIR_MODULES . $this->name . '/icq_cmd.inc.php');
	}
	function icq_events(&$out) {
		require(DIR_MODULES . $this->name . '/icq_events.inc.php');
	}
	function getKeyb($user) {
		$visible = true;
		// Create option for the custom keyboard. Array of array string
		if($user['CMD'] == 0) {
			$option = array();
			$visible = false;
		} else {
			//$option = array( array("A", "B"), array("C", "D") );
			$option = array();
			$sql = "SELECT * FROM icq_cmd where ACCESS=3 or ((select count(*) from icq_user_cmd where icq_cmd.ID=icq_user_cmd.CMD_ID and icq_user_cmd.USER_ID=" . $user['ID'] . ")>0 and ACCESS>0) order by icq_cmd.PRIORITY desc, icq_cmd.TITLE;";
			//$this->log($sql);
			$rec = SQLSelect($sql);
			$total = count($rec);
			if($total) {
				for($i = 0; $i < $total; $i++) {
					$view = false;
					if($rec[$i]["SHOW_MODE"] == 1) $view = true;
					elseif($rec[$i]["SHOW_MODE"] == 3) {
						if ($rec[$i]["LINKED_OBJECT"] && $rec[$i]["LINKED_PROPERTY"]) {
							$val = gg($rec[$i]["LINKED_OBJECT"].".".$rec[$i]["LINKED_PROPERTY"]);
							if($val!='') {
								if($rec[$i]["CONDITION"] == 1 && $val == $rec[$i]["CONDITION_VALUE"]) $view = true;
								if($rec[$i]["CONDITION"] == 2 && $val > $rec[$i]["CONDITION_VALUE"]) $view = true;
								if($rec[$i]["CONDITION"] == 3 && $val < $rec[$i]["CONDITION_VALUE"]) $view = true;
								if($rec[$i]["CONDITION"] == 4 && $val <> $rec[$i]["CONDITION_VALUE"]) $view = true;
							}
						}
					}
					if($view) $option[] = $rec[$i]["TITLE"];
				}
				$count_row = $this->config['ICQ_COUNT_ROW'];
				if(!$count_row) $count_row = 3;
				$option = array_chunk($option, $count_row);
			}
		}
		// Get the keyboard
		$keyb = $this->icqBot->buildKeyBoard($option, false, true, $selective = $visible);
		//print_r($keyb);
		return $keyb;
	}
	function buildInlineKeyboardButton($text, $url = "", $callback_data = "", $switch_inline_query = "") {
		return $this->icqBot->buildInlineKeyboardButton($text, $url, $callback_data, $switch_inline_query);
	}
	function buildInlineKeyBoard(array $option) {
		return $this->icqBot->buildInlineKeyBoard($option);
	}
	function sendContent($content, $endpoint = "sendMessage") {
		$this->debug($content);
		$res = $this->icqBot->endpoint($endpoint, $content);
		$this->debug($res);
		return $res;
	}
	function getUsers($where) {
		$query = "SELECT * FROM icq_user";
		if($where != "") $query = $query . " WHERE " . $where;
		$users = SQLSelect($query);
		return $users;
	}
	function getUserName($chat_id) {
		$query = "SELECT * FROM icq_user WHERE USER_ID=" . $chat_id;
		$user = SQLSelectOne($query);
		if($user) return $user['NAME'];
		return "Unknow";
	}

	// send message
	function sendMessage($user_id, $message) {
		$this->log("sendMessage " . $user_id.' '.$message);
		$splited = str_split($message, 4096);
		foreach ($splited as $mess) {
			$content = array(
				'chat_id' => $user_id,
				'text' => $mess,
			);
			$res = $this->icq_send_message($user_id, $mess);
			$this->debug($res);
		}
		return $res;
	}
	function sendMessageTo($where, $message) {
		$this->log("sendMessageTo " . $where.' '.$message);
		$users = $this->getUsers($where);
		foreach($users as $user) {
			$user_id = $user['USER_ID'];
			if ($user_id === '0') $user_id = $user['NAME'];
			$this->sendMessage($user_id,$message);
		}
	}
	function sendMessageToUser($user_id, $message) {
		$this->log("sendMessageToUser " . $user_id.' '.$message);
		$this->sendMessageTo('(USER_ID="' . DBSafe($user_id) . '" OR NAME LIKE "' . DBSafe($user_id) .  '")', $message);
	}
	function sendMessageToAdmin($message) {
		$this->log("sendMessageToAdmin ".$message);
		$this->sendMessageTo("ADMIN=1", $message);
	}
	function sendMessageToAll($message) {
		$this->log("sendMessageToAll ".$message);
		$this->sendMessageTo("", $message);
	}

	function init() {
		$this->log("Token bot - " . $this->config['ICQ_TOKEN']);
		if (!$this->config['LAST_SEQNUM']) $this->config['LAST_SEQNUM'] = 0;
		$this->fetchUrl = $this->baseURL.'fetchEvents?'.http_build_query([
			'aimsid' => $this->config['ICQ_TOKEN'],
			'seqNum' => $this->config['LAST_SEQNUM'],
			'timeout' => $this->config["ICQ_TIMEOUT"],
		]);
	}

	function icq_curl_request($url, $post=null) {
		$this->log('icq_curl_request');
		$crl = curl_init();
		$header = array();
		curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($crl, CURLOPT_HEADER, false);
		if ($post != null) {
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $this->config["ICQ_TIMEOUT"]);
		curl_setopt($crl, CURLOPT_TIMEOUT, $this->config["ICQ_TIMEOUT"]);
		curl_setopt($crl, CURLOPT_URL, $url );
		//Получаем данные
		$response = curl_exec($crl);
		$this->log($url.' return '.$response);
		curl_close($crl);
		return $response;
	}

	function icq_send_message($uin, $message) {
		$this->log('icq_send_message');
		$this->log($uin.' '.$message);
		$url = $this->baseURL.'im/sendIM';
		$result = $this->icq_curl_request($url,http_build_query([
			'aimsid' => $this->config['ICQ_TOKEN'],
			't' => $uin,
			'r' => rand(),
			'message' => $message
		]));
		$response = json_decode($result,true);
		
		$status = $response['response']['statusCode'];
		
		if ($status != 200) {
			$error_str = sprintf(__FILE__.' Error sending message length=%s to uin=%s with api response=%s', strlen($message), $uin, $result);
			$this->log($error_str);
			return false;
		}
		return true;
	}
	

	public function icq_fetchEvents($url) {
		$this->log('icq_fetchEvents');
		$this->log($url);
		$result = $this->icq_curl_request($url);
		$this->log($result);
		$response = json_decode($result,true);
		$status = $response['response']['statusCode'];
		
		if ($status != 200) {
			$error_str = sprintf(__FILE__.' Error get icq_fetchEvents with api response=%s', $result);
			$this->log($error_str);
			return false;
		}
		return $response['response'];
	}

	function processCycle() {
		$this->log('processCycle');
		$this->getConfig();
		$response = $this->icq_fetchEvents($this->fetchUrl);
		if ($response['data']) {
			if ($response['data']['events']) {
				foreach($response['data']['events'] as $event) {
					if ($event['seqNum'] <= $this->config['LAST_SEQNUM']) continue;
					$this->config['LAST_SEQNUM'] = $event['seqNum'];
					if ($event['type'] == 'typing') continue;
					if ($event['type'] == 'im') {
						$this->log(print_r($event,1));
						$this->data = $event['eventData'];
						$this->processMessage();
					} else {
						$this->log(print_r($event,1));
					}
				}
				
			}
			$this->fetchUrl = $response['data']['fetchBaseURL'];
		}
		$this->saveConfig();
	}

	function processMessage() {
		$this->log('processMessage');
		$skip = false;
		$data = $this->data;
		echo $data;
		$this->debug($data);
		$text = $data['message'];
		$chat_id = $data['source']['aimId'];
		$username = $data['source']['displayId'];
		$fullname = $data['source']['friendly'];
		$bot_name = $this->config['ICQ_BOTNAME'];
		$this->log(print_r($text,1));
		// поиск в базе пользователя
		$user = SQLSelectOne("SELECT * FROM icq_user WHERE USER_ID LIKE '" . DBSafe($chat_id) . "';");
		
		$this->debug("Chatid: ".$chat_id."; Bot-name: ".$bot_name."; Message: ".$text);
		
		if($text == "/start") {
			$this->log('START CMD');
			// если нет добавляем
			if(!$user['ID']) {
				$user['USER_ID'] = $chat_id;
				$user['CREATED'] = date('Y/m/d H:i:s');
				$user['NAME'] = $fullname;
				$user['ID'] = SQLInsert('icq_user', $user);
				$this->log("Added new user: " . $username . " - " . $chat_id);
			}
			$reply = "Вы зарегистрированы! Обратитесь к администратору для получения доступа к функциям.";
			$this->sendMessage($chat_id, $reply);
			return;
		}
		
		// пользователь не найден
		if(!$user['ID']) {
			$this->debug("Unknow user: ".$chat_id."; Message: ".$text);
			return;
		}
		
		if ($user['ADMIN'] != 1 && 
			$user['HISTORY'] != 1 && 
			$user['CMD'] != 1 && 
			$user['PATTERNS'] != 1 && 
			$user['DOWNLOAD'] != 1 && 
			$user['PLAY'] != 1)
		{
			$this->log("WARNING!!! Permission denied!! User: ".$chat_id."; Message: ".$text);
			$reply = "Обратитесь к администратору для получения доступа к функциям!";
			$this->sendMessage($chat_id, $reply);
			return;
		}
		if($text == "") {
			return;
		}
		$this->log($chat_id . " (" . $username . ", " . $fullname . ")=" . $text);

		if($user['CMD'] == 1) {
		// get events for text message
			$events = SQLSelect("SELECT * FROM icq_event WHERE TYPE_EVENT=1 and ENABLE=1;");
			foreach($events as $event) {
				if($event['CODE']) {
					$this->log("Execute code event " . $event['TITLE']);
					try {
						eval($event['CODE']);
					}
					catch(Exception $e) {
						registerError('icq', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
					}
				}
				if($skip) {
					$this->log("Skip next processing events message");
					break;
				}
			}
			// пропуск дальнейшей обработки если с обработчике событий установили $skip
			if($skip) {
				$this->log("Skip next processing message");
				return;
			}
		}
			
		if($user['ID']) {
			//смотрим разрешения на обработку команд
			if($user['CMD'] == 1) {
				// поиск полного соответствия команды
				$sql = "SELECT * FROM icq_cmd where icq_cmd.TITLE='" . DBSafe($text) . "' and (ACCESS=3  OR ((select count(*) from icq_user_cmd where icq_user_cmd.USER_ID=" . $user['ID'] . " and icq_cmd.ID=icq_user_cmd.CMD_ID)>0 and ACCESS>0))";
				$cmd = SQLSelectOne($sql);
				if (count($cmd) == 0) {
					// поиск команд с параметрами
					$sql = "SELECT * FROM icq_cmd where '" . DBSafe($text) . "' LIKE CONCAT(icq_cmd.TITLE,'%') and (ACCESS=3  OR ((select count(*) from icq_user_cmd where icq_user_cmd.USER_ID=" . $user['ID'] . " and icq_cmd.ID=icq_user_cmd.CMD_ID)>0 and ACCESS>0))";
					//$this->debug($sql);
					$cmd = SQLSelectOne($sql);
				}
				if($cmd['ID']) {
					$this->log("Find command");
					//нашли команду
					if($cmd['CODE']) {
						$this->log("Execute user`s code command");
						try {
							$success = eval($cmd['CODE']);
							$this->log("Command:" . $text . " Result:" . $success);
							if($success == false) {
								//нет в выполняемом куске кода return
							} else {
								$this->sendMessage($chat_id, $success);
								$this->log("Send result to " . $chat_id . ". Command:" . $text . " Result:" . $success);
							}
						}
						catch(Exception $e) {
							registerError('icq', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
							$this->sendMessage($chat_id, "Ошибка выполнения кода команды " . $text);
						}
						return;
					}
					// если нет кода, который надо выполнить, то передаем дальше на обработку
				} else $this->log("Command not found");
			}
			if ($user['PATTERNS'] == 1) say(htmlspecialchars($text), 0, $user['MEMBER_ID'], 'icq' . $user['ID']);
		}
	}
	
	function execCommand($chat_id, $command)
	{
		$user = SQLSelectOne("SELECT * FROM icq_user WHERE USER_ID LIKE '" . DBSafe($chat_id) . "';");
		$cmd = SQLSelectOne("SELECT * FROM icq_cmd INNER JOIN icq_user_cmd on icq_cmd.ID=icq_user_cmd.CMD_ID where (ACCESS=3  OR (icq_user_cmd.USER_ID=" . $user['ID'] . " and ACCESS>0)) and '" . DBSafe($command) . "' LIKE CONCAT(TITLE,'%');");
        if($cmd['ID']) {
			$this->log("execCommand => Find command");
            if($cmd['CODE']) {
                $this->log("execCommand => Execute user`s code command");
                try {
				$text = $command;
                    $success = eval($cmd['CODE']);
                    $this->log("Command:" . $text . " Result:" . $success);
                    if($success == false) {
                        //нет в выполняемом куске кода return
                    } else {
                        $content = array(
                        'chat_id' => $chat_id,
                        'text' => $success,
                        'parse_mode' => 'HTML'
                        );
                        $this->sendContent($content);
                        $this->log("Send result to " . $chat_id . ". Command:" . $text . " Result:" . $success);
                    }
                }
                catch(Exception $e) {
                    registerError('icq', sprintf('Exception in "%s" method ' . $e->getMessage(), $text));
                }
			}
        }        
	}
	
	/**
	* FrontEnd
	*
	* Module frontend
	*
	* @access public
	*/
	function usual(&$out) {
		$this->admin($out);
	}
	function processSubscription($event, &$details) {
		DebMes("processSubscription ".$event);
		$this->getConfig();
		if($event == 'SAY' || $event=='SAYTO' || $event=='REPLYTO' ) { // 
			$level = $details['level'];
			$message = $details['message'];
			if($details['destination']) {
				$destination = $details['destination'];
			} elseif($details['source']) {
				$destination = $details['source'];
			}
			$users = SQLSelect("SELECT * FROM icq_user WHERE HISTORY=1;");
			$c_users = count($users);
			if($c_users) {
				$reply = $message;
				for($j = 0; $j < $c_users; $j++) {
					$user_id = $users[$j]['USER_ID'];
					if ($user_id === '0') {
					$user_id = $users[$j]['NAME'];
					}
					if($destination == 'icq' . $users[$j]['ID'] || (!$destination && ($level >= $users[$j]['HISTORY_LEVEL']))) {
					$this->log(" Send to " . $user_id . " - " . $reply);
					$url=BASE_URL."/ajax/icq.html?sendMessage=1&user=".$user_id."&text=".urlencode($reply);
					getURLBackground($url,0);
					}
				}
				$this->debug("Sended - " . $reply);
			} else {
				$this->log("No users to send data");
			}
		}
	}
	/**
	* Install
	*
	* Module installation routine
	*
	* @access private
	*/
	function install($data='') {
		subscribeToEvent($this->name, 'SAY', '', 10);
		subscribeToEvent($this->name, 'SAYTO', '', 10);
		subscribeToEvent($this->name, 'SAYREPLY', '', 10);
		parent::install();
	}
	/**
	* Uninstall
	*
	* Module uninstall routine
	*
	* @access public
	*/
	function uninstall() {
		SQLExec('DROP TABLE IF EXISTS icq_user_cmd');
		SQLExec('DROP TABLE IF EXISTS icq_user');
		SQLExec('DROP TABLE IF EXISTS icq_cmd');
		SQLExec('DROP TABLE IF EXISTS icq_event');
		unsubscribeFromEvent($this->name, 'SAY'); 
		unsubscribeFromEvent($this->name, 'SAYTO'); 
		unsubscribeFromEvent($this->name, 'SAYREPLY'); 
		parent::uninstall();
	}
	/**
	* dbInstall
	*
	* Database installation routine
	*
	* @access private
	*/
	function dbInstall($data) {
		$data = <<<EOD
 icq_user: ID int(10) unsigned NOT NULL auto_increment
 icq_user: NAME varchar(255) NOT NULL DEFAULT ''
 icq_user: USER_ID varchar(25) NOT NULL DEFAULT '0'
 icq_user: MEMBER_ID int(10) NOT NULL DEFAULT '1'
 icq_user: CREATED datetime
 icq_user: ADMIN int(3) unsigned NOT NULL DEFAULT '0' 
 icq_user: HISTORY int(3) unsigned NOT NULL DEFAULT '0' 
 icq_user: HISTORY_LEVEL int(3) unsigned NOT NULL DEFAULT '0' 
 icq_user: CMD int(3) unsigned NOT NULL DEFAULT '0' 
 icq_user: PATTERNS int(3) unsigned NOT NULL DEFAULT '0' 
 icq_user: DOWNLOAD int(3) unsigned NOT NULL DEFAULT '0' 
 icq_user: PLAY int(3) unsigned NOT NULL DEFAULT '0' 
 
 icq_cmd: ID int(10) unsigned NOT NULL auto_increment
 icq_cmd: TITLE varchar(255) NOT NULL DEFAULT ''
 icq_cmd: DESCRIPTION text
 icq_cmd: CODE text
 icq_cmd: ACCESS int(10) NOT NULL DEFAULT '0'
 icq_cmd: SHOW_MODE int(10) NOT NULL DEFAULT '1'
 icq_cmd: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 icq_cmd: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT '' 
 icq_cmd: CONDITION int(10) NOT NULL DEFAULT '1' 
 icq_cmd: CONDITION_VALUE varchar(255) NOT NULL DEFAULT '' 
 icq_cmd: PRIORITY int(10) NOT NULL DEFAULT '1' 
 
 icq_user_cmd: ID int(10) unsigned NOT NULL auto_increment
 icq_user_cmd: USER_ID int(10) NOT NULL
 icq_user_cmd: CMD_ID int(10) NOT NULL
 
 icq_event: ID int(10) unsigned NOT NULL auto_increment
 icq_event: TITLE varchar(255) NOT NULL DEFAULT ''
 icq_event: DESCRIPTION text
 icq_event: TYPE_EVENT int(3) unsigned NOT NULL DEFAULT '1' 
 icq_event: ENABLE int(3) unsigned NOT NULL DEFAULT '0' 
 icq_event: CODE text
EOD;
		parent::dbInstall($data);
		$cmds = SQLSelectOne("SELECT * FROM icq_cmd;");
		if(count($cmds) == 0) {
			$rec['TITLE'] = 'Ping';
			$rec['DESCRIPTION'] = 'Example command Ping-Pong';
			$rec['CODE'] = 'return "Pong!";';
			$rec['ACCESS'] = 2;
			SQLInsert('icq_cmd', $rec);
		}
	}
	// --------------------------------------------------------------------
}
?>
