<?php

namespace Photonic_Plugin\Lightboxes;

require_once 'Lightbox.php';

class Thickbox extends Lightbox {
	protected function __construct() {
		$this->library = 'thickbox';
		parent::__construct();
	}

	public function get_lightbox_title($photo, $module, $title, $alt_title, $target) {
		return ($title ?: $alt_title);
	}
}
