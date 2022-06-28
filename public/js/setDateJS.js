function setCurrentDate(x) {
  let currentDate = document.querySelector(x);
  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0');
  let yyyy = today.getFullYear();
  today = yyyy + '-' + mm + '-' + dd;
  currentDate.setAttribute('value', today);
}
if (document.querySelector('input[type="date"]').getAttribute('value') == '') {
  setCurrentDate('input[type="date"]');
}