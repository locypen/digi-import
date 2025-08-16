<?php
/**
 * Helper Functions for Digikala Product Importer
 * Path: includes/helper-functions.php
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * تبدیل اعداد فارسی به انگلیسی
 */
function dpi_persian_to_english_numbers($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persian, $english, $string);
}

/**
 * تبدیل اعداد انگلیسی به فارسی
 */
function dpi_english_to_persian_numbers($string) {
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($english, $persian, $string);
}

/**
 * پاکسازی و فرمت کردن قیمت
 */
function dpi_format_price($price) {
    $price = floatval($price);
    return number_format($price, 0, '.', ',');
}

/**
 * تبدیل قیمت از ریال به تومان
 */
function dpi_rial_to_toman($rial_price) {
    return intval($rial_price / 10);
}

/**
 * بررسی صحت URL دیجی‌کالا
 */
function dpi_validate_digikala_url($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return strpos($url, 'digikala.com') !== false;
}

/**
 * استخراج ID محصول از URL
 */
function dpi_extract_product_id_from_url($url) {
    if (preg_match('/\/dkp-(\d+)\//', $url, $matches)) {
        return intval($matches[1]);
    }
    return false;
}

/**
 * تولید slug یکتا برای محصول
 */
function dpi_generate_unique_slug($title, $id) {
    $slug = sanitize_title($title);
    
    // اضافه کردن ID در صورت تکراری بودن
    $existing = get_page_by_path($slug, OBJECT, 'product');
    if ($existing) {
        $slug .= '-' . $id;
    }
    
    return $slug;
}

/**
 * لاگ کردن خطاها
 */
function dpi_log($message, $type = 'info') {
    if (!WP_DEBUG_LOG) {
        return;
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] DPI {$type}: {$message}" . PHP_EOL;
    
    error_log($log_entry, 3, WP_CONTENT_DIR . '/debug.log');
}

/**
 * ذخیره آمار import
 */
function dpi_save_import_stats($stats) {
    $existing_stats = get_option('dpi_import_stats', []);
    
    $new_stats = [
        'timestamp' => time(),
        'success_count' => $stats['success_count'] ?? 0,
        'error_count' => $stats['error_count'] ?? 0,
        'total_products' => ($stats['success_count'] ?? 0) + ($stats['error_count'] ?? 0),
        'import_type' => $stats['import_type'] ?? 'single'
    ];
    
    $existing_stats[] = $new_stats;
    
    // نگه داشتن فقط 50 آمار آخر
    if (count($existing_stats) > 50) {
        $existing_stats = array_slice($existing_stats, -50);
    }
    
    update_option('dpi_import_stats', $existing_stats);
}

/**
 * دریافت آمار import
 */
function dpi_get_import_stats() {
    return get_option('dpi_import_stats', []);
}

/**
 * پاکسازی کش محصولات
 */
function dpi_clear_product_cache($product_id = null) {
    global $wpdb;
    
    if ($product_id) {
        delete_transient('dpi_product_' . $product_id);
    } else {
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dpi_product_%'"
        );
    }
}

/**
 * بررسی وضعیت سرور دیجی‌کالا
 */
