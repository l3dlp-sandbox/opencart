$('#button-send').on('click', function(e) {
    e.preventDefault();

    $.ajax({
        url: 'index.php?route=common/authorize.send&user_token={{ user_token }}',
        dataType: 'json',
        beforeSend: function() {
            $('#button-send').button('loading');
        },
        complete: function() {
            $('#button-send').button('reset');
        },
        success: function(json) {
            console.log(json);

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }

            if (json['success']) {
                $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

$('#button-reset').on('click', function(e) {
    e.preventDefault();

    $.ajax({
        url: 'index.php?route=common/authorize.confirm&user_token={{ user_token }}',
        dataType: 'json',
        beforeSend: function() {
            $('#button-reset').button('loading');
        },
        complete: function() {
            $('#button-reset').button('reset');
        },
        success: function(json) {
            console.log(json);

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }

            if (json['success']) {
                $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});