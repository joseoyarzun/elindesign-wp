<?php

namespace Photonic_Plugin\Add_Ons\Beaver;

class Beaver_Module extends \FLBuilderModule {
	public function __construct() {
		parent::__construct(
			[
				'name'        => __('Photonic', 'photonic'),
				'description' => __('A module to display a Photonic gallery', 'photonic'),
				'group'       => __('Photonic', 'photonic'),
				'category'    => __('Gallery', 'photonic'),
			]
		);
	}
}
