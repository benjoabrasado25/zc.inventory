<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - ZCA Inventory</title>
    <?php wp_head(); ?>
</head>
<body class="zc-login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">ZCA Inventory</h2>
                            <p class="text-muted">Login to your account</p>
                        </div>

                        <?php if (isset($_GET['deactivated'])): ?>
                            <div class="alert alert-danger" role="alert">
                                Your account has been deactivated. Please contact the owner.
                            </div>
                        <?php endif; ?>

                        <div id="login-message"></div>

                        <form id="zc-login-form">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" id="login-btn">
                                <span class="btn-text">Login</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#zc-login-form').on('submit', function(e) {
            e.preventDefault();

            var $btn = $('#login-btn');
            var $btnText = $btn.find('.btn-text');
            var $spinner = $btn.find('.spinner-border');
            var $message = $('#login-message');

            $btn.prop('disabled', true);
            $btnText.text('Logging in...');
            $spinner.removeClass('d-none');
            $message.html('');

            $.ajax({
                url: zcInventory.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'zca_login',
                    username: $('#username').val(),
                    password: $('#password').val(),
                    remember: $('#remember').is(':checked'),
                    nonce: '<?php echo wp_create_nonce('zca_login_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $message.html('<div class="alert alert-success">' + response.data.message + '</div>');
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 500);
                    } else {
                        $message.html('<div class="alert alert-danger">' + response.data.message + '</div>');
                        $btn.prop('disabled', false);
                        $btnText.text('Login');
                        $spinner.addClass('d-none');
                    }
                },
                error: function() {
                    $message.html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                    $btn.prop('disabled', false);
                    $btnText.text('Login');
                    $spinner.addClass('d-none');
                }
            });
        });
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
