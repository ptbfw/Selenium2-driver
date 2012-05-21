<?php

namespace Ptbfw\Selenium2Driver;

/**
 * Description of ptbfwSelenium2Driver
 *
 * @author potaka
 */
class Selenium2Driver extends \Behat\Mink\Driver\Selenium2Driver {

	private $retries;
	private $retryWait;

	public function __construct($options = array()) {
		$defaultOptions = array(
			'browserName' => 'firefox',
			'desiredCapabilities' => null,
			'wdHost' => 'http://localhost:4444/wd/hub',
			'retries' => 2,
			'retryWait' => 1000,
		);

		$options = array_merge($defaultOptions, $options);
		parent::__construct($options['browserName'], $options['desiredCapabilities'], $options['wdHost']);

		$this->retries = $options['retries'];
		$this->retryWait = $options['retryWait'];
	}

	public function getContent() {
		throw new \Exception('not implemented yet');
	}

	/**
	 * Finds elements with specified XPath query.
	 *
	 * @param   string  $xpath
	 *
	 * @return  array           array of Behat\Mink\Element\NodeElement
	 */
	public function find($xpath) {
		return $this->retry(__METHOD__, func_get_args(), true);
	}

	public function executeJsOnXpath($xpath, $script, $sync = true) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function getTagName($xpath) {
		return $this->retry(__METHOD__, func_get_args(), true);
	}

	public function getText($xpath) {
		return $this->retry(__METHOD__, func_get_args(), true);
	}

	public function getHtml($xpath) {
		return $this->retry(__METHOD__, func_get_args(), true);
	}

	public function getAttribute($xpath, $attr) {
		return $this->retry(__METHOD__, func_get_args(), true);
	}

	public function getValue($xpath) {
		return $this->retry(__METHOD__, func_get_args(), true);
	}

	public function setValue($xpath, $value) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function check($xpath) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function uncheck($xpath) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function isChecked($xpath) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function selectOption($xpath, $value, $multiple = false) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function click($xpath) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function executeScript($script) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function evaluateScript($script) {
		return $this->retry(__METHOD__, func_get_args());
	}

	private function retry($callback, $params = null, $requireReturn = false) {

		$callback = preg_replace('/^.*\:(.*)$/', '$1', $callback);

		$return = null;
		$tries = 0;
		$ex = null;
		do {
			if ($tries > 0) {
				echo PHP_EOL . "{$tries}/{$this->retries} retries" . PHP_EOL;
			}
			
			// wait only if there is a fail
			if ($tries > 0) {
				$this->wait($this->retryWait);
			}
			$tries++;
			try {
				$return = call_user_func_array(array('parent', $callback), $params);
				$ex = null;
			} catch (\Exception $e) {
				$ex = $e;
			}
		} while (empty($return) && $tries <= $this->retries && $requireReturn);

		if ($ex !== null) {
			throw $ex;
		}
		
		if ($return === null && $requireReturn) {
			throw new \Exception("error, probably not found element");
		}
		
		return $return;
	}

	public function wait($time, $condition = 'false') {
		parent::wait($time, $condition);
	}

}