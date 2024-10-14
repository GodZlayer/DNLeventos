const input = document.querySelector("#phonenumber");

window.intlTelInput(input, {
  initialCountry: "br",
  utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
});