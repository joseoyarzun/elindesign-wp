<?php

namespace Photonic_Plugin\Lightboxes;

require_once 'Lightbox.php';

class BigPicture extends Lightbox {
	protected function __construct() {
		$this->library = 'bigpicture';
		parent::__construct();
	}

	public function get_photo_attributes($photo_data, $module) {
		$out = parent::get_photo_attributes($photo_data, $module);
		if (!empty($photo_data['video'])) {
			return $out . ' data-bp="' . ($photo_data['video']) . '" data-bp-type="video"';
		}
		return $out . ' data-bp="' . (!empty($photo_data['image']) ? $photo_data['image'] : $photo_data['video']) . '" ';
	}
}
