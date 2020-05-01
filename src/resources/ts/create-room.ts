import { field, Field } from './utils';

class CreateRoomViewModel {
  private readonly fields: { [key: string]: Field };

  public constructor() {
    const form = document.querySelector<HTMLElement>('.create-room');
    if (!form) {
      throw new Error('Create room form not found');
    }

    this.fields = ['name', 'url']
      .map(fieldName => field(form, fieldName))
      .reduce((fields, field) => ({ ...fields, [field.name]: field }), {});
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const viewModel = new CreateRoomViewModel();
});
