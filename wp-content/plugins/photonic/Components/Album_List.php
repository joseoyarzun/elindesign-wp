<?php

namespace Photonic_Plugin\Components;

use Photonic_Plugin\Layouts\Core_Layout;
use Photonic_Plugin\Modules\Core;

require_once 'Pagination.php';

class Album_List implements Printable {
	public $albums = [];
	public $row_constraints = [];
	public $type;
	public $singular_type;
	public $title_position;
	public $level_1_count_display;
	public $indent = '';
	public $short_code;

	/**
	 * @var Pagination $pagination
	 */
	public $pagination;

	public function __construct(array $short_code) {
		$this->pagination = new Pagination();
		$this->short_code = $short_code;
	}

	/**
	 * {@inheritDoc}
	 */
	public function html(Core $module, Core_Layout $layout, $print = false) {
		if (is_a($layout, 'Photonic_Plugin\Layouts\Level_Two_Gallery')) {
			return $layout->generate_level_2_gallery($this, $this->short_code, $module);
		}
		return '';
	}
}
