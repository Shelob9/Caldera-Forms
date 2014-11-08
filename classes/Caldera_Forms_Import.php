<?php
/**
 * Importer for Caldera Forms
 *
 * @package   @caldera-forms
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 */

class Caldera_Forms_Import {

	/**
	 * Import multiple files from a directory
	 *
	 * @param string|null $path Optional. File path to directory to import forms from. if null, value of option "caldera_forms_import_dir" is used.
	 *
	 * @since 0.1.9.1
	 */
	static public function import_forms( $path = null ) {
		$files = self::files( $path );

		if ( is_array( $files ) ) {
			$path = self::form_directory();
			foreach( $files as $file ) {
				$file = trailingslashit( $path ) . $file;
				self::import_form( $file );
			}

		}

	}

	/**
	 * Import a form
	 *
	 * @since 0.1.9.1
	 *
	 * @param string $file Path to file to import
	 */
	static public function import_form( $file ) {
		if ( file_exists( $file ) ) {
			$data = json_decode(file_get_contents( $file ), true);
			if(isset($data['ID']) && isset($data['name']) && isset($data['fields'])){

				// get form registry
				$forms = get_option( '_caldera_forms' );

				// return if already exists
				if( isset( $forms[$data['ID']] ) ){
					return;
				}

				// if a new install and no registery
				if(empty($forms)){
					$forms = array();
				}

				// add form to registry
				$forms[$data['ID']] = $data;

				// remove undeeded settings for registry
				if(isset($forms[$data['ID']]['layout_grid'])){
					unset($forms[$data['ID']]['layout_grid']);
				}
				if(isset($forms[$data['ID']]['fields'])){
					unset($forms[$data['ID']]['fields']);
				}
				if(isset($forms[$data['ID']]['processors'])){
					unset($forms[$data['ID']]['processors']);
				}
				if(isset($forms[$data['ID']]['settings'])){
					unset($forms[$data['ID']]['settings']);
				}

				// add from to list
				update_option($data['ID'], $data);
				do_action('caldera_forms_import_form', $data);

				update_option( '_caldera_forms', $forms );
				do_action('caldera_forms_save_form_register', $data);

				return __( sprintf( 'Import of form from % achieved!', $file ), 'caldera-forms' );

			}
			else {
				return new \WP_Error( 'caldera-forms-bad-import-file', __( 'Import file is invalid.', 'caldera-forms' ) );
			}
		}
		else {
			return new \WP_Error( 'caldera-forms-no-import-file', __( 'No import file found:(', 'caldera-forms' ) );
		}

	}

	/**
	 * Get all form files to import
	 *
	 * @param string|null $bool Optional. File path to Director of . if null, value of option "caldera_forms_import_dir" is used.
	 *
	 * @since 0.1.9.1
	 *
	 * @return array|bool
	 */
	static private function files( $dir = null ) {
		$dir = self::form_directory( $dir );
		if ( ! $dir ) {
			return false;
		}

		$forms = false;
		$files = scandir( $dir  );
		foreach ( $files as $file  ) {
			$path = pathinfo( $file, PATHINFO_EXTENSION );
			if ( 'json' == $path ) {
				$forms[] = $file;
			}
		}

		if ( is_array( $forms ) ) {
			return $forms;

		}
		else {
			return new WP_Error( 'caldera-form-import-directory-bad', __( 'Import Directory is bad', 'caldera-forms' ) );''
		}

	}

	/**
	 * Get the directory to import from.
	 *
	 * @since 0.1.9.1
	 *
	 * @param string|null $path Optional. File path to import form. if null, value of option "caldera_forms_import_dir" is used.
	 *
	 * @return string|bool
	 */
	static private function form_directory( $dir = null ) {

		if( is_null( $dir ) ) {
			$dir = get_option( 'caldera_forms_import_dir', null );
		}

		if ( $dir && file_exists( $dir ) ) {
			return $dir;
		}

	}

} 
