window.onboardingEvents = {
    'click .edit_btn': function (e, value, row) {
        $('#edit_status_active').attr('checked', false);
        $('#edit_status_inactive').attr('checked', false);
        $('#edit_id').val(row.id);
        $('#edit_title').val(row.title);
        $('#edit_image').attr('src', row.image);
        $('#edit_description').val(row.description);
        if (row.status == "1") {
            $('#edit_status_inactive').attr('checked', false);
            $('#edit_status_active').attr('checked', true);

        }
       else {
            $('#edit_status_active').attr('checked', false);
            $('#edit_status_inactive').attr('checked', true);

        }
    }
}
window.draweritemEvents = {
    'click .edit_btn': function (e, value, row) {
        $('#edit_id').val(row.id);
        $('#edit_title').val(row.title);
        $('#edit_image').attr('src', row.image);
        $('#edit_url').val(row.url);

        if (row.status == "1") {
            $('#edit_status_inactive').attr('checked', false);
            $('#edit_status_active').attr('checked', true);

        }
       else {
            $('#edit_status_active').attr('checked', false);
            $('#edit_status_inactive').attr('checked', true);

        }
    }
}
