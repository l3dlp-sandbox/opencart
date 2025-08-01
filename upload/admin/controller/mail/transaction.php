<?php
namespace Opencart\Admin\Controller\Mail;
/**
 * Class Transaction
 *
 * @package Opencart\Admin\Controller\Mail
 */
class Transaction extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * admin/model/customer/customer.addTransaction/after
	 *
	 * @param string            $route
	 * @param array<int, mixed> $args
	 * @param mixed             $output
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function index(string &$route, array &$args, &$output): void {
		if (isset($args[0])) {
			$customer_id = $args[0];
		} else {
			$customer_id = 0;
		}

		if (isset($args[1])) {
			$description = $args[1];
		} else {
			$description = '';
		}

		if (isset($args[2])) {
			$amount = $args[2];
		} else {
			$amount = 0;
		}

		if (isset($args[3])) {
			$order_id = $args[3];
		} else {
			$order_id = 0;
		}

		// Customer
		$this->load->model('customer/customer');

		$customer_info = $this->model_customer_customer->getCustomer($customer_id);

		if (!$customer_info) {
			return;
		}

		$this->load->language('mail/transaction');

		// Setting
		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

		if ($store_info) {
			$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
			$store_url = $store_info['store_url'];
		} else {
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_url = $this->config->get('config_url');
		}

		// Language
		$this->load->model('localisation/language');

		$language_info = $this->model_localisation_language->getLanguage($customer_info['language_id']);

		if ($language_info) {
			$language_code = $language_info['code'];
		} else {
			$language_code = $this->config->get('config_language');
		}

		$this->load->language('default', 'mail', $language_code);
		$this->load->language('mail/transaction', 'mail', $language_code);

		$subject = sprintf($this->language->get('mail_text_subject'), $store_name);

		$data['text_received'] = sprintf($this->language->get('mail_text_received'), $this->currency->format($amount, $this->config->get('config_currency')));
		$data['text_total'] = sprintf($this->language->get('mail_text_total'), $this->currency->format($this->model_customer_customer->getTransactionTotal($customer_id), $this->config->get('config_currency')));

		$data['store'] = $store_name;
		$data['store_url'] = $store_url;

		$task_data = [
			'code'   => 'mail_transaction',
			'action' => 'admin/mail',
			'args'   => [
				'to'      => $customer_info['email'],
				'from'    => $this->config->get('config_email'),
				'sender'  => $store_name,
				'subject' => $subject,
				'content' => $this->load->view('mail/transaction', $data)
			]
		];

		$this->load->model('setting/task');

		$this->model_setting_task->addTask($task_data);
	}
}
