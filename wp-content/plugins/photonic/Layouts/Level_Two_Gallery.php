<?php
namespace Photonic_Plugin\Layouts;

use Photonic_Plugin\Components\Album_List;
use Photonic_Plugin\Modules\Core;

interface Level_Two_Gallery {
	/**
	 * Generates the HTML for a group of level-2 items, i.e. Photosets (Albums) and Galleries for Flickr, Albums for Google Photos,
	 * Albums for SmugMug, and Photosets (Galleries and Collections) for Zenfolio. No concept of albums
	 * exists in native WP and Instagram.
	 *
	 * @param Album_List $album_list
	 * @param $short_code
	 * @param $module Core
	 * @return string
	 */
	public function generate_level_2_gallery($album_list, $short_code, $module);
}
