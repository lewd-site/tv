import Axios from 'axios';

Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

document.addEventListener('dragstart', event => {
  const { target } = event;
  if (target instanceof HTMLElement
    && target.hasAttribute('data-draggable')
    && target.getAttribute('data-draggable') === 'false') {
    event.preventDefault();
  }
});
