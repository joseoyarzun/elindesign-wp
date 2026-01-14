<?php

namespace Photonic_Plugin\Core;

use Photonic_Plugin\Layouts\Core_Layout;
use Photonic_Plugin\Layouts\Grid;
use Photonic_Plugin\Layouts\Slideshow;
use Photonic_Plugin\Modules\Core;
use Photonic_Plugin\Modules\Flickr;
use Photonic_Plugin\Modules\Google_Photos;
use Photonic_Plugin\Modules\Instagram;
use Photonic_Plugin\Modules\Native;
use Photonic_Plugin\Modules\SmugMug;
use Photonic_Plugin\Modules\Zenfolio;

class Gallery {
	private $attr;
	/** @var Core */
	private $module;

	/** @var Core_Layout */
	private $layout;

	public function __construct($attr) {
		$this->attr = $attr;
		$type = $this->attr['type'];

		$this->set_module($type);

		if ((!empty($attr['layout']) && in_array($type, ['flickr', 'smugmug', 'google', 'zenfolio', 'instagram'], true) && in_array($attr['layout'], ['strip-above', 'strip-below', 'strip-right', 'no-strip'], true)) ||
			(!empty($attr['style']) && in_array($type, ['default', 'wp'], true) && in_array($attr['style'], ['strip-above', 'strip-below', 'strip-right', 'no-strip'], true))) {
			require_once PHOTONIC_PATH . '/Layouts/Slideshow.php';
			$this->layout = Slideshow::get_instance();
		}
		else {
			require_once PHOTONIC_PATH . '/Layouts/Grid.php';
			$this->layout = Grid::get_instance();
		}
	}

	private function set_module($type) {
		if ('flickr' === $type) {
			require_once PHOTONIC_PATH . "/Modules/Flickr.php";
			$this->module = Flickr::get_instance();
		}
		elseif ('smugmug' === $type || 'smug' === $type) {
			require_once PHOTONIC_PATH . "/Modules/SmugMug.php";
			$this->module = SmugMug::get_instance();
		}
		elseif ('google' === $type) {
			require_once PHOTONIC_PATH . "/Modules/Google_Photos.php";
			$this->module = Google_Photos::get_instance();
		}
		elseif ('instagram' === $type) {
			require_once PHOTONIC_PATH . "/Modules/Instagram.php";
			$this->module = Instagram::get_instance();
		}
		elseif ('zenfolio' === $type) {
			require_once PHOTONIC_PATH . "/Modules/Zenfolio.php";
			$this->module = Zenfolio::get_instance();
		}
		else {
			require_once PHOTONIC_PATH . "/Modules/Native.php";
			$this->module = Native::get_instance();
		}
	}

	/**
	 * Fetch the contents of a gallery. This first invokes the <code>get_gallery_images</code> method for each platform.
	 * Once the results are obtained, this method prints out the results.
	 *
	 * @return string
	 */
	public function get_contents(): string {
		$this->module->increment_gallery_index();
		$contents = $this->module->get_gallery_images($this->attr);
		if (is_array($contents)) {
			$output = '';
			foreach ($contents as $component) {
				if (method_exists($component, 'html')) {
					$output .= $component->html($this->module, $this->layout);
				}
				else {
					$output .= $component;
				}
			}
			return $this->finalize_markup($output);
		}
		return $contents;
	}

	public function get_helper_contents() {
		return $this->module->execute_helper($this->attr);
	}

	/**
	 * Wraps the output of a gallery in markup tags indicating that it is a Photonic gallery.
	 *
	 * @param $content
	 * @return string
	 */
	public function finalize_markup($content): string {
		if ('modal' !== $this->attr['display']) {
			$additional_classes = '';
			if (!empty($this->attr['custom_classes'])) {
				$additional_classes = $this->attr['custom_classes'];
			}
			if (!empty($this->attr['alignment'])) {
				$additional_classes .= ' align' . $this->attr['alignment'];
			}
			$ret = "<div class='photonic-{$this->module->provider}-stream photonic-stream $additional_classes' id='photonic-{$this->module->provider}-stream-{$this->module->gallery_index}'>\n";
		}
		else {
			$popup_id = "id='photonic-{$this->module->provider}-panel-" . $this->attr['panel'] . "'";
			$ret = "<div class='photonic-{$this->module->provider}-panel photonic-panel' $popup_id>\n";
		}
		$ret .= $content . "\n";
		$ret .= "</div><!-- .photonic-stream or .photonic-panel -->\n";
		return $ret;
	}
}
