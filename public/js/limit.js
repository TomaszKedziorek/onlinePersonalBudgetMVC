const limitSection = document.querySelector('#limitSection');
const changeCategory = document.querySelector('select#expens-cat');
const changeDate = document.querySelector('input#date');
const changeAmount = document.querySelector("input#amount");

function getInputDate() {
  return document.querySelector('#date').value;
}
function monthFromDate(fullDate) {
  const date = new Date(fullDate);
  const year = date.getFullYear();
  const options = { month: 'long' };
  const monthName = new Intl.DateTimeFormat('en-US', options).format(date);
  return monthName + ' ' + year;
}
function getCategoryValue() {
  return document.querySelector('option:checked').value;
}
function getCategoryName() {
  return document.querySelector('option:checked').innerText;
}
function getCurrentAmount() {
  if (changeAmount.value != '')
    return roundToSecondPlace(parseFloat(changeAmount.value));
  else
    return "0.00";
}
function roundToSecondPlace(number) {
  return (Math.round((number) * 100) / 100).toFixed(2);
}
async function checkLimit() {
  const id = getCategoryValue();
  const date = getInputDate();
  try {
    const config = { method: 'GET' };
    const response = await fetch(`https://budget.tomasz-kedziorek.profesjonalnyprogramista.pl/api/limit/${id}?inputDate=${date}`, config);
    const data = await response.json();
    return data;
  } catch (error) {
    console.log('ERROR!!!', error);
    return "ERROR";
  }
}

const spanLimitCategory = document.querySelector('#span-limit-category');
const spanLimitDate = document.querySelector('#span-limit-date');
const spanLimitAmount = document.querySelector('#span-limit-amount');
const spanTotalExpenses = document.querySelector('#span-total-expenses');
const spanLeftSpend = document.querySelector('#span-left-spend');
const spanCurrentExpense = document.querySelector('#current-expense');
const spanCurrentExpenseText = document.querySelector('#current-expense-text');
const spanExceededMessage = document.querySelector('#exceeded-message');

let leftToSpend = null;
async function limit() {
  if (getCategoryValue() != 0 && getInputDate() != '') {
    const limitData = await checkLimit();
    if (limitData.limit_id != null) {
      limitSection.classList.remove("d-none");
      spanLimitCategory.innerText = limitData.name;
      spanLimitDate.innerText = monthFromDate(limitData.date_of_limit);
      spanLimitAmount.innerText = limitData.limit;
      spanTotalExpenses.innerText = limitData.total_expenses;
      leftToSpend = roundToSecondPlace(limitData.limit - limitData.total_expenses);
      spanLeftSpend.innerText = leftToSpend;
      setColorForSpanValue(spanLeftSpend);
      toSpendWithCurrentExpense();
      limitExceeded();
    } else {
      limitSection.classList.add("d-none");
      spanLimitCategory.innerText = '';
      spanLimitDate.innerText = '';
      spanLimitAmount.innerText = '';
      spanTotalExpenses.innerText = '';
      leftToSpend = null;
      spanLeftSpend.innerText = '';
    }
  }
}
function toSpendWithCurrentExpense() {
  if (getCurrentAmount() == 0) {
    spanCurrentExpenseText.classList.add("d-none");
  } else {
    spanCurrentExpenseText.classList.remove("d-none");
    spanCurrentExpense.innerText = roundToSecondPlace(leftToSpend - getCurrentAmount());
    setColorForSpanValue(spanCurrentExpense);
    limitExceeded();
  }
}
function setColorForSpanValue(givenSpan) {
  spanValue = parseFloat(givenSpan.innerText);
  givenSpan.className = '';
  if (spanValue < 0) {
    givenSpan.classList.add('text-danger');
  } else if (spanValue == 0) {
    givenSpan.classList.add('text-warning');
  } else {
    givenSpan.classList.add('text-info');
  }
}
function limitExceeded() {
  let left = parseFloat(spanLeftSpend.innerText);
  let expense = parseFloat(spanCurrentExpense.innerText);
  spanExceededMessage.innerText = '';
  if (left < 0 || expense < 0) {
    spanExceededMessage.innerText = ' The limit exceeded!';
  }
}

changeCategory.addEventListener("change", limit);
changeDate.addEventListener("change", limit);
changeAmount.addEventListener("keyup", toSpendWithCurrentExpense);
changeAmount.addEventListener("change", toSpendWithCurrentExpense);
window.addEventListener("load", limit);