function dpi_check_digikala_api_status() {
    $test_url = 'https://api.digikala.com/v1/categories/mobile-phone/search/?page=1&rows=1';
    
    $response = wp_remote_get($test_url, [
        'timeout' => 10,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

/**
 * تولید نام فایل یکتا برای تصاویر
 */
function dpi_generate_unique_filename($url, $product_id) {
    $path_info = pathinfo(parse_url($url, PHP_URL_PATH));
    $extension = isset($path_info['extension']) ? $path_info['extension'] : 'jpg';
    
    $filename = 'dpi-' . $product_id . '-' . time() . '-' . wp_generate_password(8, false) . '.' . $extension;
    return $filename;
}

/**
 * بررسی محدودیت‌های سرور
 */
function dpi_check_server_limits() {
    $limits = [];
    
    // Memory limit
    $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
    $limits['memory_limit'] = size_format($memory_limit);
    $limits['memory_available'] = $memory_limit > wp_convert_hr_to_bytes('256M');
    
    // Max execution time
    $max_execution_time = ini_get('max_execution_time');
    $limits['max_execution_time'] = $max_execution_time;
    $limits['time_sufficient'] = $max_execution_time == 0 || $max_execution_time >= 300;
    
    // Upload size
    $upload_max_size = wp_convert_hr_to_bytes(ini_get('upload_max_filesize'));
    $limits['upload_max_size'] = size_format($upload_max_size);
    $limits['upload_sufficient'] = $upload_max_size >= wp_convert_hr_to_bytes('10M');
    
    return $limits;
}

/**
 * پردازش توضیحات محصول
 */
function dpi_process_product_description($description) {
    // حذف تگ‌های غیرضروری
    $allowed_tags = '<p><br><strong><b><em><i><ul><ol><li><h3><h4><img>';
    $description = strip_tags($description, $allowed_tags);
    
    // تمیز کردن فاصله‌های اضافی
    $description = preg_replace('/\s+/', ' ', $description);
    $description = trim($description);
    
    // اضافه کردن target="_blank" به لینک‌ها
    $description = preg_replace('/<a\s/', '<a target="_blank" ', $description);
    
    return $description;
}

/**
 * بررسی وجود محصول با SKU
 */
function dpi_product_exists_by_sku($sku) {
    global $wpdb;
    
    $product_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s",
        $sku
    ));
    
    return $product_id ? wc_get_product($product_id) : false;
}

/**
 * تولید گزارش خطا برای عملیات bulk
 */
function dpi_generate_error_report($errors) {
    $report = "گزارش خطاهای ورود گروهی\n";
    $report .= "تاریخ: " . date('Y/m/d H:i:s') . "\n";
    $report .= str_repeat('=', 50) . "\n\n";
    
    foreach ($errors as $error) {
        $report .= "محصول ID: " . $error['product_id'] . "\n";
        $report .= "خطا: " . $error['message'] . "\n";
        $report .= str_repeat('-', 30) . "\n";
    }
    
    return $report;
}

/**
 * ارسال ایمیل گزارش
 */
function dpi_send_import_report($stats, $admin_email = null) {
    if (!$admin_email) {
        $admin_email = get_option('admin_email');
    }
    
    $subject = 'گزارش ورود گروهی محصولات از دیجی‌کالا';
    $message = "سلام،\n\n";
    $message .= "عملیات ورود گروهی محصولات با موفقیت انجام شد.\n\n";
    $message .= "خلاصه عملیات:\n";
    $message .= "- تعداد محصولات موفق: " . ($stats['success_count'] ?? 0) . "\n";
    $message .= "- تعداد محصولات ناموفق: " . ($stats['error_count'] ?? 0) . "\n";
    $message .= "- زمان عملیات: " . date('Y/m/d H:i:s') . "\n\n";
    $message .= "با تشکر،\n";
    $message .= "سیستم مدیریت محتوا";
    
    wp_mail($admin_email, $subject, $message);
}

/**
 * بررسی دسترسی‌های کاربر
 */
function dpi_check_user_permissions() {
    return current_user_can('manage_woocommerce') || current_user_can('manage_options');
}

/**
 * تمیز کردن داده‌های ورودی
 */
function dpi_sanitize_input_data($data) {
    if (is_array($data)) {
        return array_map('dpi_sanitize_input_data', $data);
    } else {
        return sanitize_text_field($data);
    }
}

/**
 * محاسبه حجم تقریبی محصول
 */
function dpi_estimate_product_size($product_data) {
    $size = 0;
    
    // متن‌ها
    $size += strlen($product_data['title_fa'] ?? '');
    $size += strlen($product_data['title_en'] ?? '');
    $size += strlen(json_encode($product_data['specifications'] ?? []));
    
    // تصاویر (تخمین 100KB برای هر تصویر)
    $image_count = 1; // تصویر اصلی
    if (isset($product_data['images']['list'])) {
        $image_count += count($product_data['images']['list']);
    }
    $size += $image_count * 102400; // 100KB
    
    return $size;
}

/**
 * بررسی فضای دیسک موجود
 */
function dpi_check_disk_space() {
    $upload_dir = wp_upload_dir();
    $free_bytes = disk_free_space($upload_dir['basedir']);
    
    return [
        'free_space' => $free_bytes,
        'free_space_formatted' => size_format($free_bytes),
        'sufficient' => $free_bytes > (100 * 1024 * 1024) // 100MB
    ];
}

/**
 * تنظیم timeout برای درخواست‌های طولانی
 */
function dpi_set_long_timeout() {
    if (!ini_get('safe_mode')) {
        @set_time_limit(300); // 5 minutes
        @ini_set('memory_limit', '512M');
    }
    
    // افزایش timeout برای HTTP requests
    add_filter('http_request_timeout', function($timeout) {
        return 60; // 60 seconds
    });
}

/**
 * ایجاد backup از تنظیمات
 */
function dpi_create_settings_backup() {
    $settings = [
        'dpi_import_stats' => get_option('dpi_import_stats', []),
        'dpi_last_sync_time' => get_option('dpi_last_sync_time', ''),
        'dpi_settings' => get_option('dpi_settings', [])
    ];
    
    $backup_file = WP_CONTENT_DIR . '/uploads/dpi-backup-' . date('Y-m-d-H-i-s') . '.json';
    file_put_contents($backup_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $backup_file;
}

/**
 * بازیابی backup
 */
function dpi_restore_settings_backup($backup_file) {
    if (!file_exists($backup_file)) {
        return false;
    }
    
    $settings = json_decode(file_get_contents($backup_file), true);
    
    if (!$settings) {
        return false;
    }
    
    foreach ($settings as $option_name => $option_value) {
        update_option($option_name, $option_value);
    }
    
    return true;
}

/**
 * پاکسازی فایل‌های موقت
 */
function dpi_cleanup_temp_files() {
    $upload_dir = wp_upload_dir();
    $temp_pattern = $upload_dir['basedir'] . '/dpi-temp-*';
    
    $temp_files = glob($temp_pattern);
    $deleted = 0;
    
    foreach ($temp_files as $temp_file) {
        // پاک کردن فایل‌های قدیمی‌تر از 1 ساعت
        if (filemtime($temp_file) < (time() - 3600)) {
            if (unlink($temp_file)) {
                $deleted++;
            }
        }
    }
    
    return $deleted;
}

/**
 * تولید نام منحصر به فرد برای category
 */
function dpi_generate_category_name($original_name) {
    // تبدیل نام انگلیسی به فارسی در صورت امکان
    $category_mappings = [
        'Mobile Phone' => 'گوشی موبایل',
        'Laptop' => 'لپ‌تاپ',
        'Tablet' => 'تبلت',
        'Smart Watch' => 'ساعت هوشمند',
        'Headphone' => 'هدفون و هندزفری',
        'Speaker' => 'اسپیکر',
        'Power Bank' => 'پاوربانک',
        'Cable' => 'کابل و مبدل',
        'Case' => 'کیف و کاور',
        'Charger' => 'شارژر'
    ];
    
    return $category_mappings[$original_name] ?? $original_name;
}

/**
 * بررسی سازگاری نسخه وردپرس
 */
function dpi_check_wordpress_compatibility() {
    global $wp_version;
    
    return version_compare($wp_version, '5.0', '>=');
}

/**
 * بررسی سازگاری نسخه ووکامرس
 */
function dpi_check_woocommerce_compatibility() {
    if (!function_exists('WC')) {
        return false;
    }
    
    return version_compare(WC()->version, '5.0', '>=');
}

/**
 * تولید کد رنگ hex از نام رنگ
 */
function dpi_get_color_hex_from_name($color_name) {
    $color_mappings = [
        'سیاه' => '#000000',
        'سفید' => '#ffffff', 
        'قرمز' => '#ff0000',
        'آبی' => '#0000ff',
        'سبز' => '#00ff00',
        'زرد' => '#ffff00',
        'نارنجی' => '#ffa500',
        'بنفش' => '#800080',
        'صورتی' => '#ffc0cb',
        'قهوه‌ای' => '#8b4513',
        'طوسی' => '#808080',
        'طلایی' => '#ffd700',
        'نقره‌ای' => '#c0c0c0',
        'برنزی' => '#cd7f32'
    ];
    
    $color_name = trim($color_name);
    return $color_mappings[$color_name] ?? '#cccccc';
}

/**
 * فرمت کردن اندازه فایل
 */
function dpi_format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * بررسی وضعیت SSL
 */
function dpi_check_ssl_status() {
    return is_ssl() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/**
 * تولید hash برای تصاویر
 */
function dpi_generate_image_hash($image_url) {
    return md5($image_url . get_option('auth_salt'));
}

/**
 * بررسی rate limit
 */
function dpi_check_rate_limit($action = 'general', $limit = 60) {
    $transient_key = 'dpi_rate_limit_' . $action . '_' . get_current_user_id();
    $current_count = get_transient($transient_key);
    
    if ($current_count === false) {
        set_transient($transient_key, 1, 60); // 1 minute
        return true;
    }
    
    if ($current_count >= $limit) {
        return false;
    }
    
    set_transient($transient_key, $current_count + 1, 60);
    return true;
}

/**
 * لاگ عملیات کاربر
 */
function dpi_log_user_action($action, $details = []) {
    $log_entry = [
        'timestamp' => time(),
        'user_id' => get_current_user_id(),
        'user_login' => wp_get_current_user()->user_login,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $existing_logs = get_option('dpi_user_logs', []);
    $existing_logs[] = $log_entry;
    
    // نگه داشتن فقط 100 لاگ آخر
    if (count($existing_logs) > 100) {
        $existing_logs = array_slice($existing_logs, -100);
    }
    
    update_option('dpi_user_logs', $existing_logs);
}

/**
 * پاکسازی لاگ‌ها
 */
function dpi_cleanup_logs($days_old = 30) {
    $cutoff_time = time() - ($days_old * 24 * 60 * 60);
    
    // پاکسازی آمار import
    $import_stats = get_option('dpi_import_stats', []);
    $import_stats = array_filter($import_stats, function($stat) use ($cutoff_time) {
        return $stat['timestamp'] > $cutoff_time;
    });
    update_option('dpi_import_stats', array_values($import_stats));
    
    // پاکسازی لاگ‌های کاربر
    $user_logs = get_option('dpi_user_logs', []);
    $user_logs = array_filter($user_logs, function($log) use ($cutoff_time) {
        return $log['timestamp'] > $cutoff_time;
    });
    update_option('dpi_user_logs', array_values($user_logs));
}

/**
 * تولید گزارش عملکرد
 */
function dpi_generate_performance_report() {
    $import_stats = get_option('dpi_import_stats', []);
    
    if (empty($import_stats)) {
        return null;
    }
    
    $total_imports = count($import_stats);
    $successful_imports = 0;
    $total_products = 0;
    $recent_imports = 0;
    $last_week = time() - (7 * 24 * 60 * 60);
    
    foreach ($import_stats as $stat) {
        if ($stat['success_count'] > 0) {
            $successful_imports++;
        }
        $total_products += $stat['total_products'];
        
        if ($stat['timestamp'] > $last_week) {
            $recent_imports++;
        }
    }
    
    return [
        'total_imports' => $total_imports,
        'successful_imports' => $successful_imports,
        'success_rate' => $total_imports > 0 ? round(($successful_imports / $total_imports) * 100, 2) : 0,
        'total_products' => $total_products,
        'recent_imports' => $recent_imports,
        'average_products_per_import' => $total_imports > 0 ? round($total_products / $total_imports, 2) : 0
    ];
}

/**
 * بررسی وضعیت plugin
 */
function dpi_get_plugin_status() {
    return [
        'version' => DPI_VERSION,
        'wordpress_compatible' => dpi_check_wordpress_compatibility(),
        'woocommerce_compatible' => dpi_check_woocommerce_compatibility(),
        'api_accessible' => dpi_check_digikala_api_status(),
        'ssl_enabled' => dpi_check_ssl_status(),
        'server_limits' => dpi_check_server_limits(),
        'disk_space' => dpi_check_disk_space()
    ];
}