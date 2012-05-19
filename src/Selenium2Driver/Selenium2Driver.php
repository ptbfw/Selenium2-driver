<?php

namespace ptbfw\Selenium2Driver;

/**
 * Description of ptbfwSelenium2Driver
 *
 * @author potaka
 */
class Selenium2Driver extends \Behat\Mink\Driver\Selenium2Driver {

	private $retries;
	private $retryWait;
	
	public function __construct($options) {
		$defaultOptions = array(
			'browserName' => 'firefox',
			'desiredCapabilities' => null,
			'wdHost' => 'http://localhost:4444/wd/hub',
			'retries' => 2,
			'retryWait' => 1000,
		);

		$optios = array_merge($defaultOptions, $options);
		parent::__construct($options['browserName'], $options['desiredCapabilities'], $options['wdHost']);
		
		$this->retries = $options['retries'];
		$this->retryWait = $options['retryWait'];
	}

	/**
	 * Finds elements with specified XPath query.
	 *
	 * @param   string  $xpath
	 *
	 * @return  array           array of Behat\Mink\Element\NodeElement
	 */
	public function find($xpath) {
		$return = null;
		$tries = 0;
		do {
			if ($tries == 0) {
				$this->wait($this->retryWait);
			}
			$tries++;
			$return = parent::find($xpath);
		} while(empty($return) && $tries < $this->retries);
	}

}