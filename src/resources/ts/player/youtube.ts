import { Player } from './types';
import { EventBus } from '../utils';

const youtubePatterns = [
  /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/,
  /^(?:https?:\/\/)?(?:www\.)?youtu\.be\/([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/,
];

export interface YouTubeSubtitleTrack {
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

export class YouTubePlayer implements Player {
  private player: any = null;

  public readonly eventBus: EventBus = new EventBus();

  public constructor() {
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
        onReady: () => this.eventBus.emit('ready'),
        onApiChange: () => this.eventBus.emit('subtitleTracksChanged'),
        onPlaybackQualityChange: ({ data }: { data: string }) => this.eventBus.emit('qualityChanged', data),
        onStateChange: ({ data }: { data: number }) => {
          switch (data) {
            case window.YT.PlayerState.ENDED:
              this.eventBus.emit('stateChanged', 'ended');
              break;

            case window.YT.PlayerState.PLAYING:
              this.eventBus.emit('stateChanged', 'playing');
              break;

            case window.YT.PlayerState.PAUSED:
              this.eventBus.emit('stateChanged', 'paused');
              break;

            case window.YT.PlayerState.BUFFERING:
              this.eventBus.emit('stateChanged', 'buffering');
              break;
          }
        },
      },
    });
  }

  public dispose = () => {
    if (!this.player) {
      return;
    }

    this.player.stopVideo();
    this.player.destroy();
    this.player = null;

    this.eventBus.unsubscribeAll();
  };

  public readonly canPlayVideo = (url: string) => YouTubePlayer.canPlayVideo(url);

  public hasVideo = () => this.getVideoUrl() !== null;

  public getVideoUrl = () => {
    if (!this.player || !this.player.getVideoUrl) {
      return null;
    }

    const videoUrl = this.player.getVideoUrl();
    if (videoUrl === 'https://www.youtube.com/watch') {
      return null;
    }

    return videoUrl;
  };

  public static canPlayVideo = (url: string) => {
    return youtubePatterns.some(pattern => url.match(pattern));
  };

  public static getVideoId = (url: string) => {
    for (let pattern of youtubePatterns) {
      const match = url.match(pattern);
      if (match) {
        return match[1];
      }
    }

    return null;
  };

  public setVideoUrl = (url: string, time: number | null = null) => {
    if (!this.player || !this.player.loadVideoById) {
      return;
    }

    const videoId = YouTubePlayer.getVideoId(url);
    if (!videoId) {
      return;
    }

    const currentUrl = this.getVideoUrl();
    if (currentUrl) {
      const currentVideoId = YouTubePlayer.getVideoId(currentUrl)
      if (currentVideoId === videoId) {
        return;
      }
    }

    if (time) {
      this.player.loadVideoById(videoId, time);
    } else {
      this.player.loadVideoById(videoId);
    }
  };

  public playVideo = () => {
    if (!this.player || !this.player.playVideo) {
      return;
    }

    this.player.playVideo();
  };

  public pauseVideo = () => {
    if (!this.player || !this.player.pauseVideo) {
      return;
    }

    this.player.pauseVideo();
  };

  public getCurrentTime = () => {
    if (!this.player || !this.player.getCurrentTime) {
      return 0;
    }

    return this.player.getCurrentTime() || 0;
  };

  public setCurrentTime = (time: number, allowSeekAhead: boolean = true) => {
    if (!this.player || !this.player.seekTo) {
      return;
    }

    this.player.seekTo(time, allowSeekAhead);
  };


  public getDuration = () => {
    if (!this.player || !this.player.getDuration) {
      return;
    }

    return this.player.getDuration();
  };

  public getVideoLoadedFraction = () => {
    if (!this.player || !this.player.getVideoLoadedFraction) {
      return;
    }

    return this.player.getVideoLoadedFraction();
  };

  public setVolume = (volume: number) => {
    if (!this.player || !this.player.setVolume) {
      return;
    }

    if (volume > 0) {
      this.player.unMute();
      this.player.setVolume(volume);
    } else {
      this.player.mute();
      this.player.setVolume(1);
    }
  };

  public enableSubtitles = () => {
    if (!this.player || !this.player.loadModule) {
      return;
    }

    this.player.loadModule('captions');
    this.player.loadModule('cc');
  };

  public disableSubtitles = () => {
    if (!this.player || !this.player.unloadModule) {
      return;
    }

    this.player.unloadModule('captions');
    this.player.unloadModule('cc');
  };

  public getSubtitleTracks = () => {
    if (!this.player || !this.player.getOptions) {
      return [];
    }

    const options = this.player.getOptions() as string[];
    if (options.indexOf('captions') !== -1) {
      return this.player.getOption('captions', 'tracklist') as YouTubeSubtitleTrack[];
    } else if (options.indexOf('cc') !== -1) {
      return this.player.getOption('cc', 'tracklist') as YouTubeSubtitleTrack[];
    }

    return [];
  };

  public getSubtitleTrack = () => {
    if (!this.player || !this.player.getOptions) {
      return null;
    }

    const options = this.player.getOptions() as string[];
    if (options.indexOf('captions') !== -1) {
      return this.player.getOption('captions', 'track') as YouTubeSubtitleTrack;
    } else if (options.indexOf('cc') !== -1) {
      return this.player.getOption('cc', 'track') as YouTubeSubtitleTrack;
    }

    return null;
  };

  public setSubtitleTrack = (languageCode: string) => {
    if (!this.player || !this.player.getOptions) {
      return null;
    }

    const options = this.player.getOptions() as string[];
    if (options.indexOf('captions') !== -1) {
      this.player.setOption('captions', 'track', { languageCode });
    } else if (options.indexOf('cc') !== -1) {
      this.player.setOption('cc', 'track', { languageCode });
    }
  };

  public getAvailableQualityLevels = () => {
    if (!this.player || !this.player.getAvailableQualityLevels) {
      return ['auto'];
    }

    return this.player.getAvailableQualityLevels() as string[];
  };

  public setQuality = (qualityLevel: string) => {
    if (!this.player || !this.player.stopVideo) {
      return;
    }

    const url = this.getVideoUrl();
    if (!url) {
      return;
    }

    const videoId = YouTubePlayer.getVideoId(url);
    if (!videoId) {
      return;
    }

    const time = this.getCurrentTime();

    this.player.stopVideo();
    this.player.clearVideo();

    if (['auto', 'default'].indexOf(qualityLevel) !== -1) {
      this.player.loadVideoById(videoId, time);
      this.player.setPlaybackQuality('default');
    } else {
      this.player.loadVideoById(videoId, time, qualityLevel);
      this.player.setPlaybackQuality(qualityLevel);
    }
  };
}
