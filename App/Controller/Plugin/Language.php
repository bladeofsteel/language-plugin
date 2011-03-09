<?php

/**
 * Zeus library
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   Copyright (c) 2008 Oleg Lobach <oleg@lobach.info>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html (GPLv3)
 */


/**
 * Connecting and configuring Zend_Translate
 * 
 * @uses       Zend_Controller_Plugin_Abstract
 * @category   Zeus
 * @package    Zeus_Controller
 * @subpackage Pligin
 * @copyright  Copyright (c) 2008 Lobach Oleg (http://lobach.info)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html (GPLv3)
 * @author     Lobach Oleg <oleg@lobach.info>
 * @version    $Id$
 */
class Zeus_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract {

	/**
	 * Instance of Zend_Locale class
	 * @var Zend_Locale
	 */
	protected $_locale;

	/**
	 * Instance of Zend_Translate class
	 * @var Zend_Translate
	 */
	protected $_translate;

	/**
	 * The name of the key parameters in the request that contains the name of the language
	 * @var string
	 */
	protected $_lang_key = 'lang';

	/**
	 * Flag for skip registration instances of Zend_Locale and Zend_Translate in the registry
	 * @var boolean
	 */
	protected $_skip_registry = false;

	/**
	 * Options for creating instance of Zend_Translate
	 *
	 * This property must contain:
	 *   [adapter] - name of Zend_Translate_Adapter
	 *   [data]    - data, specific for adepter
	 *   [locale]  - using locale
	 *   [options] - options for configure Zend_Translate
	 *
	 * @var array
	 */
	protected $_options;

	/**
	 * Constructor
	 *
	 * @param array $options - Options for creating instance of Zend_Translate
	 * @param Zend_Translate $translate - Instance of Zend_Locale class
	 * @param Zend_Locale $locale - Instance of Zend_Translate class
	 * @param boolean $skipRegistry - Flag for skip registration instances of Zend_Locale and Zend_Translate in the registry
	 * @return void
	 */
	public function __construct(array $options = array(), Zend_Translate $translate = null, Zend_Locale $locale = null, $skipRegistry = false) {
		$this->_locale = $locale;
		$this->_translate = $translate;
		$this->_options = $options;
		$this->_lang_key = isset($options['langKey']) ? $options['langKey'] : $this->_lang_key;
		$this->_skip_registry = (bool)$skipRegistry;
	}

	/**
	 * routeShutdown() plugin hook -- configure and registering Zend_Translate
	 *
	 * @param Zend_Controller_Request_Abstract $request - Current request
	 * @return void
	 * @throw Zend_Exception
	 */
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{

		if (!$this->_locale) {
			$this->_locale = new Zend_Locale();
		}

		if (!$this->_translate) {
			$this->_translate = new Zend_Translate($this->_options['adapter'], $this->_options['data'], $this->_options['locale'], $this->_options['options']);
		}

		$language = $this->getRequest()->getParam($this->_lang_key, 'ru');
		if (!$this->_translate->isAvailable($language)) {
			throw new Zend_Controller_Action_Exception('This page dont exist',404);
		}

		$this->_locale->setLocale($language);
		$this->_translate->setLocale($this->_locale);

		Zend_Form::setDefaultTranslator($this->_translate);

		if (!$this->_skip_registry) {
			Zend_Registry::set('Zend_Locale', $this->_locale);
			Zend_Registry::set('Zend_Translate', $this->_translate);
		}
	}

	/**
	 * Set value of 'locale' property
	 *
	 * @param Zend_Locale $locale
	 * @return Zeus_Controller_Plugin_Language 
	 */
	public function setLocale(Zend_Locale $locale) {
		$this->_locale = $locale;
		return $this;
	}

	/**
	 * Retrieve value of 'locale' property
	 *
	 * @return Zend_Locale | boolean
	 */
	public function getLocale() {
		if (isset($this->_locale)) return $this->_locale;
		if (isset($this->_translate)) return $this->_translate->getAdapter()->getLocale();
		return false;
	}

	/**
	 * Set value of 'translate' property
	 *
	 * @param Zend_Translate $translate
	 * @return Zeus_Controller_Plugin_Language 
	 */
	public function setTranslate(Zend_Translate $translate) {
		$this->_translate = $translate;
		return $this;
	}

	/**
	 * Retrieve value of 'translate' property
	 *
	 * @return Zend_Translate
	 */
	public function getTranslate() {
		return $this->_translate;
	}

	/**
	 * Set value of 'skip_registry' property
	 *
	 * @param boolean $value
	 * @return Zeus_Controller_Plugin_Language
	 */
	public function setSkipRegistry($value) {
		$this->_skip_registry = (bool)$value;
		return $this;
	}
}