/* jshint esversion: 6 */
/* global jQuery, wpaal_admin */
(function ($) {
    'use strict';

    // Pro tab click — show upgrade modal ONLY, do NOT navigate to that tab
    $(document).on('click', '.wpaal-pro-tab-link, .wpaal-pro-link', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        var featureName = $(this).data('pro-feature') || $(this).text().trim() || 'Pro Feature';
        $('#wpaal_pro_req_feature').val(featureName);
        $('#wpaal_pro_req_msg').val('I am interested in the ' + featureName + ' feature for Admin Activity Logger.');
        
        $('#wpaal-pro-form-view').hide();
        $('#wpaal-pro-promo-view').show();
        
        // Never switch tab — just show the upgrade modal
        $('#wpaal-pro-modal').addClass('is-visible');
    });

    // Inner Tab navigation (non-pro only)
    $(document).on('click', '.wpaal-inner-tab-link', function (e) {
        if ($(this).hasClass('wpaal-pro-tab-link')) {
            return; // handled by the pro tab click event above
        }
        e.preventDefault();
        var target = $(this).attr('href');
        if (!target || target === '#') { return; }
        $('.wpaal-inner-tab-link').removeClass('active');
        $('.wpaal-inner-tab-panel').hide().removeClass('active');
        $(this).addClass('active');
        $(target).show().addClass('active');
    });

    // Tab navigation
    $(document).on('click', '.wpaal-tab-link', function (e) {
        // If it's a pro tab link, the separate handler above will handle it.
        if ($(this).hasClass('wpaal-pro-tab-link')) {
            return;
        }
        e.preventDefault();
        var target = $(this).attr('href');
        if (!target || target === '#') { return; }
        $('.wpaal-tab-link').removeClass('active');
        $('.wpaal-tab-panel').removeClass('active');
        $(this).addClass('active');
        $(target).addClass('active');
    });

    // Pro modal close
    $(document).on('click', '#wpaal-pro-modal-backdrop, #wpaal-pro-modal-close', function () {
        $('#wpaal-pro-modal').removeClass('is-visible');
    });

    // Toggle cleanup panel
    $('#wpaal-toggle-cleanup').on('click', function () {
        var panel = $('#wpaal-cleanup-panel');
        panel.toggle();
    });

    // Custom Elegant Deletion Confirmation Modal
    var activeAction = null; // 'delete_single' or 'clear_logs'
    var deleteUrl = '';
    var clearForm = null;

    // Trigger for single log deletion
    $(document).on('click', '.wpaal-delete-log-btn', function (e) {
        e.preventDefault();
        activeAction = 'delete_single';
        deleteUrl = $(this).attr('href');
        
        $('#wpaal-confirm-title').text(wpaal_admin.confirm_title_single || 'Confirm Deletion');
        $('#wpaal-confirm-message').text(wpaal_admin.confirm_msg_single || 'Are you sure you want to delete this log entry? This action cannot be undone.');
        $('#wpaal-confirm-modal').addClass('is-visible');
    });

    // Trigger for clear logs
    $(document).on('click', '.wpaal-confirm-clear', function (e) {
        e.preventDefault();
        activeAction = 'clear_logs';
        clearForm = $(this).closest('form');
        var val = $('#wpaal_cleanup_action_select').val();
        var msg = val === 'clear_all'
            ? wpaal_admin.confirm_clear_all
            : wpaal_admin.confirm_clear;
            
        $('#wpaal-confirm-title').text(wpaal_admin.confirm_title_clear || 'Clear Logs');
        $('#wpaal-confirm-message').text(msg);
        $('#wpaal-confirm-modal').addClass('is-visible');
    });

    $('#wpaal-confirm-cancel, .wpaal-confirm-modal-backdrop').on('click', function () {
        $('#wpaal-confirm-modal').removeClass('is-visible');
        deleteUrl = '';
        clearForm = null;
        activeAction = null;
    });

    // Select all checkboxes toggle
    $(document).on('change', '#cb-select-all-1', function () {
        var checked = $(this).prop('checked');
        $('.wpaal-logs-table tbody .check-column input[type="checkbox"]').prop('checked', checked);
    });

    // Individual checkbox change updating the Select All checkbox state
    $(document).on('change', '.wpaal-logs-table tbody .check-column input[type="checkbox"]', function () {
        var total = $('.wpaal-logs-table tbody .check-column input[type="checkbox"]').length;
        var checkedCount = $('.wpaal-logs-table tbody .check-column input[type="checkbox"]:checked').length;
        $('#cb-select-all-1').prop('checked', total === checkedCount && total > 0);
    });

    // Trigger for bulk deletion
    $(document).on('submit', '#wpaal-bulk-actions-form', function (e) {
        var action = $('#bulk-action-selector-top').val();
        if (action === 'delete') {
            var checkedCount = $('.wpaal-logs-table tbody .check-column input[type="checkbox"]:checked').length;
            if (checkedCount === 0) {
                alert(wpaal_admin.select_alert || 'Please select at least one log entry.');
                e.preventDefault();
                return;
            }
            e.preventDefault();
            activeAction = 'bulk_delete';
            clearForm = $(this);
            
            $('#wpaal-confirm-title').text(wpaal_admin.confirm_title_single || 'Confirm Deletion');
            var bulkMsg = (wpaal_admin.confirm_msg_bulk || 'Are you sure you want to delete the selected %d log entries?').replace('%d', checkedCount);
            $('#wpaal-confirm-message').text(bulkMsg);
            $('#wpaal-confirm-modal').addClass('is-visible');
        }
    });

    $('#wpaal-confirm-ok').on('click', function () {
        $('#wpaal-confirm-modal').removeClass('is-visible');
        if (activeAction === 'delete_single' && deleteUrl) {
            window.location.href = deleteUrl;
        } else if (activeAction === 'clear_logs' && clearForm) {
            clearForm.submit();
        } else if (activeAction === 'bulk_delete' && clearForm) {
            clearForm[0].submit();
        }
        deleteUrl = '';
        clearForm = null;
        activeAction = null;
    });

    // Clean up query parameters from URL so they do not persist on page refresh/reload
    if (window.history.replaceState) {
        var url = new URL(window.location.href);
        if (url.searchParams.has('wpaal_msg')) {
            url.searchParams.delete('wpaal_msg');
            url.searchParams.delete('wpaal_count');
            url.searchParams.delete('wpaal_notice_nonce');
            window.history.replaceState(null, null, url.href);
        }
    }

})(jQuery);
