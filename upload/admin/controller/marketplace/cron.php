<?php
namespace Opencart\Admin\Controller\Marketplace;
/**
 * Class Cron
 *
 * @package Opencart\Admin\Controller\Marketplace
 */
class Cron extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('marketplace/cron');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['delete'] = $this->url->link('marketplace/cron.delete', 'user_token=' . $this->session->data['user_token']);
		$data['enable']	= $this->url->link('marketplace/cron.enable', 'user_token=' . $this->session->data['user_token']);
		$data['disable'] = $this->url->link('marketplace/cron.disable', 'user_token=' . $this->session->data['user_token']);

		// Example cron URL
		$data['cron'] = DIR_APPLICATION . 'index.php';

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/cron', $data));
	}

	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		$this->load->language('marketplace/cron');

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	public function getList(): string {
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('marketplace/cron.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['crons'] = [];

		$filter_data = [
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model('setting/cron');

		$results = $this->model_setting_cron->getCrons($filter_data);

		foreach ($results as $result) {
			$data['crons'][] = [
				'cycle' => $this->language->get('text_' . $result['cycle']),
				'run'   => $this->url->link('marketplace/cron.run', 'user_token=' . $this->session->data['user_token'] . '&cron_id=' . $result['cron_id'])
			] + $result;
		}

		$cron_total = $this->model_setting_cron->getTotalCrons();

		// Pagination
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $cron_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('marketplace/cron.list', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($cron_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($cron_total - $this->config->get('config_pagination_admin'))) ? $cron_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $cron_total, ceil($cron_total / $this->config->get('config_pagination_admin')));

		return $this->load->view('marketplace/cron_list', $data);
	}

	/**
	 * Run
	 *
	 * @return void
	 */
	public function run(): void {
		$this->load->language('marketplace/cron');

		$json = [];

		if (isset($this->request->get['cron_id'])) {
			$cron_id = (int)$this->request->get['cron_id'];
		} else {
			$cron_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Cron
		$this->load->model('setting/cron');

		$cron_info = $this->model_setting_cron->getCron($cron_id);

		if (!$cron_info) {
			$json['error'] = $this->language->get('error_exists');
		}

		if (!$json) {
			// Create a store instance using loader class to call controllers, models, views, libraries
			$this->load->model('setting/store');

			$store = $this->model_setting_store->createStoreInstance(0, $this->config->get('config_language'));

			$store->load->controller($cron_info['action'], $cron_id, $cron_info['code'], $cron_info['cycle'], $cron_info['date_added'], $cron_info['date_modified']);

			$store->session->destroy();

			$this->model_setting_cron->editCron($cron_info['cron_id']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Enable
	 *
	 * @return void
	 */
	public function enable(): void {
		$this->load->language('marketplace/cron');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = (array)$this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/cron');

			foreach ($selected as $cron_id) {
				$this->model_setting_cron->editStatus((int)$cron_id, true);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Disable
	 *
	 * @return void
	 */
	public function disable(): void {
		$this->load->language('marketplace/cron');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = (array)$this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/cron');

			foreach ($selected as $cron_id) {
				$this->model_setting_cron->editStatus((int)$cron_id, false);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Delete
	 *
	 * @return void
	 */
	public function delete(): void {
		$this->load->language('marketplace/cron');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = (array)$this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'marketplace/event')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// Event
			$this->load->model('setting/cron');

			foreach ($selected as $cron_id) {
				$this->model_setting_cron->deleteCron((int)$cron_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
