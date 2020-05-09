import { EventBus } from '../utils';
import { Video } from '../types';

export type PlayerState = 'ended' | 'playing' | 'paused' | 'buffering';

export interface SubtitleTrack {
  readonly displayName: string;
  readonly languageCode: string;
}

export interface Player {
  readonly eventBus: EventBus;

  readonly dispose: () => void;

  readonly canPlayVideo: (url: string) => boolean;
  readonly hasVideo: () => boolean;
  readonly getVideo: () => Video | null;
  readonly setVideo: (video: Video) => void;

  readonly playVideo: () => void;
  readonly pauseVideo: () => void;

  readonly getCurrentTime: () => number;
  readonly setCurrentTime: (time: number, allowSeekAhead?: boolean) => void;

  readonly getDuration: () => number;
  readonly getVideoLoadedFraction: () => number;

  readonly setVolume: (volume: number) => void;

  readonly enableSubtitles: () => void;
  readonly disableSubtitles: () => void;
  readonly getSubtitleTracks: () => SubtitleTrack[];
  readonly getSubtitleTrack: () => SubtitleTrack | null;
  readonly setSubtitleTrack: (languageCode: string) => void;

  readonly getAvailableQualityLevels: () => string[];
  readonly setQuality: (qualityLevel: string) => void;
}
