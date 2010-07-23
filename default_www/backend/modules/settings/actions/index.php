<?php

/**
 * BackendSettingsIndex
 * This is the index-action (default), it will display the setting-overview
 *
 * @package		backend
 * @subpackage	settings
 *
 * @author 		Tijs Verkoyen <tijs@netlash.com>
 * @author 		Davy Hellemans <davy@netlash.com>
 * @since		2.0
 */
class BackendSettingsIndex extends BackendBaseActionIndex
{
	/**
	 * The form instance
	 *
	 * @var	BackendForm
	 */
	private $frm;


	/**
	 * Should we show boxes for their API keys
	 *
	 * @var	bool
	 */
	private $needsAkismet, $needsGoogleMaps;


	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// get some data
		$modulesThatRequireAkismet = BackendSettingsModel::getModulesThatRequireAkismet();
		$modulesThatRequireGoogleMaps = BackendSettingsModel::getModulesThatRequireGoogleMaps();

		// set properties
		$this->needsAkismet = (!empty($modulesThatRequireAkismet));
		$this->needsGoogleMaps = (!empty($modulesThatRequireGoogleMaps));

		// load the form
		$this->loadForm();

		// validate the form
		$this->validateForm();

		// parse the datagrid
		$this->parse();

		// display the page
		$this->display();
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// list of default domains
		$defaultDomains = array(str_replace(array('http://', 'www.', 'https://'), '', SITE_URL));

		// create form
		$this->frm = new BackendForm('generalSettings');

		// general settings
		$this->frm->addText('site_title', BackendModel::getSetting('core', 'site_title_'. BL::getWorkingLanguage(), SITE_DEFAULT_TITLE));
		$this->frm->addTextarea('site_wide_html', BackendModel::getSetting('core', 'site_wide_html', null), 'textarea code', 'textareaError code', true);
		$this->frm->addTextarea('site_domains', implode("\n", (array) BackendModel::getSetting('core', 'site_domains', $defaultDomains)), 'textarea code', 'textareaError code');

		// email settings
		$mailerFrom = BackendModel::getSetting('core', 'mailer_from');
		$this->frm->addText('mailer_from_name', (isset($mailerFrom['name'])) ? $mailerFrom['name'] : '');
		$this->frm->addText('mailer_from_email', (isset($mailerFrom['email'])) ? $mailerFrom['email'] : '');
		$mailerTo = BackendModel::getSetting('core', 'mailer_to');
		$this->frm->addText('mailer_to_name', (isset($mailerTo['name'])) ? $mailerTo['name'] : '');
		$this->frm->addText('mailer_to_email', (isset($mailerTo['email'])) ? $mailerTo['email'] : '');
		$mailerReplyTo = BackendModel::getSetting('core', 'mailer_reply_to');
		$this->frm->addText('mailer_reply_to_name', (isset($mailerReplyTo['name'])) ? $mailerReplyTo['name'] : '');
		$this->frm->addText('mailer_reply_to_email', (isset($mailerReplyTo['email'])) ? $mailerReplyTo['email'] : '');

		// smtp settings
		$this->frm->addText('smtp_server', BackendModel::getSetting('core', 'smtp_server', ''));
		$this->frm->addText('smtp_port', BackendModel::getSetting('core', 'smtp_port', 25));
		$this->frm->addText('smtp_username', BackendModel::getSetting('core', 'smtp_username', ''));
		$this->frm->addText('smtp_password', BackendModel::getSetting('core', 'smtp_password', ''));

		// theme
		$this->frm->addDropdown('theme', BackendModel::getThemes(), BackendModel::getSetting('core', 'theme', null));
		$this->frm->getField('theme')->setDefaultElement(BL::getLabel('NoTheme'));

		// api keys
		$this->frm->addText('fork_api_public_key', BackendModel::getSetting('core', 'fork_api_public_key', null));
		$this->frm->addText('fork_api_private_key', BackendModel::getSetting('core', 'fork_api_private_key', null));

		// date & time formats
		$this->frm->addDropdown('time_format', BackendModel::getTimeFormats(), BackendModel::getSetting('core', 'time_format'));
		$this->frm->addDropdown('date_format_short', BackendModel::getDateFormatsShort(), BackendModel::getSetting('core', 'date_format_short'));
		$this->frm->addDropdown('date_format_long', BackendModel::getDateFormatsLong(), BackendModel::getSetting('core', 'date_format_long'));

