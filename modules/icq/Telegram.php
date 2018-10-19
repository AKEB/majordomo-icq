<?php //$Id:$
/**
 * ICQ Bot Class.
 *
 * @author Vadim Babajanyan <akeb@akeb.ru>
 */
class ICQBot {
	private $bot_token = '';
	private $data = [];
	private $updates = [];

	public function __construct($bot_token) {
		$this->bot_token = $bot_token;
		$this->data = $this->getData();
	}

	public function endpoint($api, array $content, $post = true) {
		$url = 'https://botapi.icq.net/'.$api;
		$content['aimsid'] = $this->bot_token;
		$content['r'] = rand();
		if ($post) {
			$reply = $this->sendAPIRequest($url, $content);
		} else {
			if (strpos($url,'?') !== false) $url .= '&';
			else $url .= '?';
			$url .= http_build_query($content);
			$reply = $this->sendAPIRequest($url, [], false);
		}
		$json = json_decode($reply, true);
		if (!$json) return false;
		return $json['response'];
	}

	function icq_fetchEvents($url) {
		$result = icq_curl_request($url);
		$response = json_decode($result,true);
		$status = $response['response']['statusCode'];
	
		if ($status != 200) {
			$error_str = sprintf(__FILE__.' Error sending message length=%s to uin=%s with api response=%s', strlen($message), $uin, $result);
			$this->log($error_str);
			return false;
		}
		return $response['response'];
	}

	public function sendMessage(string $uid, string $message) {
		$content = array(
			't' => $uid,
			'message' => $message,
		);
		return $this->endpoint('/im/sendIM', $content);
	}
	public function getUpdates($offset = 0, $timeout = 10, $update = true) {
		$content = ['seqNum' => $offset, 'timeout' => $timeout];
		$this->updates = $this->endpoint('fetchEvents', $content, false);
		if ($update) {
			if (count($this->updates['data']['events']) >= 1) { //for CLI working.
				$last_element_id = $this->updates['result'][count($this->updates['result']) - 1]['update_id'] + 1;
				$content = ['seqNum' => $last_element_id, 'timeout' => $timeout];
				$this->endpoint('fetchEvents', $content);
			}
		}
		return $this->updates;
	}

	


	public function fetchEvents($content) {
		$url = ICQ_BOT_URL.'fetchEvents?'.http_build_query([
			'aimsid' => ICQ_BOT_SECRET,
			'seqNum' => $seqNum,
			'timeout' => 60000,
		]);
		
	}

	public function getData() {
		if (empty($this->data)) {
			$rawData = file_get_contents('php://input');
			return json_decode($rawData, true);
		} else {
			return $this->data;
		}
	}

	/// Set the data currently used
	public function setData(array $data)
	{
		$this->data = $data;
	}

	private function sendAPIRequest($url, array $content, $post = true) {
		$ch = curl_init();
		$header = [];
		if (!$content['timeout']) $content['timeout'] = 10;
		$timeout = $content['timeout'];
		curl_setopt($ch, CURLOPT_HEADER, false);
		if ($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($crl, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($crl, CURLOPT_URL, $url );
		
		$result = curl_exec($ch);
		if ($result === false) {
			$result = json_encode(['ok'=>false, 'curl_error_code' => curl_errno($ch), 'curl_error' => curl_error($ch)]);
		}
		curl_close($ch);
		return $result;
	}
}

	// Helper for Uploading file using CURL
if (!function_exists('curl_file_create')) {
	function curl_file_create($filename, $mimetype = '', $postname = '') {
		return "@$filename;filename="
		.($postname ?: basename($filename))
		.($mimetype ? ";type=$mimetype" : '');
	}
}
