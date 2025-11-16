/**
 * Z Inventory Plugin Scripts
 */

(function($) {
    'use strict';

    // Toast notification system
    window.zcShowToast = function(message, type = 'info') {
        var bgClass = 'bg-primary';
        var icon = 'bi-info-circle';

        switch(type) {
            case 'success':
                bgClass = 'bg-success';
                icon = 'bi-check-circle';
                break;
            case 'error':
            case 'danger':
                bgClass = 'bg-danger';
                icon = 'bi-x-circle';
                break;
            case 'warning':
                bgClass = 'bg-warning text-dark';
                icon = 'bi-exclamation-triangle';
                break;
        }

        var toastId = 'toast-' + Date.now();
        var toastHtml = `
            <div id="${toastId}" class="toast align-items-center ${bgClass} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Append to toast container
        if (!$('#toast-container').length) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;"></div>');
        }

        $('#toast-container').append(toastHtml);

        var toastElement = document.getElementById(toastId);
        var toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });

        toast.show();

        // Remove from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            $(this).remove();
        });
    };

    // Confirmation dialog
    window.zcConfirm = function(message, callback) {
        var modalId = 'confirm-modal-' + Date.now();
        var modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-question-circle text-warning"></i> Confirm Action
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary confirm-btn">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);

        var modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();

        $('#' + modalId + ' .confirm-btn').on('click', function() {
            modal.hide();
            if (callback) callback();
        });

        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            $(this).remove();
        });
    };

    // Document ready
    $(document).ready(function() {

        // Initialize tooltips if Bootstrap tooltips are available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Auto-dismiss alerts after 5 seconds
        $('.alert:not(.alert-permanent)').delay(5000).fadeOut('slow');

        // Confirm delete actions
        $('.confirm-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
                return false;
            }
        });

        // Currency format helper
        window.formatCurrency = function(amount) {
            var formatted = parseFloat(amount).toFixed(2);
            var parts = formatted.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            formatted = parts.join('.');

            if (typeof zcInventory !== 'undefined' && zcInventory.currency) {
                var symbol = zcInventory.currency.symbol || '₱';
                var position = zcInventory.currency.position || 'before';

                if (position === 'before') {
                    return symbol + formatted;
                } else {
                    return formatted + ' ' + symbol;
                }
            }

            return '₱' + formatted;
        };

        // Date format helper
        window.formatDate = function(date) {
            var d = new Date(date);
            return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
        };

        // Prevent form double submission
        $('form').on('submit', function() {
            var $form = $(this);
            if ($form.data('submitted') === true) {
                return false;
            }
            $form.data('submitted', true);

            // Re-enable after 3 seconds as a safety measure
            setTimeout(function() {
                $form.data('submitted', false);
            }, 3000);
        });

        // Auto-focus first input in modals
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('input:text:visible:first').focus();
        });

        // Clear form when modal is hidden
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('form')[0]?.reset();
            $(this).find('.alert').remove();
        });

        // DataTable-like search functionality
        if ($('.searchable-table').length) {
            $('.searchable-table').each(function() {
                var $table = $(this);
                var $search = $('<input type="text" class="form-control form-control-sm mb-3" placeholder="Search...">');

                $table.before($search);

                $search.on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $table.find('tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
            });
        }

        // Numeric input validation
        $('input[type="number"]').on('keypress', function(e) {
            var charCode = e.which ? e.which : e.keyCode;
            var value = $(this).val();

            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(charCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (charCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (charCode >= 35 && charCode <= 40)) {
                return;
            }

            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (charCode < 48 || charCode > 57)) && (charCode < 96 || charCode > 105)) {
                e.preventDefault();
            }
        });

        // Price input formatting
        $('.price-input').on('blur', function() {
            var value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value.toFixed(2));
            }
        });

        // Stock warning
        function checkLowStock() {
            $('.stock-badge').each(function() {
                var stock = parseInt($(this).text());
                if (stock <= 5) {
                    $(this).removeClass('bg-warning bg-success').addClass('bg-danger');
                } else if (stock <= 20) {
                    $(this).removeClass('bg-danger bg-success').addClass('bg-warning');
                } else {
                    $(this).removeClass('bg-danger bg-warning').addClass('bg-success');
                }
            });
        }

        checkLowStock();

        // Smooth scroll to top
        if ($('.scroll-to-top').length) {
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('.scroll-to-top').fadeIn();
                } else {
                    $('.scroll-to-top').fadeOut();
                }
            });

            $('.scroll-to-top').click(function() {
                $('html, body').animate({scrollTop: 0}, 'slow');
                return false;
            });
        }

        // Print functionality
        $('.print-btn').on('click', function() {
            window.print();
        });

        // Copy to clipboard
        $('.copy-to-clipboard').on('click', function() {
            var text = $(this).data('clipboard-text');
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();

            // Show feedback
            var $btn = $(this);
            var originalText = $btn.html();
            $btn.html('<i class="bi bi-check"></i> Copied!');
            setTimeout(function() {
                $btn.html(originalText);
            }, 2000);
        });

        // Ajax error handler
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            console.error('Ajax Error:', thrownError);
            if (jqxhr.status === 401) {
                alert('Your session has expired. Please log in again.');
                window.location.href = '/zc-inventory/login';
            }
        });

        // Prevent accidental page leave with unsaved changes
        var formChanged = false;

        $('form input, form textarea, form select').on('change', function() {
            formChanged = true;
        });

        $('form').on('submit', function() {
            formChanged = false;
        });

        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
                e.preventDefault();
                $('input[type="search"], input[placeholder*="Search"]').first().focus();
            }

            // ESC to close modals
            if (e.keyCode === 27) {
                $('.modal').modal('hide');
            }
        });

        // Auto-save form data to localStorage (for recovery)
        if ($('.auto-save-form').length) {
            var formId = $('.auto-save-form').attr('id');

            // Load saved data
            if (localStorage.getItem(formId)) {
                var savedData = JSON.parse(localStorage.getItem(formId));
                $.each(savedData, function(name, value) {
                    $('[name="' + name + '"]').val(value);
                });
            }

            // Save on change
            $('.auto-save-form input, .auto-save-form textarea, .auto-save-form select').on('change', function() {
                var formData = {};
                $('.auto-save-form').find('input, textarea, select').each(function() {
                    formData[$(this).attr('name')] = $(this).val();
                });
                localStorage.setItem(formId, JSON.stringify(formData));
            });

            // Clear on submit
            $('.auto-save-form').on('submit', function() {
                localStorage.removeItem(formId);
            });
        }

        // Loading overlay
        window.showLoading = function() {
            if (!$('#loading-overlay').length) {
                $('body').append('<div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            } else {
                $('#loading-overlay').show();
            }
        };

        window.hideLoading = function() {
            $('#loading-overlay').fadeOut();
        };

    });

})(jQuery);
