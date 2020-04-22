import Axios from 'axios';
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

interface PresenceChannelUser {
  readonly id: number;
  readonly name: string;
}

const CHAT_MESSAGES = 100;

class ChatModel {
  public readonly users = new Observable<PresenceChannelUser[]>([]);
  public readonly messages = new Observable<ChatMessage[]>([]);

  public constructor() {
    if (!window.Echo) {
      console.warn('Echo is not defined');
      return;
    }

    if (!window.room) {
      console.warn('room is not defined');
      return;
    }

    window.Echo.channel(`rooms.${window.room.id}`)
      .listen('ChatMessageEvent', (message: ChatMessage) => {
        const messages = [...this.messages.get(), message];
        if (messages.length > CHAT_MESSAGES) {
          messages.shift();
        }

        this.messages.set(messages);
      });

    window.Echo.join(`rooms.${window.room.id}`)
      .here((users: PresenceChannelUser[]) => this.users.set(users))
      .joining((user: PresenceChannelUser) => {
        const users = [...this.users.get(), user];
        this.users.set(users);
      })
      .leaving((user: PresenceChannelUser) => {
        const users = this.users.get().filter(u => u.id !== user.id);
        this.users.set(users);
      });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const model = new ChatModel();
  model.messages.set(window.messages || []);
  window.chat = model;

  const viewModel = new Chat({ propsData: { messages: model.messages } });
  viewModel.$mount('.chat__main', true);

  // Show count of online users.

  const count = document.querySelector<HTMLElement>('.chat__count');
  if (count) {
    model.users.subscribe(users => {
      count.textContent = `${users.length} online`;
    });
  } else {
    console.warn('.chat__count not found');
  }

  // Handle form submit.

  const chatForm = document.querySelector<HTMLFormElement>('.chat__form');
  if (!chatForm) {
    return console.warn('.chat__form not found');
  }

  const messageInput = chatForm.querySelector<HTMLInputElement>('.chat__input');
  if (!messageInput) {
    return console.warn('.chat__input not found');
  }

  chatForm.addEventListener('submit', async e => {
    e.preventDefault();
    messageInput.setAttribute('disabled', 'true');

    try {
      const message = messageInput.value;
      const response = await Axios.post(`/api/rooms/${window.room?.url}/chat`, { message }, { withCredentials: true });
      if (response.status === 201) {
        messageInput.value = '';
      } else {
        console.error(`Error: ${response.status} ${response.statusText}`);
      }
    } finally {
      messageInput.removeAttribute('disabled');
    }
  });
});
