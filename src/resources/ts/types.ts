export interface ChatMessage {
  readonly id: number;
  readonly message: string;

  readonly userId: number;
  readonly userName: string;
  readonly userUrl: string;
  readonly userAvatar: string;

  readonly roomId: number;
  readonly roomName: string;
}

export interface Room {
  readonly id: number;
  readonly url: string;
  readonly name: string;

  readonly userId: number;
}
