function setDateInModal() {
  let sinceDate = document.querySelector('#datesince');
  let forDate = document.querySelector('#datefor');

  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0');
  let yyyy = today.getFullYear();
  today = yyyy + '-' + mm + '-' + dd;
  let monthStart = yyyy + '-' + mm + '-01';
  forDate.setAttribute('value', today);
  sinceDate.setAttribute('value', monthStart)
}
//Submit form on clicking radio buttos
function submitform(formName) {
  document.forms[formName].submit();
}
setDateInModal();


