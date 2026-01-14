<?php

namespace WPML\Forms\Loader;

use SitePress;
use WPML\Forms\Hooks\WpForms\ConversationalForms;
use WPML\Forms\Hooks\WpForms\DynamicChoices;
use WPML\Forms\Hooks\WpForms\EntryPreviewField;
use WPML\Forms\Hooks\WpForms\FormPages;
use WPML\Forms\Hooks\WpForms\Notifications;
use WPML\Forms\Hooks\WpForms\Package;
use WPML\Forms\Hooks\WpForms\Strings;
use WPML\Forms\Addons\WpForms\SaveAndResume;
use WPML\Forms\Translation\Factory;
use WPML\Forms\Addons\WpForms\SurveyAndPolls;


class WpForms extends Base {

	/** Gets package slug. */
	protected function getSlug() {
		return 'wpforms';
	}

	/** Gets package title. */
	protected function getTitle() {
		return 'WPForms';
	}

	/** Adds hooks. */
	protected function addHooks() {
		/** @var SitePress $sitepress */
		global $sitepress;

		$factory = new Factory( $this->preferences );

		$wpforms = new Strings(
			$this->getSlug(),
			$this->getTitle(),
			$factory,
			$sitepress
		);
		$wpforms->addHooks();

		$notifications = new Notifications(
			$this->getSlug(),
			$this->getTitle(),
			$factory
		);
		$notifications->addHooks();

		$package_filter = new Package( $this->getSlug() );
		$package_filter->addHooks();

		$conversational_forms = new ConversationalForms( $wpforms );
		$conversational_forms->addHooks();

		$dynamic_choices = new DynamicChoices();
		$dynamic_choices->addHooks();

		$form_pages = new FormPages( $wpforms );
		$form_pages->addHooks();

		$entryPreviewField = new EntryPreviewField(
			$this->getSlug(),
			$this->getTitle(),
			$factory,
			$sitepress
		);
		$entryPreviewField->addHooks();

		if ( defined( 'WPFORMS_SURVEYS_POLLS_VERSION' ) ) {
			$surveyAndPolls = new SurveyAndPolls(
				$this->getSlug(),
				$this->getTitle(),
				$factory
			);
			$surveyAndPolls->addHooks();
		}

		if ( defined( 'WPFORMS_SAVE_RESUME_VERSION' ) ) {
			$saveAndResume = new SaveAndResume(
				$this->getSlug(),
				$this->getTitle(),
				$factory
			);
			$saveAndResume->addHooks();
		}
	}
}
