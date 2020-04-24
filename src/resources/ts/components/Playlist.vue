<template>
  <ul class="room-playlist__list">
    <li v-for="video of videosValue" :key="video.id" class="room-playlist__item">
      <span class="room-playlist__item-title">{{ video.title }}</span>
    </li>
  </ul>
</template>

<script>
import Vue from 'vue';

export default Vue.extend({
  name: 'Playlist',
  props: {
    videos: { required: true },
  },
  data() {
    return { videosValue: [] };
  },
  beforeMount() {
    this.unsubscribe = this.videos.subscribe(videos => {
      this.videosValue = [...videos];
    });

    this.videosValue = this.videos.get();
  },
  beforeDestroy() {
    this.unsubscribe();
  },
});
</script>
