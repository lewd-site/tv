import Chat from './components/Chat.vue';
import Observable from './observable';
import { ChatMessage } from './types';

declare global {
  interface Window {
    readonly messages?: ChatMessage[];
    chat?: ChatModel;
  }
}

class ChatModel {
  public constructor(public readonly messages: Observable<ChatMessage[]>) { }
}

document.addEventListener('DOMContentLoaded', () => {
  const model = new ChatModel(new Observable(window.messages || []));
  window.chat = model;

  const viewModel = new Chat({ propsData: { messages: model.messages } });
  viewModel.$mount('.chat__main', true);
});
