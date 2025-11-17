<?php
// Check access - only owner
ZCA_Auth::check_access('owner');

$page_title = 'Settings';
$active_page = 'settings';

include 'header.php';

$currency_symbol = ZCA_Settings::get_currency_symbol();
$currency_code = ZCA_Settings::get_currency_code();
$currency_position = ZCA_Settings::get_currency_position();
$available_currencies = ZCA_Settings::get_available_currencies();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-gear"></i> Settings</h1>
        <p class="text-muted">Configure your inventory system</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-currency-exchange"></i> Currency Settings</h5>
            </div>
            <div class="card-body">
                <form id="settingsForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Currency *</label>
                        <select class="form-select" id="currency_select" name="currency_code" required>
                            <option value="">Select currency...</option>
                            <?php foreach ($available_currencies as $code => $info): ?>
                                <option value="<?php echo $code; ?>"
                                        data-symbol="<?php echo esc_attr($info['symbol']); ?>"
                                        <?php echo $currency_code === $code ? 'selected' : ''; ?>>
                                    <?php echo $info['name']; ?> (<?php echo $code; ?>) - <?php echo $info['symbol']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Choose the currency for your inventory system</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Currency Symbol *</label>
                        <input type="text" class="form-control" name="currency_symbol" id="currency_symbol"
                               value="<?php echo esc_attr($currency_symbol); ?>" required>
                        <small class="text-muted">Symbol to display (e.g., ₱, $, €)</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Symbol Position *</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="currency_position" id="position_before"
                                   value="before" <?php echo $currency_position === 'before' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="position_before">
                                Before amount (e.g., <strong id="example_before">₱100.00</strong>)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="currency_position" id="position_after"
                                   value="after" <?php echo $currency_position === 'after' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="position_after">
                                After amount (e.g., <strong id="example_after">100.00 ₱</strong>)
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> Changing the currency will only affect how prices are displayed.
                        It will not convert existing prices in your database.
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-eye"></i> Preview</h5>
            </div>
            <div class="card-body">
                <h6>Current Display:</h6>
                <div class="alert alert-light border">
                    <strong>Product Price:</strong><br>
                    <span class="fs-4" id="price_preview"><?php echo ZCA_Settings::format_currency(1234.56); ?></span>
                </div>

                <h6 class="mt-3">Popular Currencies:</h6>
                <ul class="list-unstyled small">
                    <li><strong>PHP</strong> - Philippine Peso (₱)</li>
                    <li><strong>USD</strong> - US Dollar ($)</li>
                    <li><strong>EUR</strong> - Euro (€)</li>
                    <li><strong>GBP</strong> - British Pound (£)</li>
                    <li><strong>JPY</strong> - Japanese Yen (¥)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update symbol when currency is selected
    $('#currency_select').on('change', function() {
        var symbol = $(this).find(':selected').data('symbol');
        if (symbol) {
            $('#currency_symbol').val(symbol);
            updatePreview();
        }
    });

    // Update preview when inputs change
    $('#currency_symbol, input[name="currency_position"]').on('change input', function() {
        updatePreview();
    });

    function updatePreview() {
        var symbol = $('#currency_symbol').val() || '₱';
        var position = $('input[name="currency_position"]:checked').val() || 'before';
        var amount = '1,234.56';

        var display = position === 'before' ? symbol + amount : amount + ' ' + symbol;

        $('#price_preview').text(display);
        $('#example_before').text(symbol + '100.00');
        $('#example_after').text('100.00 ' + symbol);
    }

    // Save settings
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=zc_update_settings&nonce=' + zcaInventory.nonce;

        $.ajax({
            url: zcaInventory.ajaxUrl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#settingsForm button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    zcShowToast(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    zcShowToast(response.data.message, 'error');
                    $('#settingsForm button[type="submit"]').prop('disabled', false).html('<i class="bi bi-save"></i> Save Settings');
                }
            },
            error: function() {
                zcShowToast('An error occurred while saving settings', 'error');
                $('#settingsForm button[type="submit"]').prop('disabled', false).html('<i class="bi bi-save"></i> Save Settings');
            }
        });
    });

    // Initial preview update
    updatePreview();
});
</script>

<?php include 'footer.php'; ?>
