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

    readonly YT?: any;
    onYouTubeIframeAPIReady?: () => void;
    player?: any;
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

const youTubeRegExp = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([A-Za-z0-9_-]+).*$/;

const syncVideo = () => {
  if (!window.model || !window.player) {
    return;
  }

  const video = window.model.getCurrentVideo();
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

const syncVideoTime = () => {
  if (!window.model || !window.player) {
    return;
  }

  const video = window.model.getCurrentVideo();
  if (!video) {
    return;
  }

  const playerTime = window.player.getCurrentTime();
  const time = (new Date().getTime() - new Date(video.startAt).getTime()) / 1000;
  if (Math.abs(playerTime - time) > 1.0) {
    window.player.seekTo(time, true);
  }
};

class RoomModel {
  public readonly users = new Observable<PresenceChannelUser[]>([]);
  public readonly videos = new Observable<Video[]>([]);
  public readonly messages = new Observable<ChatMessage[]>([]);

  public readonly currentVideo = new Observable<null | Video>(null);

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

  public getCurrentVideo(): null | Video {
    const videos = this.videos.get();
    const video = videos.find(video => {
      const startAt = new Date(video.startAt).getTime();
      const endAt = new Date(video.endAt).getTime();
      const now = new Date().getTime();

      return startAt <= now && now < endAt;
    }) || null;

    this.currentVideo.set(video);

    return video;
  }
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

        if (!youTubeRegExp.test(urlInput.value)) {
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

  model.getCurrentVideo(); // Trigger update.
});
