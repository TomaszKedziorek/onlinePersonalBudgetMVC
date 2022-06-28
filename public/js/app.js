//Add custom method to validate format of password
$.validator.addMethod('validPassword',
  function (value, element, param) {
    //value - vlaue of the field

    //if not empty
    if (value != '') {
      //chck pattern of regex
      if (value.match(/.*[a-z]+.*/i) == null) {
        return false;
      }
      if (value.match(/.*\d+.*/i) == null) {
        return false;
      }
    }
    return true;
  },
  //Message when faild
  'Must contain at least one letter and one number'
);