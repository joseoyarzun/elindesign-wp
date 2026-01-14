<?php
namespace Photonic_Plugin\Layouts;

use Photonic_Plugin\Components\Photo_List;
use Photonic_Plugin\Modules\Core;

interface Level_One_Gallery {
	/**
	 * Generates the HTML for the lowest level gallery, i.e. the photos. This is used for local, modal and template displays.
	 * The code for the random layouts is handled in JS, but just the HTML markers for it are provided here.
	 *
	 * @param Photo_List $photo_list
	 * @param $short_code
	 * @param $module Core
	 * @return string
	 */
	public function generate_level_1_gallery($photo_list, $short_code, $module);
}
