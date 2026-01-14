<?php
namespace Photonic_Plugin\Modules;

require_once PHOTONIC_PATH . '/Components/Album.php';
require_once PHOTONIC_PATH . '/Components/Album_List.php';

interface Level_Two_Module {
	public function build_level_2_objects($objects_or_response, array $short_code, $filter_list = [], &$options = []);
}
