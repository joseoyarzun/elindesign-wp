<?php

namespace Photonic_Plugin\Lightboxes\Features;

trait Show_Videos_Inline {
	public function get_video_id($photo, $module) {
		return $module->provider . '-' . $module->gallery_index . '-' . $photo->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_video_markup($photo, $module, $indent) {
		$ret = '';
		$video_id = $this->get_video_id($photo, $module);
		$width = !empty($photo->main_size) ? ('width="' . $photo->main_size['w'] . '"') : '';
		$height = !empty($photo->main_size) ? ('height="' . $photo->main_size['h'] . '"') : '';
		$poster = !empty($photo->main_image) ? ('poster="' . $photo->main_image . '"') : '';
		$ret .= $indent . "\t\t" . '<div class="photonic-html5-external" id="photonic-video-' . $video_id . '">' . "\n";
		$ret .= $indent . "\t\t\t" . '<video class="photonic" controls preload="none" ' . $width . ' ' . $height . ' ' . $poster . '>' . "\n";
		$ret .= $indent . "\t\t\t\t" . '<source src="' . $photo->video . '" type="' . ($photo->mime ?: 'video/mp4') . '">' . "\n";
		$ret .= $indent . "\t\t\t\t" . esc_html__('Your browser does not support HTML5 videos.', 'photonic') . "\n";
		$ret .= $indent . "\t\t\t" . '</video>' . "\n";
		$ret .= $indent . "\t\t" . '</div>' . "\n";
		return $ret;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_grid_link($photo, $short_code, $module = null) {
		if (!empty($photo->video)) {
			return esc_attr('#photonic-video-' . $this->get_video_id($photo, $module));
		}
		else {
			return esc_url($photo->main_image);
		}
	}
}
