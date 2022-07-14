$("button.edit").on("click", function () {
  const editButtonID = $(this).attr('id');
  $("input[id=" + editButtonID + "]").prop('disabled', function (i, v) { return !v; });
  $("button.save[id=" + editButtonID + "]").prop('disabled', function (i, v) { return !v; });
  $("button.delete[id=" + editButtonID + "]").prop('disabled', function (i, v) { return !v; });
});

$("button.deleteModal").on("click", function () {
  const deleteButtonID = $(this).attr('id');
  $("input[id=delete-" + deleteButtonID + "][name=processedCategoryDelete]").prop('disabled', false).prop('checked', true);
});


const cxpenseCategoriesTab = document.querySelector("#expense-categories-tab");
const incomeCategoriesTab = document.querySelector("#income-categories-tab");
const paymentMethodsTab = document.querySelector("#payment-methods-tab");


const hideLimitLabel = () => {
  document.querySelector("#set-limit-label").classList.add("d-none");
}
const showLimitLabel = () => {
  document.querySelector("#set-limit-label").classList.remove("d-none");
}

$("button.deleteLimit").on("click", function () {
  const deleteButtonID = $(this).attr('id');
  console.log(deleteButtonID);
  $("input[id=" + deleteButtonID + "]").prop('disabled', false).prop('checked', true);
});


function currentDate() {
  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0');
  let yyyy = today.getFullYear();
  today = yyyy + '-' + mm + '-' + dd;
  return today;
}
function setCurrentDate(dateInput) {
  dateInput.value = currentDate();
}

const limitDateInputs = document.querySelectorAll('.addLimitDate');
limitDateInputs.forEach(limitDate => {
  if (limitDate.value == '') {
    setCurrentDate(limitDate);
  }
});

const categoryIDRadio = document.querySelectorAll(".category-ID-if-limit-is-set");
function changeColorIfLimitIsSet() {
  categoryIDRadio.forEach(cat => {
    limitBtnID = cat.getAttribute('id').replace('category-ID-if-limit-is-set-', 'expenseCategory');
    document.querySelector('.limit#' + limitBtnID).classList.add('text-primary')
  });
}
//expenseCategory{{ category.id }}
window.addEventListener('load', changeColorIfLimitIsSet);
incomeCategoriesTab.addEventListener('click', hideLimitLabel);
paymentMethodsTab.addEventListener('click', hideLimitLabel);
cxpenseCategoriesTab.addEventListener('click', showLimitLabel);
