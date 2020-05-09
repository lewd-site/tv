export interface VideoSource {
  readonly url: string;
  readonly title: string;
  readonly default: boolean;
  readonly videoId: number;
}

export interface Video {
  readonly id: number;
  readonly url: string;
  readonly type: string;
  readonly title: string;
  readonly startAt: string;
  readonly endAt: string;
  readonly offset: number;
  readonly userId: number;
  readonly roomId: number;
  readonly sources: VideoSource[];
}
