import axios from './axios';
import Chat from './components/Chat.vue';
import PlayerOptions from './components/PlayerOptions.vue';
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
  }
}

interface TimeResponse {
  readonly time: string;
}

interface PresenceChannelUser {
  readonly id: number;
  readonly name: string;
}

interface YouTubeSubtitleTrack {
  readonly displayName: string;
  readonly id: null;
  readonly is_default: boolean;
  readonly is_servable: boolean;
  readonly is_translateable: boolean;
  readonly kind: string;
  readonly languageCode: string;
  readonly languageName: string;
  readonly name: null;
  readonly vss_id: string;
}

const CHAT_MESSAGES = 100;

const youTubeRegExp = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw]).*$/;

class RoomModel {
  public readonly users = new Observable<PresenceChannelUser[]>([]);
  public readonly videos = new Observable<Video[]>([]);
  public readonly messages = new Observable<ChatMessage[]>([]);

  public readonly currentVideo = new Observable<null | Video>(null);

  private serverTimeOffset: null | number = null;

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
        eventBus.emit('videoCreated', video);
      })
      .listen('VideoDeletedEvent', ({ id }: { id: number }) => {
        this.videos.set(this.videos.get().filter(v => v.id !== id));
        eventBus.emit('videoDeleted', id);
      })
      .listen('ChatMessageCreatedEvent', (message: ChatMessage) => {
        const messages = [...this.messages.get(), message];
        if (messages.length > CHAT_MESSAGES) {
          messages.shift();
        }

        this.messages.set(messages);
        eventBus.emit('chatMessageCreated', message);
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

  public now = async () => {
    if (this.serverTimeOffset !== null) {
      return new Date().getTime() + this.serverTimeOffset;
    } else {
      try {
        const timeBefore = new Date().getTime();
        const serverTime = new Date((await axios.get<TimeResponse>('/api/time')).data.time).getTime();
        const timeAfter = new Date().getTime();
        const localTime = (timeBefore + timeAfter) / 2;
        this.serverTimeOffset = serverTime - localTime;

        return new Date().getTime() + this.serverTimeOffset;
      } catch {
        return new Date().getTime();
      }
    }
  };

  public async getCurrentVideo(): Promise<null | Video> {
    const timeNow = await this.now();

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
  private readonly enableStart: HTMLInputElement | null;
  private readonly enableEnd: HTMLInputElement | null;
  private readonly startInput: HTMLInputElement | null;
  private readonly endInput: HTMLInputElement | null;
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
    const closeButton = this.modal.querySelector('.add-video__close');
    if (!closeButton) {
      throw new Error('Close button not found');
    }

    this.urlInput = this.modal.querySelector<HTMLInputElement>('.add-video__url > .input');
    if (!this.urlInput) {
      throw new Error('URL input not found');
    }

    this.enableStart = this.modal.querySelector<HTMLInputElement>('.add-video__enable-start > input');
    if (!this.enableStart) {
      throw new Error('Enable start input not found');
    }

    this.enableEnd = this.modal.querySelector<HTMLInputElement>('.add-video__enable-end > input');
    if (!this.enableEnd) {
      throw new Error('Enable end input not found');
    }

    this.startInput = this.modal.querySelector<HTMLInputElement>('.add-video__start');
    if (!this.startInput) {
      throw new Error('Start input not found');
    }

    this.endInput = this.modal.querySelector<HTMLInputElement>('.add-video__end');
    if (!this.endInput) {
      throw new Error('End input not found');
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

    addVideoButton?.addEventListener('click', this.onAddVideoButtonClick);
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

    if (this.submittingForm || !this.urlInput) {
      return;
    }

    this.submittingForm = true;
    this.urlInput.disabled = true;

    try {
      const url = `/api/rooms/${window.room?.url}/videos`;
      const data = {
        url: this.urlInput?.value,
        start: this.enableStart?.checked ? this.startInput?.value : undefined,
        end: this.enableEnd?.checked ? this.endInput?.value : undefined,
      };
      const response = await axios.post(url, data, { withCredentials: true });
      if (response.status === 201) {
        this.close();
      } else {
        console.error(`Error: ${response.status} ${response.statusText}`);
      }
    } finally {
      this.submittingForm = false;
      this.urlInput.disabled = false;
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

const TIME_EPS = 1;

class PlayerViewModel {
  private readonly playerWrapper: HTMLElement | null;
  private readonly playerOptions: HTMLElement | null;
  private readonly playButton: HTMLElement | null;
  private readonly controls: HTMLElement | null;
  private readonly controlsPlay: HTMLElement | null;
  private readonly controlsMute: HTMLElement | null;
  private readonly controlsVolume: HTMLElement | null;
  private readonly controlsVolumeFill: HTMLElement | null;
  private readonly controlsVolumeHandle: HTMLElement | null;
  private readonly controlsCurrentTime: HTMLElement | null;
  private readonly controlsDuration: HTMLElement | null;
  private readonly controlsSync: HTMLElement | null;
  private readonly controlsSub: HTMLElement | null;
  private readonly controlsOptions: HTMLElement | null;
  private readonly controlsCinema: HTMLElement | null;
  private readonly controlsFullscreen: HTMLElement | null;
  private readonly controlsSeek: HTMLElement | null;
  private readonly controlsSeekBuffered: HTMLElement | null;
  private readonly controlsSeekFill: HTMLElement | null;
  private readonly controlsSeekHandle: HTMLElement | null;

  private player: any = null;

  private isPlaying = new Observable(false);
  private isMute = new Observable(false);
  private volume = new Observable(50);
  private currentTime = new Observable(0);
  private duration = new Observable(0);
  private buffered = new Observable(0);
  private isSyncEnabled = new Observable(false);
  private isSubEnabled = new Observable(true);
  private showOptions = new Observable(false);
  private subtitleTracks = new Observable<YouTubeSubtitleTrack[]>([]);
  private subtitleTrack = new Observable<YouTubeSubtitleTrack | null>(null);
  private lastSubtitleTrack: YouTubeSubtitleTrack | null = null;
  private isFullscreen = new Observable(false);
  private lastVolume = 50;
  private isSeeking = false;
  private lastPlaying = false;

  public constructor(private readonly room: RoomModel) {
    this.playerWrapper = document.getElementById('player');
    if (!this.playerWrapper) {
      throw new Error('Player wrapper not found');
    }
    this.playerOptions = document.getElementById('player-options');
    if (!this.playerOptions) {
      throw new Error('Player options not found');
    }


    this.playButton = document.getElementById('play');
    if (!this.playButton) {
      throw new Error('Play button not found');
    }

    this.controls = document.getElementById('controls');
    if (!this.controls) {
      throw new Error('Controls not found');
    }

    this.controlsPlay = document.getElementById('controls-play');
    if (!this.controlsPlay) {
      throw new Error('Controls play button not found');
    }

    this.controlsMute = document.getElementById('controls-mute');
    if (!this.controlsMute) {
      throw new Error('Controls mute button not found');
    }

    this.controlsVolume = document.getElementById('controls-volume');
    if (!this.controlsVolume) {
      throw new Error('Controls volume not found');
    }

    this.controlsVolumeFill = document.getElementById('controls-volume-fill');
    if (!this.controlsVolumeFill) {
      throw new Error('Controls volume fill not found');
    }

    this.controlsVolumeHandle = document.getElementById('controls-volume-handle');
    if (!this.controlsVolumeHandle) {
      throw new Error('Controls volume handle not found');
    }

    this.controlsCurrentTime = document.getElementById('controls-current-time');
    if (!this.controlsCurrentTime) {
      throw new Error('Controls current time not found');
    }

    this.controlsDuration = document.getElementById('controls-duration');
    if (!this.controlsDuration) {
      throw new Error('Controls duration not found');
    }

    this.controlsSync = document.getElementById('controls-sync');
    if (!this.controlsSync) {
      throw new Error('Controls duration not found');
    }

    this.controlsSub = document.getElementById('controls-sub');
    if (!this.controlsSub) {
      throw new Error('Controls sub not found');
    }

    this.controlsOptions = document.getElementById('controls-options');
    if (!this.controlsOptions) {
      throw new Error('Controls options not found');
    }

    this.controlsCinema = document.getElementById('controls-cinema');
    if (!this.controlsCinema) {
      throw new Error('Controls cinema not found');
    }

    this.controlsFullscreen = document.getElementById('controls-fullscreen');
    if (!this.controlsFullscreen) {
      throw new Error('Controls fullscreen not found');
    }

    this.controlsSeek = document.getElementById('seek');
    if (!this.controlsSeek) {
      throw new Error('Seek not found');
    }

    this.controlsSeekBuffered = document.getElementById('seek-buffered');
    if (!this.controlsSeekBuffered) {
      throw new Error('Seek buffered not found');
    }

    this.controlsSeekFill = document.getElementById('seek-fill');
    if (!this.controlsSeekFill) {
      throw new Error('Seek fill not found');
    }

    this.controlsSeekHandle = document.getElementById('seek-handle');
    if (!this.controlsSeekHandle) {
      throw new Error('Seek handle not found');
    }

    this.isPlaying.subscribe(isPlaying => {
      if (!this.controlsPlay) {
        return;
      }

      if (isPlaying) {
        this.controlsPlay.classList.remove('room-video__controls-play');
        this.controlsPlay.classList.add('room-video__controls-pause');
      } else {
        this.controlsPlay.classList.add('room-video__controls-play');
        this.controlsPlay.classList.remove('room-video__controls-pause');
      }
    });

    this.volume.subscribe(volume => {
      if (!this.controlsVolumeFill || !this.controlsVolumeHandle) {
        return;
      }

      this.controlsVolumeFill.style.width = `${volume}%`;
      this.controlsVolumeHandle.style.left = `${volume}%`;
    });

    const seekTo = (currentTime: number, duration: number) => {
      if (!this.isSeeking && duration > 0) {
        if (this.controlsSeekFill) {
          this.controlsSeekFill.style.width = `${currentTime * 100 / duration}%`;
        }

        if (this.controlsSeekHandle) {
          this.controlsSeekHandle.style.left = `${currentTime * 100 / duration}%`;
        }
      }
    };

    this.currentTime.subscribe(currentTime => {
      if (this.controlsCurrentTime) {
        this.controlsCurrentTime.textContent = this.formatTime(currentTime);
      }

      const duration = this.duration.get();
      seekTo(currentTime, duration);
    });

    this.duration.subscribe(duration => {
      if (this.controlsDuration) {
        this.controlsDuration.textContent = this.formatTime(duration);
      }

      const currentTime = this.currentTime.get();
      seekTo(currentTime, duration);
    });

    this.buffered.subscribe(buffered => {
      if (this.controlsSeekBuffered) {
        this.controlsSeekBuffered.style.width = `${buffered}%`;
      }
    });

    this.isSyncEnabled.subscribe(isSyncEnabled => {
      if (!this.controlsSync) {
        return;
      }

      if (isSyncEnabled) {
        this.controlsSync.classList.remove('room-video__controls-sync-off');
        this.controlsSync.classList.add('room-video__controls-sync-on');
      } else {
        this.controlsSync.classList.add('room-video__controls-sync-off');
        this.controlsSync.classList.remove('room-video__controls-sync-on');
      }
    });

    this.isSubEnabled.subscribe(isSubEnabled => {
      if (this.player) {
        if (isSubEnabled) {
          this.player.loadModule('captions');
          this.player.loadModule('cc');
        } else {
          this.player.unloadModule('captions');
          this.player.unloadModule('cc');
        }
      }

      if (isSubEnabled) {
        if (this.lastSubtitleTrack !== null) {
          this.subtitleTrack.set(this.lastSubtitleTrack);
        } else {
          const options = this.player.getOptions() as string[];

          let track = null;
          if (options.indexOf('captions') !== -1) {
            track = this.player.getOption('captions', 'track');
          } else if (options.indexOf('cc') !== -1) {
            track = this.player.getOption('cc', 'track');
          }

          this.subtitleTrack.set(track);
        }
      } else {
        this.lastSubtitleTrack = this.subtitleTrack.get();
        this.subtitleTrack.set(null);
      }

      if (this.controlsSub) {
        if (isSubEnabled) {
          this.controlsSub.classList.remove('room-video__controls-sub-off');
          this.controlsSub.classList.add('room-video__controls-sub-on');
        } else {
          this.controlsSub.classList.add('room-video__controls-sub-off');
          this.controlsSub.classList.remove('room-video__controls-sub-on');
        }
      }
    });

    this.showOptions.subscribe(showOptions => {
      if (showOptions) {
        this.playerOptions?.removeAttribute('hidden');
      } else {
        this.playerOptions?.setAttribute('hidden', 'true');
      }
    });

    this.isFullscreen.subscribe(isFullscreen => {
      if (!this.controlsFullscreen) {
        return;
      }

      if (isFullscreen) {
        this.playerWrapper?.requestFullscreen();

        this.playerWrapper?.classList.add('fullscreen');

        this.controlsFullscreen.classList.remove('room-video__controls-fullscreen-off');
        this.controlsFullscreen.classList.add('room-video__controls-fullscreen-on');
      } else {
        document.exitFullscreen();

        this.playerWrapper?.classList.remove('fullscreen');

        this.controlsFullscreen.classList.add('room-video__controls-fullscreen-off');
        this.controlsFullscreen.classList.remove('room-video__controls-fullscreen-on');
      }
    });

    this.playButton.addEventListener('click', this.onPlayButtonClick);
    this.controlsPlay.addEventListener('click', this.onControlsPlayClick);
    this.controlsMute.addEventListener('click', this.onControlsMuteClick);
    this.controlsVolume.addEventListener('mousedown', this.onControlsVolumeMouseDown);
    this.controlsSync.addEventListener('click', this.onControlsSyncClick);
    this.controlsSub.addEventListener('click', this.onControlsSubClick);
    this.controlsOptions.addEventListener('click', this.onControlsOptionsClick);
    this.controlsCinema.addEventListener('click', this.onControlsCinemaClick);
    this.controlsFullscreen.addEventListener('click', this.onControlsFullscreenClick);
    this.controlsSeek.addEventListener('mousedown', this.onControlsSeekMouseDown);

    document.addEventListener('fullscreenchange', () => {
      this.isFullscreen.set(!!document.fullscreenElement);
    });

    eventBus.subscribe('videoCreated', this.syncVideo);
    eventBus.subscribe('videoDeleted', this.syncVideo);

    const volume = localStorage.getItem('player.volume');
    if (typeof volume === 'string' && volume.length) {
      this.volume.set(+volume);
      this.isMute.set(Math.round(+volume) === 0);

      if (+volume > 0) {
        this.lastVolume = +volume;
      }
    }

    setInterval(this.onUpdate, 500);

    const playerOptionsViewModel = new PlayerOptions({
      propsData: {
        subtitleTracks: this.subtitleTracks,
        subtitleTrack: this.subtitleTrack,
      },
    });

    playerOptionsViewModel.$mount('#player-options-mount');
    playerOptionsViewModel.$on('subtitleTrackChange', (track: YouTubeSubtitleTrack | null) => {
      if (track !== null) {
        this.isSubEnabled.set(true);

        if (this.player) {
          this.player.setOption('captions', 'track', { languageCode: track.languageCode });
          this.player.setOption('cc', 'track', { languageCode: track.languageCode });
        }
      } else {
        this.lastSubtitleTrack = this.subtitleTrack.get();
        this.isSubEnabled.set(false);
      }

      this.subtitleTrack.set(track);
    });
  }

  private syncVideo = async () => {
    if (!this.player || !this.player.getVideoUrl) {
      return;
    }

    const video = await this.room.getCurrentVideo();
    if (!video) {
      if (this.player.pauseVideo) {
        this.player.pauseVideo();
      }

      return;
    }

    const match = video.url.match(youTubeRegExp);
    if (!match) {
      return;
    }

    const loadVideo = (videoId: string) => {
      this.player.loadVideoById({ videoId });

      if (this.volume.get() > 0) {
        this.player.unMute();
        this.player.setVolume(this.volume.get());

        this.isMute.set(false);
      } else {
        this.player.mute();
        this.player.setVolume(1);

        this.isMute.set(true);
      }

      this.player.playVideo();
    };

    const videoId = match[1];
    const currentVideoUrl = this.player.getVideoUrl();
    if (currentVideoUrl === 'https://www.youtube.com/watch') {
      return loadVideo(videoId);
    }

    const currentVideoMatch = currentVideoUrl.match(youTubeRegExp);
    if (!currentVideoMatch) {
      return loadVideo(videoId);
    }

    const currentVideoId = currentVideoMatch[1];
    if (currentVideoId !== videoId) {
      return loadVideo(videoId);
    }
  };

  private syncVideoTime = async () => {
    if (!this.player || !this.player.getCurrentTime) {
      return;
    }

    const video = await this.room.getCurrentVideo();
    if (!video) {
      return;
    }

    const timeNow = await this.room.now();
    const playerTime = this.player.getCurrentTime();
    const time = (timeNow - new Date(video.startAt).getTime()) / 1000 + video.offset;
    if (Math.abs(playerTime - time) > 1) {
      this.player.seekTo(time, true);
    }
  };

  private formatTime = (time: number): string => {
    const s = Math.floor(time) % 60;
    const m = Math.floor(time / 60) % 60;
    const h = Math.floor(time / 3600);

    const sStr = s.toFixed(0).padStart(2, '0');
    if (h) {
      const mStr = m.toFixed(0).padStart(2, '0');
      const hStr = h.toFixed(0);

      return `${hStr}:${mStr}:${sStr}`;
    } else {
      const mStr = m.toFixed(0);

      return `${mStr}:${sStr}`;
    }
  };

  private onUpdate = async () => {
    if (this.isSyncEnabled.get()) {
      await this.syncVideo();
      await this.syncVideoTime();
    }

    if (!this.player || !this.player.getCurrentTime) {
      return;
    }

    this.currentTime.set(this.player.getCurrentTime() || 0);
    this.duration.set(this.player.getDuration() || 0);
    this.buffered.set(this.player.getVideoLoadedFraction() * 100);
  };

  private initPlayer = () => {
    if (this.player) {
      return;
    }

    const onReady = () => {
      this.controls?.removeAttribute('hidden');
      this.isSyncEnabled.set(true);
    };

    const onApiChange = () => {
      const isSubEnabled = this.isSubEnabled.get();
      const options = this.player.getOptions() as string[];

      if (options.indexOf('captions') !== -1) {
        const tracks = this.player.getOption('captions', 'tracklist');
        this.subtitleTracks.set(tracks);

        if (isSubEnabled) {
          const track = this.player.getOption('captions', 'track')
          this.subtitleTrack.set(track);
        } else {
          this.subtitleTrack.set(null);
        }
      }

      if (options.indexOf('cc') !== -1) {
        const tracks = this.player.getOption('cc', 'tracklist');
        this.subtitleTracks.set(tracks);

        if (isSubEnabled) {
          const track = this.player.getOption('cc', 'track')
          this.subtitleTrack.set(track);
        } else {
          this.subtitleTrack.set(null);
        }
      }
    };

    const onStateChange = ({ data }: { data: number }) => {
      switch (data) {
        case window.YT.PlayerState.PAUSED:
        case window.YT.PlayerState.ENDED:
          this.isPlaying.set(false);
          break;

        case window.YT.PlayerState.BUFFERING:
        case window.YT.PlayerState.PLAYING:
          this.isPlaying.set(true);
          break;
      }
    };

    this.player = new window.YT.Player('video', {
      host: `${window.location.protocol}//www.youtube.com`,
      origin: window.location.origin,
      videoId: null,
      playerVars: {
        autoplay: 1,
        autohide: 1,
        cc_load_policy: 1,
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
        onReady,
        onApiChange,
        onStateChange,
      },
    });
  };

  private onPlayButtonClick = (e: Event) => {
    e.preventDefault();
    this.playButton?.setAttribute('hidden', 'true');
    this.initPlayer();
  };

  private onControlsPlayClick = (e: Event) => {
    e.preventDefault();

    this.isSyncEnabled.set(false);

    if (!this.player || !this.player.playVideo) {
      return;
    }

    if (this.isPlaying.get()) {
      this.player.pauseVideo();
    } else if (this.player.getVideoUrl() !== 'https://www.youtube.com/watch') {
      this.player.playVideo();
    }
  };

  private onControlsMuteClick = (e: Event) => {
    e.preventDefault();

    if (this.volume.get() > 0) {
      this.lastVolume = this.volume.get();

      this.player.mute();
      this.player.setVolume(1);

      this.isMute.set(true);
      this.volume.set(0);
    } else {
      this.player.unMute();
      this.player.setVolume(this.lastVolume);

      this.isMute.set(false);
      this.volume.set(this.lastVolume);
    }
  };

  private onControlsVolumeMouseDown = (e: MouseEvent) => {
    const setVolume = (event: MouseEvent) => {
      if (!this.controlsVolume) {
        return;
      }

      const { left, width } = this.controlsVolume.getBoundingClientRect();
      const volume = Math.min(Math.max(0, Math.round((event.clientX - left) * 100 / width)), 100);
      if (volume > 0) {
        if (this.player && this.player.setVolume) {
          this.player.unMute();
          this.player.setVolume(volume);
        }

        this.isMute.set(false);
        this.volume.set(volume);
      } else {
        if (this.player && this.player.setVolume) {
          this.player.mute();
          this.player.setVolume(1);
        }

        this.isMute.set(true);
        this.volume.set(0);
      }

      localStorage.setItem('player.volume', volume.toString());
    };

    setVolume(e);

    const onMouseMove = (e: MouseEvent) => setVolume(e);

    const onMouseUp = (e: MouseEvent) => {
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
    };

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  };

  private onControlsSyncClick = (e: Event) => {
    e.preventDefault();

    if (this.isSyncEnabled.get()) {
      this.isSyncEnabled.set(false);
    } else {
      this.isSyncEnabled.set(true);

      if (this.player && this.player.getVideoUrl() !== 'https://www.youtube.com/watch' && !this.isPlaying.get()) {
        this.player.playVideo();
      }
    }
  };

  private onControlsSubClick = (e: Event) => {
    e.preventDefault();

    this.isSubEnabled.set(!this.isSubEnabled.get());
  };

  private onControlsOptionsClick = (e: Event) => {
    e.preventDefault();

    this.showOptions.set(!this.showOptions.get());
  };

  private onControlsCinemaClick = (e: Event) => {
    e.preventDefault();
  };

  private onControlsFullscreenClick = (e: Event) => {
    e.preventDefault();

    if (this.isFullscreen.get()) {
      this.isFullscreen.set(false);
    } else {
      this.isFullscreen.set(true);
    }
  };

  private onControlsSeekMouseDown = (e: MouseEvent) => {
    e.preventDefault();

    this.isSyncEnabled.set(false);
    this.isSeeking = true;
    this.lastPlaying = this.isPlaying.get();

    const seek = (event: MouseEvent, options: { checkPlayerTime: boolean, allowSeekAhead: boolean }) => {
      if (!this.controlsSeek || !this.player || !this.player.seekTo
        || this.player.getVideoUrl() === 'https://www.youtube.com/watch') {
        return;
      }

      const { checkPlayerTime, allowSeekAhead } = options;
      const duration = this.duration.get();
      const { left, width } = this.controlsSeek.getBoundingClientRect();
      const time = Math.round(Math.min(Math.max(0, Math.round((event.clientX - left) * duration / width)), duration));
      const playerTime = this.player.getCurrentTime();
      if (checkPlayerTime) {
        if (Math.abs(playerTime - time) > 1) {
          this.player.seekTo(time, allowSeekAhead);
        }
      } else {
        this.player.seekTo(time, allowSeekAhead);
      }

      if (this.controlsSeekFill) {
        this.controlsSeekFill.style.width = `${time * 100 / duration}%`;
      }

      if (this.controlsSeekHandle) {
        this.controlsSeekHandle.style.left = `${time * 100 / duration}%`;
      }
    };

    seek(e, { checkPlayerTime: true, allowSeekAhead: false });

    const onMouseMove = (e: MouseEvent) => seek(e, { checkPlayerTime: true, allowSeekAhead: false });

    const onMouseUp = (e: MouseEvent) => {
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);

      seek(e, { checkPlayerTime: false, allowSeekAhead: true });

      this.isSeeking = false;

      if (this.lastPlaying && this.player && this.player.playVideo
        && this.player.getVideoUrl() !== 'https://www.youtube.com/watch') {
        this.player.playVideo();
      }
    };

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  };
}

document.addEventListener('DOMContentLoaded', () => {
  const room = new RoomModel();
  room.videos.set(window.videos || []);
  room.messages.set(window.messages || []);
  window.model = room;

  window.onYouTubeIframeAPIReady = () => {
    const playButton = document.getElementById('play');
    if (!playButton) {
      throw new Error('Play button not found');
    }

    playButton.removeAttribute('hidden');
  };

  const playlistViewModel = new Playlist({ propsData: { videos: room.videos } });
  playlistViewModel.$mount('.room-playlist__list', true);

  const chatViewModel = new Chat({ propsData: { messages: room.messages } });
  chatViewModel.$mount('.chat__main', true);

  const addVideoModalViewModel = new AddVideoModalViewModel();
  const playerViewModel = new PlayerViewModel(room);

  // Update video title.

  const videoTitle = document.querySelector<HTMLElement>('.room-video__title');
  if (videoTitle) {
    room.currentVideo.subscribe(video => {
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
    room.users.subscribe(users => {
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

  room.getCurrentVideo(); // Trigger update.
});