		// create a list of the languages
		foreach(BackendModel::getSetting('core', 'languages', array('nl')) as $abbreviation)
		{
			// is this the default language
			$defaultLanguage = ($abbreviation == SITE_DEFAULT_LANGUAGE) ? true : false;

			// attributes
			$activeAttributes = array();
			$activeAttributes['id'] = 'active_language_'. $abbreviation;
			$redirectAttributes = array();
			$redirectAttributes['id'] = 'redirect_language_'. $abbreviation;

			// fetch label
			$label = BackendLanguage::getMessage(mb_strtoupper($abbreviation), 'core');

			// default may not be unselected
			if($defaultLanguage)
			{
				// add to attributes
				$activeAttributes['disabled'] = 'disabled';
				$redirectAttributes['disabled'] = 'disabled';

				// overrule in $_POST
				if(!isset($_POST['active_languages']) || !is_array($_POST['active_languages'])) $_POST['active_languages'] = array(SITE_DEFAULT_LANGUAGE);
				elseif(!in_array($abbreviation, $_POST['active_languages'])) $_POST['active_languages'][] = $abbreviation;
				if(!isset($_POST['redirect_languages']) || !is_array($_POST['redirect_languages'])) $_POST['redirect_languages'] = array(SITE_DEFAULT_LANGUAGE);
				elseif(!in_array($abbreviation, $_POST['redirect_languages'])) $_POST['redirect_languages'][] = $abbreviation;
			}

			// add to the list
			$activeLanguages[] = array('label' => $label, 'value' => $abbreviation, 'attributes' => $activeAttributes, 'variables' => array('default' => $defaultLanguage));
			$redirectLanguages[] = array('label' => $label, 'value' => $abbreviation, 'attributes' => $redirectAttributes, 'variables' => array('default' => $defaultLanguage));
		}

		// create multilanguage checkbox
		$this->frm->addMultiCheckbox('active_languages', $activeLanguages, BackendModel::getSetting('core', 'active_languages', array(SITE_MULTILANGUAGE)));
		$this->frm->addMultiCheckbox('redirect_languages', $redirectLanguages, BackendModel::getSetting('core', 'redirect_languages', array(SITE_MULTILANGUAGE)));

