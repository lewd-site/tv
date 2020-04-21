import Axios from 'axios';
import Echo from 'laravel-echo';
import * as Pusher from 'pusher-js';
import config from './config';

declare global {
  interface Window {
    Echo?: Echo;
    Pusher?: typeof Pusher;
  }
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
