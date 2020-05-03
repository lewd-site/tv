export interface ChatMessage {
  readonly id: number;
  readonly message: string;

  readonly userId: number;
  readonly userName: string;
  readonly userUrl: string;
  readonly userAvatar: string;

  readonly roomId: number;
  readonly roomName: string;

  readonly createdAt: string;
}
