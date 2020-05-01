export interface Video {
  readonly id: number;
  readonly url: string;
  readonly type: string;
  readonly title: string;
  readonly startAt: string;
  readonly endAt: string;
  readonly userId: number;
  readonly roomId: number;
}
