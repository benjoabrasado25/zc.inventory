<?php
// Check access - only owner
ZCA_Auth::check_access('owner');

$page_title = 'Cashiers';
$active_page = 'cashiers';

include 'header.php';

$cashiers = ZCA_Cashiers::get_all_cashiers();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-people"></i> Cashiers Management</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCashierModal">
                <i class="bi bi-plus-circle"></i> Add Cashier
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Display Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($cashiers)): ?>
                                <?php foreach ($cashiers as $cashier): ?>
                                    <tr>
                                        <td><?php echo $cashier->ID; ?></td>
                                        <td><?php echo esc_html($cashier->user_login); ?></td>
                                        <td><?php echo esc_html($cashier->display_name); ?></td>
                                        <td><?php echo esc_html($cashier->user_email); ?></td>
                                        <td>
                                            <?php if ($cashier->is_active): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-cashier"
                                                    data-id="<?php echo $cashier->ID; ?>"
                                                    data-email="<?php echo esc_attr($cashier->user_email); ?>"
                                                    data-display-name="<?php echo esc_attr($cashier->display_name); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm <?php echo $cashier->is_active ? 'btn-warning' : 'btn-success'; ?> toggle-cashier"
                                                    data-id="<?php echo $cashier->ID; ?>"
                                                    data-active="<?php echo $cashier->is_active ? '1' : '0'; ?>"
                                                    data-name="<?php echo esc_attr($cashier->display_name); ?>">
                                                <i class="bi bi-<?php echo $cashier->is_active ? 'pause' : 'play'; ?>-circle"></i>
                                                <?php echo $cashier->is_active ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No cashiers found. Add your first cashier!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Cashier Modal -->
<div class="modal fade" id="addCashierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Cashier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCashierForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required>
                        <small class="text-muted">Used for login</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Name</label>
                        <input type="text" class="form-control" name="display_name">
                        <small class="text-muted">Leave empty to use username</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Cashier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Cashier Modal -->
<div class="modal fade" id="editCashierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Cashier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCashierForm">
                <input type="hidden" name="user_id" id="edit_cashier_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Display Name *</label>
                        <input type="text" class="form-control" name="display_name" id="edit_display_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" id="edit_password">
                        <small class="text-muted">Leave empty to keep current password</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Cashier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Add cashier
    $('#addCashierForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=zc_add_cashier&nonce=' + zcInventory.nonce;

        $.ajax({
            url: zcInventory.ajaxUrl,
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

    // Edit cashier button
    $('.edit-cashier').on('click', function() {
        var id = $(this).data('id');
        var email = $(this).data('email');
        var displayName = $(this).data('display-name');

        $('#edit_cashier_id').val(id);
        $('#edit_email').val(email);
        $('#edit_display_name').val(displayName);
        $('#edit_password').val('');

        $('#editCashierModal').modal('show');
    });

    // Update cashier
    $('#editCashierForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=zc_update_cashier&nonce=' + zcInventory.nonce;

        $.ajax({
            url: zcInventory.ajaxUrl,
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

    // Toggle cashier status
    $('.toggle-cashier').on('click', function() {
        var id = $(this).data('id');
        var isActive = $(this).data('active') === '1';
        var name = $(this).data('name');
        var action = isActive ? 'deactivate' : 'activate';

        zcConfirm('Are you sure you want to ' + action + ' "' + name + '"?', function() {
            $.ajax({
                url: zcInventory.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'zca_toggle_cashier',
                    user_id: id,
                    is_active: !isActive,
                    nonce: zcInventory.nonce
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
