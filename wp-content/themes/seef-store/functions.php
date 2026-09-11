<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/Theme.php';
require_once get_template_directory() . '/inc/template-tags.php';

SeefStoreTheme\Theme::instance()->boot();
