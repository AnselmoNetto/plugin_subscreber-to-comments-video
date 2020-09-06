<?php

add_action(  'after_theme_setup', 'custom_setup'  );

function custom_setup(){


}

define('PW_URL', get_home_url());
define('PW_THEME_URL','/wordpress/wp-content/themes/hyacinth/');
define('PW_SITE_NAME',get_bloginfo('name'));