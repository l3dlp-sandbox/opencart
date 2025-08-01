<?php
namespace Opencart\Admin\Controller\Task\Admin;
/**
 * Class Sass
 *
 * @package Opencart\Admin\Controller\Task\Admin
 */
class Sass extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @throws \Exception\ScssPhp\ScssPhp\Exception\SassException
	 *
	 * @return void
	 */
	public function index(): void {
		$files = glob(DIR_APPLICATION . 'view/stylesheet/*.scss');

		if ($files) {
			foreach ($files as $file) {
				// Get the filename
				$filename = basename($file, '.scss');

				$stylesheet = DIR_APPLICATION . 'view/stylesheet/' . $filename . '.css';

				$scss = new \ScssPhp\ScssPhp\Compiler();
				$scss->setImportPaths(DIR_APPLICATION . 'view/stylesheet/');

				$output = $scss->compileString('@import "' . $filename . '.scss"')->getCss();

				$handle = fopen($stylesheet, 'w');

				flock($handle, LOCK_EX);

				fwrite($handle, $output);

				fflush($handle);

				flock($handle, LOCK_UN);

				fclose($handle);
			}
		}
	}
}
