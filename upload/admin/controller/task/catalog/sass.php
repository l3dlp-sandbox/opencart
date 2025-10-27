<?php
namespace Opencart\Admin\Controller\Task\Catalog;
/**
 * Class SASS
 *
 * Can be loaded using $this->load->controller('task/catalog/sass');
 *
 * @package Opencart\Admin\Controller\Task\Catalog
 */
class Sass extends \Opencart\System\Engine\Controller {
	/**
	 * SASS Admin
	 *
	 * Generate admin SASS file.
	 *
	 * @return array
	 */
	public function index(array $args = []): array {
		$this->load->language('task/catalog/sass');

		// Before we delete we need to make sure there is a sass file to regenerate the css
		$file = DIR_CATALOG . 'view/sass/stylesheet.scss';

		if (!is_file($file)) {
			return ['error' => sprintf($this->language->get('error_file'), $file)];
		}

		$filename = basename($file, '.scss');
		$directory = dirname($file) . '/';

		$stylesheet = DIR_CATALOG . 'view/stylesheet/' . $filename . '.css';

		if (is_file($stylesheet)) {
			unlink($stylesheet);
		}

		$scss = new \ScssPhp\ScssPhp\Compiler();
		$scss->setImportPaths($directory);

		$output = $scss->compileString('@import "' . $filename . '.scss"')->getCss();

		$handle = fopen($stylesheet, 'w');

		fwrite($handle, $output);

		fclose($handle);

		return ['success' => $this->language->get('text_success')];
	}
}
