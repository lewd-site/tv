export interface VideoSource {
  readonly url: string;
  readonly title: string;
  readonly default: boolean;
  readonly videoId: number;
}

export type VideoType = 'youtube' | 'html5' | 'hls';

export interface Video {
  readonly id: number;
  readonly url: string;
  readonly type: VideoType;
  readonly title: string;
  readonly startAt: string;
  readonly endAt: string;
  readonly offset: number;
  readonly userId: number;
  readonly roomId: number;
  readonly sources: VideoSource[];
}
