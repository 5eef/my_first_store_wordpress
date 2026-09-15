<?php

declare(strict_types=1);

namespace SeefStore;

use SeefStore\Admin\ContactAdmin;
use SeefStore\Controllers\ContactController;
use SeefStore\Controllers\NewsletterController;
use SeefStore\Frontend\ContactForm;
use SeefStore\Frontend\LocaleSwitcher;
use SeefStore\Frontend\ProductFilters;
use SeefStore\Security\Headers;
use SeefStore\Security\LoginRateLimiter;
use SeefStore\Support\Service;
use SeefStore\Support\Translator;

final class Plugin {
	private static ?self $instance = null;

	/** @var list<Service> */
	private array $services = array();

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function boot(): void {
		load_plugin_textdomain( 'seef-store-core', false, dirname( plugin_basename( SEEF_STORE_CORE_FILE ) ) . '/languages' );

		$this->services = array(
			new Translator(),
			new LocaleSwitcher(),
			new ContactController(),
			new NewsletterController(),
			new ContactForm(),
			new ContactAdmin(),
			new ProductFilters(),
			new LoginRateLimiter(),
			new Headers(),
		);

		foreach ( $this->services as $service ) {
			$service->register();
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
		}
	}

	public function woocommerce_notice(): void {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'SEEF Store Core nécessite WooCommerce.', 'seef-store-core' ) . '</p></div>';
		}
	}
}
