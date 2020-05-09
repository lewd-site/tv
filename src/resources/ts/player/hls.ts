import * as Hls from 'hls.js';
import { Player } from './types';
import { Video } from '../types';
import { EventBus } from '../utils';

export class HlsPlayer implements Player {
  public readonly eventBus: EventBus = new EventBus();

  private readonly videoWrapper: HTMLElement | null;
  private readonly videoElement: HTMLVideoElement | null;

  private hls: Hls | null = null;

  private video: Video | null = null;

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

    this.hls = new Hls();
    this.hls.attachMedia(this.videoElement);

    setTimeout(() => {
      this.eventBus.emit('ready');
      this.eventBus.emit('subtitleTracksChanged', []);
      this.eventBus.emit('qualityChanged', 'default');
    });
  }

  public dispose = () => {
    this.hls?.detachMedia();
    this.hls?.destroy();

    this.videoElement?.remove();
    this.eventBus.unsubscribeAll();
  };

  public static canPlayVideo = (video: Video) => video.type === 'hls';

  public canPlayVideo = (video: Video) => HlsPlayer.canPlayVideo(video);

  public hasVideo = () => this.getVideo() !== null;

  public getVideo = (): Video | null => {
    if (!this.videoElement) {
      return null;
    }

    return this.video;
  };

  public setVideo = (video: Video) => {
    if (!this.videoElement || !this.hls) {
      return;
    }

    this.video = video;

    const paused = this.videoElement.paused;
    const time = this.videoElement.currentTime;

    this.hls.loadSource(this.video.url || '');
    this.hls.once('hlsManifestLoaded', () => {
      this.eventBus.emit('qualityChanged', 'auto');
    });

    this.videoElement.currentTime = time;

    if (paused) {
      this.videoElement.pause();
    } else {
      this.videoElement.play();
    }
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

  public getAvailableQualityLevels = () => {
    if (!this.hls || !this.hls.levels) {
      return ['auto'];
    }

    return ['auto', ...this.hls.levels.map(level => `${level.height}p`)].reverse();
  };

  public setQuality = (qualityLevel: string) => {
    if (!this.hls || !this.hls.levels) {
      return;
    }

    if (qualityLevel === 'auto') {
      this.hls.currentLevel = -1;
      this.eventBus.emit('qualityChanged', 'auto');
    } else {
      const index = this.hls.levels.findIndex(level => `${level.height}p` === qualityLevel);
      this.hls.currentLevel = index;

      if (index === -1) {
        this.eventBus.emit('qualityChanged', 'auto');
      } else {
        const level = this.hls.levels[index];
        this.eventBus.emit('qualityChanged', `${level.height}p`);
      }
    }
  };
}
