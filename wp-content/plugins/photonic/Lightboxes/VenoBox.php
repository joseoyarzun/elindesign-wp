<?php

namespace Photonic_Plugin\Lightboxes;

use Photonic_Plugin\Modules\Core;
use Photonic_Plugin\Lightboxes\Features\Show_Videos_Inline;

require_once 'Lightbox.php';
require_once 'Features/Show_Videos_Inline.php';

class VenoBox extends Lightbox {
	use Show_Videos_Inline {
		get_video_markup as get_inline_video_markup;
		get_grid_link as get_inline_video_grid_link;
	}

	protected function __construct() {
		$this->library = 'venobox';
		parent::__construct();
	}

	/**
	 * @param $rel_id
	 * @param Core $module
	 * @return array
	 */
	public function get_gallery_attributes($rel_id, $module) {
		return [
			'class'    => $this->class,
			'rel'      => ['lightbox-photonic-' . $module->provider . '-stream-' . (empty($rel_id) ? $module->gallery_index : $rel_id)],
			'specific' => [
				'data-gall' => ['photonic-' . $module->provider . '-stream-' . (empty($rel_id) ? $module->gallery_index : $rel_id)]
			],
		];
	}

	public function get_photo_attributes($photo_data, $module) {
		$out = parent::get_photo_attributes($photo_data, $module);
		if (in_array($module->provider, ['google', 'flickr'], true)) {
			return $out . (!empty($photo_data['video']) ? ' data-vbtype="inline" data-html5-href="' . $photo_data['video'] . '" ' : '');
		}
		return $out . (!empty($photo_data['video']) ? ' data-vbtype="video"' : ' ');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_video_markup($photo, $module, $indent) {
		if (in_array($module->provider, ['flickr', 'google'], true)) {
			return $this->get_inline_video_markup($photo, $module, $indent);
		}
		else {
			return parent::get_video_markup($photo, $module, $indent);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_grid_link($photo, $short_code, $module = null) {
		if (!empty($photo->video) && in_array($module->provider, ['flickr', 'google'], true)) {
			return $this->get_inline_video_grid_link($photo, $short_code, $module);
		}
		else {
			return parent::get_grid_link($photo, $short_code, $module);
		}
	}
}
