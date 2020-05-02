import axios from './axios';
import Chat from './components/Chat.vue';
import Playlist from './components/Playlist.vue';
import { Room, ChatMessage, Video } from './types';
import { eventBus, Observable } from './utils';

declare global {
  interface Window {
    readonly room?: Room;
    readonly videos?: Video[];
    readonly messages?: ChatMessage[];

    model?: RoomModel;

    readonly YT?: any;
    onYouTubeIframeAPIReady?: () => void;
    player?: any;
  }
}

interface TimeResponse {
  readonly time: string;
}

interface PresenceChannelUser {
  readonly id: number;
  readonly name: string;
}

const CHAT_MESSAGES = 100;

const youTubeRegExp = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw]).*$/;

let serverTimeOffset: null | number = null;
const now = async () => {
  if (serverTimeOffset !== null) {
    return new Date().getTime() + serverTimeOffset;
  } else {
    try {
      const timeBefore = new Date().getTime();
      const serverTime = new Date((await axios.get<TimeResponse>('/api/time')).data.time).getTime();
      const timeAfter = new Date().getTime();
      const localTime = (timeBefore + timeAfter) / 2;
      serverTimeOffset = serverTime - localTime;

      return new Date().getTime() + serverTimeOffset;
    } catch {
      return new Date().getTime();
    }
  }
};

const syncVideo = async () => {
  if (!window.model || !window.player) {
    return;
  }

  const video = await window.model.getCurrentVideo();
  if (!video) {
    return;
  }

  const match = video.url.match(youTubeRegExp);
  if (!match) {
    return;
  }

  const videoId = match[1];
  const currentVideoUrl = window.player.getVideoUrl();
  const currentVideoMatch = currentVideoUrl.match(youTubeRegExp);
  if (!currentVideoMatch) {
    window.player.loadVideoById({ videoId });
    window.player.playVideo();
    return;
  }

  const currentVideoId = currentVideoMatch[1];
  if (currentVideoId !== videoId) {
    window.player.loadVideoById({ videoId });
    window.player.playVideo();
  }
};

const syncVideoTime = async () => {
  if (!window.model || !window.player) {
    return;
  }

  const video = await window.model.getCurrentVideo();
  if (!video) {
    return;
  }

  const timeNow = await now();
  const playerTime = window.player.getCurrentTime();
  const time = (timeNow - new Date(video.startAt).getTime()) / 1000;
  if (Math.abs(playerTime - time) > 1.0) {
    window.player.seekTo(time, true);
  }
};

class RoomModel {
  public readonly users = new Observable<PresenceChannelUser[]>([]);
  public readonly videos = new Observable<Video[]>([]);
  public readonly messages = new Observable<ChatMessage[]>([]);

