import { Player } from './types';
import { EventBus } from '../utils';

export class Html5Player implements Player {
  public readonly eventBus: EventBus = new EventBus();

  private readonly videoWrapper: HTMLElement | null;
  private readonly video: HTMLVideoElement | null;

  public constructor(elementId: string) {
    this.videoWrapper = document.getElementById(elementId);
    if (!this.videoWrapper) {
      throw new Error('Video wrapper not found');
    }

    this.video = document.createElement('video');
    this.video.style.width = '100%';
    this.video.style.height = '100%';
    this.videoWrapper.appendChild(this.video);

    this.video.addEventListener('pause', () => {
      this.eventBus.emit('stateChanged', 'paused');
    });

    this.video.addEventListener('play', () => {
      this.eventBus.emit('stateChanged', 'playing');
    });

    this.video.addEventListener('ended', () => {
      this.eventBus.emit('stateChanged', 'ended');
    });

    setTimeout(() => {
      this.eventBus.emit('ready');
      this.eventBus.emit('subtitleTracksChanged', []);
      this.eventBus.emit('qualityChanged', 'default');
    });
  }

  public dispose = () => {
    this.video?.remove();
    this.eventBus.unsubscribeAll();
  };

  public static canPlayVideo = (url: string) => {
    return url.endsWith('.webm') || url.endsWith('.mp4');
  };

  public canPlayVideo = (url: string) => Html5Player.canPlayVideo(url);

  public hasVideo = () => this.getVideoUrl() !== null;

  public getVideoUrl = () => {
    if (!this.video) {
      return null;
    }

    return this.video.src;
  };

  public setVideoUrl = (url: string, time?: number) => {
    if (!this.video) {
      return;
    }

    this.video.src = url;

    if (typeof time !== 'undefined') {
      this.video.currentTime = time;
    }
  };

  public playVideo = () => {
    if (!this.video) {
      return;
    }

    this.video.play();
  };

  public pauseVideo = () => {
    if (!this.video) {
      return;
    }

    this.video.pause();
  };

  public getCurrentTime = () => this.video?.currentTime || 0;

  public setCurrentTime = (time: number, allowSeekAhead?: boolean) => {
    if (!this.video) {
      return;
    }

    this.video.currentTime = time;
  };

  public getDuration = () => this.video?.duration || 0;

  /** @todo */
  public getVideoLoadedFraction = () => 0;

  public setVolume = (volume: number) => {
    if (!this.video) {
      return;
    }

    this.video.volume = volume / 100;
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
  public getAvailableQualityLevels = () => ['default'];

  /** @todo */
  public setQuality = (qualityLevel: string) => { };
}
