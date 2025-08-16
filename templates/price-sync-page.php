<div class="wrap">
    <h1>هماهنگ‌سازی قیمت محصولات</h1>
    
    <div class="dpi-container">
        <div class="dpi-form-section">
            <h2>تنظیمات هماهنگ‌سازی</h2>
            
            <div class="price-sync-info">
                <p><strong>توضیحات:</strong></p>
                <ul>
                    <li>این عملیات قیمت تمام محصولاتی که از دیجی‌کالا وارد شده‌اند را بروزرسانی می‌کند</li>
                    <li>محصولات بر اساس SKU (شناسه دیجی‌کالا) شناسایی می‌شوند</li>
                    <li>بروزرسانی خودکار روزانه دو بار انجام می‌شود</li>
                    <li>محصولات متغیر (دارای رنگ‌های مختلف) نیز پشتیبانی می‌شوند</li>
                </ul>
            </div>

            <?php
            // Get sync statistics
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
            $total_products = count($products);
            $digikala_products = 0;
            
            foreach ($products as $post) {
                $product = wc_get_product($post->ID);
                $sku = $product->get_sku();
                if (is_numeric($sku)) {
                    $digikala_products++;
                }
            }
            ?>

            <div class="sync-stats">
                <h3>آمار محصولات</h3>
                <table class="form-table">
                    <tr>
                        <th>کل محصولات فروشگاه:</th>
                        <td><?php echo number_format($total_products); ?> محصول</td>
                    </tr>
                    <tr>
                        <th>محصولات وارد شده از دیجی‌کالا:</th>
                        <td><?php echo number_format($digikala_products); ?> محصول</td>
                    </tr>
                    <tr>
                        <th>آخرین بروزرسانی خودکار:</th>
                        <td>
                            <?php
                            $last_sync = get_option('dpi_last_sync_time', 'هرگز');
                            if ($last_sync !== 'هرگز') {
                                echo date('Y/m/d H:i', $last_sync) . ' (به وقت ایران)';
                            } else {
                                echo $last_sync;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>بروزرسانی خودکار بعدی:</th>
                        <td>
                            <?php
                            $next_sync = wp_next_scheduled('dpi_daily_price_sync');
                            if ($next_sync) {
                                echo date('Y/m/d H:i', $next_sync) . ' (به وقت ایران)';
                            } else {
                                echo 'برنامه‌ریزی نشده';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="sync-controls">
                <button type="button" id="dpi-manual-sync-btn" class="button button-primary button-hero">
                    شروع بروزرسانی دستی
                </button>
                
                <button type="button" id="dpi-schedule-sync-btn" class="button button-secondary">
                    برنامه‌ریزی مجدد بروزرسانی خودکار
                </button>
            </div>
        </div>

        <div id="dpi-sync-progress" class="dpi-sync-progress" style="display: none;">
            <h2>گزارش بروزرسانی قیمت‌ها</h2>
            <div class="progress-info">
                <div class="progress-stats">
                    <span class="updated-count">بروزرسانی شده: 0</span>
                    <span class="error-count">خطا: 0</span>
                    <span class="total-count">کل: 0</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
            <div id="dpi-sync-log" class="sync-log"></div>
        </div>

        <div id="dpi-loading" class="dpi-loading" style="display: none;">
            <p>در حال بروزرسانی قیمت‌ها... لطفاً صبر کنید</p>
            <div class="dpi-spinner"></div>
        </div>
    </div>
</div>