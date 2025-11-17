<?php
// Check access
ZCA_Auth::check_access();

$page_title = 'Dashboard';
$active_page = 'dashboard';

include 'header.php';

$user_role = ZCA_Auth::get_user_role();
$is_owner = ZCA_Roles::is_owner();
$is_cashier = ZCA_Roles::is_cashier();

// Get statistics
$total_products = count(ZCA_Products::get_all_products());
$low_stock_products = array_filter(ZCA_Products::get_all_products(), function($product) {
    return $product->stock <= 10;
});
$low_stock_count = count($low_stock_products);

if ($is_owner) {
    $all_sales = ZCA_Sales::get_all_sales();
    $total_sales = count($all_sales);
    $sales_stats = ZCA_Sales::get_sales_stats();
    $total_revenue = $sales_stats->total_revenue ? $sales_stats->total_revenue : 0;
    $all_cashiers = ZCA_Cashiers::get_all_cashiers();
    $active_cashiers = array_filter($all_cashiers, function($cashier) {
        return $cashier->is_active;
    });
    $cashier_count = count($active_cashiers);
}

if ($is_cashier) {
    $my_today_stats = ZCA_Register::get_cashier_today_stats(get_current_user_id());
    $my_today_sales = $my_today_stats->total_sales;
    $my_today_revenue = $my_today_stats->total_revenue;
    $active_session = ZCA_Register::get_active_session_data(get_current_user_id());
}

