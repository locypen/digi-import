jQuery(document).ready(function($) {
    // Variables for single product import
    const $form = $('#dpi-import-form');
    const $previewBtn = $('#dpi-preview-btn');
    const $importBtn = $('#dpi-import-btn');
    const $previewSection = $('#dpi-preview-section');
    const $resultSection = $('#dpi-result-section');
    const $loading = $('#dpi-loading');
    const $productPreview = $('#dpi-product-preview');
    const $digikalaUrl = $('#digikala_url');

    // Variables for bulk import
    const $bulkForm = $('#dpi-bulk-form');
    const $fetchCategoryBtn = $('#dpi-fetch-category-btn');
    const $bulkImportBtn = $('#dpi-bulk-import-btn');
    const $categoryProducts = $('#dpi-category-products');
    const $categoryInfo = $('#dpi-category-info');
    const $productsTable = $('#dpi-products-table');
    const $pagination = $('#dpi-pagination');
    const $bulkProgress = $('#dpi-bulk-progress');
    const $categoryUrl = $('#category_url');

    // Variables for price sync
    const $manualSyncBtn = $('#dpi-manual-sync-btn');
    const $scheduleSyncBtn = $('#dpi-schedule-sync-btn');
    const $syncProgress = $('#dpi-sync-progress');

    let currentProductData = null;
    let currentCategoryData = null;
    let currentPage = 1;

    // ============================================
    // SINGLE PRODUCT IMPORT FUNCTIONS
    // ============================================

    // Single product preview
    $previewBtn.on('click', function() {
        const url = $digikalaUrl.val().trim();

        if (!url) {
            showNotification(dpi_ajax.i18n.invalid_url, 'error');
            return;
        }

        showLoading();
        hideResults();

        $.ajax({
            url: dpi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dpi_fetch_product',
                nonce: dpi_ajax.nonce,
                url: url
            },
            success: function(response) {
                if (response.success) {
                    currentProductData = response.data.product;
                    $productPreview.html(response.data.html);
                    $previewSection.show();
                    
                    // Set English name in field
                    if (response.data.product.title_en) {
                        $('#english_name').val(response.data.product.title_en);
                    }

                    // Show specifications section if available
                    if (response.data.product.specifications && response.data.product.specifications.length > 0) {
                        $('#dpi-specifications-section').show();
                    }

                    showNotification('محصول با موفقیت دریافت شد!', 'success');
                } else {
                    showNotification(response.data.message || dpi_ajax.i18n.error_fetching, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                showNotification(dpi_ajax.i18n.server_error + ': ' + error, 'error');
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Single product import
    $importBtn.on('click', function() {
        if (!currentProductData) {
            showNotification(dpi_ajax.i18n.preview_first, 'error');
            return;
        }

        const customFields = {
            warranty_text: $('#warranty_text').val(),
            english_name: $('#english_name').val(),
            stock_quantity: $('#stock_quantity').val(),
            show_hamta: $('#show_hamta').val()
        };

        const imageSettings = {
            set_featured: $('#set_featured').val(),
            import_gallery: $('#import_gallery').val(),
            max_gallery_images: $('#max_gallery_images').val()
        };

        // Collect selected specifications
        const selectedSpecs = [];
        $('input[name="selected_specs[]"]:checked').each(function() {
            selectedSpecs.push($(this).val());
        });

        // Collect selected variants
        const selectedVariants = [];
        $('input[name="selected_variants[]"]:checked').each(function() {
            const index = $(this).val();
            const variantData = {
                index: index,
                color: $('input[name="variant_colors[' + index + ']"]').val(),
                original_price: $('input[name="variant_original_prices[' + index + ']"]').val(),
                custom_price: $('input[name="variant_custom_prices[' + index + ']"]').val(),
                stock: $('input[name="variant_stock[' + index + ']"]').val(),
                variant_id: $('input[name="variant_ids[' + index + ']"]').val(),
                color_hex: $('input[name="variant_color_hex[' + index + ']"]').val()
            };
            selectedVariants.push(variantData);
        });

        // Single product price
        const singleProductPrice = $('input[name="single_product_price"]').val();

        showLoading();
        disableImportButtons();

        $.ajax({
            url: dpi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dpi_import_product',
                nonce: dpi_ajax.nonce,
                url: $digikalaUrl.val(),
                custom_fields: customFields,
                image_settings: imageSettings,
                selected_specs: selectedSpecs,
                selected_variants: selectedVariants,
                single_product_price: singleProductPrice
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(`
                        ${response.data.message}<br>
                        <a href="${response.data.edit_url}" target="_blank" class="button button-secondary">ویرایش محصول</a> 
                        <a href="${response.data.product_url}" target="_blank" class="button button-secondary">مشاهده محصول</a>
                    `);
                    
                    // Reset form for new product import
                    resetSingleForm();
                } else {
                    showNotification(response.data.message || dpi_ajax.i18n.import_error, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Import Error:', status, error);
                showNotification(dpi_ajax.i18n.server_error + ': ' + error, 'error');
            },
            complete: function() {
                hideLoading();
                enableImportButtons();
            }
        });
    });

    // ============================================
    // BULK IMPORT FUNCTIONS
    // ============================================

    // Fetch category products
    $fetchCategoryBtn.on('click', function() {
        const url = $categoryUrl.val().trim();

        if (!url) {
            showNotification('لطفاً لینک دسته‌بندی را وارد کنید', 'error');
            return;
        }

        if (!url.includes('digikala.com')) {
            showNotification('لطفاً فقط لینک‌های دیجی‌کالا را وارد کنید', 'error');
            return;
        }

        currentPage = 1;
        fetchCategoryProducts(url, currentPage);
    });

    function fetchCategoryProducts(url, page) {
        showLoading();
        $categoryProducts.hide();

        $.ajax({
            url: dpi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dpi_fetch_category_products',
                nonce: dpi_ajax.nonce,
                url: url,
                page: page
            },
            success: function(response) {
                if (response.success) {
                    currentCategoryData = response.data;
                    displayCategoryProducts(response.data);
                    $categoryProducts.show();
                    showNotification(`${response.data.products.length} محصول یافت شد!`, 'success');
                } else {
                    showNotification(response.data.message || 'خطا در دریافت محصولات دسته‌بندی', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Category fetch error:', status, error);
                showNotification('خطا در دریافت محصولات: ' + error, 'error');
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    function displayCategoryProducts(data) {
        // Display category info
        const categoryInfo = `
            <h4>اطلاعات دسته‌بندی</h4>
            <p><strong>دسته‌بندی:</strong> ${data.category_info.name}</p>
            ${data.category_info.brand ? `<p><strong>برند:</strong> ${data.category_info.brand}</p>` : ''}
            <p><strong>تعداد کل محصولات:</strong> ${data.category_info.total_products.toLocaleString('fa-IR')} محصول</p>
            <p><strong>صفحه فعلی:</strong> ${data.pagination.current_page} از ${data.pagination.total_pages}</p>
        `;
        $categoryInfo.html(categoryInfo);

        // Display products table
        let tableHtml = `
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">انتخاب</th>
                        <th style="width: 80px;">تصویر</th>
                        <th>نام محصول</th>
                        <th>دسته‌بندی</th>
                        <th>برند</th>
                        <th>قیمت</th>
                        <th>وضعیت</th>
                        <th>شناسه</th>
                    </tr>
                </thead>
                <tbody>
        `;

        data.products.forEach(function(product) {
            const price = product.price > 0 ? product.price.toLocaleString('fa-IR') + ' تومان' : 'نامشخص';
            const status = product.status === 'marketable' ? 'موجود' : 'ناموجود';
            const statusClass = product.status === 'marketable' ? 'in-stock' : 'out-of-stock';
            const existingClass = product.exists ? 'existing-product' : '';
            const existingStatus = product.exists ? 'موجود در فروشگاه' : '';
            
            tableHtml += `
                <tr class="${existingClass}">
                    <td>
                        <input type="checkbox" name="selected_products[]" value="${product.id}" class="product-checkbox" ${product.exists ? 'disabled' : ''}>
                    </td>
                    <td>
                        ${product.image ? `<img src="${product.image}" class="product-image" alt="" loading="lazy">` : '<div class="no-image">بدون تصویر</div>'}
                    </td>
                    <td class="product-title" title="${product.title_fa}">${product.title_fa}</td>
                    <td class="product-category">${product.category}</td>
                    <td class="product-brand">${product.brand || 'نامشخص'}</td>
                    <td class="product-price">${price}</td>
                    <td>
                        <span class="product-status ${statusClass}">${status}</span>
                        ${existingStatus ? `<br><span class="product-status existing">${existingStatus}</span>` : ''}
                    </td>
                    <td><code>${product.id}</code></td>
                </tr>
            `;
        });

        tableHtml += `
                </tbody>
            </table>
        `;

        $productsTable.html(tableHtml);

        // Display pagination
        displayPagination(data.pagination);

        // Update selected count
        updateSelectedCount();
    }

    function displayPagination(pagination) {
        let paginationHtml = '';

        if (pagination.total_pages > 1) {
            paginationHtml += `<div class="page-info">صفحه ${pagination.current_page} از ${pagination.total_pages} (${pagination.total_rows} محصول)</div>`;

            // Previous button
            paginationHtml += `<button type="button" class="pagination-btn" data-page="${pagination.current_page - 1}" ${pagination.current_page === 1 ? 'disabled' : ''}>قبلی</button>`;

            // Page numbers
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

            if (startPage > 1) {
                paginationHtml += `<button type="button" class="pagination-btn" data-page="1">1</button>`;
                if (startPage > 2) {
                    paginationHtml += `<span>...</span>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `<button type="button" class="pagination-btn ${i === pagination.current_page ? 'current' : ''}" data-page="${i}">${i}</button>`;
            }

            if (endPage < pagination.total_pages) {
                if (endPage < pagination.total_pages - 1) {
                    paginationHtml += `<span>...</span>`;
                }
                paginationHtml += `<button type="button" class="pagination-btn" data-page="${pagination.total_pages}">${pagination.total_pages}</button>`;
            }

            // Next button
            paginationHtml += `<button type="button" class="pagination-btn" data-page="${pagination.current_page + 1}" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>بعدی</button>`;
        }

        $pagination.html(paginationHtml);
    }

    // Pagination click handler
    $(document).on('click', '.pagination-btn', function() {
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage && !$(this).is(':disabled')) {
            currentPage = page;
            fetchCategoryProducts($categoryUrl.val(), page);
        }
    });

    // Select all products
    $(document).on('click', '#select-all-products', function() {
        $('.product-checkbox:not(:disabled)').prop('checked', true);
        updateSelectedCount();
    });

    // Deselect all products
    $(document).on('click', '#deselect-all-products', function() {
        $('.product-checkbox').prop('checked', false);
        updateSelectedCount();
    });

    // Update selected count when checkbox changes
    $(document).on('change', '.product-checkbox', function() {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const selectedCount = $('.product-checkbox:checked').length;
        const totalCount = $('.product-checkbox:not(:disabled)').length;
        $('.selected-count').text(`${selectedCount} از ${totalCount} محصول انتخاب شده`);
        $bulkImportBtn.prop('disabled', selectedCount === 0);
    }

    // Bulk import
    $bulkImportBtn.on('click', function() {
        const selectedProducts = [];
        $('.product-checkbox:checked').each(function() {
            selectedProducts.push($(this).val());
        });

        if (selectedProducts.length === 0) {
            showNotification(dpi_ajax.i18n.select_products, 'error');
            return;
        }

        if (!confirm(`آیا مطمئن هستید که می‌خواهید ${selectedProducts.length} محصول را وارد کنید؟`)) {
            return;
        }

        const customFields = {
            warranty_text: $('#bulk_warranty_text').val(),
            english_name: '',
            stock_quantity: $('#bulk_stock_quantity').val(),
            show_hamta: $('#bulk_show_hamta').val()
        };

        const imageSettings = {
            set_featured: $('#bulk_set_featured').val(),
            import_gallery: $('#bulk_import_gallery').val(),
            max_gallery_images: $('#bulk_max_gallery_images').val()
        };

        startBulkImport(selectedProducts, customFields, imageSettings);
    });

    function startBulkImport(productIds, customFields, imageSettings) {
        $bulkProgress.show();
        $bulkImportBtn.prop('disabled', true);
        showLoading();
        
        const $progressFill = $('.progress-fill');
        const $successCount = $('.success-count');
        const $errorCount = $('.error-count');
        const $totalCount = $('.total-count');
        const $importLog = $('#dpi-import-log');

        let successCount = 0;
        let errorCount = 0;
        const totalCount = productIds.length;

        $totalCount.text(`کل: ${totalCount}`);
        $importLog.html('');
        $progressFill.css('width', '0%');

        addLogEntry('info', `شروع ورود گروهی ${totalCount} محصول...`);
        addLogEntry('info', 'لطفاً تا پایان عملیات صبر کنید و صفحه را ببندید نبندید.');

        $.ajax({
            url: dpi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dpi_bulk_import_products',
                nonce: dpi_ajax.nonce,
                product_ids: productIds,
                custom_fields: customFields,
                image_settings: imageSettings
            },
            timeout: 300000, // 5 minutes timeout
            success: function(response) {
                if (response.success) {
                    successCount = response.data.success_count;
                    errorCount = response.data.error_count;

                    response.data.results.forEach(function(result) {
                        if (result.status === 'success') {
                            addLogEntry('success', `✓ محصول "${result.title}" با موفقیت وارد شد (ID: ${result.wc_product_id})`);
                        } else {
                            addLogEntry('error', `✗ خطا در ورود محصول ${result.product_id}: ${result.message}`);
                        }
                    });

                    addLogEntry('info', `✓ ورود گروهی کامل شد. ${successCount} موفق، ${errorCount} خطا`);
                    
                    if (successCount > 0) {
                        showNotification(`${successCount} محصول با موفقیت وارد شد!`, 'success');
                    }
                } else {
                    addLogEntry('error', response.data.message || 'خطا در ورود گروهی');
                    showNotification('خطا در ورود گروهی محصولات', 'error');
                }

                $successCount.text(`موفق: ${successCount}`);
                $errorCount.text(`خطا: ${errorCount}`);
                $progressFill.css('width', '100%');
            },
            error: function(xhr, status, error) {
                console.error('Bulk import error:', status, error);
                addLogEntry('error', `خطای سرور در ورود گروهی: ${error}`);
                $errorCount.text(`خطا: ${totalCount}`);
                showNotification('خطای سرور در ورود گروهی', 'error');
            },
            complete: function() {
                hideLoading();
                $bulkImportBtn.prop('disabled', false);
                // Refresh the products list
                if (currentCategoryData) {
                    fetchCategoryProducts($categoryUrl.val(), currentPage);
                }
            }
        });
    }

    // ============================================
    // PRICE SYNC FUNCTIONS
    // ============================================

    // Manual price sync
    $manualSyncBtn.on('click', function() {
        if (!confirm('آیا مطمئن هستید که می‌خواهید قیمت تمام محصولات را بروزرسانی کنید؟')) {
            return;
        }

        startPriceSync();
    });

    // Schedule sync
    $scheduleSyncBtn.on('click', function() {
        showNotification('بروزرسانی خودکار مجدداً برنامه‌ریزی شد', 'success');
        // This would normally trigger a server-side action to reschedule
    });

    function startPriceSync() {
        $syncProgress.show();
        $manualSyncBtn.prop('disabled', true);
        showLoading();

        const $progressFill = $('.sync-progress .progress-fill');
        const $updatedCount = $('.updated-count');
        const $errorCount = $('.sync-progress .error-count');
        const $totalCount = $('.sync-progress .total-count');
        const $syncLog = $('#dpi-sync-log');

        $syncLog.html('');
        $progressFill.css('width', '0%');
        addSyncLogEntry('info', 'شروع بروزرسانی قیمت‌ها...');

        $.ajax({
            url: dpi_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dpi_sync_prices',
                nonce: dpi_ajax.nonce
            },
            timeout: 120000, // 2 minutes timeout
            success: function(response) {
                if (response.success) {
                    const results = response.data.results;
                    let updatedCount = 0;
                    let errorCount = 0;

                    results.results.forEach(function(result) {
                        if (result.status === 'updated') {
                            updatedCount++;
                            addSyncLogEntry('success', `✓ قیمت محصول "${result.title}" بروزرسانی شد (SKU: ${result.sku})`);
                        } else {
                            errorCount++;
                            addSyncLogEntry('error', `✗ خطا در بروزرسانی محصول ${result.sku}: ${result.message}`);
                        }
                    });

                    $updatedCount.text(`بروزرسانی شده: ${updatedCount}`);
                    $errorCount.text(`خطا: ${errorCount}`);
                    $totalCount.text(`کل: ${results.results.length}`);
                    $progressFill.css('width', '100%');

                    addSyncLogEntry('info', `✓ بروزرسانی کامل شد. ${updatedCount} محصول بروزرسانی شد`);
                    showNotification(`${updatedCount} محصول بروزرسانی شد`, 'success');
                } else {
                    addSyncLogEntry('error', response.data.message || 'خطا در بروزرسانی قیمت‌ها');
                    showNotification('خطا در بروزرسانی قیمت‌ها', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Price sync error:', status, error);
                addSyncLogEntry('error', `خطای سرور در بروزرسانی قیمت‌ها: ${error}`);
                showNotification('خطای سرور در بروزرسانی', 'error');
            },
            complete: function() {
                hideLoading();
                $manualSyncBtn.prop('disabled', false);
            }
        });
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    function addLogEntry(type, message) {
        const $importLog = $('#dpi-import-log');
        const timestamp = new Date().toLocaleTimeString('fa-IR');
        const entry = $(`<div class="log-entry ${type}">[${timestamp}] ${message}</div>`);
        $importLog.append(entry);
        $importLog.scrollTop($importLog[0].scrollHeight);
    }

    function addSyncLogEntry(type, message) {
        const $syncLog = $('#dpi-sync-log');
        const timestamp = new Date().toLocaleTimeString('fa-IR');
        const entry = $(`<div class="log-entry ${type}">[${timestamp}] ${message}</div>`);
        $syncLog.append(entry);
        $syncLog.scrollTop($syncLog[0].scrollHeight);
    }

    // Gallery settings change
    $('#import_gallery, #bulk_import_gallery').on('change', function() {
        const $maxImagesRow = $(this).attr('id') === 'import_gallery' 
            ? $('#max_gallery_images').closest('tr')
            : $('#bulk_max_gallery_images').closest('tr');
            
        if ($(this).val() === 'yes') {
            $maxImagesRow.show();
        } else {
            $maxImagesRow.hide();
        }
    });

    // URL validation
    $digikalaUrl.on('input', function() {
        const url = $(this).val();
        
        if (url && !url.includes('digikala.com')) {
            $(this).css('border-color', '#dc3232');
            $previewBtn.prop('disabled', true);
            showNotification('لطفاً فقط لینک‌های دیجی‌کالا را وارد کنید', 'warning');
        } else {
            $(this).css('border-color', '');
            $previewBtn.prop('disabled', false);
        }
        
        // Reset preview if URL changed
        if (currentProductData) {
            $previewSection.hide();
            $resultSection.hide();
            currentProductData = null;
        }
    });

    $categoryUrl.on('input', function() {
        const url = $(this).val();
        
        if (url && !url.includes('digikala.com')) {
            $(this).css('border-color', '#dc3232');
            $fetchCategoryBtn.prop('disabled', true);
        } else {
            $(this).css('border-color', '');
            $fetchCategoryBtn.prop('disabled', false);
        }
        
        // Reset category data if URL changed
        if (currentCategoryData) {
            $categoryProducts.hide();
            currentCategoryData = null;
        }
    });

    function resetSingleForm() {
        $digikalaUrl.val('');
        $('#warranty_text').val('گارانتی 18 ماهه');
        $('#english_name').val('');
        $('#stock_quantity').val('25');
        $('#show_hamta').val('no');
        $('#set_featured').val('yes');
        $('#import_gallery').val('yes');
        $('#max_gallery_images').val('10');
        
        $previewSection.hide();
        $resultSection.hide();
        $('#dpi-specifications-section').hide();
        currentProductData = null;
    }

    function showLoading() {
        $loading.show();
        $previewSection.find('.dpi-form-section').addClass('loading');
    }

    function hideLoading() {
        $loading.hide();
        $previewSection.find('.dpi-form-section').removeClass('loading');
    }

    function hideResults() {
        $resultSection.hide();
    }

    function disableImportButtons() {
        $previewBtn.prop('disabled', true);
        $importBtn.prop('disabled', true);
        $fetchCategoryBtn.prop('disabled', true);
        $bulkImportBtn.prop('disabled', true);
    }

    function enableImportButtons() {
        $previewBtn.prop('disabled', false);
        $importBtn.prop('disabled', false);
        $fetchCategoryBtn.prop('disabled', false);
        // Bulk import button state depends on selection
        updateSelectedCount();
    }

    function showSuccess(message) {
        showResult(message, 'success');
    }

    function showError(message) {
        showResult(message, 'error');
    }

    function showResult(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        $resultSection.find('#dpi-result-message').html(`
            <div class="notice ${noticeClass} dpi-notice" style="padding: 15px; margin: 15px 0;">
                ${message}
            </div>
        `);
        $resultSection.show();
        
        // Scroll to result
        $('html, body').animate({
            scrollTop: $resultSection.offset().top - 50
        }, 500);
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const noticeClasses = {
            'success': 'notice-success',
            'error': 'notice-error', 
            'warning': 'notice-warning',
            'info': 'notice-info'
        };
        
        const noticeClass = noticeClasses[type] || 'notice-info';
        
        const $notification = $(`
            <div class="notice ${noticeClass} is-dismissible dpi-notification" style="margin: 15px 0; padding: 12px 20px; border-radius: 4px;">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">بستن</span>
                </button>
            </div>
        `);
        
        // Add to page
        $('.dpi-container').first().prepend($notification);
        
        // Auto remove after 5 seconds for success/info messages
        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Handle dismiss button
        $notification.on('click', '.notice-dismiss', function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Scroll to notification
        $('html, body').animate({
            scrollTop: $notification.offset().top - 50
        }, 300);
    }

    // Initialize tooltips
    $(document).on('mouseenter', '[title]', function() {
        const $this = $(this);
        const title = $this.attr('title');
        
        if (title && title.length > 50) {
            $this.attr('data-original-title', title);
            $this.attr('title', '');
            
            const $tooltip = $('<div class="dpi-tooltip">' + title + '</div>');
            $('body').append($tooltip);
            
            const offset = $this.offset();
            $tooltip.css({
                top: offset.top - $tooltip.outerHeight() - 5,
                left: offset.left + ($this.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
            }).fadeIn(200);
        }
    });

    $(document).on('mouseleave', '[data-original-title]', function() {
        const $this = $(this);
        $this.attr('title', $this.attr('data-original-title'));
        $this.removeAttr('data-original-title');
        $('.dpi-tooltip').fadeOut(200, function() {
            $(this).remove();
        });
    });

    // Handle form submission with Enter key
    $digikalaUrl.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $previewBtn.click();
        }
    });

    $categoryUrl.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $fetchCategoryBtn.click();
        }
    });

    // Initialize max gallery images visibility
    $('#import_gallery, #bulk_import_gallery').trigger('change');
});