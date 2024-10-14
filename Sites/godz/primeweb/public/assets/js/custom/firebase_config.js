
const firebaseConfig = {
    apiKey: apiKey.value,
    authDomain: authDomain.value,
    projectId: projectId.value,
    storageBucket: storageBucket.value,
    messagingSenderId: messagingSenderId.value,
    appId: appId.value,
    measurementId: measurementId.value
};

if (!firebase.apps.length) {

    firebase.initializeApp(firebaseConfig);
}

const messaging = firebase.messaging();
messaging.requestPermission()
    .then(function () {
        console.log('Notification permission granted.');

        getRegToken();
    })
    .catch(function (err) {
        console.log('Unable to get permission to notify.', err);
        Swal.fire({
            title: 'Allow Notification Permission!',
            icon: 'error',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        })
    });

function getRegToken(argument) {
    messaging.getToken()
        .then(function (currentToken) {

            saveToken(currentToken);
        })
        .catch(function (err) {
            console.log('An error occurred while retrieving token. ', err);

        });
}


function saveToken(currentToken) {

    console.log(currentToken);
    $.ajax({
        url: "updateFCMID",
        method: 'get',
        data: {
            token: currentToken,
            id: 1
        }
    }).done(function (result) {

    });
}

messaging.onMessage(function (payload) {
    notificationTitle = payload.data.title;
    notificationOptions = {
        body: payload.data.body,
        icon: payload.data.icon,
        // image:  payload.data.image,
        data: {
            time: new Date(Date.now()).toString(),

        }
    };
    var notification = new Notification(notificationTitle, notificationOptions);


});