if ($is_owner) {
    $all_today_stats = ZCA_Register::get_all_today_stats();
    $today_total_sales = $all_today_stats->total_sales;
    $today_total_revenue = $all_today_stats->total_revenue;
    $today_stats_by_cashier = ZCA_Register::get_today_stats_by_cashier();
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
    </div>
</div>

<?php if ($is_owner): ?>
    <!-- Owner Dashboard -->
    <!-- Today's Stats -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <h5 class="alert-heading"><i class="bi bi-calendar-day"></i> Today's Summary</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total Sales Today:</strong> <?php echo $today_total_sales; ?> transaction(s)
                    </div>
                    <div class="col-md-6">
                        <strong>Total Revenue Today:</strong> <?php echo ZCA_Settings::format_currency($today_total_revenue); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Total Products</h6>
                            <h2 class="card-title mb-0"><?php echo $total_products; ?></h2>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-box fs-1"></i>
                        </div>
                    </div>
                    <a href="<?php echo home_url('/zca-inventory/products'); ?>" class="btn btn-sm btn-outline-primary mt-3 w-100">
                        Manage Products
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Active Cashiers</h6>
                            <h2 class="card-title mb-0"><?php echo $cashier_count; ?></h2>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                    <a href="<?php echo home_url('/zca-inventory/cashiers'); ?>" class="btn btn-sm btn-outline-success mt-3 w-100">
                        Manage Cashiers
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Total Sales (All Time)</h6>
                            <h2 class="card-title mb-0"><?php echo $total_sales; ?></h2>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-receipt fs-1"></i>
                        </div>
                    </div>
                    <a href="<?php echo home_url('/zca-inventory/sales-report'); ?>" class="btn btn-sm btn-outline-info mt-3 w-100">
                        View Sales
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Total Revenue (All Time)</h6>
                            <h2 class="card-title mb-0"><?php echo ZCA_Settings::format_currency($total_revenue); ?></h2>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-currency-dollar fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Sales by Cashier -->
    <?php if (!empty($today_stats_by_cashier)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Today's Sales by Cashier</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Cashier</th>
                                        <th>Sales Count</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_stats_by_cashier as $stat): ?>
                                        <tr>
                                            <td><?php echo esc_html($stat->cashier_name); ?></td>
                                            <td><?php echo $stat->total_sales; ?></td>
                                            <td><?php echo ZCA_Settings::format_currency($stat->total_revenue); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Low Stock Alert -->
    <?php if ($low_stock_count > 0): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
                    <p class="mb-0"><?php echo $low_stock_count; ?> product(s) have low stock (10 or fewer items remaining).</p>
                    <hr>
                    <a href="<?php echo home_url('/zca-inventory/inventory'); ?>" class="btn btn-warning btn-sm">
                        Manage Inventory
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Sales -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Sales</h5>
                </div>
                <div class="card-body">
                    <?php
                    $recent_sales = ZCA_Sales::get_all_sales(10);
                    if (!empty($recent_sales)):
                    ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Cashier</th>
                                        <th>Total Amount</th>
                                        <th>Cash Received</th>
                                        <th>Change</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_sales as $sale): ?>
                                        <tr>
                                            <td>#<?php echo $sale->id; ?></td>
                                            <td><?php echo esc_html($sale->cashier_name); ?></td>
                                            <td><?php echo ZCA_Settings::format_currency($sale->total_amount); ?></td>
                                            <td><?php echo ZCA_Settings::format_currency($sale->cash_received); ?></td>
                                            <td><?php echo ZCA_Settings::format_currency($sale->change_amount); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($sale->sale_date)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No sales yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($is_cashier): ?>
    <!-- Cashier Dashboard -->

    <!-- Cash Register Status -->
    <div class="row mb-4">
        <div class="col-12">
            <?php if ($active_session): ?>
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="bi bi-cash-coin"></i> Cash Register is Open</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Opening Cash:</strong> <?php echo ZCA_Settings::format_currency($active_session->opening_cash); ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Opened At:</strong> <?php echo date('M d, Y h:i A', strtotime($active_session->opened_at)); ?>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#closeRegisterModal">
                                <i class="bi bi-lock"></i> Close Register
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Cash Register Not Open</h5>
                    <p>Please open your cash register to start selling.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#openRegisterModal">
                        <i class="bi bi-unlock"></i> Open Cash Register
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">My Sales Today</h6>
                            <h2 class="card-title mb-0"><?php echo $my_today_sales; ?></h2>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-receipt fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">My Revenue Today</h6>
                            <h2 class="card-title mb-0"><?php echo ZCA_Settings::format_currency($my_today_revenue); ?></h2>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-dollar fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Available Products</h6>
                            <h2 class="card-title mb-0"><?php echo $total_products; ?></h2>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-box fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h3><i class="bi bi-cash-register"></i> Ready to Make a Sale?</h3>
                    <p class="mb-3">Use our Point of Sale system to process transactions quickly and efficiently.</p>
                    <a href="<?php echo home_url('/zca-inventory/pos'); ?>" class="btn btn-light btn-lg">
                        <i class="bi bi-arrow-right-circle"></i> Go to POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Open Cash Register Modal -->
    <div class="modal fade" id="openRegisterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-unlock"></i> Open Cash Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="openRegisterForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small><i class="bi bi-info-circle"></i> Enter the initial cash amount in your register at the start of your shift.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opening Cash Amount *</label>
                            <input type="number" class="form-control form-control-lg" name="opening_cash" step="0.01" min="0" required autofocus>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Open Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Close Cash Register Modal -->
    <div class="modal fade" id="closeRegisterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-lock"></i> Close Cash Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="closeRegisterForm">
                    <input type="hidden" name="session_id" value="<?php echo $active_session ? $active_session->id : ''; ?>">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <small><i class="bi bi-exclamation-triangle"></i> Count all cash in your register and enter the total amount.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Closing Cash Amount *</label>
                            <input type="number" class="form-control form-control-lg" name="closing_cash" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any notes about your shift..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Close Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Open register
        $('#openRegisterForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            formData += '&action=zc_open_register&nonce=' + zcInventory.nonce;

            $.ajax({
                url: zcInventory.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        zcShowToast(response.data.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        zcShowToast(response.data.message, 'error');
                    }
                }
            });
        });

        // Close register
        $('#closeRegisterForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            formData += '&action=zc_close_register&nonce=' + zcInventory.nonce;

            $.ajax({
                url: zcInventory.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        var variance = response.data.variance;
                        var varianceMsg = '';

                        if (variance > 0) {
                            varianceMsg = 'Overage: ' + formatCurrency(Math.abs(variance));
                        } else if (variance < 0) {
                            varianceMsg = 'Shortage: ' + formatCurrency(Math.abs(variance));
                        } else {
                            varianceMsg = 'Balanced perfectly!';
                        }

                        zcShowToast(response.data.message + ' - ' + varianceMsg, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        zcShowToast(response.data.message, 'error');
                    }
                }
            });
        });
    });
    </script>
<?php endif; ?>

<?php include 'footer.php'; ?>
