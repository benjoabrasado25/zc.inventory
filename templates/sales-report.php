<?php
// Check access - only owner
ZC_Auth::check_access('owner');

$page_title = 'Sales Report';
$active_page = 'sales-report';

include 'header.php';

$all_sales = ZC_Sales::get_all_sales();
$sales_stats = ZC_Sales::get_sales_stats();
$all_cashiers = ZC_Cashiers::get_all_cashiers();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-graph-up"></i> Sales Report</h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Sales</h6>
                <h2 class="card-title mb-0"><?php echo $sales_stats->total_sales ? $sales_stats->total_sales : 0; ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Revenue</h6>
                <h2 class="card-title mb-0">$<?php echo $sales_stats->total_revenue ? number_format($sales_stats->total_revenue, 2) : '0.00'; ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Average Sale</h6>
                <h2 class="card-title mb-0">$<?php echo $sales_stats->average_sale ? number_format($sales_stats->average_sale, 2) : '0.00'; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filter by Cashier -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <label class="form-label">Filter by Cashier</label>
                <select class="form-select" id="cashier-filter">
                    <option value="">All Cashiers</option>
                    <?php foreach ($all_cashiers as $cashier): ?>
                        <option value="<?php echo $cashier->ID; ?>">
                            <?php echo esc_html($cashier->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Sales Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> All Sales Transactions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="salesTable">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Cashier</th>
                                <th>Total Amount</th>
                                <th>Cash Received</th>
                                <th>Change</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_sales)): ?>
                                <?php foreach ($all_sales as $sale): ?>
                                    <tr data-cashier-id="<?php echo $sale->cashier_id; ?>">
                                        <td>#<?php echo $sale->id; ?></td>
                                        <td><?php echo esc_html($sale->cashier_name); ?></td>
                                        <td>$<?php echo number_format($sale->total_amount, 2); ?></td>
                                        <td>$<?php echo number_format($sale->cash_received, 2); ?></td>
                                        <td>$<?php echo number_format($sale->change_amount, 2); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($sale->sale_date)); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-sale-details"
                                                    data-sale-id="<?php echo $sale->id; ?>">
                                                <i class="bi bi-eye"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No sales recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sale Details Modal -->
<div class="modal fade" id="saleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sale-details-content">
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
    // Filter by cashier
    $('#cashier-filter').on('change', function() {
        var cashierId = $(this).val();

        if (cashierId === '') {
            $('#salesTable tbody tr').show();
        } else {
            $('#salesTable tbody tr').hide();
            $('#salesTable tbody tr[data-cashier-id="' + cashierId + '"]').show();
        }
    });

    // View sale details
    $('.view-sale-details').on('click', function() {
        var saleId = $(this).data('sale-id');

        $('#saleDetailsModal').modal('show');
        $('#sale-details-content').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>');

        // Load sale items (we'll create a simple display with the available data)
        // In a real application, you'd fetch the sale items via AJAX
        $.ajax({
            url: zcInventory.ajaxUrl,
            type: 'POST',
            data: {
                action: 'zc_get_sale_details',
                sale_id: saleId,
                nonce: zcInventory.nonce
            },
            success: function(response) {
                if (response.success) {
                    var sale = response.data.sale;
                    var items = response.data.items;

                    var html = '<div class="mb-3">';
                    html += '<h6>Sale #' + sale.id + '</h6>';
                    html += '<p><strong>Cashier:</strong> ' + sale.cashier_name + '</p>';
                    html += '<p><strong>Date:</strong> ' + sale.sale_date + '</p>';
                    html += '</div>';

                    html += '<table class="table">';
                    html += '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>';
                    html += '<tbody>';

                    if (items && items.length > 0) {
                        items.forEach(function(item) {
                            html += '<tr>';
                            html += '<td>' + item.product_name + '</td>';
                            html += '<td>' + item.quantity + '</td>';
                            html += '<td>$' + parseFloat(item.price).toFixed(2) + '</td>';
                            html += '<td>$' + parseFloat(item.subtotal).toFixed(2) + '</td>';
                            html += '</tr>';
                        });
                    }

                    html += '</tbody>';
                    html += '<tfoot>';
                    html += '<tr><th colspan="3">Total</th><th>$' + parseFloat(sale.total_amount).toFixed(2) + '</th></tr>';
                    html += '<tr><th colspan="3">Cash Received</th><th>$' + parseFloat(sale.cash_received).toFixed(2) + '</th></tr>';
                    html += '<tr><th colspan="3">Change</th><th>$' + parseFloat(sale.change_amount).toFixed(2) + '</th></tr>';
                    html += '</tfoot>';
                    html += '</table>';

                    $('#sale-details-content').html(html);
                } else {
                    $('#sale-details-content').html('<div class="alert alert-danger">Failed to load sale details</div>');
                }
            },
            error: function() {
                $('#sale-details-content').html('<div class="alert alert-danger">Error loading sale details</div>');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
