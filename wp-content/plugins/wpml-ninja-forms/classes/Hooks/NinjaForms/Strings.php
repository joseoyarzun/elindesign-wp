<?php

namespace WPML\Forms\Hooks\NinjaForms;

use NF_Database_Models_Field;
use WPML\Forms\Hooks\Registration;
use WPML\Forms\Translation\Package;
use WPML\FP\Obj;

class Strings extends Registration {

	const REPEATER_FIELD_TYPE = 'repeater';

	/**
	 * Get ignored field types
	 *
	 * @return array
	 */
	private function getIgnoredFieldTypes() {
		/**
		 * Get not translatable fields
		 *
		 * @since 0.3.0
		 *
		 * @param array $ignoredFieldTypes
		 */
		return apply_filters( 'wpml_ninjaforms_strings_ignored_field_types', [ 'liststate', 'hidden', 'label' ] );
	}

	/** Adds hooks. */
	public function addHooks() {
		parent::addHooks();
		add_action( 'nf_get_form_id', [ $this, 'setFormId' ] );
		add_filter( 'ninja_forms_display_form_settings', [ $this, 'applySettingsTranslation' ], 10, 2 );
		add_filter( 'ninja_forms_localize_fields', [ $this, 'applyFieldTranslation' ] );
		add_action( 'ninja_forms_save_form', [ $this, 'register' ] );
	}

	/**
	 * Applies translation to form settings.
	 *
	 * @param array $settings Form settings.
	 * @param int   $formId
	 *
	 * @return array
	 */
	public function applySettingsTranslation( array $settings, $formId ) {

		$this->setFormId( $formId );

		return $this->getPackage()->translateFormSettings( $settings );
	}

	/**
	 * Applies translations to form field.
	 *
	 * @param array $field Form field.
	 *
	 * @return array
	 */
	public function applyFieldTranslation( array $field ) {

		if (
			$this->getFormId() && $this->notEmpty( 'settings', $field )
			&& $this->notEmpty( 'type', $field['settings'] )
			&& $this->isTranslatableFieldType( $field['settings']['type'] )
			&& ! $this->isFieldInRepeater( $field )
		) {
			$field['settings'] = $this->getPackage()->translateField( $field['settings'], $this->getId( $field ) );
		}

		if ( isset( $field['settings']['type'] ) && self::REPEATER_FIELD_TYPE === $field['settings']['type'] ) {
			foreach ( $field['settings']['fields'] as $index => $subField ) {
				$field['settings']['fields'][ $index ] = $this->getPackage()->translateField( $subField, $subField['id'] );
			}
		}

		return $field;
	}

	/**
	 * Registers form for translation.
	 *
	 * @param int $formId Form ID.
	 */
	public function register( $formId ) {

		$form     = Ninja_Forms()->form( $formId );
		$package  = $this->newPackage( $formId );
		$settings = $form->get()->get_settings();
		$fields   = $form->get_fields();

		$package->registerFormSettings( $settings );
		foreach ( $fields as $field ) {
			$this->applyFormFieldRegistration( $field, $package );
		}

		do_action( 'wpml_forms_ninja_forms_register', $form, $package );
		$package->cleanup();
	}

	/**
	 * Adds forms info for bulk registration.
	 *
	 * @param array $items Array of form infos.
	 *
	 * @return array
	 */
	public function bulkRegistrationItems( array $items ) {

		$forms = Ninja_Forms()->form()->get_forms();
		if ( is_array( $forms ) ) {
			foreach ( $forms as $form ) {
				$items[] = $this->getBulkRegistrationItem( $form->get_id(), $form->get_setting( 'title' ) );
			}
		}

		return $items;
	}

	/**
	 * Registers forms for translation.
	 *
	 * @param array $forms Array of form IDs.
	 */
	public function bulkRegistration( array $forms ) {
		foreach ( $forms as $formId ) {
			$this->register( $formId );
		}
	}

	/**
	 * Register single form field for translation based on the field type
	 *
	 * @param NF_Database_Models_Field $field
	 * @param Package                  $package
	 *
	 * @return void
	 */
	private function applyFormFieldRegistration( $field, $package ) {

		$fieldType = $field->get_setting( 'type' );

		if ( $this->isTranslatableFieldType( $fieldType ) ) {

			$package->registerField( $field->get_id(), $field->get_settings() );

			if ( self::REPEATER_FIELD_TYPE === $fieldType && $field->get_settings()['fields'] ) {
				foreach ( $field->get_settings()['fields'] as $subfield ) {
					$package->registerField( $subfield['id'], $subfield );
				}
			}
		}
	}

	/**
	 * Checks if a field type is translatable
	 *
	 * @param string $fieldType
	 *
	 * @return bool
	 */
	private function isTranslatableFieldType( $fieldType ) {
		return ! in_array( $fieldType, $this->getIgnoredFieldTypes(), true );
	}

	/**
	 * @param array $field
	 *
	 * @return bool
	 */
	private function isFieldInRepeater( $field ) {
		return Obj::path( [ 'settings', 'repeaterField' ], $field );
	}

}
