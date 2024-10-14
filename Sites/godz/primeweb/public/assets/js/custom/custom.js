// @ts-nocheck


$(document).ready(function () {

    /// START :: ACTIVE MENU CODE
    $(".menu a").each(function () {
        let pageUrl = window.location.href.split(/[?#]/)[0];
        if (this.href == pageUrl) {
            $(this).parent().parent().addClass("active");
            $(this).parent().addClass("active"); // add active to li of the current link
            $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().parent().addClass("active"); // add active class to an anchor
        }

        let subURL = $("a#subURL").attr("href");
        if (subURL != 'undefined') {
            if (this.href == subURL) {
                $(this).parent().addClass("active"); // add active to li of the current link
                $(this).parent().parent().addClass("active");
                $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
                $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
            }
        }
    });
    /// END :: ACTIVE MENU CODE
    if ($('.select2').length > 0) {
        $('.select2').select2();
    }

    $('.select2-selection__clear').hide();

    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType);

    if ($('.filepond').length > 0) {
        $('.filepond').filepond({
            credits: null,
            allowFileSizeValidation: "true",
            maxFileSize: '25MB',
            labelMaxFileSizeExceeded: 'File is too large',
            labelMaxFileSize: 'Maximum file size is {filesize}',
            allowFileTypeValidation: true,
            acceptedFileTypes: ['image/*'],
            labelFileTypeNotAllowed: 'File of invalid type',
            fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
            storeAsFile: true,
            allowPdfPreview: true,
            pdfPreviewHeight: 320,
            pdfComponentExtraParams: 'toolbar=0&navpanes=0&scrollbar=0&view=fitH',
            allowVideoPreview: true, // default true
            allowAudioPreview: true // default true
        });
    }

    //magnific popup
    $(document).on('click', '.image-popup-no-margins', function () {
        $(this).magnificPopup({
            type: 'image',
            closeOnContentClick: true,
            closeBtnInside: false,
            fixedContentPos: true,
            image: {
                verticalFit: true
            },
            zoom: {
                enabled: true,
                duration: 300 // don't forget to change the duration also in CSS
            },
            gallery: {
                enabled: true
            },
        }).magnificPopup('open');
        return false;
    });

    $('#table_list').on('load-success.bs.table', function () {
        if ($('.gallery').length > 0) {
            $('.gallery').each(function () { // the containers for all your galleries
                $(this).magnificPopup({
                    delegate: 'a', // the selector for gallery item
                    type: 'image',
                    gallery: {
                        enabled: true
                    }
                });
            });
        }
    })

    $(document).off('focusin');
});


/// START :: TinyMCE
document.addEventListener("DOMContentLoaded", () => {
    tinymce.init({
        selector: '#tinymce_editor',
        height: 400,
        menubar: true,
        plugins: [
            'advlist autolink lists link charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime table paste code help wordcount'
        ],

        toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        setup: function (editor) {
            editor.on("change keyup", function () {
                //tinyMCE.triggerSave(); // updates all instances
                editor.save(); // updates this instance's textarea
                $(editor.getElement()).trigger('change'); // for garlic to detect change
            });
        }
    });
});

$('body').append('<div id="loader-container"><div class="loader"></div></div>');
$(window).on('load', function () {
    $('#loader-container').fadeOut('slow');
});

setTimeout(function () {
    $(".error-msg").fadeOut(1500)
}, 5000);

document.addEventListener('touchstart', event => {
    if (event.cancelable) {
        event.preventDefault();
    }
});

document.addEventListener('touchmove', event => {
    if (event.cancelable) {
        event.preventDefault();
    }
});

document.addEventListener('touchcancel', event => {
    if (event.cancelable) {
        event.preventDefault();
    }
});

$('.status-switch').on('change', function () {
    if ($(this).is(":checked")) {
        $(this).siblings('input[type="hidden"]').val(1);
    } else {
        $(this).siblings('input[type="hidden"]').val(0);
    }
})

$('input[type="radio"][name="duration_type"]').on('click', function () {
    if ($(this).hasClass('edit_duration_type')) {
        if ($(this).is(':checked')) {
            if ($(this).val() == 'limited') {
                $('#edit_limitation_for_duration').show();
                $('#edit_durationLimit').attr("required", "true").val("");
            } else {
                // Unlimited
                $('#edit_limitation_for_duration').hide();
                $('#edit_durationLimit').removeAttr("required").val("");
            }
        }
    } else {
        if ($(this).is(':checked')) {
            if ($(this).val() == 'limited') {
                $('#limitation_for_duration').show();
                $('#durationLimit').attr("required", "true").val("");
            } else {
                // Unlimited
                $('#limitation_for_duration').hide();
                $('#durationLimit').removeAttr("required").val("");
            }
        }
    }
});

$('input[type="radio"][name="item_limit_type"]').on('click', function () {
    if ($(this).hasClass('edit_item_limit_type')) {
        if ($(this).is(':checked')) {
            if ($(this).val() == 'limited') {
                $('#edit_limitation_for_limit').show();
                $('#edit_ForLimit').attr("required", "true");
            } else {
                // Unlimited
                $('#edit_limitation_for_limit').hide();
                $('#edit_ForLimit').val('');
                $('#edit_ForLimit').removeAttr("required");
            }
        }
    } else {
        if ($(this).is(':checked')) {
            if ($(this).val() == 'limited') {
                $('#limitation_for_limit').show();
                $('#durationForLimit').attr("required", "true");
            } else {
                // Unlimited
                $('#limitation_for_limit').hide();
                $('#durationForLimit').removeAttr("required");
            }
        }
    }
});


$("#include_image").change(function () {
    if (this.checked) {
        $('#show_image').show('fast');
        $('#file').attr('required', 'required');
    } else {
        $('#file').val('');
        $('#file').removeAttr('required');
        $('#show_image').hide('fast');
    }
});


$('#user_notification_list').on('check.bs.table  uncheck.bs.table', function () {
    let fcm_list = [];
    let user_list = [];
    let data = $("#user_notification_list").bootstrapTable('getSelections');
    data.forEach(function (value) {
        if (value.fcm_id != "") {
            fcm_list.push(value.fcm_id);
        }
        if (value.id != "") {
            user_list.push(value.id);
        }
    })

    $('textarea#fcm_id').text(fcm_list);
    $('textarea#user_id').text(user_list);
});

$('#delete_multiple').on('click', function (e) {
    e.preventDefault();
    let table = $('#table_list');
    let selected = table.bootstrapTable('getSelections');
    let ids = "";

    $.each(selected, function (i, e) {
        ids += e.id + ",";
    });
    ids = ids.slice(0, -1);
    if (ids == "") {
        showErrorToast(trans('Please Select Notification First'));
    } else {
        showDeletePopupModal($(this).attr('href'), {
            data: {
                id: ids
            }, successCallBack: function () {
                $('#table_list').bootstrapTable('refresh');
            }
        })
    }
});

$('#type').on('change', function () {
    if ($.inArray($(this).val(), ['checkbox', 'radio', 'dropdown']) > -1) {
        $('#field-values-div').slideDown(500);
        $('.min-max-fields').slideUp(500);
    } else {
        $('#field-values-div').slideUp(500);
        $('.min-max-fields').slideDown(500);
    }
});

$('.image').on('change', function () {
    const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    const fileInput = this;
    const [file] = fileInput.files;
    if (!file) {
        return; // No file selected
    }

    if (!allowedExtensions.exec(file.name)) {
        $('.img_error').text('Invalid file type. Please choose an image file.');
        fileInput.value = '';
        return;
    }

    const maxFileSize = 2 * 1024 * 1024; // 5MB (adjust as needed)
    if (file.size > maxFileSize) {
        $('.img_error').text('File size exceeds the maximum allowed size (2MB).');
        fileInput.value = '';
    }
    if (file) {
        $(this).siblings('.preview-image').attr('src', URL.createObjectURL(file))
    }
});

$('.img_input').on('click', function () {
    $(this).siblings('.image').click();
});

$(".toggle-password").on('click', function () {
    $(this).toggleClass("bi bi-eye bi-eye-slash");
    let input = $(this).parent().siblings("input");
    if (input.attr("type") == "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});

$(document).on('change', '.update-user-status', function () {
    let url = window.baseurl + "common/change-status";
    ajaxRequest('PUT', url, {
        id: $(this).attr('id'),
        table: "users",
        column: "deleted_at",
        status: $(this).is(':checked') ? 1 : 0
    }, null, function (response) {
        showSuccessToast(response.message);
    }, function (error) {
        showErrorToast(error.message);
    })
})

$('#switch_banner_ad_status').on('change', function () {
    $('#banner_ad_id_android').attr('required', $(this).is(':checked'));
    $('#banner_ad_id_ios').attr('required', $(this).is(':checked'));
})


$('#switch_interstitial_ad_status').on('change', function () {
    $('#interstitial_ad_id_android').attr('required', $(this).is(':checked'));
    $('#interstitial_ad_id_ios').attr('required', $(this).is(':checked'));
})

$("#include_image").change(function () {
    if (this.checked) {
        $('#show_image').show('fast');
        $('#file').attr('required', 'required');
    } else {
        $('#file').val('');
        $('#file').removeAttr('required');
        $('#show_image').hide('fast');
    }
});
$(document).ready(function () {

    /// START :: ACTIVE MENU CODE
    $(".menu a").each(function () {
        let pageUrl = window.location.href.split(/[?#]/)[0];
        if (this.href == pageUrl) {
            $(this).parent().parent().addClass("active");
            $(this).parent().addClass("active"); // add active to li of the current link
            $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().parent().addClass("active"); // add active class to an anchor
        }

        let subURL = $("a#subURL").attr("href");
        if (subURL != 'undefined') {
            if (this.href == subURL) {
                $(this).parent().addClass("active"); // add active to li of the current link
                $(this).parent().parent().addClass("active");
                $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
                $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
            }
        }
    });
    /// END :: ACTIVE MENU CODE
    if ($('.select2').length > 0) {
        $('.select2').select2();
    }

    $('.select2-selection__clear').hide();

    FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType);

    if ($('.filepond').length > 0) {
        $('.filepond').filepond({
            credits: null,
            allowFileSizeValidation: "true",
            maxFileSize: '25MB',
            labelMaxFileSizeExceeded: 'File is too large',
            labelMaxFileSize: 'Maximum file size is {filesize}',
            allowFileTypeValidation: true,
            acceptedFileTypes: ['image/*'],
            labelFileTypeNotAllowed: 'File of invalid type',
            fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
            storeAsFile: true,
            allowPdfPreview: true,
            pdfPreviewHeight: 320,
            pdfComponentExtraParams: 'toolbar=0&navpanes=0&scrollbar=0&view=fitH',
            allowVideoPreview: true, // default true
            allowAudioPreview: true // default true
        });
    }

    //magnific popup
    $(document).on('click', '.image-popup-no-margins', function () {
        $(this).magnificPopup({
            type: 'image',
            closeOnContentClick: true,
            closeBtnInside: false,
            fixedContentPos: true,
            image: {
                verticalFit: true
            },
            zoom: {
                enabled: true,
                duration: 300 // don't forget to change the duration also in CSS
            },
            gallery: {
                enabled: true
            },
        }).magnificPopup('open');
        return false;
    });

    $('#table_list').on('load-success.bs.table', function () {
        if ($('.gallery').length > 0) {
            $('.gallery').each(function () { // the containers for all your galleries
                $(this).magnificPopup({
                    delegate: 'a', // the selector for gallery item
                    type: 'image',
                    gallery: {
                        enabled: true
                    }
                });
            });
        }
    })

    $(document).off('focusin');
});
