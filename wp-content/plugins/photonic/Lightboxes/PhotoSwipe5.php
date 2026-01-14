<?php

namespace Photonic_Plugin\Lightboxes;

use Photonic_Plugin\Lightboxes\Features\Show_Videos_Inline;

require_once 'Lightbox.php';
require_once 'Features/Show_Videos_Inline.php';

class PhotoSwipe5 extends Lightbox {
	use Show_Videos_Inline;

	protected function __construct() {
		$this->library = 'photoswipe5';
		parent::__construct();
	}

	public function get_photo_attributes($photo_data, $module) {
		$out = parent::get_photo_attributes($photo_data, $module);
		if (!empty($photo_data['height']) && !empty($photo_data['width'])) {
			$out .= ' data-pswp-height="' . $photo_data['height'] . '" data-pswp-width="' . $photo_data['width'] . '" ';
		}
		return $out . (!empty($photo_data['video']) ? ' data-html5-href="' . $photo_data['video'] . '" ' : '');
	}
}
