<?php

namespace Photonic_Plugin\Lightboxes;

require_once 'Lightbox.php';

class Lightgallery extends Lightbox {
	protected function __construct() {
		$this->library = 'lightgallery';
		parent::__construct();
	}

	public function get_photo_attributes($photo_data, $module) {
		$out      = parent::get_photo_attributes($photo_data, $module);
		$download = !empty($photo_data['download']) ? 'data-download-url="' . $photo_data['download'] . '" ' : '';
		$video    = !empty($photo_data['video']) ? " data-video='{\"source\": [{\"src\": \"" . $photo_data['video'] . "\", \"type\": \"video/mp4\"}], \"attributes\": {\"preload\": false, \"playsinline\": true, \"controls\": true}}' " : '';
		// $video    = !empty($photo_data['video']) ? ' data-html="#photonic-video-' . $module->provider . '-' . $module->gallery_index . '-' . $photo_data['id'] . '" ' : '';
		return $out . ' data-sub-html="' . $photo_data['title'] . '" ' . $video . $download;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_grid_link($photo, $short_code, $module) {
		if (!empty($photo->video)) {
			return '';
		}
		return parent::get_grid_link($photo, $short_code, $module);
	}
}
