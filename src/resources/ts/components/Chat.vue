<template>
  <div class="chat__main" ref="main">
    <ul class="chat__list">
      <template v-for="(messages, date) in messagesByDate">
        <li class="chat__date-separator" :key="date">
          {{ formatDate(date) }}
        </li>

        <li class="chat__item" v-for="message of messages" :key="message.id">
          <div class="chat__avatar">
            <img class="chat__avatar-image" :src="message.userAvatar" data-draggable="false" />
          </div>

          <div class="chat__message">
            <a class="chat__name" :href="message.userUrl">{{ message.userName }}</a>
            <span class="chat__message-text">{{ message.message }}</span>
          </div>

          <div class="chat__message-right">
            <button type="button" class="chat__message-mention" @click="mention(message)">@</button>
            <time class="chat__message-time" :datetime="message.createdAt">{{ formatTime(message.createdAt) }}</time>
          </div>
        </li>
      </template>
    </ul>
  </div>
</template>

<script>
import Vue from 'vue';
import { eventBus } from '../utils';

export default Vue.extend({
  name: 'Chat',
  props: {
    messages: { required: true },
  },
  data() {
    return {
      first: true,
      messagesValue: [],
    };
  },
  beforeMount() {
    this.unsubscribe = this.messages.subscribe(messages => {
      this.messagesValue = [...messages];
      this.$nextTick(() => this.scrollToBottom());
    });

    this.messagesValue = this.messages.get();
  },
  mounted() {
    this.first = false;
    this.scrollToBottom();
  },
  beforeDestroy() {
    this.unsubscribe();
  },
  computed: {
    messagesByDate() {
      return this.messagesValue.reduce((dates, message) => {
        const date = this.getDateKey(message.createdAt);
        if (typeof dates[date] !== 'undefined') {
          return {
            ...dates,
            [date]: [...dates[date], message],
          };
        } else {
          return {
            ...dates,
            [date]: [message],
          };
        }
      }, {});
    },
  },
  methods: {
    scrollToBottom() {
      this.$refs.main.scrollTop = this.$refs.main.scrollHeight;
    },
    getDateKey(time) {
      const date = new Date(time);
      // On first render use UTC dates to match server-rendered markup.
      if (this.first) {
        const year = date.getFullYear();
        const month = (date.getUTCMonth() + 1).toString().padStart(2, '0');
        const day = date.getUTCDate().toString().padStart(2, '0');

        return `${year}-${month}-${day}`;
      } else {
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');

        return `${year}-${month}-${day}`;
      }
    },
    formatDate(time) {
      const timeDate = this.getDateKey(time);
      if (timeDate === this.getDateKey(new Date())) {
        return 'Сегодня';
      } else if (timeDate === this.getDateKey(new Date(Date.now() - 24 * 60 * 60 * 1000))) {
        return 'Вчера';
      }

      const date = new Date(time);
      const day = date.getDate().toString();
      const month = [
        'Января',
        'Февраля',
        'Марта',
        'Апреля',
        'Мая',
        'Июня',
        'Июля',
        'Августа',
        'Сентября',
        'Октября',
        'Ноября',
        'Декабря',
      ][date.getMonth()];

      return `${day} ${month}`;
    },
    formatTime(time) {
      const date = new Date(time);
      const hStr = date.getHours().toFixed(0).padStart(2, '0');
      const mStr = date.getMinutes().toFixed(0).padStart(2, '0');

      return `${hStr}:${mStr}`;
    },
    mention(message) {
      eventBus.emit('mention', message);
    },
  },
});
</script>
