<?php
/**
 * Plugin Name: Digikala Product Importer
 * Plugin URI: https://your-website.com
 * Description: وارد کردن محصولات از دیجی‌کالا به ووکامرس با پشتیبانی کامل از محصولات متغیر و ورود گروهی
 * Version: 2.1.0
 * Author: میثم نوبهار
 * Text Domain: digikala-importer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

defined('ABSPATH') || exit;

// تعریف ثوابت پلاگین
define('DPI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DPI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DPI_VERSION', '2.1.0');
define('DPI_CACHE_TIME', HOUR_IN_SECONDS * 2);

// Include helper functions
if (file_exists(DPI_PLUGIN_PATH . 'includes/helper-functions.php')) {
    require_once DPI_PLUGIN_PATH . 'includes/helper-functions.php';
}

class DigikalaProductImporter {
    
    private $category_mappings = [
        'mobile-phone' => 'گوشی موبایل',
        'laptop' => 'لپ‌تاپ', 
        'tablet' => 'تبلت',
        'smart-watch' => 'ساعت هوشمند',
        'headphone' => 'هدفون',
        'speaker' => 'اسپیکر',
        'powerbank' => 'پاوربانک',
        'cable' => 'کابل و مبدل'
    ];
    
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // AJAX actions for single product
        add_action('wp_ajax_dpi_fetch_product', [$this, 'ajax_fetch_product']);
        add_action('wp_ajax_dpi_import_product', [$this, 'ajax_import_product']);
        
        // AJAX actions for bulk import
        add_action('wp_ajax_dpi_fetch_category_products', [$this, 'ajax_fetch_category_products']);
        add_action('wp_ajax_dpi_bulk_import_products', [$this, 'ajax_bulk_import_products']);
        
        // AJAX action for price sync
        add_action('wp_ajax_dpi_sync_prices', [$this, 'ajax_sync_prices']);
        
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // Schedule price sync
        add_action('dpi_daily_price_sync', [$this, 'scheduled_price_sync']);
        add_action('wp', [$this, 'schedule_price_sync']);
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate_plugin']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        load_plugin_textdomain('digikala-importer', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        
        // Create product_brand taxonomy if not exists
        $this->create_brand_taxonomy();
    }

    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p><strong>Digikala Product Importer</strong> برای کار کردن به افزونه WooCommerce نیاز دارد.</p></div>';
    }

    public function add_admin_menu() {
        add_menu_page(
            'Digikala Product Importer',
            'وارد کردن از دیجی‌کالا',
            'manage_woocommerce',
            'digikala-importer',
            [$this, 'admin_page'],
            'dashicons-download',
            56
        );
        
        // Add submenu pages
        add_submenu_page(
            'digikala-importer',
            'ورود تکی محصول',
            'ورود تکی',
            'manage_woocommerce',
            'digikala-importer',
            [$this, 'admin_page']
        );
        
        add_submenu_page(
            'digikala-importer',
            'ورود گروهی محصولات',
            'ورود گروهی',
            'manage_woocommerce',
            'digikala-bulk-import',
            [$this, 'bulk_import_page']
        );
        
        add_submenu_page(
            'digikala-importer',
            'هماهنگ‌سازی قیمت',
            'هماهنگ‌سازی قیمت',
            'manage_woocommerce',
            'digikala-price-sync',
            [$this, 'price_sync_page']
        );
    }

    public function enqueue_scripts($hook) {
        // بررسی تمام صفحات مربوط به افزونه
        if (strpos($hook, 'digikala-') === false) {
            return;
        }

        wp_enqueue_style('dpi-admin', DPI_PLUGIN_URL . 'assets/admin.css', [], DPI_VERSION);
        wp_enqueue_script('dpi-admin', DPI_PLUGIN_URL . 'assets/admin.js', ['jquery'], DPI_VERSION, true);
        
        wp_localize_script('dpi-admin', 'dpi_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dpi_nonce'),
            'i18n' => [
                'invalid_url' => __('لطفاً لینک محصول را وارد کنید', 'digikala-importer'),
                'processing' => __('در حال پردازش...', 'digikala-importer'),
                'error_fetching' => __('خطا در دریافت اطلاعات', 'digikala-importer'),
                'server_error' => __('خطای سرور', 'digikala-importer'),
                'preview_first' => __('ابتدا پیش‌نمایش محصول را مشاهده کنید', 'digikala-importer'),
                'import_error' => __('خطا در وارد کردن محصول', 'digikala-importer'),
                'select_products' => __('لطفاً حداقل یک محصول انتخاب کنید', 'digikala-importer'),
                'bulk_import_started' => __('شروع ورود گروهی محصولات', 'digikala-importer'),
                'price_sync_started' => __('شروع هماهنگ‌سازی قیمت‌ها', 'digikala-importer')
            ]
        ]);
    }

    public function admin_page() {
        include DPI_PLUGIN_PATH . 'templates/admin-page.php';
    }

    public function bulk_import_page() {
        include DPI_PLUGIN_PATH . 'templates/bulk-import-page.php';
    }

    public function price_sync_page() {
        include DPI_PLUGIN_PATH . 'templates/price-sync-page.php';
    }

    // Single Product Import Functions
    public function ajax_fetch_product() {
        check_ajax_referer('dpi_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'digikala-importer')]);
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

        try {
            $product_id = $this->extract_product_id($url);
            $digikala_data = $this->fetch_digikala_product($product_id);
            
            wp_send_json_success([
                'product' => $digikala_data,
                'html' => $this->generate_preview_html($digikala_data)
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function ajax_import_product() {
        check_ajax_referer('dpi_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'digikala-importer')]);
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $custom_fields = isset($_POST['custom_fields']) ? $this->sanitize_custom_fields($_POST['custom_fields']) : [];
        $image_settings = isset($_POST['image_settings']) ? $this->sanitize_image_settings($_POST['image_settings']) : [];
        $selected_specs = isset($_POST['selected_specs']) ? array_map('sanitize_text_field', $_POST['selected_specs']) : [];
        $selected_variants = isset($_POST['selected_variants']) ? $this->sanitize_selected_variants($_POST['selected_variants']) : [];
        $single_product_price = isset($_POST['single_product_price']) ? floatval($_POST['single_product_price']) : 0;

        try {
            $product_id = $this->extract_product_id($url);
            $digikala_data = $this->fetch_digikala_product($product_id);
            $wc_product_id = $this->import_product($digikala_data, $custom_fields, $image_settings, $selected_specs, $selected_variants, $single_product_price);

            wp_send_json_success([
                'message' => __('محصول با موفقیت وارد شد', 'digikala-importer'),
                'product_id' => $wc_product_id,
                'product_url' => get_permalink($wc_product_id),
                'edit_url' => admin_url('post.php?post=' . $wc_product_id . '&action=edit')
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    // Bulk Import Functions
    public function ajax_fetch_category_products() {
        check_ajax_referer('dpi_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'digikala-importer')]);
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;

        try {
            $category_data = $this->parse_category_url($url);
            $products_data = $this->fetch_category_products_v2($category_data, $page);
            
            wp_send_json_success([
                'products' => $products_data['products'],
                'pagination' => $products_data['pagination'],
                'category_info' => $products_data['category_info']
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function ajax_bulk_import_products() {
        check_ajax_referer('dpi_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'digikala-importer')]);
        }

        // افزایش زمان اجرای script برای پردازش گروهی
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
        $custom_fields = isset($_POST['custom_fields']) ? $this->sanitize_custom_fields($_POST['custom_fields']) : [];
        $image_settings = isset($_POST['image_settings']) ? $this->sanitize_image_settings($_POST['image_settings']) : [];

        if (empty($product_ids)) {
            wp_send_json_error(['message' => __('هیچ محصولی انتخاب نشده است', 'digikala-importer')]);
        }

        $results = [];
        $success_count = 0;
        $error_count = 0;

        foreach ($product_ids as $product_id) {
            try {
                // فراخوانی API برای دریافت جزئیات کامل محصول
                $digikala_data = $this->fetch_digikala_product($product_id);
                
                // استفاده از منطق ورود محصول تکی
                $wc_product_id = $this->import_product($digikala_data, $custom_fields, $image_settings, [], [], 0);

                $results[] = [
                    'product_id' => $product_id,
                    'status' => 'success',
                    'wc_product_id' => $wc_product_id,
                    'title' => $digikala_data['title_fa']
                ];
                $success_count++;

                // تأخیر برای جلوگیری از فشار بر سرور
                sleep(1);

            } catch (Exception $e) {
                $results[] = [
                    'product_id' => $product_id,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $error_count++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(__('%d محصول با موفقیت وارد شد، %d محصول با خطا مواجه شد', 'digikala-importer'), $success_count, $error_count),
            'results' => $results,
            'success_count' => $success_count,
            'error_count' => $error_count
        ]);
    }

    // Price Sync Functions
    public function ajax_sync_prices() {
        check_ajax_referer('dpi_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'digikala-importer')]);
        }

        $results = $this->sync_all_prices();

        wp_send_json_success([
            'message' => sprintf(__('%d محصول بروزرسانی شد', 'digikala-importer'), $results['updated_count']),
            'results' => $results
        ]);
    }

    public function sync_all_prices() {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_sku',
                    'value' => '',
                    'compare' => '!='
                ]
            ]
        ];

        $products = get_posts($args);
        $updated_count = 0;
        $results = [];

        foreach ($products as $post) {
            $product = wc_get_product($post->ID);
            $sku = $product->get_sku();

            if (is_numeric($sku)) {
                try {
                    $digikala_data = $this->fetch_digikala_product($sku);
                    $this->update_product_prices($product, $digikala_data);
                    $updated_count++;
                    
                    $results[] = [
                        'product_id' => $post->ID,
                        'sku' => $sku,
                        'status' => 'updated',
                        'title' => $product->get_name()
                    ];

                    // Add delay to prevent server overload
                    sleep(1);

                } catch (Exception $e) {
                    $results[] = [
                        'product_id' => $post->ID,
                        'sku' => $sku,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        update_option('dpi_last_sync_time', time());

        return [
            'updated_count' => $updated_count,
            'results' => $results
        ];
    }

    private function update_product_prices($product, $digikala_data) {
        if ($product->is_type('variable')) {
            $variations = $product->get_children();
            foreach ($variations as $variation_id) {
                $variation = wc_get_product($variation_id);
                $variation_sku = $variation->get_sku();
                
                // Find matching variant in digikala data
                if (isset($digikala_data['variants'])) {
                    foreach ($digikala_data['variants'] as $variant) {
                        if ($variant['id'] == $variation_sku) {
                            $price = $variant['price']['selling_price'] / 10;
                            $variation->set_regular_price($price);
                            $variation->set_price($price);
                            $variation->save();
                            break;
                        }
                    }
                }
            }
        } else {
            if (isset($digikala_data['default_variant']['price']['selling_price'])) {
                $price = $digikala_data['default_variant']['price']['selling_price'] / 10;
                $product->set_regular_price($price);
                $product->set_price($price);
                $product->save();
            }
        }
    }

    public function schedule_price_sync() {
        if (!wp_next_scheduled('dpi_daily_price_sync')) {
            wp_schedule_event(time(), 'twicedaily', 'dpi_daily_price_sync');
        }
    }

    public function scheduled_price_sync() {
        $this->sync_all_prices();
    }

    public function activate_plugin() {
        $this->create_brand_taxonomy();
        $this->schedule_price_sync();
        flush_rewrite_rules();
    }

    public function deactivate_plugin() {
        wp_clear_scheduled_hook('dpi_daily_price_sync');
        flush_rewrite_rules();
    }

    private function create_brand_taxonomy() {
        if (!taxonomy_exists('product_brand')) {
            register_taxonomy('product_brand', 'product', [
                'label' => __('برند', 'digikala-importer'),
                'hierarchical' => false,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_tagcloud' => false,
                'query_var' => true,
                'rewrite' => ['slug' => 'brand']
            ]);
        }
    }

    private function parse_category_url($url) {
        if (!preg_match('/digikala\.com/i', $url)) {
            throw new Exception(__('لطفاً فقط لینک‌های دیجی‌کالا را وارد کنید', 'digikala-importer'));
        }

        $category = null;
        $brand = null;

        // Extract category from different URL patterns
        if (preg_match('/category-([^\/\?]+)/', $url, $matches)) {
            $category = $matches[1];
        } elseif (preg_match('/search\/([^\/\?]+)/', $url, $matches)) {
            $category = $matches[1];
        } elseif (preg_match('/categories\/([^\/\?]+)/', $url, $matches)) {
            $category = $matches[1];
        } else {
            throw new Exception(__('دسته‌بندی در URL یافت نشد', 'digikala-importer'));
        }

        // Extract brand if exists
        if (preg_match('/brands?\[?\d*\]?=(\d+)/', $url, $matches)) {
            $brand = $matches[1];
        }

        return [
            'category' => $category,
            'brand' => $brand,
            'original_url' => $url
        ];
    }

    private function fetch_category_products_v2($category_data, $page = 1) {
        $category = $category_data['category'];
        $brand = $category_data['brand'];
        
        // استفاده از API جدید دیجی‌کالا
        $api_url = "https://api.digikala.com/v1/categories/{$category}/search/";
        
        $params = [
            'page' => $page,
            'rows' => 20,
            'sort' => 'latest',
            'has_selling_stock' => 1
        ];

        if ($brand) {
            $params['brands[0]'] = $brand;
        }

        $api_url .= '?' . http_build_query($params);

        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8'
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception(__('خطا در دریافت اطلاعات: ', 'digikala-importer') . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['data']['products'])) {
            throw new Exception(__('محصولی در این دسته‌بندی یافت نشد یا پاسخ API نامعتبر است', 'digikala-importer'));
        }

        $products = [];
        foreach ($data['data']['products'] as $product) {
            // بررسی وجود محصول در ووکامرس
            $existing_product = $this->check_existing_product($product['id']);
            
            $products[] = [
                'id' => $product['id'],
                'title_fa' => $product['title_fa'],
                'title_en' => $product['title_en'] ?? '',
                'category' => isset($product['category']['title_fa']) ? $product['category']['title_fa'] : $this->category_mappings[$category] ?? $category,
                'brand' => isset($product['brand']['title_fa']) ? $product['brand']['title_fa'] : '',
                'price' => isset($product['default_variant']['price']['selling_price']) ? ($product['default_variant']['price']['selling_price'] / 10) : 0,
                'image' => isset($product['images']['main']['url'][0]) ? $product['images']['main']['url'][0] : '',
                'status' => isset($product['default_variant']['status']) ? $product['default_variant']['status'] : 'marketable',
                'exists' => $existing_product ? true : false,
                'wc_product_id' => $existing_product ? $existing_product->get_id() : null
            ];
        }

        // اطلاعات دسته‌بندی
        $category_info = [
            'name' => $this->category_mappings[$category] ?? $category,
            'brand' => $brand ? $this->get_brand_name_by_id($brand) : null,
            'total_products' => isset($data['data']['pager']['total_rows']) ? $data['data']['pager']['total_rows'] : 0
        ];

        // اطلاعات صفحه‌بندی
        $pagination = [
            'current_page' => isset($data['data']['pager']['current_page']) ? $data['data']['pager']['current_page'] : $page,
            'total_pages' => isset($data['data']['pager']['total_pages']) ? $data['data']['pager']['total_pages'] : 1,
            'per_page' => 20,
            'total_rows' => isset($data['data']['pager']['total_rows']) ? $data['data']['pager']['total_rows'] : 0
        ];

        return [
            'products' => $products,
            'pagination' => $pagination,
            'category_info' => $category_info
        ];
    }

    private function get_brand_name_by_id($brand_id) {
        // Cache for brand names
        static $brand_cache = [];
        
        if (isset($brand_cache[$brand_id])) {
            return $brand_cache[$brand_id];
        }

        try {
            $api_url = "https://api.digikala.com/v1/brands/{$brand_id}/";
            
            $response = wp_remote_get($api_url, [
                'timeout' => 15,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json'
                ]
            ]);

            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                
                if (isset($data['data']['brand']['title_fa'])) {
                    $brand_cache[$brand_id] = $data['data']['brand']['title_fa'];
                    return $brand_cache[$brand_id];
                }
            }
        } catch (Exception $e) {
            // در صورت خطا، فقط ID برند را برگردان
        }
        
        $brand_cache[$brand_id] = "برند {$brand_id}";
        return $brand_cache[$brand_id];
    }

    private function check_existing_product($digikala_id) {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_sku',
                    'value' => $digikala_id,
                    'compare' => '='
                ]
            ]
        ];
        
        $products = get_posts($args);
        if (!empty($products)) {
            return wc_get_product($products[0]->ID);
        }
        
        return false;
    }

    private function sanitize_custom_fields($fields) {
        return [
            'warranty' => sanitize_text_field($fields['warranty_text'] ?? 'گارانتی 18 ماهه'),
            'english_name' => sanitize_text_field($fields['english_name'] ?? ''),
            'stock_quantity' => absint($fields['stock_quantity'] ?? 25),
            'show_hamta' => in_array($fields['show_hamta'] ?? 'no', ['yes', 'no']) ? $fields['show_hamta'] : 'no'
        ];
    }

    private function sanitize_image_settings($settings) {
        return [
            'set_featured' => in_array($settings['set_featured'] ?? 'yes', ['yes', 'no']) ? $settings['set_featured'] : 'yes',
            'import_gallery' => in_array($settings['import_gallery'] ?? 'yes', ['yes', 'no']) ? $settings['import_gallery'] : 'yes',
            'max_gallery_images' => min(20, max(1, absint($settings['max_gallery_images'] ?? 10)))
        ];
    }

    private function sanitize_selected_variants($variants) {
        $sanitized = [];
        if (is_array($variants)) {
            // Group by color to remove duplicates
            $grouped_variants = [];
            
            foreach ($variants as $variant) {
                $color = sanitize_text_field($variant['color'] ?? '');
                $price = floatval($variant['custom_price'] ?? $variant['original_price'] ?? 0);
                
                // If color already exists, keep the one with higher price
                if (isset($grouped_variants[$color])) {
                    if ($price > $grouped_variants[$color]['custom_price']) {
                        $grouped_variants[$color] = [
                            'index' => absint($variant['index'] ?? 0),
                            'color' => $color,
                            'original_price' => floatval($variant['original_price'] ?? 0),
                            'custom_price' => $price,
                            'stock' => absint($variant['stock'] ?? 25),
                            'variant_id' => sanitize_text_field($variant['variant_id'] ?? ''),
                            'color_hex' => sanitize_text_field($variant['color_hex'] ?? '')
                        ];
                    }
                } else {
                    $grouped_variants[$color] = [
                        'index' => absint($variant['index'] ?? 0),
                        'color' => $color,
                        'original_price' => floatval($variant['original_price'] ?? 0),
                        'custom_price' => $price,
                        'stock' => absint($variant['stock'] ?? 25),
                        'variant_id' => sanitize_text_field($variant['variant_id'] ?? ''),
                        'color_hex' => sanitize_text_field($variant['color_hex'] ?? '')
                    ];
                }
            }
            
            $sanitized = array_values($grouped_variants);
        }
        return $sanitized;
    }

    private function extract_product_id($url) {
        if (!wp_http_validate_url($url)) {
            throw new Exception(__('URL وارد شده معتبر نیست', 'digikala-importer'));
        }
        
        if (!preg_match('/digikala\.com/i', $url)) {
            throw new Exception(__('لطفاً فقط لینک‌های دیجی‌کالا را وارد کنید', 'digikala-importer'));
        }

        if (preg_match('/\/dkp-(\d+)\//', $url, $matches)) {
            return (int) $matches[1];
        }
        
        throw new Exception(__('ID محصول در URL یافت نشد', 'digikala-importer'));
    }

    private function fetch_digikala_product($product_id) {
        $cache_key = 'dpi_product_' . $product_id;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $api_url = "https://api.digikala.com/v2/product/{$product_id}/";
        
        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
                'Referer' => 'https://www.digikala.com/'
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception(__('خطا در دریافت اطلاعات: ', 'digikala-importer') . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            throw new Exception(__('خطا در دریافت محصول. کد خطا: ', 'digikala-importer') . $status_code);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['data']['product'])) {
            throw new Exception(__('محصول یافت نشد یا در دسترس نیست', 'digikala-importer'));
        }

        set_transient($cache_key, $data['data']['product'], DPI_CACHE_TIME);

        return $data['data']['product'];
    }

    private function generate_preview_html($product) {
        ob_start();
        include DPI_PLUGIN_PATH . 'templates/product-preview.php';
        return ob_get_clean();
    }

    private function import_product($digikala_data, $custom_fields, $image_settings, $selected_specs, $selected_variants, $single_product_price) {
        // بررسی محصول تکراری
        $existing_product = $this->check_existing_product($digikala_data['id']);
        if ($existing_product) {
            throw new Exception(__('این محصول قبلاً وارد شده است: ', 'digikala-importer') . $existing_product->get_name());
        }

        $has_variants = !empty($selected_variants) && count($selected_variants) > 1;

        if ($has_variants) {
            return $this->create_variable_product($digikala_data, $custom_fields, $image_settings, $selected_specs, $selected_variants);
        }
        
        return $this->create_simple_product($digikala_data, $custom_fields, $image_settings, $selected_specs, $selected_variants, $single_product_price);
    }

    private function create_variable_product($data, $custom_fields, $image_settings, $selected_specs, $selected_variants) {
        $product = new WC_Product_Variable();
        
        $product->set_name($data['title_fa']);
        $product->set_description($this->generate_description($data));
        $product->set_short_description($this->generate_short_description($data));
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_sku($data['id']); // Set Digikala ID as SKU

        $this->set_product_categories($product, $data);
        $this->set_product_brand($product, $data);
        $this->set_product_tags($product, $data);

        $product_id = $product->save();

        $this->create_color_attribute($product_id, $selected_variants);

        foreach ($selected_variants as $variant_data) {
            $this->create_product_variation($product_id, $variant_data, $data);
        }

        $this->set_product_images($product_id, $data, $image_settings);
        $this->set_product_attributes($product_id, $data, $selected_specs);
        $this->set_custom_fields($product_id, $custom_fields);

        return $product_id;
    }

    private function create_simple_product($data, $custom_fields, $image_settings, $selected_specs, $selected_variants, $single_product_price) {
        $product = new WC_Product_Simple();
        
        $product->set_name($data['title_fa']);
        $product->set_description($this->generate_description($data));
        $product->set_short_description($this->generate_short_description($data));
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_sku($data['id']); // Set Digikala ID as SKU

        // Set price
        if (!empty($selected_variants) && isset($selected_variants[0]['custom_price']) && $selected_variants[0]['custom_price'] > 0) {
            $price = $selected_variants[0]['custom_price'];
        } elseif ($single_product_price > 0) {
            $price = $single_product_price;
        } elseif (isset($data['default_variant']['price']['selling_price'])) {
            $price = $data['default_variant']['price']['selling_price'] / 10;
        } else {
            $price = 0;
        }
        
        if ($price > 0) {
            $product->set_regular_price($price);
            $product->set_price($price);
        }

        // Set stock
        $stock_quantity = (!empty($selected_variants) && isset($selected_variants[0]['stock'])) 
            ? $selected_variants[0]['stock'] 
            : $custom_fields['stock_quantity'];
            
        $product->set_stock_quantity($stock_quantity);
        $product->set_manage_stock(true);
        $product->set_stock_status('instock');

        // Set product status based on Digikala availability
        if (isset($data['default_variant']['status'])) {
            $stock_status = $data['default_variant']['status'] === 'marketable' ? 'instock' : 'outofstock';
            $product->set_stock_status($stock_status);
        }

        $product_id = $product->save();

        $this->set_product_categories($product, $data);
        $this->set_product_brand($product, $data);
        $this->set_product_tags($product, $data);
        $this->set_product_images($product_id, $data, $image_settings);
        $this->set_product_attributes($product_id, $data, $selected_specs);
        $this->set_custom_fields($product_id, $custom_fields);

        return $product_id;
    }

    private function create_color_attribute($product_id, $selected_variants) {
        $attribute_name = 'pa_color';
        
        if (!taxonomy_exists($attribute_name)) {
            register_taxonomy($attribute_name, 'product', [
                'label' => __('رنگ', 'digikala-importer'),
                'hierarchical' => false,
                'public' => true,
                'show_ui' => false
            ]);
        }

        $colors = [];

        foreach ($selected_variants as $variant) {
            if (!empty($variant['color'])) {
                $color_name = $variant['color'];
                $color_slug = sanitize_title($color_name);
                $colors[] = $color_name;

                if (!term_exists($color_name, $attribute_name)) {
                    wp_insert_term($color_name, $attribute_name, [
                        'slug' => $color_slug
                    ]);
                }

                wp_set_object_terms($product_id, $color_name, $attribute_name, true);
            }
        }

        $attributes = get_post_meta($product_id, '_product_attributes', true) ?: [];

        $attributes[$attribute_name] = [
            'name' => $attribute_name,
            'value' => implode(' | ', $colors),
            'position' => 0,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 1
        ];

        update_post_meta($product_id, '_product_attributes', $attributes);
    }

    private function create_product_variation($product_id, $variant_data, $parent_data) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_sku($variant_data['variant_id']); // Set variant ID as SKU

        // Set color attribute
        if (!empty($variant_data['color'])) {
            $color_term = get_term_by('name', $variant_data['color'], 'pa_color');
            if ($color_term) {
                $variation->set_attributes(['pa_color' => $color_term->slug]);
            }
        }

        // Set price
        $price = $variant_data['custom_price'] > 0 ? $variant_data['custom_price'] : $variant_data['original_price'];
        if ($price > 0) {
            $variation->set_regular_price($price);
            $variation->set_price($price);
        }

        // Set stock
        $variation->set_stock_status('instock');
        $variation->set_manage_stock(true);
        $variation->set_stock_quantity($variant_data['stock']);

        $variation->set_status('publish');
        $variation_id = $variation->save();

        return $variation_id;
    }

    private function set_product_categories($product, $data) {
        $category_ids = [];
        
        // استفاده از data_layer برای دسته‌بندی‌ها اگر موجود است
        if (isset($data['data_layer'])) {
            $categories_to_create = [];
            
            if (!empty($data['data_layer']['item_category2'])) {
                $categories_to_create[] = $data['data_layer']['item_category2'];
            }
            if (!empty($data['data_layer']['item_category3'])) {
                $categories_to_create[] = $data['data_layer']['item_category3'];
            }
            if (!empty($data['data_layer']['item_category4'])) {
                $categories_to_create[] = $data['data_layer']['item_category4'];
            }
            if (!empty($data['data_layer']['item_category5'])) {
                $categories_to_create[] = $data['data_layer']['item_category5'];
            }
            
            $parent_id = 0;
            foreach ($categories_to_create as $cat_name) {
                $cat_id = $this->get_or_create_category($cat_name, $parent_id);
                if ($cat_id) {
                    $category_ids[] = $cat_id;
                    $parent_id = $cat_id;
                }
            }
        } else {
            // Fallback to category from API data
            $category_name = 'گوشی موبایل';
            
            if (isset($data['category']['title_fa'])) {
                $category_name = $data['category']['title_fa'];
            } elseif (isset($data['category']['code'])) {
                $category_code = $data['category']['code'];
                $category_name = $this->category_mappings[$category_code] ?? $category_name;
            }

            $category_id = $this->get_or_create_category($category_name);
            $category_ids[] = $category_id;
        }

        if (!empty($category_ids)) {
            $product->set_category_ids($category_ids);
        }
    }

    private function get_or_create_category($name, $parent_id = 0) {
        $term = get_term_by('name', $name, 'product_cat');
        
        if (!$term) {
            $result = wp_insert_term($name, 'product_cat', [
                'parent' => $parent_id
            ]);
            return is_wp_error($result) ? 0 : $result['term_id'];
        }
        
        return $term->term_id;
    }

    private function set_product_brand($product, $data) {
        if (!isset($data['brand']['title_fa']) || !taxonomy_exists('product_brand')) {
            return;
        }

        $brand_name = $data['brand']['title_fa'];
        $brand_term = get_term_by('name', $brand_name, 'product_brand');
        
        if (!$brand_term) {
            $brand_result = wp_insert_term($brand_name, 'product_brand');
            $brand_term_id = is_wp_error($brand_result) ? 0 : $brand_result['term_id'];
        } else {
            $brand_term_id = $brand_term->term_id;
        }
        
        if ($brand_term_id) {
            wp_set_object_terms($product->get_id(), $brand_term_id, 'product_brand');
        }
    }

    private function set_product_tags($product, $data) {
        if (!isset($data['tags']) || !is_array($data['tags'])) {
            return;
        }

        $tag_names = [];
        foreach ($data['tags'] as $tag) {
            if (isset($tag['title'])) {
                $tag_names[] = $tag['title'];
            }
        }

        if (!empty($tag_names)) {
            wp_set_object_terms($product->get_id(), $tag_names, 'product_tag');
        }
    }

    private function set_product_images($product_id, $data, $image_settings) {
        if (empty($data['images'])) {
            return;
        }

        $image_ids = [];
        $main_image_id = null;

        // Set featured image
        if ($image_settings['set_featured'] === 'yes' && isset($data['images']['main']['url'][0])) {
            $main_image_url = $data['images']['main']['url'][0];
            $main_image_id = $this->upload_image($main_image_url, $product_id);
            if ($main_image_id) {
                set_post_thumbnail($product_id, $main_image_id);
            }
        }

        // Set gallery images
        if ($image_settings['import_gallery'] === 'yes' && isset($data['images']['list'])) {
            $max_images = $image_settings['max_gallery_images'];
            $imported_count = 0;

            foreach ($data['images']['list'] as $image) {
                if ($imported_count >= $max_images) {
                    break;
                }

                if (isset($image['url'][0])) {
                    $image_id = $this->upload_image($image['url'][0], $product_id);
                    if ($image_id && $image_id !== $main_image_id) {
                        $image_ids[] = $image_id;
                        $imported_count++;
                    }
                }
            }

            if (!empty($image_ids)) {
                update_post_meta($product_id, '_product_image_gallery', implode(',', $image_ids));
            }
        }
    }

    private function upload_image($image_url, $product_id) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        try {
            // بررسی اینکه آیا تصویر قبلاً آپلود شده است
            $image_hash = md5($image_url);
            $existing_image = get_posts([
                'post_type' => 'attachment',
                'meta_query' => [
                    [
                        'key' => '_dpi_image_hash',
                        'value' => $image_hash,
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => 1
            ]);

            if (!empty($existing_image)) {
                return $existing_image[0]->ID;
            }

            $temp_file = download_url($image_url);
            
            if (is_wp_error($temp_file)) {
                return false;
            }

            $file_array = [
                'name' => basename(parse_url($image_url, PHP_URL_PATH)) . '.jpg',
                'tmp_name' => $temp_file
            ];

            $id = media_handle_sideload($file_array, $product_id);
            
            if (is_wp_error($id)) {
                @unlink($temp_file);
                return false;
            }

            // ذخیره hash تصویر برای جلوگیری از تکرار
            update_post_meta($id, '_dpi_image_hash', $image_hash);

            return $id;

        } catch (Exception $e) {
            if (isset($temp_file) && file_exists($temp_file)) {
                @unlink($temp_file);
            }
            return false;
        }
    }

    private function set_product_attributes($product_id, $data, $selected_specs) {
        if (empty($data['specifications'])) {
            return;
        }

        $attributes = get_post_meta($product_id, '_product_attributes', true) ?: [];
        $position = count($attributes);

        foreach ($data['specifications'] as $spec_group) {
            if (isset($spec_group['attributes'])) {
                foreach ($spec_group['attributes'] as $attribute) {
                    // اگر مشخصات خاصی انتخاب شده، فقط آن‌ها را اضافه کن
                    if (!empty($selected_specs)) {
                        $attr_key = $spec_group['title'] . '|' . $attribute['title'];
                        if (!in_array($attr_key, $selected_specs)) {
                            continue;
                        }
                    }

                    if (!empty($attribute['values'])) {
                        $attr_name = sanitize_title($attribute['title']);
                        $attr_value = implode(', ', $attribute['values']);

                        $attributes[$attr_name] = [
                            'name' => $attribute['title'],
                            'value' => $attr_value,
                            'position' => $position++,
                            'is_visible' => 1,
                            'is_variation' => 0,
                            'is_taxonomy' => 0
                        ];
                    }
                }
            }
        }

        update_post_meta($product_id, '_product_attributes', $attributes);
    }

    private function set_custom_fields($product_id, $custom_fields) {
        $default_fields = [
            'product_granti_text' => $custom_fields['warranty'] ?? 'گارانتی 18 ماهه',
            'product_hamta_text' => 'هشدار سامانه همتا: حتما در زمان تحویل دستگاه، به کمک کد فعال‌سازی چاپ شده روی جعبه یا کارت گارانتی، دستگاه را از طریق #7777*، برای سیم‌کارت خود فعال‌سازی کنید.',
            'product_Original_text' => 'ضمانت اصالت کالا',
            'product_return_text' => 'امکان برگشت کالا با دلیل "انصراف از خرید" تنها در صورتی مورد قبول است که پلمب کالا باز نشده باشد.',
            'product_granti_show' => 'yes',
            'product_hamta_show' => $custom_fields['show_hamta'] ?? 'no',
            'product_Original_show' => 'yes',
            'product_return_show' => 'yes',
            'en_pro_name' => $custom_fields['english_name'] ?? '',
            '_mojoodi' => $custom_fields['stock_quantity'] ?? 25
        ];

        foreach ($default_fields as $key => $value) {
            update_post_meta($product_id, $key, $value);
        }

        // اضافه کردن meta برای شناسایی محصولات وارد شده از دیجی‌کالا
        update_post_meta($product_id, '_dpi_imported', 'yes');
        update_post_meta($product_id, '_dpi_import_date', current_time('mysql'));
    }

    private function generate_short_description($data) {
        $short_desc = '';

        if (isset($data['expert_reviews']['description']) && !empty($data['expert_reviews']['description'])) {
            $short_desc = wp_kses_post(wp_trim_words($data['expert_reviews']['description'], 50));
        }
        
        if (empty($short_desc) && isset($data['title_en']) && !empty($data['title_en'])) {
            $short_desc = esc_html($data['title_en']);
        }

        // اضافه کردن اطلاعات کلیدی محصول
        $key_info = [];
        
        if (isset($data['brand']['title_fa'])) {
            $key_info[] = 'برند: ' . $data['brand']['title_fa'];
        }

        if (isset($data['default_variant']['warranty']['title_fa'])) {
            $key_info[] = 'گارانتی: ' . $data['default_variant']['warranty']['title_fa'];
        }

        if (!empty($key_info)) {
            $short_desc .= "\n\n" . implode(' | ', $key_info);
        }
        
        return $short_desc;
    }

    private function generate_description($data) {
        $description = '';

        // استفاده از review_sections برای توضیحات تفصیلی
        if (isset($data['expert_reviews']['review_sections']) && !empty($data['expert_reviews']['review_sections'])) {
            foreach ($data['expert_reviews']['review_sections'] as $section) {
                if (isset($section['title'])) {
                    $description .= '<h3>' . esc_html($section['title']) . '</h3>';
                }
                
                if (isset($section['sections']) && is_array($section['sections'])) {
                    foreach ($section['sections'] as $subsection) {
                        if (isset($subsection['text']) && !empty($subsection['text'])) {
                            $description .= '<p>' . wp_kses_post($subsection['text']) . '</p>';
                        }
                        
                        if (isset($subsection['image']) && !empty($subsection['image'])) {
                            $description .= '<img src="' . esc_url($subsection['image']) . '" alt="تصویر محصول" style="max-width: 100%; height: auto; margin: 10px 0;" />';
                        }
                    }
                }
            }
        }

        // Fallback به توضیحات پایه
        if (empty($description)) {
            $description .= '<div class="product-info-section">';
            
            if (isset($data['brand']['title_fa'])) {
                $description .= '<p><strong>برند:</strong> ' . esc_html($data['brand']['title_fa']) . '</p>';
            }

            if (isset($data['default_variant']['warranty']['title_fa'])) {
                $description .= '<p><strong>گارانتی:</strong> ' . esc_html($data['default_variant']['warranty']['title_fa']) . '</p>';
            }

            if (isset($data['category']['title_fa'])) {
                $description .= '<p><strong>دسته‌بندی:</strong> ' . esc_html($data['category']['title_fa']) . '</p>';
            }

            // اضافه کردن ویژگی‌های کلیدی
            if (isset($data['specifications']) && !empty($data['specifications'])) {
                $description .= '<h3>ویژگی‌های کلیدی</h3><ul>';
                $feature_count = 0;
                
                foreach ($data['specifications'] as $spec_group) {
                    if ($feature_count >= 5) break; // حداکثر 5 ویژگی کلیدی
                    
                    if (isset($spec_group['attributes'])) {
                        foreach ($spec_group['attributes'] as $attribute) {
                            if ($feature_count >= 5) break;
                            
                            if (!empty($attribute['values'])) {
                                $description .= '<li><strong>' . esc_html($attribute['title']) . ':</strong> ' . esc_html(implode(', ', $attribute['values'])) . '</li>';
                                $feature_count++;
                            }
                        }
                    }
                }
                $description .= '</ul>';
            }

            $description .= '</div>';
        }

        return $description;
    }
}

new DigikalaProductImporter();

register_activation_hook(__FILE__, function() {
    // ایجاد جداول پایگاه داده اگر نیاز باشد
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    // پاکسازی cron jobs
    wp_clear_scheduled_hook('dpi_daily_price_sync');
    flush_rewrite_rules();
});
