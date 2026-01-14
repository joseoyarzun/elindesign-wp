<?php

namespace WPML\Forms\Hooks\NinjaForms;

class Package {

	/** @var string $kind_slug Translation package kind slug. */
	private $kind_slug;

	/**
	 * Package constructor.
	 *
	 * @param string $kind_slug Translation package kind slug.
	 */
	public function __construct( $kind_slug ) {
		$this->kind_slug = $kind_slug;
	}

	/** Adds hooks. */
	public function addHooks() {
		add_filter( "wpml_forms_{$this->kind_slug}_package", array( $this, 'applyFilter' ), 10, 2 );
	}

	/**
	 * Applies filter to translation package properties.
	 *
	 * @param array $package Translation package configuration.
	 * @param int   $formId Form ID.
	 *
	 * @return array
	 */
	public function applyFilter( $package, $formId ) {
		$package['title']     = Ninja_Forms()->form( $formId )->get()->get_setting( 'title' );
		$package['edit_link'] = esc_url(
			add_query_arg(
				array(
					'form_id' => $formId,
					'tab'     => 'builder',
				),
				admin_url( 'admin.php' )
			)
		);
		$package['view_link'] = esc_url( add_query_arg( 'nf_preview_form', $formId, site_url() ) );

		return $package;
	}
}
