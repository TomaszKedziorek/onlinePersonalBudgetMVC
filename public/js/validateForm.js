$(document).ready(function () {
  //Validate the form
  $('#formSignup').validate({
    errorClass: "text-danger",
    rules: {
      name: 'required',
      email: {
        required: true,
        email: true,
        remote: '/account/validate-email'
      },
      password: {
        required: true,
        minlength: 6,
        validPassword: true
      }
    },
    messages: {
      email: {
        remote: 'Email already taken'
      }
    },
    errorPlacement: function (error, element) {
      if (element.attr("name") == "password")
        error.appendTo(".passwordErrorHere");
      else
        error.insertAfter(element);
    }
  });
});
