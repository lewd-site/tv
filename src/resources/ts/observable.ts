export type Listener<T> = (value: T, prevValue: T) => void;
export type Unsubscriber = () => void;

export class Observable<T> {
  private listeners: Listener<T>[] = [];

  public constructor(private value: T) { }

  public subscribe(listener: Listener<T>): Unsubscriber {
    this.listeners.push(listener);

    return () => this.unsubscribe(listener);
  }

  public get(): T {
    return this.value;
  }

  public set(value: T): void {
    if (this.value !== value) {
      const prevValue = this.value;
      this.value = value;
      this.listeners.forEach(listener => listener(value, prevValue));
    }
  }

  public unsubscribe(listener: Listener<T>): void {
    this.listeners = this.listeners.filter(l => l !== listener);
  }

  public unsubscribeAll(): void {
    this.listeners = [];
  }
}

export default Observable;
