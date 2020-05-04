<template>
  <ul class="player-options__list">
    <li class="player-options__title">
      <button type="button" class="player-options__title-text" @click="onSubTitleClick()">
        Субтитры
      </button>
    </li>

    <li class="player-options__item" v-for="track of subtitleTracksValue" :key="track.languageCode"
      :class="{ 'player-options__item_checked': subtitleTrackValue && subtitleTrackValue.languageCode === track.languageCode }">
      <button type="button" class="player-options__item-text" @click="onSubItemClick(track)">
        {{ track.displayName }}
      </button>
    </li>

    <li class="player-options__item" :class="{ 'player-options__item_checked': subtitleTrackValue === null }">
      <button type="button" class="player-options__item-text" @click="onSubItemClick(null)">
        Выключить
      </button>
    </li>
  </ul>
</template>

<script>
import Vue from 'vue';

export default Vue.extend({
  name: 'PlayerOptions',
  props: {
    subtitleTracks: { required: true },
    subtitleTrack: { required: true },
  },
  data() {
    return {
      subtitleTracksValue: [],
      subtitleTrackValue: [],
    };
  },
  beforeMount() {
    this.unsubscribeSubtitleTracks = this.subtitleTracks.subscribe(subtitleTracks => {
      this.subtitleTracksValue = [...subtitleTracks];
    });

    this.unsubscribeSubtitleTrack = this.subtitleTrack.subscribe(subtitleTrack => {
      this.subtitleTrackValue = subtitleTrack;
    });

    this.subtitleTracksValue = this.subtitleTracks.get();
    this.subtitleTrackValue = this.subtitleTrack.get();
  },
  beforeDestroy() {
    this.unsubscribeSubtitleTracks();
    this.unsubscribeSubtitleTrack();
  },
  methods: {
    onSubTitleClick() {
    },
    onSubItemClick(track) {
      this.$emit('subtitleTrackChange', track);
    },
  },
});
</script>
