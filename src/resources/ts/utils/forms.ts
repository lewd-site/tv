export interface Field {
  readonly name: string;
  readonly element: HTMLInputElement;
  readonly validate: () => boolean;
}

export function field(form: HTMLElement, fieldName: string): Field {
  const fieldElement = form.querySelector<HTMLInputElement>(`[name="${fieldName}"]`);
  const errorElement = form.querySelector<HTMLElement>(`[name="${fieldName}"] ~ .input-error`);

  if (!fieldElement) {
    throw new Error(`${fieldName} field not found`);
  }

  const validate = () => {
    const isValid = fieldElement.checkValidity();

    // Mark input as "invalid"/"valid".
    if (isValid) {
      fieldElement.classList.add('valid');
      fieldElement.classList.remove('invalid');
    } else {
      fieldElement.classList.add('invalid');
      fieldElement.classList.remove('valid');
    }

    // Update validation message.
    if (errorElement) {
      errorElement.textContent = isValid ? '' : fieldElement.validationMessage;
    }

    return isValid;
  };

  fieldElement.addEventListener('input', () => {
    // Mark input as "dirty" on input.
    fieldElement.classList.add('dirty');

    // Mark/unmark input as "empty".
    if (fieldElement.value.length) {
      fieldElement.classList.remove('empty');
    } else {
      fieldElement.classList.add('empty');
    }

    validate();
  });

  return {
    name: fieldName,
    element: fieldElement,
    validate,
  };
}
