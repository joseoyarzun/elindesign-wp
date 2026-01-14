<?php

namespace Photonic_Plugin\Lightboxes;

use Photonic_Plugin\Lightboxes\Features\Show_Videos_Inline;

require_once 'Lightbox.php';
require_once 'Features/Show_Videos_Inline.php';

class Colorbox extends Lightbox {
	use Show_Videos_Inline;

	protected function __construct() {
		$this->library = 'colorbox';
		parent::__construct();
	}

	public function get_photo_attributes($photo_data, $module) {
		$out = parent::get_photo_attributes($photo_data, $module);
		return $out . (!empty($photo_data['video']) ? ' data-html5-href="' . $photo_data['video'] . '" ' : '');
	}
}
