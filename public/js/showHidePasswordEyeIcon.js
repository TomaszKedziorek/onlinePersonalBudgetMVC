const togglePassword = document.querySelector('#eyeIcon');
const password = document.querySelector('#inputPassword');
if (togglePassword) {
  togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type');
    if (type === 'password') password.setAttribute('type', 'text');
    else password.setAttribute('type', 'password');
    this.classList.toggle('bi-eye');
  });
}
