<?php
// Check access - only owner
ZCA_Auth::check_access('owner');

$page_title = 'Inventory Management';
$active_page = 'inventory';

include 'header.php';

$products = ZCA_Products::get_all_products();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-clipboard-data"></i> Inventory Management</h1>
        <p class="text-muted">Update stock quantities and track inventory changes</p>
    </div>
</div>

<!-- Products Inventory Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Products Inventory</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product->id; ?></td>
                                        <td><?php echo esc_html($product->name); ?></td>
                                        <td><?php echo esc_html($product->sku); ?></td>
                                        <td>
                                            <?php if ($product->stock <= 10): ?>
                                                <span class="badge bg-danger fs-6"><?php echo $product->stock; ?></span>
                                            <?php elseif ($product->stock <= 20): ?>
                                                <span class="badge bg-warning text-dark fs-6"><?php echo $product->stock; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success fs-6"><?php echo $product->stock; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product->stock == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($product->stock <= 10): ?>
                                                <span class="badge bg-warning text-dark">Low Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary update-inventory"
                                                    data-id="<?php echo $product->id; ?>"
                                                    data-name="<?php echo esc_attr($product->name); ?>"
                                                    data-stock="<?php echo $product->stock; ?>">
                                                <i class="bi bi-pencil-square"></i> Update Stock
                                            </button>
                                            <button class="btn btn-sm btn-info view-logs"
                                                    data-id="<?php echo $product->id; ?>"
                                                    data-name="<?php echo esc_attr($product->name); ?>">
                                                <i class="bi bi-clock-history"></i> View Logs
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Inventory Modal -->
<div class="modal fade" id="updateInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateInventoryForm">
                <input type="hidden" name="product_id" id="inventory_product_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Product:</strong> <span id="inventory_product_name"></span><br>
                        <strong>Current Stock:</strong> <span id="inventory_current_stock"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Stock Quantity *</label>
                        <input type="number" class="form-control" name="quantity" id="inventory_new_quantity" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Change</label>
                        <select class="form-select" id="inventory_reason_select">
                            <option value="">Select reason...</option>
                            <option value="Restock">Restock</option>
                            <option value="Stock adjustment">Stock adjustment</option>
                            <option value="Damaged items">Damaged items</option>
                            <option value="Lost items">Lost items</option>
                            <option value="Return">Return</option>
                            <option value="custom">Custom reason...</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="custom_reason_container">
                        <label class="form-label">Custom Reason</label>
                        <input type="text" class="form-control" name="reason" id="inventory_custom_reason">
                    </div>

                    <input type="hidden" name="reason" id="inventory_reason">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Logs Modal -->
<div class="modal fade" id="viewLogsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inventory Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logs-content">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update inventory button
    $('.update-inventory').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var stock = $(this).data('stock');

        $('#inventory_product_id').val(id);
        $('#inventory_product_name').text(name);
        $('#inventory_current_stock').text(stock);
        $('#inventory_new_quantity').val(stock);
        $('#inventory_reason_select').val('');
        $('#custom_reason_container').addClass('d-none');

        $('#updateInventoryModal').modal('show');
    });

    // Reason select change
    $('#inventory_reason_select').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom_reason_container').removeClass('d-none');
        } else {
            $('#custom_reason_container').addClass('d-none');
        }
    });

    // Update inventory form submit
    $('#updateInventoryForm').on('submit', function(e) {
        e.preventDefault();

        var reason = $('#inventory_reason_select').val();
        if (reason === 'custom') {
            reason = $('#inventory_custom_reason').val();
        }

        var formData = {
            action: 'zca_update_inventory',
            product_id: $('#inventory_product_id').val(),
            quantity: $('#inventory_new_quantity').val(),
            reason: reason,
            nonce: zcaInventory.nonce
        };

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

    // View logs button
    $('.view-logs').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#viewLogsModal .modal-title').text('Inventory Logs - ' + name);
        $('#viewLogsModal').modal('show');
        $('#logs-content').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>');

        $.ajax({
            url: zcaInventory.ajaxUrl,
            type: 'POST',
            data: {
                action: 'zca_get_inventory_logs',
                product_id: id,
                nonce: zcaInventory.nonce
            },
            success: function(response) {
                if (response.success) {
                    var logs = response.data.logs;

                    if (logs && logs.length > 0) {
                        var html = '<div class="table-responsive">';
                        html += '<table class="table table-sm">';
                        html += '<thead><tr><th>Date</th><th>User</th><th>Before</th><th>After</th><th>Change</th><th>Reason</th></tr></thead>';
                        html += '<tbody>';

                        logs.forEach(function(log) {
                            var changeClass = log.quantity_change >= 0 ? 'text-success' : 'text-danger';
                            var changeIcon = log.quantity_change >= 0 ? '▲' : '▼';

                            html += '<tr>';
                            html += '<td>' + log.log_date + '</td>';
                            html += '<td>' + log.user_name + '</td>';
                            html += '<td>' + log.quantity_before + '</td>';
                            html += '<td>' + log.quantity_after + '</td>';
                            html += '<td class="' + changeClass + '">' + changeIcon + ' ' + Math.abs(log.quantity_change) + '</td>';
                            html += '<td>' + (log.reason ? log.reason : '-') + '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        $('#logs-content').html(html);
                    } else {
                        $('#logs-content').html('<div class="alert alert-info">No inventory logs found for this product.</div>');
                    }
                } else {
                    $('#logs-content').html('<div class="alert alert-danger">Failed to load logs</div>');
                }
            },
            error: function() {
                $('#logs-content').html('<div class="alert alert-danger">Error loading logs</div>');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
