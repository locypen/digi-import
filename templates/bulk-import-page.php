<div class="wrap">
    <h1>ورود گروهی محصولات از دیجی‌کالا</h1>
    
    <div class="notice notice-info">
        <p><strong>راهنمای استفاده:</strong></p>
        <ul>
            <li>لینک دسته‌بندی یا برند مورد نظر را از دیجی‌کالا کپی کرده و در فیلد زیر وارد کنید</li>
            <li>تمام محصولات آن دسته‌بندی/برند دریافت و نمایش داده می‌شوند</li>
            <li>محصولات مورد نظر خود را انتخاب کرده و تنظیمات را اعمال کنید</li>
            <li>محصولاتی که قبلاً وارد شده‌اند با رنگ متفاوت نمایش داده می‌شوند</li>
            <li>عملیات ورود ممکن است بسته به تعداد محصولات چند دقیقه طول بکشد</li>
        </ul>
    </div>
    
    <div class="dpi-container">
        <div class="dpi-form-section">
            <h2>اطلاعات دسته‌بندی</h2>
            
            <form id="dpi-bulk-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="category_url">لینک دسته‌بندی یا برند دیجی‌کالا</label></th>
                        <td>
                            <input type="url" id="category_url" name="category_url" class="regular-text" placeholder="https://www.digikala.com/search/category-mobile-phone/" required>
                            <p class="description">لینک دسته‌بندی یا برند خاص در دسته‌بندی را وارد کنید</p>
                            <p class="description"><strong>مثال دسته‌بندی:</strong> https://www.digikala.com/search/category-mobile-phone/</p>
                            <p class="description"><strong>مثال برند:</strong> https://www.digikala.com/search/category-mobile-phone/?brands[0]=18</p>
                            <p class="description"><strong>نکته:</strong> می‌توانید از فیلترهای مختلف دیجی‌کالا مانند برند، رنج قیمت و غیره استفاده کنید</p>
                        </td>
                    </tr>
                </table>

                <button type="button" id="dpi-fetch-category-btn" class="button button-primary">دریافت محصولات دسته‌بندی</button>
            </form>
        </div>

        <div id="dpi-category-products" class="dpi-category-products" style="display: none;">
            <h2>محصولات یافت شده</h2>
            
            <div id="dpi-category-info" class="dpi-category-info"></div>
            
            <div class="dpi-bulk-controls">
                <button type="button" id="select-all-products" class="button button-secondary">انتخاب همه</button>
                <button type="button" id="deselect-all-products" class="button button-secondary">لغو انتخاب همه</button>
                <span class="selected-count">0 محصول انتخاب شده</span>
                
                <div class="bulk-actions">
                    <label>
                        <input type="checkbox" id="skip-existing" checked> رد کردن محصولات موجود
                    </label>
                </div>
            </div>
            
            <div id="dpi-products-table"></div>
            
            <div id="dpi-pagination"></div>
            
            <div class="dpi-import-settings">
                <h3>تنظیمات ورود گروهی</h3>
                
                <div class="settings-grid">
                    <div class="settings-column">
                        <h4>تنظیمات محصول</h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="bulk_warranty_text">متن گارانتی</label></th>
                                <td>
                                    <input type="text" id="bulk_warranty_text" name="bulk_warranty_text" class="regular-text" value="گارانتی 18 ماهه">
                                    <p class="description">متن گارانتی که برای همه محصولات اعمال می‌شود</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="bulk_stock_quantity">تعداد موجودی پیش‌فرض</label></th>
                                <td>
                                    <input type="number" id="bulk_stock_quantity" name="bulk_stock_quantity" value="25" min="0" max="1000">
                                    <p class="description">موجودی اولیه که برای همه محصولات تنظیم می‌شود</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="bulk_show_hamta">نمایش هشدار همتا</label></th>
                                <td>
                                    <select id="bulk_show_hamta" name="bulk_show_hamta">
                                        <option value="no">خیر</option>
                                        <option value="yes">بله</option>
                                    </select>
                                    <p class="description">نمایش هشدار سامانه همتا در صفحه محصولات</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="settings-column">
                        <h4>تنظیمات تصاویر</h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="bulk_set_featured">تنظیم تصویر شاخص</label></th>
                                <td>
                                    <select id="bulk_set_featured" name="bulk_set_featured">
                                        <option value="yes">بله</option>
                                        <option value="no">خیر</option>
                                    </select>
                                    <p class="description">آیا تصویر اصلی محصول به عنوان تصویر شاخص تنظیم شود؟</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="bulk_import_gallery">واردکردن گالری</label></th>
                                <td>
                                    <select id="bulk_import_gallery" name="bulk_import_gallery">
                                        <option value="yes">بله</option>
                                        <option value="no">خیر</option>
                                    </select>
                                    <p class="description">آیا تصاویر دیگر محصول به گالری اضافه شوند؟</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="bulk_max_gallery_images">حداکثر تصاویر گالری</label></th>
                                <td>
                                    <input type="number" id="bulk_max_gallery_images" name="bulk_max_gallery_images" value="10" min="1" max="20">
                                    <p class="description">حداکثر تعداد تصاویری که به گالری هر محصول اضافه می‌شود</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="import-summary">
                    <h4>خلاصه عملیات</h4>
                    <div class="summary-stats">
                        <div class="stat-item">
                            <span class="stat-label">محصولات انتخابی:</span>
                            <span class="stat-value selected-products-count">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">زمان تخمینی:</span>
                            <span class="stat-value estimated-time">0 دقیقه</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bulk-import-actions">
                <button type="button" id="dpi-bulk-import-btn" class="button button-primary button-hero" disabled>
                    <span class="dashicons dashicons-download"></span>
                    شروع ورود گروهی
                </button>
                
                <div class="import-warnings">
                    <p class="description">
                        <strong>توجه:</strong> لطفاً تا پایان عملیات صفحه را نبندید. ورود گروهی ممکن است چندین دقیقه طول بکشد.
                    </p>
                </div>
            </div>
        </div>

        <div id="dpi-bulk-progress" class="dpi-bulk-progress" style="display: none;">
            <h2>گزارش ورود گروهی</h2>
            
            <div class="progress-header">
                <div class="progress-info">
                    <div class="progress-stats">
                        <span class="success-count">موفق: 0</span>
                        <span class="error-count">خطا: 0</span>
                        <span class="total-count">کل: 0</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
                
                <div class="progress-actions">
                    <button type="button" id="pause-import" class="button button-secondary" style="display: none;">
                        <span class="dashicons dashicons-controls-pause"></span>
                        توقف موقت
                    </button>
                    <button type="button" id="cancel-import" class="button button-secondary" style="display: none;">
                        <span class="dashicons dashicons-no"></span>
                        لغو عملیات
                    </button>
                </div>
            </div>
            
            <div class="log-container">
                <div class="log-header">
                    <h4>جزئیات عملیات</h4>
                    <div class="log-controls">
                        <button type="button" id="clear-log" class="button button-small">پاک کردن لاگ</button>
                        <button type="button" id="download-log" class="button button-small">دانلود گزارش</button>
                    </div>
                </div>
                <div id="dpi-import-log" class="import-log"></div>
            </div>
        </div>

        <div id="dpi-loading" class="dpi-loading" style="display: none;">
            <p>در حال پردازش... لطفاً صبر کنید</p>
            <div class="dpi-spinner"></div>
            <p class="loading-detail">دریافت اطلاعات از دیجی‌کالا</p>
        </div>
    </div>

    <!-- Help and Tips Section -->
    <div class="dpi-container" style="margin-top: 40px;">
        <div class="dpi-form-section">
            <h2>نکات مهم و راهنمایی‌ها</h2>
            
            <div class="tips-grid">
                <div class="tip-card">
                    <h4><span class="dashicons dashicons-info"></span> نحوه انتخاب دسته‌بندی</h4>
                    <ul>
                        <li>به سایت دیجی‌کالا مراجعه کنید</li>
                        <li>دسته‌بندی مورد نظر را انتخاب کنید</li>
                        <li>در صورت نیاز فیلترهای برند، قیمت و غیره را اعمال کنید</li>
                        <li>آدرس نهایی صفحه را کپی و در فیلد بالا وارد کنید</li>
                    </ul>
                </div>
                
                <div class="tip-card">
                    <h4><span class="dashicons dashicons-admin-settings"></span> تنظیمات پیشنهادی</h4>
                    <ul>
                        <li><strong>موجودی:</strong> حداقل 10 عدد برای محصولات جدید</li>
                        <li><strong>تصاویر:</strong> فعال کردن گالری برای نمایش بهتر</li>
                        <li><strong>گارانتی:</strong> تنظیم متن مناسب بر اساس نوع محصول</li>
                        <li><strong>همتا:</strong> فعال کردن برای محصولات موبایل و تبلت</li>
                    </ul>
                </div>
                
                <div class="tip-card">
                    <h4><span class="dashicons dashicons-clock"></span> زمان‌بندی عملیات</h4>
                    <ul>
                        <li>هر محصول حدود 3-5 ثانیه زمان می‌برد</li>
                        <li>برای 50 محصول حدود 5 دقیقه زمان لازم است</li>
                        <li>در ساعات کم‌ترافیک (شب) سرعت بالاتر است</li>
                        <li>حتماً تا پایان عملیات صبر کنید</li>
                    </ul>
                </div>
                
                <div class="tip-card">
                    <h4><span class="dashicons dashicons-warning"></span> نکات مهم</h4>
                    <ul>
                        <li>محصولات تکراری به طور خودکار رد می‌شوند</li>
                        <li>تصاویر با کیفیت از دیجی‌کالا دانلود می‌شوند</li>
                        <li>مشخصات فنی به طور کامل وارد می‌شود</li>
                        <li>در صورت خطا، جزئیات در لاگ نمایش داده می‌شود</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 15px;
}

