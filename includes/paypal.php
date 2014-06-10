<?php

class PayPal {
	private $config;

	private $urls = array(
		"sandbox" => array(
			"api"      => "https://svcs.sandbox.paypal.com/AdaptivePayments/",
			"redirect" => "https://www.sandbox.paypal.com/webscr",
		),
		"live" => array(
			"api"      => "https://svcs.paypal.com/AdaptivePayments/",
			"redirect" => "https://www.paypal.com/webscr",
		)
	);

	public function __construct($config) {
		$this->config = $config;
	}

	public function call($options = [], $calltype) {
		return $this->_curl($this->api_url($calltype), $options, $this->config['header']);
	}

	public function redirect($response) {
		$redirect_url = sprintf("%s?cmd=_ap-payment&paykey=%s", $this->redirect_url(), $response["payKey"]);
		header("Location: $redirect_url");
	}

	private function redirect_url() {
		return $this->urls[$this->config["environment"]]["redirect"];
	}

	private function api_url($calltype) {
		return $this->urls[$this->config["environment"]]["api"].$calltype;
	}

	private function _curl($url, $values, $header) {
		$curl = curl_init($url);

		$options = array(
			CURLOPT_HTTPHEADER      => $header,
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_SSL_VERIFYHOST  => false,
			CURLOPT_POSTFIELDS  => json_encode($values),
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_TIMEOUT        => 10
		);

		curl_setopt_array($curl, $options);
		$rep = curl_exec($curl);

		$response = json_decode($rep, true);

		curl_close($curl);

		if (@$response['payKey']) {
			$_SESSION['payKey'] = $response['payKey'];
		}

		return $response;

	}
}
