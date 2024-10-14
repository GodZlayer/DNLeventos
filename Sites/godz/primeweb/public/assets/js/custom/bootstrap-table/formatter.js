function userStatusSwitchFormatter(value, row) {
    return `<div class="form-check form-switch">
        <input class = "form-check-input switch1 update-user-status" id="${row.user_id}" type = "checkbox" role = "switch${status}" ${value ? 'checked' : ''}>
    </div>`
}

function status_badge(value, row) {
    let badgeClass, badgeText;
    if (value == '0') {
        badgeClass = 'danger';
        badgeText = 'OFF';
    } else {
        badgeClass = 'success';
        badgeText = 'ON';
    }
    return '<span class="badge rounded-pill bg-' + badgeClass + '">' + badgeText + '</span>';
}

function userStatusBadgeFormatter(value, row) {
    let badgeClass, badgeText;
    if (value == '0') {
        badgeClass = 'danger';
        badgeText = 'Inactive';
    } else {
        badgeClass = 'success';
        badgeText = 'Active';
    }
    return '<span class="badge rounded-pill bg-' + badgeClass + +'">' + badgeText + '</span>';
}


function adminFile(value, row) {
    return "<a href='languages/" + row.code + ".json ' )+' > View File < /a>";
}

function appFile(value, row) {
    return "<a href='lang/" + row.code + ".json ' )+' > View File < /a>";
}

function textReadableFormatter(value, row) {
    let string = value.replace("_", " ");
    return string.charAt(0).toUpperCase() + string.slice(1);
}


function unlimitedBadgeFormatter(value) {
    if (!value) {
        return 'Unlimited';
    }
    return value;
}

function detailFormatter(index, row) {
    let html = []
    $.each(row.translations, function (key, value) {
        html.push('<p><b>' + value.language.name + ':</b> ' + value.description + '</p>')
    })
    return html.join('')
}
function imageFormatter(value) {
    if (value) {
        return '<a class="image-popup-no-margins one-image" href="' + value + '">' +
            '<img class="rounded avatar-md shadow img-fluid " alt="" src="' + value + '" width="55" onerror="onErrorImage(event)">' +
            '</a>'
    } else {
        return '-'
    }
}
function statusFormatter(value, row) {
    let badgeClass, badgeText;
    if (row == '0') {
        badgeClass = 'danger';
        badgeText = 'Inactive';
    } else {
        badgeClass = 'success';
        badgeText = 'Active';
    }
    return '<span class="badge rounded-pill bg-' + badgeClass + +'">' + badgeText + '</span>';
}
function newStatusFormatter(value, row) {
    let statusClass, statusText;

    if (row.status == '1') {
        statusClass = 'status-active';
        statusText = 'Active';
    } else {
        statusClass = 'status-inactive';
        statusText = 'Inactive';
    }

    const badgeHtml = `<span class="status-badge ${statusClass}">${statusText}</span>`;
    console.log(badgeHtml);
    console.log(row.status);

    return badgeHtml;
}
