<div class="wrap">
    <h1>ورود تکی محصول از دیجی‌کالا</h1>
    
    <div class="notice notice-info">
        <p><strong>راهنمای استفاده:</strong></p>
        <ul>
            <li>لینک محصول مورد نظر را از دیجی‌کالا کپی کرده و در فیلد زیر وارد کنید</li>
            <li>پس از پیش‌نمایش، تنظیمات مورد نظر خود را اعمال کنید</li>
            <li>محصول با شناسه دیجی‌کالا (SKU) و اطلاعات کامل وارد خواهد شد</li>
            <li>برای محصولات دارای رنگ‌های مختلف، فقط رنگ‌های منحصر به فرد اضافه می‌شوند</li>
        </ul>
    </div>
    
    <div class="dpi-container">
        <div class="dpi-form-section">
            <h2>اطلاعات محصول</h2>
            
            <form id="dpi-import-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="digikala_url">لینک محصول دیجی‌کالا</label></th>
                        <td>
                            <input type="url" id="digikala_url" name="digikala_url" class="regular-text" placeholder="https://www.digikala.com/product/dkp-xxxxxxx/" required>
                            <p class="description">لینک کامل محصول از دیجی‌کالا را وارد کنید</p>
                            <p class="description"><strong>مثال:</strong> https://www.digikala.com/product/dkp-12345678/</p>
                        </td>
                    </tr>
                </table>

                <button type="button" id="dpi-preview-btn" class="button button-primary">پیش‌نمایش محصول</button>
            </form>
        </div>

        <div id="dpi-preview-section" class="dpi-preview-section" style="display: none;">
            <h2>پیش‌نمایش و تنظیمات</h2>
            
            <div id="dpi-product-preview"></div>
            
            <div class="dpi-custom-fields">
                <h3>فیلدهای قابل تنظیم</h3>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="warranty_text">متن گارانتی</label></th>
                        <td>
                            <input type="text" id="warranty_text" name="warranty_text" class="regular-text" value="گارانتی 18 ماهه">
                            <p class="description">متن گارانتی که در صفحه محصول نمایش داده می‌شود</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="english_name">نام انگلیسی</label></th>
                        <td>
                            <input type="text" id="english_name" name="english_name" class="regular-text">
                            <p class="description">نام انگلیسی محصول (به صورت خودکار از دیجی‌کالا دریافت می‌شود)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="stock_quantity">تعداد موجودی</label></th>
                        <td>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="25" min="0">
                            <p class="description">تعداد موجودی اولیه محصول</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="show_hamta">نمایش هشدار همتا</label></th>
                        <td>
                            <select id="show_hamta" name="show_hamta">
                                <option value="no">خیر</option>
                                <option value="yes">بله</option>
                            </select>
                            <p class="description">نمایش هشدار سامانه همتا در صفحه محصول</p>
                        </td>
                    </tr>
                </table>

                <h3>تنظیمات تصاویر</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="set_featured">تنظیم تصویر شاخص</label></th>
                        <td>
                            <select id="set_featured" name="set_featured">
                                <option value="yes">بله</option>
                                <option value="no">خیر</option>
                            </select>
                            <p class="description">آیا تصویر اصلی محصول به عنوان تصویر شاخص تنظیم شود؟</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="import_gallery">واردکردن گالری</label></th>
                        <td>
                            <select id="import_gallery" name="import_gallery">
                                <option value="yes">بله</option>
                                <option value="no">خیر</option>
                            </select>
                            <p class="description">آیا تصاویر دیگر محصول به گالری اضافه شوند؟</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="max_gallery_images">حداکثر تصاویر گالری</label></th>
                        <td>
                            <input type="number" id="max_gallery_images" name="max_gallery_images" value="10" min="1" max="20">
                            <p class="description">حداکثر تعداد تصاویری که به گالری اضافه می‌شود</p>
                        </td>
                    </tr>
                </table>

                <div id="dpi-specifications-section" style="display: none;">
                    <h3>انتخاب مشخصات فنی</h3>
                    <p class="description">ویژگی‌هایی که می‌خواهید به محصول اضافه شوند را انتخاب کنید:</p>
                    <div id="dpi-specifications-list"></div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="button" id="dpi-import-btn" class="button button-primary button-hero">وارد کردن محصول</button>
            </div>
        </div>

        <div id="dpi-result-section" class="dpi-result-section" style="display: none;">
            <h2>نتیجه عملیات</h2>
            <div id="dpi-result-message"></div>
        </div>

        <div id="dpi-loading" class="dpi-loading" style="display: none;">
            <p>در حال پردازش... لطفاً صبر کنید</p>
            <div class="dpi-spinner"></div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="dpi-container" style="margin-top: 40px;">
        <div class="dpi-form-section">
            <h2>راهنمای کامل</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div style="background: #e3f2fd; padding: 15px; border-radius: 6px; border-right: 4px solid #2196f3;">
                    <h4 style="margin-top: 0; color: #1976d2;">ورود تکی محصول</h4>
                    <p>برای وارد کردن یک محصول خاص، لینک آن را در این صفحه وارد کنید.</p>
                    <ul>
                        <li>دریافت اطلاعات کامل محصول</li>
                        <li>پیش‌نمایش قبل از ورود</li>
                        <li>تنظیمات دقیق برای هر محصول</li>
                        <li>پشتیبانی از محصولات متغیر</li>
                    </ul>
                </div>
                
                <div style="background: #e8f5e8; padding: 15px; border-radius: 6px; border-right: 4px solid #4caf50;">
                    <h4 style="margin-top: 0; color: #388e3c;">ورود گروهی محصولات</h4>
                    <p>برای وارد کردن چندین محصول از یک دسته‌بندی، از بخش ورود گروهی استفاده کنید.</p>
                    <ul>
                        <li>دریافت تمام محصولات یک دسته‌بندی</li>
                        <li>فیلتر بر اساس برند</li>
                        <li>انتخاب محصولات مورد نظر</li>
                        <li>ورود دسته‌ای با تنظیمات یکسان</li>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=digikala-bulk-import'); ?>" class="button button-secondary">ورود گروهی</a>
                </div>
                
                <div style="background: #fff3e0; padding: 15px; border-radius: 6px; border-right: 4px solid #ff9800;">
                    <h4 style="margin-top: 0; color: #f57c00;">هماهنگ‌سازی قیمت</h4>
                    <p>قیمت محصولات وارد شده را به صورت خودکار یا دستی بروزرسانی کنید.</p>
                    <ul>
                        <li>بروزرسانی خودکار روزانه</li>
                        <li>بروزرسانی دستی</li>
                        <li>پشتیبانی از محصولات متغیر</li>
                        <li>گزارش‌گیری کامل</li>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=digikala-price-sync'); ?>" class="button button-secondary">هماهنگ‌سازی قیمت</a>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                <h4>نکات مهم:</h4>
                <ul>
                    <li><strong>SKU محصولات:</strong> شناسه دیجی‌کالا به عنوان SKU محصول در ووکامرس ذخیره می‌شود</li>
                    <li><strong>دسته‌بندی‌ها:</strong> دسته‌بندی‌های محصول به صورت خودکار ایجاد می‌شوند</li>
                    <li><strong>برندها:</strong> برندها در تاکسونومی product_brand ذخیره می‌شوند</li>
                    <li><strong>تگ‌ها:</strong> تگ‌های محصول از دیجی‌کالا اضافه می‌شوند</li>
                    <li><strong>تصاویر:</strong> تصاویر با کیفیت از سرور دیجی‌کالا دانلود و ذخیره می‌شوند</li>
                    <li><strong>محصولات تکراری:</strong> برای متغیرهایی با رنگ یکسان، فقط یکی با بالاترین قیمت اضافه می‌شود</li>
                </ul>
            </div>
        </div>
    </div>
</div>