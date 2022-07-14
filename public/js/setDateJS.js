function currentDate() {
  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0');
  let yyyy = today.getFullYear();
  today = yyyy + '-' + mm + '-' + dd;
  return today;
}
function setCurrentDate(x) {
  const dateInput = document.querySelector(x);
  dateInput.value = currentDate();
}
if (document.querySelector('input[type="date"]').getAttribute('value') == '') {
  setCurrentDate('input[type="date"]');
}