  public readonly currentVideo = new Observable<null | Video>(null);

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
        syncVideo();
      })
      .listen('VideoDeletedEvent', ({ id }: { id: number }) => {
        this.videos.set(this.videos.get().filter(v => v.id !== id));
        syncVideo();
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

  public async getCurrentVideo(): Promise<null | Video> {
    const timeNow = await now();

    const videos = this.videos.get();
    const video = videos.find(video => {
      const startAt = new Date(video.startAt).getTime();
      const endAt = new Date(video.endAt).getTime();

      return startAt <= timeNow && timeNow < endAt;
    }) || null;

    this.currentVideo.set(video);

    return video;
  }
}

class AddVideoModalViewModel {
  private readonly modal: HTMLElement | null;
  private readonly urlInput: HTMLInputElement | null;
  private readonly isVisible = new Observable(false);

  private submittingForm = false;

  public constructor() {
    this.modal = document.querySelector('.add-video-modal');
    if (!this.modal) {
      throw new Error('Add video modal not found');
    }

    const form = this.modal.querySelector('.add-video__inner');
    if (!form) {
      throw new Error('Add video form not found');
    }

    const addVideoButton = document.querySelector('.room-playlist__add');
    if (!addVideoButton) {
      throw new Error('Add video button not found');
    }

    const closeButton = this.modal.querySelector('.add-video__close');
    if (!closeButton) {
      throw new Error('Close button not found');
    }

    this.urlInput = this.modal.querySelector<HTMLInputElement>('.add-video__url > .input');
    if (!this.urlInput) {
      throw new Error('URL input not found');
    }

    const submitButton = this.modal.querySelector('.add-video__submit');
    if (!submitButton) {
      throw new Error('Submit button not found');
    }

    const cancelButton = this.modal.querySelector('.add-video__cancel');
    if (!cancelButton) {
      throw new Error('Cancel button not found');
    }

    this.isVisible.subscribe(this.onVisibilityChange);

    addVideoButton.addEventListener('click', this.onAddVideoButtonClick);
    this.modal.addEventListener('click', this.onModalClick);
    form.addEventListener('submit', this.onSubmit);
    closeButton.addEventListener('click', this.onCloseButtonClick);
    cancelButton.addEventListener('click', this.onCancelButtonClick);
  }

  public open = () => {
    this.isVisible.set(true);
  };

  public close = () => {
    this.isVisible.set(false);
  };

  private onVisibilityChange = (visible: boolean) => {
    if (visible) {
      this.modal?.removeAttribute('hidden');
      eventBus.emit('addVideoModalOpened');
    } else {
      this.modal?.setAttribute('hidden', 'true');
      eventBus.emit('addVideoModalClosed');
    }
  };

  private onAddVideoButtonClick = (e: Event) => {
    e.preventDefault();
    this.open();
  };

  private onModalClick = (e: Event) => {
    if (e.target !== this.modal) {
      return;
    }

    this.close();
  };

  private onSubmit = async (e: Event) => {
    e.preventDefault();

    if (this.submittingForm) {
      return;
    }

    this.submittingForm = true;
    this.urlInput?.setAttribute('disabled', 'true');

    try {
      const url = `/api/rooms/${window.room?.url}/videos`;
      const videoUrl = this.urlInput?.value;
      const response = await axios.post(url, { url: videoUrl }, { withCredentials: true });
      if (response.status === 201) {
        this.close();
      } else {
        console.error(`Error: ${response.status} ${response.statusText}`);
      }
    } finally {
      this.submittingForm = false;
      this.urlInput?.removeAttribute('disabled');
    }
  };

  private onCloseButtonClick = (e: Event) => {
    e.preventDefault();
    this.close();
  };

  private onCancelButtonClick = (e: Event) => {
    e.preventDefault();
    this.close();
  };
}

document.addEventListener('DOMContentLoaded', () => {
  const model = new RoomModel();
  model.videos.set(window.videos || []);
  model.messages.set(window.messages || []);
  window.model = model;

  window.onYouTubeIframeAPIReady = () => {
    const playButton = document.querySelector<HTMLButtonElement>('.room-video__play');
    if (playButton) {
      playButton.removeAttribute('hidden');
    } else {
      console.warn('.room-video__play not found');
    }
  };

  const playlistViewModel = new Playlist({ propsData: { videos: model.videos } });
  playlistViewModel.$mount('.room-playlist__list', true);

  const chatViewModel = new Chat({ propsData: { messages: model.messages } });
  chatViewModel.$mount('.chat__main', true);

  const addVideoModalViewModel = new AddVideoModalViewModel();

  // Handle play button.

  const playButton = document.querySelector<HTMLButtonElement>('.room-video__play');
  if (playButton) {
    playButton.addEventListener('click', e => {
      e.preventDefault();

      playButton.setAttribute('hidden', 'true');

      const onStateChange = ({ data }: { data: number }) => {
        switch (data) {
          case window.YT.PlayerState.ENDED:
            return setTimeout(syncVideo, 1000);

          case window.YT.PlayerState.PLAYING:
            return syncVideoTime();
        }
      };

      window.player = new window.YT.Player('player', {
        host: `${window.location.protocol}//www.youtube.com`,
        origin: window.location.origin,
        videoId: null,
        playerVars: {
          autoplay: 1,
          autohide: 1,
          controls: 0,
          disablekb: 1,
          fs: 0,
          iv_load_policy: 3,
          modestbranding: 1,
          playsinline: 1,
          rel: 0,
          showinfo: 0,
        },
        events: {
          onReady: syncVideo,
          onStateChange: onStateChange,
        },
      });
    });
  } else {
    console.warn('.room-video__play not found');
  }

  // Update video title.

  const videoTitle = document.querySelector<HTMLElement>('.room-video__title');
  if (videoTitle) {
    model.currentVideo.subscribe(video => {
      if (video?.endAt) {
        videoTitle.textContent = video.title;
      } else {
        videoTitle.textContent = '';
      }
    });
  } else {
    console.warn('.room-video__title');
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
      const response = await axios.post(url, { message }, { withCredentials: true });
      if (response.status === 201) {
        messageInput.value = '';
      } else {
        console.error(`Error: ${response.status} ${response.statusText}`);
      }
    } finally {
      messageInput.removeAttribute('disabled');
    }
  });

  model.getCurrentVideo(); // Trigger update.
});
