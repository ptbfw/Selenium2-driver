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
		return $this->retry(__METHOD__, func_get_args(), true);
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

	public function executeJsOnXpath($xpath, $script, $sync = true, $requireReturn = false) {
		return $this->retry(__METHOD__, func_get_args(), $requireReturn);
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
		$valueEscaped = $this->escapeStringForJs($xpath, true);
		$this->withSyn();
		$elementJavaScriptName = 'ptbfw_' . uniqid();
		$JS = "{$elementJavaScriptName} = document.evaluate('{$xpath}', document, null, XPathResult.ANY_TYPE, null).iterateNext();";
		$this->evaluateScript($JS);
		$this->executeScript("{$elementJavaScriptName}.value = '';");

		// if value === '' there is syn exception
		// @todo onchange() ???
		if ($value) {
			$this->executeScript("Syn.type( \"{$valueEscaped}\", {$elementJavaScriptName})");

			/*
			 * everage 1 symbol per second
			 */
			$waitTIme = strlen($value) * 1000;
			$jsEvent = "{$elementJavaScriptName}.value == \"{$valueEscaped}\"";
			$this->wait($waitTIme, $jsEvent);
		}
		return true;
	}

	public function check($xpath) {
		$xpathEscaped = $this->escapeStringForJs($xpath);
		$this->retry(__METHOD__, func_get_args());
		$elementJavaScriptName = 'ptbfw_' . uniqid();
		$JS = <<< JS
      
        {$elementJavaScriptName} = document.evaluate("{$xpathEscaped}", document, null, XPathResult.ANY_TYPE, null).iterateNext()
JS;

		$this->evaluateScript($JS);

		$js = <<<JS
            var evt = document.createEvent("MouseEvents");
            evt.initMouseEvent("change", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            {$elementJavaScriptName}.dispatchEvent(evt);
            if ({$elementJavaScriptName}.onchange) {
                {$elementJavaScriptName}.onchange();
            }
JS;

		$this->evaluateScript($js);
	}

	public function uncheck($xpath) {
		$xpathEscaped = $this->escapeStringForJs($xpath);
		$this->retry(__METHOD__, func_get_args());
		$elementJavaScriptName = 'ptbfw_' . uniqid();
		$JS = <<< JS
      
        {$elementJavaScriptName} = document.evaluate("{$xpathEscaped}", document, null, XPathResult.ANY_TYPE, null).iterateNext()
JS;

		$this->evaluateScript($JS);

		$js = <<<JS
            var evt = document.createEvent("MouseEvents");
            evt.initMouseEvent("change", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            {$elementJavaScriptName}.dispatchEvent(evt);
            if ({$elementJavaScriptName}.onchange) {
                {$elementJavaScriptName}.onchange();
            }
JS;

		$this->evaluateScript($js);
	}

	public function isChecked($xpath) {
		return $this->retry(__METHOD__, func_get_args());
	}

	public function selectOption($xpath, $value, $multiple = false) {
		$xpathEscaped = $this->escapeStringForJs($xpath);
		$this->retry(__METHOD__, func_get_args());
		$elementJavaScriptName = 'ptbfw_' . uniqid();
		$JS = <<< JS
      
        {$elementJavaScriptName} = document.evaluate("{$xpathEscaped}", document, null, XPathResult.ANY_TYPE, null).iterateNext()
JS;

		$this->evaluateScript($JS);

		$js = <<<JS
            var evt = document.createEvent("MouseEvents");
            evt.initMouseEvent("change", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            {$elementJavaScriptName}.dispatchEvent(evt);
            if ({$elementJavaScriptName}.onchange) {
                {$elementJavaScriptName}.onchange();
            }
JS;

		$this->evaluateScript($js);
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

	/**
	 * true = element is select
	 * false = element is not select
	 * 
	 * @param type $xpath
	 * @return boolean
	 */
	public function isSelect($xpath) {
		$type = $this->getAttribute($xpath, 'tagName');
		if ($type == 'SELECT') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * return escaped string witch could be used in JS expressions
	 * 
	 * @param string $xpath
	 * @param bool $escapeNewLine
	 * @return string
	 */
	protected function escapeStringForJs($xpath, $escapeNewLine = false) {
		if ($escapeNewLine) {
			$xpath = preg_replace("~\n~", '\n', $xpath);
		} else {
			assertNotRegExp("~\n~", $xpath, 'xpath should not contains new line');
		}

		return addslashes($xpath);
	}

}