<?php
// templates/category-products-table.php

if (!isset($data) || empty($data['products'])) {
    echo '<div class="notice notice-error"><p>هیچ محصولی یافت نشد</p></div>';
    return;
}
?>

<div class="dpi-category-info">
    <h3>اطلاعات دسته‌بندی</h3>
    <p><strong>نام دسته‌بندی:</strong> <?php echo esc_html($data['category_info']['name']); ?></p>
    <?php if (!empty($data['category_info']['brand'])): ?>
        <p><strong>برند:</strong> <?php echo esc_html($data['category_info']['brand']); ?></p>
    <?php endif; ?>
    <p><strong>تعداد کل محصولات:</strong> <?php echo number_format($data['category_info']['total_products']); ?></p>
    <p><strong>صفحه فعلی:</strong> <?php echo $data['pagination']['current_page']; ?> از <?php echo $data['pagination']['total_pages']; ?></p>
</div>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th style="width: 30px;"><input type="checkbox" id="select-all-products"></th>
            <th style="width: 80px;">تصویر</th>
            <th>نام محصول</th>
            <th>قیمت</th>
            <th>شناسه</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['products'] as $product): ?>
        <tr>
            <td><input type="checkbox" name="product_ids[]" value="<?php echo esc_attr($product['id']); ?>"></td>
            <td>
                <?php if (!empty($product['image'])): ?>
                    <img src="<?php echo esc_url($product['image']); ?>" style="max-width: 60px; height: auto;">
                <?php endif; ?>
            </td>
            <td><?php echo esc_html($product['title_fa']); ?></td>
            <td><?php echo number_format($product['price']); ?> تومان</td>
            <td><?php echo esc_html($product['id']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="dpi-pagination">
    <?php if ($data['pagination']['current_page'] > 1): ?>
        <button class="button" data-page="<?php echo $data['pagination']['current_page'] - 1; ?>">صفحه قبلی</button>
    <?php endif; ?>
    
    <?php if ($data['pagination']['current_page'] < $data['pagination']['total_pages']): ?>
        <button class="button button-primary" data-page="<?php echo $data['pagination']['current_page'] + 1; ?>">صفحه بعدی</button>
    <?php endif; ?>
</div>
