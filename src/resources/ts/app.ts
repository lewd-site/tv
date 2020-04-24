import Axios from 'axios';
import Echo from 'laravel-echo';
import * as Pusher from 'pusher-js';
import config from './config';
import { User } from './types';

declare global {
  interface Window {
    readonly user?: User;
    Echo?: Echo;
    Pusher?: typeof Pusher;
  }
}

const token = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
if (token) {
  Axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;
window.Echo = new Echo({
  broadcaster: 'pusher',
  key: config.pusherKey,
  cluster: config.pusherCluster,
});

document.addEventListener('dragstart', event => {
  const { target } = event;
  if (target instanceof HTMLElement
    && target.hasAttribute('data-draggable')
    && target.getAttribute('data-draggable') === 'false') {
    event.preventDefault();
  }
});
