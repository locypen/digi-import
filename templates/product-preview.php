<?php
/**
 * Template for product preview
 * Path: templates/product-preview.php
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="dpi-product-preview">
    <div class="product-info-grid">
        <div class="product-images">
            <?php if (isset($product['images']['main']['url'][0])): ?>
                <img src="<?php echo esc_url($product['images']['main']['url'][0]); ?>" alt="<?php echo esc_attr($product['title_fa']); ?>" class="main-product-image">
            <?php endif; ?>
            
            <?php if (isset($product['images']['list']) && count($product['images']['list']) > 1): ?>
                <div class="gallery-images">
                    <?php foreach (array_slice($product['images']['list'], 0, 4) as $image): ?>
                        <?php if (isset($image['url'][0])): ?>
                            <img src="<?php echo esc_url($image['url'][0]); ?>" alt="گالری محصول" class="gallery-image">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-details">
            <h3><?php echo esc_html($product['title_fa']); ?></h3>
            
            <?php if (isset($product['title_en'])): ?>
                <p class="english-title"><?php echo esc_html($product['title_en']); ?></p>
            <?php endif; ?>
            
            <div class="product-meta">
                <?php if (isset($product['brand']['title_fa'])): ?>
                    <p><strong>برند:</strong> <?php echo esc_html($product['brand']['title_fa']); ?></p>
                <?php endif; ?>
                
                <?php if (isset($product['category']['title_fa'])): ?>
                    <p><strong>دسته‌بندی:</strong> <?php echo esc_html($product['category']['title_fa']); ?></p>
                <?php endif; ?>
                
                <p><strong>شناسه دیجی‌کالا:</strong> <?php echo esc_html($product['id']); ?></p>
            </div>

            <?php if (isset($product['default_variant']['price']['selling_price'])): ?>
                <div class="price-info">
                    <p class="price"><strong>قیمت:</strong> <?php echo number_format($product['default_variant']['price']['selling_price'] / 10); ?> تومان</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($product['variants']) && count($product['variants']) > 1): ?>
        <div class="variants-section">
            <h4>انتخاب رنگ‌های مورد نظر:</h4>
            <div class="variants-grid">
                <?php 
                $unique_colors = [];
                foreach ($product['variants'] as $index => $variant): 
                    $color = $variant['color']['title'] ?? 'بدون رنگ';
                    $price = $variant['price']['selling_price'] / 10;
                    
                    // حذف رنگ‌های تکراری - فقط گران‌ترین را نگه داریم
                    if (isset($unique_colors[$color])) {
                        if ($price > $unique_colors[$color]['price']) {
                            $unique_colors[$color] = [
                                'index' => $index,
                                'price' => $price,
                                'variant' => $variant
                            ];
                        }
                        continue;
                    } else {
                        $unique_colors[$color] = [
                            'index' => $index,
                            'price' => $price,
                            'variant' => $variant
                        ];
                    }
                endforeach; ?>
                
                <?php foreach ($unique_colors as $color => $data): 
                    $variant = $data['variant'];
                    $index = $data['index'];
                ?>
                    <div class="variant-item">
                        <label>
                            <input type="checkbox" name="selected_variants[]" value="<?php echo $index; ?>" checked>
                            <div class="variant-info">
                                <strong><?php echo esc_html($color); ?></strong>
                                <span class="variant-price"><?php echo number_format($data['price']); ?> تومان</span>
                                <?php if (isset($variant['color']['hex_code'])): ?>
                                    <div class="color-preview" style="background-color: <?php echo esc_attr($variant['color']['hex_code']); ?>"></div>
                                <?php endif; ?>
                            </div>
                        </label>
                        
                        <div class="variant-fields">
                            <input type="hidden" name="variant_colors[<?php echo $index; ?>]" value="<?php echo esc_attr($color); ?>">
                            <input type="hidden" name="variant_original_prices[<?php echo $index; ?>]" value="<?php echo $data['price']; ?>">
                            <input type="hidden" name="variant_ids[<?php echo $index; ?>]" value="<?php echo esc_attr($variant['id']); ?>">
                            <input type="hidden" name="variant_color_hex[<?php echo $index; ?>]" value="<?php echo esc_attr($variant['color']['hex_code'] ?? ''); ?>">
                            
                            <label>قیمت سفارشی:</label>
                            <input type="number" name="variant_custom_prices[<?php echo $index; ?>]" value="<?php echo $data['price']; ?>" step="0.01" min="0">
                            
                            <label>موجودی:</label>
                            <input type="number" name="variant_stock[<?php echo $index; ?>]" value="25" min="0">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="single-product-price">
            <h4>تنظیم قیمت محصول:</h4>
            <label>قیمت محصول:</label>
            <input type="number" name="single_product_price" value="<?php echo isset($product['default_variant']['price']['selling_price']) ? ($product['default_variant']['price']['selling_price'] / 10) : 0; ?>" step="0.01" min="0">
        </div>
    <?php endif; ?>

    <?php if (isset($product['specifications'])): ?>
        <div class="specifications-section">
            <h4>انتخاب مشخصات فنی:</h4>
            <div class="specs-grid">
                <?php foreach ($product['specifications'] as $spec_group): ?>
                    <?php if (isset($spec_group['attributes'])): ?>
                        <div class="spec-group">
                            <h5><?php echo esc_html($spec_group['title']); ?></h5>
                            <?php foreach ($spec_group['attributes'] as $attribute): ?>
                                <?php if (!empty($attribute['values'])): ?>
                                    <label>
                                        <input type="checkbox" name="selected_specs[]" value="<?php echo esc_attr($spec_group['title'] . '|' . $attribute['title']); ?>" checked>
                                        <strong><?php echo esc_html($attribute['title']); ?>:</strong>
                                        <?php echo esc_html(implode(', ', $attribute['values'])); ?>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.dpi-product-preview {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.product-info-grid {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.main-product-image {
    width: 100%;
    max-width: 200px;
    height: auto;
    border-radius: 4px;
    border: 1px solid #eee;
}

.gallery-images {
    display: flex;
    gap: 5px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.gallery-image {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #eee;
}

.english-title {
    color: #666;
    font-style: italic;
    margin-bottom: 10px;
}

.product-meta p {
    margin: 5px 0;
}

.price-info .price {
    font-size: 18px;
    color: #e91e63;
    font-weight: bold;
}

.variants-section {
    margin-top: 20px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.variants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.variant-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    background: #f9f9f9;
}

.variant-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.variant-price {
    color: #e91e63;
    font-weight: bold;
}

.color-preview {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 3px rgba(0,0,0,0.3);
}

.variant-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    align-items: center;
}

.variant-fields label {
    font-weight: bold;
    font-size: 12px;
}

.variant-fields input[type="number"] {
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 12px;
}

.specifications-section {
    margin-top: 20px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.spec-group {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 6px;
}

.spec-group h5 {
    margin: 0 0 10px 0;
    color: #333;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
}

.spec-group label {
    display: block;
    margin: 8px 0;
    font-size: 13px;
    cursor: pointer;
}

.spec-group input[type="checkbox"] {
    margin-left: 8px;
}

.single-product-price {
    margin-top: 20px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.single-product-price label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.single-product-price input {
    width: 200px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .product-info-grid {
        grid-template-columns: 1fr;
    }
    
    .variants-grid,
    .specs-grid {
        grid-template-columns: 1fr;
    }
}
</style>