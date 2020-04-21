import Chat from './components/Chat.vue';
import Observable from './observable';
import { ChatMessage, Room } from './types';

declare global {
  interface Window {
    readonly room?: Room;
    readonly messages?: ChatMessage[];
    chat?: ChatModel;
  }
}

const CHAT_MESSAGES = 100;

class ChatModel {
  public constructor(public readonly messages: Observable<ChatMessage[]>) {
    if (!window.Echo) {
      console.warn('Echo is not defined');
      return;
    }

    window.Echo.channel(`rooms.${window.room?.id}`)
      .listen('ChatMessageEvent', (message: ChatMessage) => {
        const messages = [...this.messages.get(), message];
        if (messages.length > CHAT_MESSAGES) {
          messages.shift();
        }

        this.messages.set(messages);
      });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const model = new ChatModel(new Observable(window.messages || []));
  window.chat = model;

  const viewModel = new Chat({ propsData: { messages: model.messages } });
  viewModel.$mount('.chat__main', true);
});
