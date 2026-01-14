<?php

namespace Photonic_Plugin\Lightboxes;

require_once 'Lightbox.php';

class Spotlight extends Lightbox {
	protected function __construct() {
		$this->library = 'spotlight';
		parent::__construct();
		$this->class = ['photonic-lb', 'photonic-spotlight'];
	}

	public function get_container_classes() {
		return "spotlight-group";
	}

	public function get_photo_attributes($photo_data, $module) {
		$out = parent::get_photo_attributes($photo_data, $module);
		return $out . (!empty($photo_data['video']) ? ' data-src-mp4="' . $photo_data['video'] . '" data-media="video" data-poster="' . $photo_data['poster'] . '"' : '');
	}
}
