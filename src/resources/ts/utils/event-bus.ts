export type EventBusListener = (...args: any[]) => any;
export type EventBusListenerCollection = { [event: string]: EventBusListener[] };
export type EventBusUnsubscriber = () => void;

export class EventBus {
  private readonly listeners: EventBusListenerCollection = {};

  public subscribe(event: string, listener: EventBusListener): EventBusUnsubscriber {
    if (this.listeners[event]) {
      this.listeners[event].push(listener);
    } else {
      this.listeners[event] = [listener];
    }

    return () => this.unsubscribe(event, listener);
  }

  public unsubscribe(event: string, listener: EventBusListener) {
    if (!this.listeners[event]) {
      return;
    }

    this.listeners[event] = this.listeners[event].filter(l => l !== listener);
  }

  public unsubscribeAll(event?: string): void {
    if (event) {
      delete this.listeners[event];
    } else {
      const events = Object.keys(this.listeners);
      events.forEach(event => delete this.listeners[event]);
    }
  }

  public emit(event: string, ...args: any[]) {
    if (!this.listeners[event]) {
      return;
    }

    this.listeners[event].forEach(l => l.apply(this, args));
  }
}

export default EventBus;

export const eventBus = new EventBus();
