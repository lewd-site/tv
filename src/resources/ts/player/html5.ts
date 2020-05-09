import { Player } from './types';
import { Video, VideoSource } from '../types';
import { EventBus } from '../utils';

const anilibriaPatterns = [
  /^(?:https?:\/\/)?(?:www\.)?anilibria\.tv\/public\/iframe.php\?.*id=(\d+)#(\d+)/,
];

export class Html5Player implements Player {
  public readonly eventBus: EventBus = new EventBus();

  private readonly videoWrapper: HTMLElement | null;
  private readonly videoElement: HTMLVideoElement | null;

  private video: Video | null = null;
  private source: VideoSource | null = null;

  public constructor(elementId: string) {
    this.videoWrapper = document.getElementById(elementId);
    if (!this.videoWrapper) {
      throw new Error('Video wrapper not found');
    }

    this.videoElement = document.createElement('video');
    this.videoElement.style.width = '100%';
    this.videoElement.style.height = '100%';
    this.videoWrapper.appendChild(this.videoElement);

    this.videoElement.addEventListener('pause', () => {
      this.eventBus.emit('stateChanged', 'paused');
    });

    this.videoElement.addEventListener('play', () => {
      this.eventBus.emit('stateChanged', 'playing');
    });

    this.videoElement.addEventListener('ended', () => {
      this.eventBus.emit('stateChanged', 'ended');
    });

    setTimeout(() => {
      this.eventBus.emit('ready');
      this.eventBus.emit('subtitleTracksChanged', []);
      this.eventBus.emit('qualityChanged', 'default');
    });
  }

  public dispose = () => {
    this.videoElement?.remove();
    this.eventBus.unsubscribeAll();
  };

  public static canPlayVideo = (url: string) => {
    return anilibriaPatterns.some(pattern => url.match(pattern));
  };

  public canPlayVideo = (url: string) => Html5Player.canPlayVideo(url);

  public hasVideo = () => this.getVideo() !== null;

  public getVideo = (): Video | null => {
    if (!this.videoElement) {
      return null;
    }

    return this.video;
  };

  public setVideo = (video: Video) => {
    if (!this.videoElement) {
      return;
    }

    this.video = video;
    this.source = video.sources.find(source => source.default) || null;

    const paused = this.videoElement.paused;
    const time = this.videoElement.currentTime;

    this.videoElement.src = this.source?.url || '';
    this.videoElement.load();

    this.videoElement.currentTime = time;

    if (paused) {
      this.videoElement.pause();
    } else {
      this.videoElement.play();
    }

    this.eventBus.emit('qualityChanged', this.source?.title || 'default');
  };

  public playVideo = () => {
    if (!this.videoElement) {
      return;
    }

    this.videoElement.play();
  };

  public pauseVideo = () => {
    if (!this.videoElement) {
      return;
    }

    this.videoElement.pause();
  };

  public getCurrentTime = () => this.videoElement?.currentTime || 0;

  public setCurrentTime = (time: number, allowSeekAhead?: boolean) => {
    if (!this.videoElement) {
      return;
    }

    this.videoElement.currentTime = time;
  };

  public getDuration = () => this.videoElement?.duration || 0;

  public getVideoLoadedFraction = () => {
    if (!this.videoElement || !this.videoElement.duration) {
      return 0;
    }

    const { buffered, currentTime, duration } = this.videoElement;
    for (let i = 0; i < buffered.length; ++i) {
      if (buffered.start(i) <= currentTime && currentTime < buffered.end(i)) {
        return buffered.end(i) / duration;
      }
    }

    return 0;
  };

  public setVolume = (volume: number) => {
    if (!this.videoElement) {
      return;
    }

    this.videoElement.volume = volume / 100;
  };

  /** @todo */
  public enableSubtitles = () => { };

  /** @todo */
  public disableSubtitles = () => { };

  /** @todo */
  public getSubtitleTracks = () => [];

  /** @todo */
  public getSubtitleTrack = () => null;

  /** @todo */
  public setSubtitleTrack = (languageCode: string) => { };

  /** @todo */
  public getAvailableQualityLevels = () => {
    return this.video?.sources.map(source => source.title) || ['default'];
  };

  /** @todo */
  public setQuality = (qualityLevel: string) => {
    if (!this.videoElement) {
      return;
    }

    if (qualityLevel === 'default') {
      this.source = this.video?.sources.find(source => source.default) || null;
    } else {
      this.source = this.video?.sources.find(source => source.title === qualityLevel) || null;
    }

    const paused = this.videoElement.paused;
    const time = this.videoElement.currentTime;

    this.videoElement.src = this.source?.url || '';
    this.videoElement.load();

    this.videoElement.currentTime = time;

    if (paused) {
      this.videoElement.pause();
    } else {
      this.videoElement.play();
    }

    this.eventBus.emit('qualityChanged', this.source?.title || 'default');
  };
}
