export type ObserverListener<T> = (value: T, prevValue: T) => void;
export type ObserverUnsubscriber = () => void;

export class Observable<T> {
  private listeners: ObserverListener<T>[] = [];

  public constructor(private value: T) { }

  public subscribe(listener: ObserverListener<T>): ObserverUnsubscriber {
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

  public unsubscribe(listener: ObserverListener<T>): void {
    this.listeners = this.listeners.filter(l => l !== listener);
  }

  public unsubscribeAll(): void {
    this.listeners = [];
  }
}

export default Observable;
