import { field, Field } from './utils';

class LoginViewModel {
  private readonly fields: { [key: string]: Field };

  public constructor() {
    const form = document.querySelector<HTMLElement>('.login');
    if (!form) {
      throw new Error('Login form not found');
    }

    this.fields = ['email', 'password']
      .map(fieldName => field(form, fieldName))
      .reduce((fields, field) => ({ ...fields, [field.name]: field }), {});
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const viewModel = new LoginViewModel();
});
