document.addEventListener('DOMContentLoaded', () => {
  const password = document.querySelector<HTMLInputElement>('[name="password"]');
  const confirmPassword = document.querySelector<HTMLInputElement>('[name="confirm-password"]');

  if (!password) {
    return console.warn('Password field not found');
  }

  if (!confirmPassword) {
    return console.warn('Confirm password field not found');
  }

  const callback = () => {
    if (password.value !== confirmPassword.value) {
      confirmPassword.setCustomValidity('Passwords don\'t match');
    } else {
      confirmPassword.setCustomValidity('');
    }
  };

  password.addEventListener('input', callback);
  confirmPassword.addEventListener('input', callback);
});
