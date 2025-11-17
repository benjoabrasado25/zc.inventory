<?php
// Check access - only owner
ZCA_Auth::check_access('owner');

$page_title = 'Products';
$active_page = 'products';

include 'header.php';

$products = ZCA_Products::get_all_products();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-box"></i> Products Management</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle"></i> Add Product
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product->id; ?></td>
                                        <td><?php echo esc_html($product->sku); ?></td>
                                        <td><?php echo esc_html($product->name); ?></td>
                                        <td><?php echo esc_html(substr($product->description, 0, 50)) . (strlen($product->description) > 50 ? '...' : ''); ?></td>
                                        <td>$<?php echo number_format($product->price, 2); ?></td>
                                        <td>
                                            <?php if ($product->stock <= 10): ?>
                                                <span class="badge bg-danger"><?php echo $product->stock; ?></span>
                                            <?php elseif ($product->stock <= 20): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $product->stock; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo $product->stock; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product->stock > 0): ?>
                                                <span class="badge bg-success">In Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-product"
                                                    data-id="<?php echo $product->id; ?>"
                                                    data-name="<?php echo esc_attr($product->name); ?>"
                                                    data-description="<?php echo esc_attr($product->description); ?>"
                                                    data-price="<?php echo $product->price; ?>"
                                                    data-stock="<?php echo $product->stock; ?>"
                                                    data-sku="<?php echo esc_attr($product->sku); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-product"
                                                    data-id="<?php echo $product->id; ?>"
                                                    data-name="<?php echo esc_attr($product->name); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No products found. Add your first product!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addProductForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" class="form-control" name="sku">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price *</label>
                        <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" class="form-control" name="stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProductForm">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" class="form-control" name="sku" id="edit_sku">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price *</label>
                        <input type="number" class="form-control" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Add product
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=zca_add_product&nonce=' + zcaInventory.nonce;

        $.ajax({
            url: zcaInventory.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    zcShowToast(response.data.message, response.success ? 'success' : 'error');
                    location.reload();
                } else {
                    zcShowToast(response.data.message, response.success ? 'success' : 'error');
                }
            }
        });
    });

    // Edit product button
    $('.edit-product').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var price = $(this).data('price');
        var stock = $(this).data('stock');
        var sku = $(this).data('sku');

        $('#edit_product_id').val(id);
        $('#edit_name').val(name);
        $('#edit_description').val(description);
        $('#edit_price').val(price);
        $('#edit_stock').val(stock);
        $('#edit_sku').val(sku);

        $('#editProductModal').modal('show');
    });

    // Update product
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=zca_update_product&nonce=' + zcaInventory.nonce;

        $.ajax({
            url: zcaInventory.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    zcShowToast(response.data.message, response.success ? 'success' : 'error');
                    location.reload();
                } else {
                    zcShowToast(response.data.message, response.success ? 'success' : 'error');
                }
            }
        });
    });

    // Delete product
    $('.delete-product').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        zcConfirm('Are you sure you want to delete "' + name + '"?', function() {
            $.ajax({
                url: zcaInventory.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'zca_delete_product',
                    product_id: id,
                    nonce: zcaInventory.nonce
                },
                success: function(response) {
                    if (response.success) {
                        zcShowToast(response.data.message, response.success ? 'success' : 'error');
                        location.reload();
                    } else {
                        zcShowToast(response.data.message, response.success ? 'success' : 'error');
                    }
                }
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>