.settings-column h4 {
    margin: 0 0 15px 0;
    padding: 10px 15px;
    background: #f1f1f1;
    border-radius: 4px;
    border-right: 4px solid #0073aa;
}

.import-summary {
    grid-column: 1 / -1;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.import-summary h4 {
    margin: 0 0 15px 0;
    color: #0073aa;
}

.summary-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 18px;
    font-weight: bold;
    color: #0073aa;
}

.bulk-import-actions {
    text-align: center;
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
}

.bulk-import-actions .button-hero {
    padding: 15px 30px;
    font-size: 16px;
    min-height: auto;
}

.bulk-import-actions .dashicons {
    margin-left: 8px;
    vertical-align: middle;
}

.import-warnings {
    margin-top: 15px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.progress-actions {
    display: flex;
    gap: 10px;
}

.progress-actions .button {
    min-height: auto;
    padding: 8px 15px;
}

.progress-actions .dashicons {
    margin-left: 5px;
    vertical-align: middle;
}

.log-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.log-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f1f1f1;
    border-bottom: 1px solid #ddd;
}

.log-header h4 {
    margin: 0;
}

.log-controls {
    display: flex;
    gap: 10px;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.tip-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.tip-card h4 {
    margin: 0 0 15px 0;
    color: #0073aa;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tip-card h4 .dashicons {
    color: #0073aa;
}

.tip-card ul {
    margin: 0;
    padding-right: 20px;
}

.tip-card li {
    margin: 8px 0;
    line-height: 1.5;
}

.bulk-actions {
    margin-right: auto;
}

.bulk-actions label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .summary-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .progress-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .progress-actions {
        justify-content: center;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
    }
    
    .log-header {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .log-controls {
        justify-content: center;
    }
}
</style>