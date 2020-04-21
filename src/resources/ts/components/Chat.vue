<template>
  <div class="chat__main" ref="main">
    <ul class="chat__list">
      <li v-for="message of messagesValue" :key="message.id" class="chat__item">
        <div class="chat__avatar">
          <img class="chat__avatar-image" :src="message.userAvatar" data-draggable="false" />
        </div>

        <div class="chat__message">
          <a class="chat__name" :href="message.userUrl">{{ message.userName }}</a>
          <span class="chat__message-text">{{ message.message }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import Vue from 'vue';

export default Vue.extend({
  name: 'Chat',
  props: {
    messages: { required: true },
  },
  data() {
    return { messagesValue: [] };
  },
  beforeMount() {
    this.unsubscribe = this.messages.subscribe(messages => {
      this.messagesValue = [...messages];
      this.$nextTick(() => this.scrollToBottom());
    });

    this.messagesValue = this.messages.get();
  },
  mounted() {
    this.scrollToBottom();
  },
  beforeDestroy() {
    this.unsubscribe();
  },
  methods: {
    scrollToBottom() {
      this.$refs.main.scrollTop = this.$refs.main.scrollHeight;
    },
  },
});
</script>