		// api keys are not required for every module
		if($this->needsAkismet) $this->frm->addText('akismet_key', BackendModel::getSetting('core', 'akismet_key', null));
		if($this->needsGoogleMaps) $this->frm->addText('google_maps_key', BackendModel::getSetting('core', 'google_maps_key', null));
	}


	/**
	 * Parse the form
	 *
	 * @return	void
	 */
	private function parse()
	{
		// show options
		if($this->needsAkismet) $this->tpl->assign('needsAkismet', true);
		if($this->needsGoogleMaps) $this->tpl->assign('needsGoogleMaps', true);

		// parse the form
		$this->frm->parse($this->tpl);

		// parse the warnings
		$this->parseWarnings();
	}


	/**
	 * Show the warnings based on the active modules & configured settings
	 *
	 * @return	void
	 */
	private function parseWarnings()
	{
		// get warnings
		$warnings = BackendSettingsModel::getWarnings();

		// assign warnings
		$this->tpl->assign('warnings', $warnings);
	}


	/**
	 * Validates the form
	 *
	 * @return	void
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// validate required fields
			$this->frm->getField('site_title')->isFilled(BL::getError('FieldIsRequired'));

			// mailer stuff
			$this->frm->getField('mailer_from_name')->isFilled(BL::getError('FieldIsRequired'));
			$this->frm->getField('mailer_from_email')->isEmail(BL::getError('EmailIsInvalid'));
			$this->frm->getField('mailer_to_name')->isFilled(BL::getError('FieldIsRequired'));
			$this->frm->getField('mailer_to_email')->isEmail(BL::getError('EmailIsInvalid'));
			$this->frm->getField('mailer_reply_to_name')->isFilled(BL::getError('FieldIsRequired'));
			$this->frm->getField('mailer_reply_to_email')->isEmail(BL::getError('EmailIsInvalid'));

			// date & time
			$this->frm->getField('time_format')->isFilled(BL::getError('FieldIsRequired'));
			$this->frm->getField('date_format_short')->isFilled(BL::getError('FieldIsRequired'));
			$this->frm->getField('date_format_long')->isFilled(BL::getError('FieldIsRequired'));

			// akismet key may be filled in
			if($this->needsAkismet && $this->frm->getField('akismet_key')->isFilled())
			{
				// key has changed
				if($this->frm->getField('akismet_key')->getValue() != BackendModel::getSetting('core', 'akismet_key', null))
				{
					 // load akismet
					 require_once PATH_LIBRARY .'/external/akismet.php';

					 // create instance
					$akismet = new Akismet($this->frm->getField('akismet_key')->getValue(), SITE_URL);

					// invalid key
					if(!$akismet->verifyKey()) $this->frm->getField('akismet_key')->setError(BL::getError('InvalidAPIKey'));
				}
			}

			// domains filled in
			if($this->frm->getField('site_domains')->isFilled())
			{
				// split on newlines
				$domains = explode("\n", trim($this->frm->getField('site_domains')->getValue()));

				// loop domains
				foreach($domains as $domain)
				{
					// strip funky stuff
					$domain = trim(str_replace(array('www.', 'http://', 'https://'), '', $domain));

					// invalid URL
					if(!SpoonFilter::isURL('http://'. $domain))
					{
						// set error
						$this->frm->getField('site_domains')->setError(BL::getError('InvalidDomain'));

						// stop looping domains
						break;
					}
				}
			}

			// no errors ?
			if($this->frm->isCorrect())
			{
				// general settings
				BackendModel::setSetting('core', 'site_title_'. BL::getWorkingLanguage(), $this->frm->getField('site_title')->getValue());
				BackendModel::setSetting('core', 'site_wide_html', $this->frm->getField('site_wide_html')->getValue());

				// e-mail settings
				BackendModel::setSetting('core', 'mailer_from', array('name' => $this->frm->getField('mailer_from_name')->getValue(), 'email' => $this->frm->getField('mailer_from_email')->getValue()));
				BackendModel::setSetting('core', 'mailer_to', array('name' => $this->frm->getField('mailer_to_name')->getValue(), 'email' => $this->frm->getField('mailer_to_email')->getValue()));
				BackendModel::setSetting('core', 'mailer_reply_to', array('name' => $this->frm->getField('mailer_reply_to_name')->getValue(), 'email' => $this->frm->getField('mailer_reply_to_email')->getValue()));

				// smtp settings
				BackendModel::setSetting('core', 'smtp_server', $this->frm->getField('smtp_server')->getValue());
				BackendModel::setSetting('core', 'smtp_port', $this->frm->getField('smtp_port')->getValue());
				BackendModel::setSetting('core', 'smtp_username', $this->frm->getField('smtp_username')->getValue());
				BackendModel::setSetting('core', 'smtp_password', $this->frm->getField('smtp_password')->getValue());

				// api keys
				BackendModel::setSetting('core', 'fork_api_public_key', $this->frm->getField('fork_api_public_key')->getValue());
				BackendModel::setSetting('core', 'fork_api_private_key', $this->frm->getField('fork_api_private_key')->getValue());
				if($this->needsAkismet) BackendModel::setSetting('core', 'akismet_key', $this->frm->getField('akismet_key')->getValue());
				if($this->needsGoogleMaps) BackendModel::setSetting('core', 'google_maps_key', $this->frm->getField('google_maps_key')->getValue());

				// theme
				BackendModel::setSetting('core', 'theme', $this->frm->getField('theme')->getValue());

				// date & time formats
				BackendModel::setSetting('core', 'time_format', $this->frm->getField('time_format')->getValue());
				BackendModel::setSetting('core', 'date_format_short', $this->frm->getField('date_format_short')->getValue());
				BackendModel::setSetting('core', 'date_format_long', $this->frm->getField('date_format_long')->getValue());

				// before we save the languages, we need to ensure that each language actually exists and may be chosen.
				$languages = array(SITE_DEFAULT_LANGUAGE);

				// save active languages
				BackendModel::setSetting('core', 'active_languages', array_unique(array_merge($languages, $this->frm->getField('active_languages')->getValue())));
				BackendModel::setSetting('core', 'redirect_languages', array_unique(array_merge($languages, $this->frm->getField('redirect_languages')->getValue())));

				// domains may not contain www, http or https. Therefor we must loop and create the list of domains.
				$siteDomains = array();

				// domains filled in
				if($this->frm->getField('site_domains')->isFilled())
				{
					// split on newlines
					$domains = explode("\n", trim($this->frm->getField('site_domains')->getValue()));

					// loop domains
					foreach($domains as $domain)
					{
						// strip funky stuff
						$siteDomains[] = trim(str_replace(array('www.', 'http://', 'https://'), '', $domain));
					}
				}

				// save domains
				BackendModel::setSetting('core', 'site_domains', $siteDomains);

				// assign report
				$this->tpl->assign('report', true);
				$this->tpl->assign('reportMessage', BL::getMessage('Saved'));
			}
		}
	}
}

?>