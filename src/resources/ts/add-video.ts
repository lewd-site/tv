import Axios, { CancelTokenSource } from 'axios';
import axios from './axios';
import { eventBus, field, Field, Observable } from './utils';

interface OEmbedResponse {
  readonly html: string;
  readonly version: string;
  readonly url: string;
  readonly type: string;
  readonly title: string;
  readonly width: number;
  readonly height: number
  readonly thumbnail_url: string;
  readonly thumbnail_width: number;
  readonly thumbnail_height: number;
  readonly author_url: string;
  readonly author_name: string;
  readonly provider_url: string;
  readonly provider_name: string;
}

interface Placeholder {
  readonly type: 'placeholder';
}

interface Loading {
  readonly type: 'loading';
}

interface Error {
  readonly type: 'error';
}

interface Info {
  readonly type: 'info';
  readonly thumbnailUrl: string;
  readonly title: string;
  readonly author: string;
  readonly authorUrl: string;
}

type State = Placeholder | Loading | Error | Info;

class Api {
  private cache: { [key: string]: OEmbedResponse } = {};
  private cancelTokenSource: CancelTokenSource | null = null;

  public getOEmbedInfo = async (url: string): Promise<OEmbedResponse> => {
    if (this.cancelTokenSource) {
      this.cancelTokenSource.cancel();
      this.cancelTokenSource = null;
    }

    if (typeof this.cache[url] !== 'undefined') {
      return this.cache[url];
    }

    this.cancelTokenSource = Axios.CancelToken.source();

    const serviceUrl = `/api/oembed?url=${encodeURIComponent(url)}`;
    const response = await axios.get<OEmbedResponse>(serviceUrl, {
      cancelToken: this.cancelTokenSource.token,
    });

    if (response.status !== 200) {
      throw new Error(`Error: ${response.status} ${response.statusText}`);
    }

    return this.cache[url] = response.data;
  };
}

const youtubePatterns = [
  /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/,
  /^(?:https?:\/\/)?(?:www\.)?youtu\.be\/([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/,
];

class AddVideoViewModel {
  private readonly enableStart: HTMLInputElement | null;
  private readonly enableEnd: HTMLInputElement | null;

  private readonly start: HTMLInputElement | null;
  private readonly end: HTMLInputElement | null;

  private readonly placeholder: HTMLElement | null;
  private readonly error: HTMLElement | null;
  private readonly info: HTMLElement | null;
  private readonly infoThumbnail: HTMLImageElement | null;
  private readonly infoTitle: HTMLElement | null;
  private readonly infoAuthor: HTMLAnchorElement | null;

  private readonly fields: { [key: string]: Field };
  private readonly state = new Observable<State>({ type: 'placeholder' });

  public constructor(private readonly api: Api) {
    const form = document.querySelector<HTMLElement>('.add-video');
    if (!form) {
      throw new Error('Add video form not found');
    }

    this.enableStart = form.querySelector('.add-video__enable-start > input');
    this.enableEnd = form.querySelector('.add-video__enable-end > input');

    this.start = form.querySelector('.add-video__start');
    this.end = form.querySelector('.add-video__end');

    this.placeholder = form.querySelector('.add-video__placeholder');
    this.error = form.querySelector('.add-video__error');
    this.info = form.querySelector('.add-video__info');
    this.infoThumbnail = form.querySelector<HTMLImageElement>('.add-video__info-thumbnail');
    this.infoTitle = form.querySelector('.add-video__info-title');
    this.infoAuthor = form.querySelector<HTMLAnchorElement>('.add-video__info-author');

    this.fields = ['url']
      .map(fieldName => field(form, fieldName))
      .reduce((fields, field) => ({ ...fields, [field.name]: field }), {});

    this.state.subscribe(this.onStateChange);

    this.enableStart?.addEventListener('input', this.onEnableStartChange);
    this.enableEnd?.addEventListener('input', this.onEnableEndChange);

    this.onEnableStartChange();
    this.onEnableEndChange();

    const urlInput = this.fields['url'].element;
    urlInput.addEventListener('input', this.onUrlChange);

    if (urlInput.value.length) {
      this.onUrlChange();
    }

    eventBus.subscribe('addVideoModalOpened', () => {
      this.onUrlChange();
      this.onEnableStartChange();
      this.onEnableEndChange();
    });

    eventBus.subscribe('addVideoModalClosed', () => {
      this.fields['url'].element.value = '';

      if (this.enableStart) {
        this.enableStart.checked = false;
      }

      if (this.enableEnd) {
        this.enableEnd.checked = false;
      }

      this.state.set({ type: 'placeholder' });
    });
  }

  private onEnableStartChange = () => {
    if (!this.start) {
      return;
    }

    this.start.disabled = !this.enableStart?.checked || false;
    if (this.start.disabled) {
      this.start.value = '0:00';
    }
  }

  private onEnableEndChange = () => {
    if (!this.end) {
      return;
    }

    this.end.disabled = !this.enableEnd?.checked || false;
    if (this.end.disabled) {
      this.end.value = '0:00';
    }
  }

  private onStateChange = (state: State) => {
    const urlInput = this.fields['url'].element;
    switch (state.type) {
      case 'loading':
        urlInput.classList.add('loading');
      case 'placeholder':
        this.placeholder?.removeAttribute('hidden');
        this.error?.setAttribute('hidden', 'true');
        this.info?.setAttribute('hidden', 'true');
        break;

      case 'error':
        this.placeholder?.setAttribute('hidden', 'true');
        this.error?.removeAttribute('hidden');
        this.info?.setAttribute('hidden', 'true');

        urlInput.classList.remove('loading');

        urlInput.classList.remove('valid');
        urlInput.classList.add('invalid');
        break;

      case 'info':
        urlInput.classList.remove('loading');

        urlInput.classList.remove('invalid');
        urlInput.classList.add('valid');

        if (this.infoThumbnail) {
          this.infoThumbnail.src = state.thumbnailUrl;
        }

        if (this.infoTitle) {
          this.infoTitle.textContent = state.title;
        }

        if (this.infoAuthor) {
          this.infoAuthor.textContent = state.author;
          this.infoAuthor.setAttribute('href', state.authorUrl);
        }

        this.placeholder?.setAttribute('hidden', 'true');
        this.error?.setAttribute('hidden', 'true');
        this.info?.removeAttribute('hidden');
        break;
    }
  };

  private isYouTubeVideo = (url: string) => {
    return youtubePatterns.some(pattern => pattern.test(url));
  };

  private onUrlChange = async () => {
    const urlInput = this.fields['url'].element;
    const { value } = urlInput;
    if (!value.length || !this.isYouTubeVideo(value)) {
      this.state.set({ type: 'placeholder' });
      return;
    }

    this.state.set({ type: 'loading' });

    try {
      const data = await this.api.getOEmbedInfo(value);
      this.state.set({
        type: 'info',
        thumbnailUrl: data.thumbnail_url,
        title: data.title,
        author: data.author_name,
        authorUrl: data.author_url,
      });
    } catch (e) {
      if (!Axios.isCancel(e)) {
        console.error(e);
        this.state.set({ type: 'error' });
      }
    }
  };
}

document.addEventListener('DOMContentLoaded', () => {
  const api = new Api();
  const viewModel = new AddVideoViewModel(api);
});
