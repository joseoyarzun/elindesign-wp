<?php

namespace WPML\Forms\Hooks\WpForms;

class FormPages {

	/** @var Strings */
	private $strings;

	public function __construct( Strings $strings ) {
		$this->strings = $strings;
	}

	public function addHooks() {
		add_filter(
			'wpforms_form_pages_frontend_handle_request_form_data',
			[ $this->strings, 'applyTranslations' ]
		);
	}
}
