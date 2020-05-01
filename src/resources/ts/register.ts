import { field, Field } from './utils';

class RegisterViewModel {
  private readonly fields: { [key: string]: Field };

  public constructor() {
    const form = document.querySelector<HTMLElement>('.register');
    if (!form) {
      throw new Error('Register form not found');
    }

    this.fields = ['name', 'email', 'password', 'confirm-password']
      .map(fieldName => field(form, fieldName))
      .reduce((fields, field) => ({ ...fields, [field.name]: field }), {});

    this.fields['password'].element.addEventListener('input', this.onPasswordInput);
    this.fields['confirm-password'].element.addEventListener('input', this.onConfirmPasswordInput);
  }

  private checkPasswordsMatch = () => {
    const password = this.fields['password'];
    const confirmPassword = this.fields['confirm-password'];
    if (password.element.value !== confirmPassword.element.value) {
      confirmPassword.element.setCustomValidity('Пароли не совпадают');
      confirmPassword.validate();
    } else {
      confirmPassword.element.setCustomValidity('');
      confirmPassword.validate();
    }
  };

  private onPasswordInput = () => {
    this.checkPasswordsMatch();
  }

  private onConfirmPasswordInput = () => {
    this.checkPasswordsMatch();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const viewModel = new RegisterViewModel();
});
