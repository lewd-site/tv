import Axios from 'axios';
import apiClient from './axios';
import Chat from './components/Chat.vue';
import Playlist from './components/Playlist.vue';
import Observable from './observable';
import { Room, ChatMessage, Video } from './types';

declare global {
  interface Window {
    readonly room?: Room;
    readonly videos?: Video[];
    readonly messages?: ChatMessage[];
    model?: RoomModel;
  }
}

interface PresenceChannelUser {
  readonly id: number;
  readonly name: string;
}

interface OEmbedResponse {
  readonly html: string;
  readonly version: string;
  readonly url: string;
  readonly type: string;
  readonly title: string;
  readonly width: number;
  readonly height: number
  readonly thumbnail_url: string;
  readonly thumbnail_width: number;
  readonly thumbnail_height: number;
  readonly author_url: string;
  readonly author_name: string;
  readonly provider_url: string;
  readonly provider_name: string;
}

const CHAT_MESSAGES = 100;

class RoomModel {
  public readonly users = new Observable<PresenceChannelUser[]>([]);
  public readonly videos = new Observable<Video[]>([]);
  public readonly messages = new Observable<ChatMessage[]>([]);
  public readonly showAddVideoModal = new Observable(false);
  public readonly addVideoModalVideoTitle = new Observable('');

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
      .listen('VideoCreatedEvent', (video: Video) => {
        this.videos.set([...this.videos.get(), video]);
      })
      .listen('VideoDeletedEvent', ({ id }: { id: number }) => {
        this.videos.set(this.videos.get().filter(v => v.id !== id));
      })
      .listen('ChatMessageCreatedEvent', (message: ChatMessage) => {
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
  const model = new RoomModel();
  model.videos.set(window.videos || []);
  model.messages.set(window.messages || []);

  window.model = model;

  const playlistViewModel = new Playlist({ propsData: { videos: model.videos } });
  playlistViewModel.$mount('.room-playlist__list', true);

  const chatViewModel = new Chat({ propsData: { messages: model.messages } });
  chatViewModel.$mount('.chat__main', true);

  // Handle video modal open.

  const addVideoButton = document.querySelector<HTMLButtonElement>('.room-playlist__add');
  if (addVideoButton) {
    addVideoButton.addEventListener('click', e => {
      e.preventDefault();
      model.showAddVideoModal.set(true);
    });
  } else {
    console.warn('.room-playlist__add not found');
  }

  const addVideoModal = document.querySelector<HTMLElement>('.add-video-modal');
  if (addVideoModal) {
    model.showAddVideoModal.subscribe(visible => {
      if (visible) {
        addVideoModal.removeAttribute('hidden');
      } else {
        addVideoModal.setAttribute('hidden', 'true');
      }
    });
  } else {
    console.warn('.add-video-modal not found');
  }

  // Handle video modal close.

  const addVideoModalClose = document.querySelector<HTMLElement>('.add-video-modal__close');
  if (addVideoModalClose) {
    addVideoModalClose.addEventListener('click', e => {
      e.preventDefault();
      model.showAddVideoModal.set(false);
    });
  } else {
    console.warn('.add-video-modal__close not found');
  }

  // Handle video modal submit.

  const addVideoForm = document.querySelector<HTMLFormElement>('.add-video-modal__form');
  if (addVideoForm) {
    const urlInput = addVideoForm.querySelector<HTMLInputElement>('.add-video-modal__url');
    if (urlInput) {
      let submittingForm = false;
      addVideoForm.addEventListener('submit', async e => {
        e.preventDefault();

        if (submittingForm) {
          return;
        }

        submittingForm = true;

        urlInput.setAttribute('disabled', 'true');

        try {
          const url = `/api/rooms/${window.room?.url}/videos`;
          const videoUrl = urlInput.value;
          const response = await apiClient.post(url, { url: videoUrl }, { withCredentials: true });
          if (response.status === 201) {
            urlInput.value = '';
            model.showAddVideoModal.set(false);
          } else {
            console.error(`Error: ${response.status} ${response.statusText}`);
          }
        } finally {
          submittingForm = false;

          urlInput.removeAttribute('disabled');

          model.addVideoModalVideoTitle.set('');
        }
      });

      urlInput.addEventListener('change', async () => {
        model.addVideoModalVideoTitle.set('');

        if (!urlInput.value.length) {
          return;
        }

        if (!/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=[A-Za-z0-9_-]+.*$/.test(urlInput.value)) {
          return;
        }

        try {
          const url = `https://noembed.com/embed?url=${encodeURIComponent(urlInput.value)}`;
          const response = await Axios.get<OEmbedResponse>(url);
          if (response.status === 200) {
            model.addVideoModalVideoTitle.set(response.data.title);
          } else {
            console.error(`Error: ${response.status} ${response.statusText}`);
          }
        } catch { }
      });
    } else {
      console.warn('.add-video-modal__url not found');
    }
  } else {
    console.warn('.add-video-modal__form');
  }

  // Video modal title.

  const videoModalTitle = document.querySelector<HTMLElement>('.add-video-modal__title');
  if (videoModalTitle) {
    model.addVideoModalVideoTitle.subscribe(title => {
      videoModalTitle.textContent = title;
    });
  } else {
    console.warn('.add-video-modal__title not found');
  }

  // Show count of online users.

  const count = document.querySelector<HTMLElement>('.chat__count');
  if (count) {
    model.users.subscribe(users => {
      count.textContent = `${users.length} online`;
    });
  } else {
    console.warn('.chat__count not found');
  }

  // Handle chat form submit.

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
      const url = `/api/rooms/${window.room?.url}/messages`;
      const message = messageInput.value;
      const response = await apiClient.post(url, { message }, { withCredentials: true });
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
