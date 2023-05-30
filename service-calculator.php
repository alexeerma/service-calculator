<?php
/*
Plugin Name: Service Calculator
Description: A calculator plugin for pricing services.
Version: 1.0
Author: Your Name
*/

// Create the shortcode for displaying the calculator form
function service_calculator_shortcode() {
    ob_start();
    service_calculator_display_form();
    return ob_get_clean();
}
add_shortcode('service_calculator', 'service_calculator_shortcode');

// Enqueue plugin styles and scripts
function service_calculator_enqueue_assets() {
    wp_enqueue_style('service-calculator-style', plugins_url('style.css', __FILE__));
    wp_enqueue_script('service-calculator-script', plugins_url('script.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'service_calculator_enqueue_assets');

// Display the calculator form
function service_calculator_display_form() {
    ob_start();
    ?>
    <form id="service-calculator-form">
        <div class="service-calculator-row">
            <label for="service-calculator-area">Area:</label>
            <?php service_calculator_display_dropdown_options('service_calculator_areas', 'area'); ?>
        </div>
        <div class="service-calculator-row">
            <label for="service-calculator-surface">Kind of Surface:</label>
            <?php service_calculator_display_dropdown_options('service_calculator_surfaces', 'surface'); ?>
        </div>
        <div class="service-calculator-row">
            <label for="service-calculator-washing-service">Washing Service Kind:</label>
            <?php service_calculator_display_dropdown_options('service_calculator_washing_services', 'washing_service'); ?>
        </div>
        <div class="service-calculator-row">
            <label for="service-calculator-square-meters">Approximate Square Meters:</label>
            <input type="number" name="service_calculator_square_meters" id="service-calculator-square-meters">
        </div>
        <div class="service-calculator-row">
            <label for="service-calculator-forklift">Need for Forklift:</label>
            <?php service_calculator_display_dropdown_options('service_calculator_forklifts', 'forklift'); ?>
        </div>
        <button type="button" id="service-calculator-calculate" class="service-calculator-submit">Calculate</button>
    </form>
    <div id="service-calculator-result"></div>
    <?php
    echo ob_get_clean();
}

// Display dropdown options
function service_calculator_display_dropdown_options($option_name, $input_name) {
    $options = get_option($option_name);
    echo '<select name="service_calculator_' . $input_name . '" id="service-calculator-' . $input_name . '">';
    if ($options) {
        foreach ($options as $option) {
            echo '<option value="' . esc_attr($option['price']) . '">' . esc_html($option['name']) . '</option>';
        }
    }
    echo '</select>';
}

// Calculate the price based on the form input
function service_calculator_calculate_price() {
    $areas = get_option('service_calculator_areas');
    $surfaces = get_option('service_calculator_surfaces');
    $washing_services = get_option('service_calculator_washing_services');
    $forklifts = get_option('service_calculator_forklifts');
    $square_meter_price = floatval(get_option('service_calculator_square_meter_price'));

    $area_price = isset($_POST['service_calculator_area']) ? floatval($_POST['service_calculator_area']) : 0;
    $surface_price = isset($_POST['service_calculator_surface']) ? floatval($_POST['service_calculator_surface']) : 0;
    $washing_service_price = isset($_POST['service_calculator_washing_service']) ? floatval($_POST['service_calculator_washing_service']) : 0;
    $square_meter_value = isset($_POST['service_calculator_square_meters']) ? floatval($_POST['service_calculator_square_meters']) : 0;
    $forklift_price = isset($_POST['service_calculator_forklift']) ? floatval($_POST['service_calculator_forklift']) : 0;

    $total_price = $area_price + $surface_price + $washing_service_price + ($square_meter_price * $square_meter_value) + $forklift_price;

    echo '<p>Total Price: $' . $total_price . '</p>';
    wp_die();
}
add_action('wp_ajax_service_calculator_calculate_price', 'service_calculator_calculate_price');
add_action('wp_ajax_nopriv_service_calculator_calculate_price', 'service_calculator_calculate_price');

// Create the admin settings page
function service_calculator_settings_init() {
    add_settings_section('service_calculator_section', 'Price Settings', function() {
        echo '<p>Please enter the prices for each value:</p>';
    }, 'service_calculator');

    service_calculator_add_settings_field('service_calculator_areas', 'Areas');
    service_calculator_add_settings_field('service_calculator_surfaces', 'Surfaces');
    service_calculator_add_settings_field('service_calculator_washing_services', 'Washing Services');
    service_calculator_add_settings_field('service_calculator_forklifts', 'Forklifts');
    service_calculator_add_settings_field('service_calculator_square_meter_price', 'Square Meter Price');

    register_setting('service_calculator', 'service_calculator_areas');
    register_setting('service_calculator', 'service_calculator_surfaces');
    register_setting('service_calculator', 'service_calculator_washing_services');
    register_setting('service_calculator', 'service_calculator_forklifts');
    register_setting('service_calculator', 'service_calculator_square_meter_price');
}
add_action('admin_init', 'service_calculator_settings_init');

// Add settings field to the admin settings page
function service_calculator_add_settings_field($option_name, $option_label) {
    add_settings_field($option_name, $option_label . ':', function() use ($option_name) {
        $options = get_option($option_name);
        ?>
        <ul id="service-calculator-<?php echo $option_name; ?>-list">
            <?php
            if ($options) {
                foreach ($options as $key => $option) {
                    echo '<li>';
                    echo '<input type="text" name="' . $option_name . '[' . $key . '][name]" value="' . esc_attr($option['name']) . '"> : ';
                    echo '<input type="number" name="' . $option_name . '[' . $key . '][price]" value="' . esc_attr($option['price']) . '">';
                    echo '<button type="button" class="service-calculator-delete-option" data-option="' . $option_name . '" data-key="' . $key . '">Delete</button>';
                    echo '</li>';
                }
            }
            ?>
        </ul>
        <button type="button" class="service-calculator-add-option" data-option="<?php echo $option_name; ?>">Add <?php echo $option_label; ?></button>
        <?php
    }, 'service_calculator', 'service_calculator_section');
}

// Create the admin settings page
function service_calculator_settings_page() {
    ?>
    <div class="wrap">
        <h1>Service Calculator Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('service_calculator');
            do_settings_sections('service_calculator');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Add the admin menu item
function service_calculator_add_admin_menu() {
    add_menu_page(
        'Service Calculator',
        'Service Calculator',
        'manage_options',
        'service-calculator',
        'service_calculator_settings_page',
        'dashicons-calculator',
        100
    );
}
add_action('admin_menu', 'service_calculator_add_admin_menu');

// Enqueue admin scripts
function service_calculator_admin_enqueue_assets($hook) {
    if ($hook === 'toplevel_page_service-calculator') {
        wp_enqueue_script('service-calculator-admin-script', plugins_url('admin-script.js', __FILE__), array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'service_calculator_admin_enqueue_assets');
