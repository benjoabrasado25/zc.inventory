<?php
// Check access - cashier or owner
ZCA_Auth::check_access();

$page_title = 'Point of Sale';
$active_page = 'pos';

include 'header.php';

$products = ZCA_Products::get_all_products();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-cash-register"></i> Point of Sale</h1>
    </div>
</div>

<div class="row">
    <!-- Products Section -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box"></i> Products</h5>
                    <input type="text" id="product-search" class="form-control form-control-sm w-50" placeholder="Search products...">
                </div>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <div class="row g-3" id="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php if ($product->stock > 0): ?>
                                <div class="col-md-6 col-lg-4 product-item"
                                     data-name="<?php echo esc_attr(strtolower($product->name)); ?>"
                                     data-sku="<?php echo esc_attr(strtolower($product->sku)); ?>">
                                    <div class="card h-100 product-card" style="cursor: pointer;"
                                         data-id="<?php echo $product->id; ?>"
                                         data-name="<?php echo esc_attr($product->name); ?>"
                                         data-price="<?php echo $product->price; ?>"
                                         data-stock="<?php echo $product->stock; ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo esc_html($product->name); ?></h6>
                                            <p class="card-text">
                                                <strong>Price:</strong> <?php echo ZCA_Settings::format_currency($product->price); ?><br>
                                                <strong>Stock:</strong>
                                                <?php if ($product->stock <= 10): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo $product->stock; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $product->stock; ?></span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center text-muted py-4">No products available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart and Payment Section -->
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cart"></i> Cart</h5>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <div id="cart-items">
                    <p class="text-center text-muted py-4">Cart is empty</p>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Total:</h5>
                    <h4 class="mb-0 text-primary" id="cart-total-display"><?php echo ZCA_Settings::format_currency(0); ?></h4>
                </div>
                <button class="btn btn-danger btn-sm w-100 mt-2" id="clear-cart">
                    <i class="bi bi-trash"></i> Clear Cart
                </button>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Payment</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Amount to Pay</label>
                    <input type="text" class="form-control form-control-lg text-end fw-bold" id="amount-to-pay" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cash Received</label>
                    <input type="number" class="form-control form-control-lg text-end" id="cash-received" step="0.01" min="0">
                </div>

                <div class="mb-3">
                    <label class="form-label">Change</label>
                    <input type="text" class="form-control form-control-lg text-end fw-bold bg-light" id="change-amount" readonly>
                </div>

                <!-- Quick Cash Buttons -->
                <div class="mb-3">
                    <label class="form-label">Quick Cash</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 quick-cash" data-amount="5">$5</button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 quick-cash" data-amount="10">$10</button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 quick-cash" data-amount="20">$20</button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 quick-cash" data-amount="50">$50</button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-secondary w-100 quick-cash" data-amount="100">$100</button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-primary w-100" id="exact-cash">Exact</button>
                        </div>
                    </div>
                </div>

                <button class="btn btn-success btn-lg w-100" id="process-sale" disabled>
                    <i class="bi bi-check-circle"></i> Process Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Sale Completed!</h5>
            </div>
            <div class="modal-body" id="receipt-content">
                <!-- Receipt will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="new-sale-btn">New Sale</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var cart = [];

    function updateCart() {
        if (cart.length === 0) {
            $('#cart-items').html('<p class="text-center text-muted py-4">Cart is empty</p>');
            $('#cart-total-display').text(formatCurrency(0));
            $('#amount-to-pay').val(formatCurrency(0));
            $('#process-sale').prop('disabled', true);
            return;
        }

        var html = '<div class="list-group">';
        var total = 0;

        cart.forEach(function(item, index) {
            var subtotal = item.price * item.quantity;
            total += subtotal;

            html += '<div class="list-group-item">';
            html += '<div class="d-flex justify-content-between align-items-center">';
            html += '<div class="flex-grow-1">';
            html += '<h6 class="mb-0">' + item.name + '</h6>';
            html += '<small class="text-muted">' + formatCurrency(item.price) + ' × ' + item.quantity + '</small>';
            html += '</div>';
            html += '<div class="text-end">';
            html += '<strong>' + formatCurrency(subtotal) + '</strong><br>';
            html += '<div class="btn-group btn-group-sm mt-1" role="group">';
            html += '<button class="btn btn-outline-secondary decrease-qty" data-index="' + index + '">-</button>';
            html += '<button class="btn btn-outline-secondary increase-qty" data-index="' + index + '">+</button>';
            html += '<button class="btn btn-outline-danger remove-item" data-index="' + index + '"><i class="bi bi-trash"></i></button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';

        $('#cart-items').html(html);
        $('#cart-total-display').text(formatCurrency(total));
        $('#amount-to-pay').val(formatCurrency(total));
        $('#process-sale').prop('disabled', false);

        // Store total for calculations
        $('#cart-total-display').data('total', total);

        // Calculate change
        calculateChange();
    }

    function calculateChange() {
        var total = $('#cart-total-display').data('total') || 0;
        var cashReceived = parseFloat($('#cash-received').val()) || 0;
        var change = cashReceived - total;

        if (change >= 0) {
            $('#change-amount').val(formatCurrency(change)).removeClass('text-danger').addClass('text-success');
        } else {
            $('#change-amount').val('Insufficient').removeClass('text-success').addClass('text-danger');
        }
    }

    // Add product to cart
    $(document).on('click', '.product-card', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var price = parseFloat($(this).data('price'));
        var stock = parseInt($(this).data('stock'));

        // Check if product already in cart
        var existingIndex = cart.findIndex(function(item) {
            return item.id === id;
        });

        if (existingIndex !== -1) {
            // Check if we can add more
            if (cart[existingIndex].quantity < stock) {
                cart[existingIndex].quantity++;
            } else {
                zcShowToast('Cannot add more. Insufficient stock.', 'warning');
                return;
            }
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                quantity: 1,
                stock: stock
            });
        }

        updateCart();
    });

    // Increase quantity
    $(document).on('click', '.increase-qty', function() {
        var index = $(this).data('index');
        if (cart[index].quantity < cart[index].stock) {
            cart[index].quantity++;
            updateCart();
        } else {
            zcShowToast('Cannot add more. Insufficient stock.', 'warning');
        }
    });

    // Decrease quantity
    $(document).on('click', '.decrease-qty', function() {
        var index = $(this).data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
            updateCart();
        }
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        var index = $(this).data('index');
        cart.splice(index, 1);
        updateCart();
    });

    // Clear cart
    $('#clear-cart').on('click', function() {
        zcConfirm('Are you sure you want to clear the cart?', function() {
            cart = [];
            $('#cash-received').val('');
            $('#change-amount').val('');
            updateCart();
        });
    });

    // Cash received input
    $('#cash-received').on('input', function() {
        calculateChange();
    });

    // Quick cash buttons
    $('.quick-cash').on('click', function() {
        var amount = $(this).data('amount');
        $('#cash-received').val(amount);
        calculateChange();
    });

    // Exact cash button
    $('#exact-cash').on('click', function() {
        var total = parseFloat($('#cart-total').text());
        $('#cash-received').val(total.toFixed(2));
        calculateChange();
    });

    // Product search
    $('#product-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();

        $('.product-item').each(function() {
            var name = $(this).data('name');
            var sku = $(this).data('sku');

            if (name.includes(searchTerm) || sku.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Process sale
    $('#process-sale').on('click', function() {
        var total = parseFloat($('#cart-total').text());
        var cashReceived = parseFloat($('#cash-received').val()) || 0;

        if (cashReceived < total) {
            zcShowToast('Insufficient cash received!', 'warning');
            return;
        }

        if (cart.length === 0) {
            zcShowToast('Cart is empty!', 'warning');
            return;
        }

        var items = cart.map(function(item) {
            return {
                product_id: item.id,
                quantity: item.quantity
            };
        });

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: zcInventory.ajaxUrl,
            type: 'POST',
            data: {
                action: 'zca_process_sale',
                items: JSON.stringify(items),
                cash_received: cashReceived,
                nonce: zcInventory.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show receipt
                    var receiptHtml = '<div class="receipt">';
                    receiptHtml += '<h4 class="text-center">RECEIPT</h4>';
                    receiptHtml += '<p class="text-center">Sale #' + response.data.sale_id + '</p>';
                    receiptHtml += '<hr>';
                    receiptHtml += '<table class="table table-sm">';
                    receiptHtml += '<thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>';
                    receiptHtml += '<tbody>';

                    cart.forEach(function(item) {
                        var subtotal = item.price * item.quantity;
                        receiptHtml += '<tr>';
                        receiptHtml += '<td>' + item.name + '</td>';
                        receiptHtml += '<td>' + item.quantity + '</td>';
                        receiptHtml += '<td>' + formatCurrency(item.price) + '</td>';
                        receiptHtml += '<td>' + formatCurrency(subtotal) + '</td>';
                        receiptHtml += '</tr>';
                    });

                    receiptHtml += '</tbody>';
                    receiptHtml += '<tfoot>';
                    receiptHtml += '<tr><th colspan="3">Total</th><th>' + formatCurrency(response.data.total) + '</th></tr>';
                    receiptHtml += '<tr><th colspan="3">Cash</th><th>' + formatCurrency(response.data.cash_received) + '</th></tr>';
                    receiptHtml += '<tr><th colspan="3">Change</th><th>' + formatCurrency(response.data.change) + '</th></tr>';
                    receiptHtml += '</tfoot>';
                    receiptHtml += '</table>';
                    receiptHtml += '<p class="text-center"><small>' + new Date().toLocaleString() + '</small></p>';
                    receiptHtml += '</div>';

                    $('#receipt-content').html(receiptHtml);
                    $('#receiptModal').modal('show');

                    // Reset
                    cart = [];
                    $('#cash-received').val('');
                    $('#change-amount').val('');
                    updateCart();
                } else {
                    zcShowToast('Error: ' + response.data.message, 'error');
                }

                $('#process-sale').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Process Sale');
            },
            error: function() {
                zcShowToast('An error occurred while processing the sale.', 'error');
                $('#process-sale').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Process Sale');
            }
        });
    });

    // New sale button
    $('#new-sale-btn').on('click', function() {
        location.reload();
    });
});
</script>

<style>
.product-card:hover {
    transform: scale(1.05);
    transition: transform 0.2s;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@media print {
    body * {
        visibility: hidden;
    }
    .receipt, .receipt * {
        visibility: visible;
    }
    .receipt {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>

<?php include 'footer.php'; ?>
