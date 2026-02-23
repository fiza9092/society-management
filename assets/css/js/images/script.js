// Society Management System - Main JavaScript

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.delete-btn').click(function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Form validation
    $('form').submit(function() {
        var valid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                valid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return valid;
    });
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.searchable-row').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Select all checkbox
    $('#selectAll').click(function() {
        $('.select-item').prop('checked', $(this).prop('checked'));
    });
    
    // Dynamic dependent dropdowns
    $('#country').change(function() {
        var country = $(this).val();
        if (country) {
            $.ajax({
                url: 'api/get_states.php',
                type: 'POST',
                data: {country: country},
                success: function(data) {
                    $('#state').html(data);
                }
            });
        }
    });
    
    // Date picker initialization
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
    
    // Number formatting for currency
    $('.currency').on('input', function() {
        var value = $(this).val().replace(/,/g, '');
        if (!isNaN(value) && value.length > 0) {
            $(this).val(parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
        }
    });
    
    // Password strength meter
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/)) strength += 25;
        
        $('#strength-bar').css('width', strength + '%');
        
        if (strength <= 25) {
            $('#strength-bar').removeClass().addClass('progress-bar bg-danger');
            $('#strength-text').text('Weak');
        } else if (strength <= 50) {
            $('#strength-bar').removeClass().addClass('progress-bar bg-warning');
            $('#strength-text').text('Fair');
        } else if (strength <= 75) {
            $('#strength-bar').removeClass().addClass('progress-bar bg-info');
            $('#strength-text').text('Good');
        } else {
            $('#strength-bar').removeClass().addClass('progress-bar bg-success');
            $('#strength-text').text('Strong');
        }
    });
    
    // Confirm password match
    $('#confirm_password').on('keyup', function() {
        var password = $('#password').val();
        var confirm = $(this).val();
        
        if (password === confirm && password.length > 0) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
});

// Print function
function printContent(elementId) {
    var content = document.getElementById(elementId).innerHTML;
    var originalContent = document.body.innerHTML;
    document.body.innerHTML = content;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// Export to CSV
function exportToCSV(tableId, filename) {
    var csv = [];
    var rows = document.getElementById(tableId).querySelectorAll('tr');
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll('td, th');
        for (var j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText + '"');
        }
        csv.push(row.join(','));
    }
    
    var csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
    var downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// AJAX form submission
function submitAjaxForm(formId, url, callback) {
    var formData = $('#' + formId).serialize();
    $.ajax({
        type: 'POST',
        url: url,
        data: formData,
        success: function(response) {
            if (callback) callback(response);
            showNotification('Success!', 'success');
        },
        error: function() {
            showNotification('Error! Please try again.', 'error');
        }
    });
}

// Show notification
function showNotification(message, type) {
    var notification = $('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');
    
    $('.container-fluid').prepend(notification);
    
    setTimeout(function() {
        notification.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

// Chart initialization
function initChart(chartId, type, labels, data) {
    var ctx = document.getElementById(chartId).getContext('2d');
    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}