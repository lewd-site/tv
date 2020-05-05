<template>
  <div>
    <ul class="player-options__list" v-show="menu === 'main'">
      <li class="player-options__item"
        :class="{ 'player-options__item_checked': isSyncEnabledValue }">
        <button type="button" class="player-options__item-text" @click.prevent="onSyncClick()">
          Синхронизация
        </button>
      </li>

      <li class="player-options__item">
        <button type="button" class="player-options__item-text player-options__item-text_right" @click.prevent="menu = 'subtitles'">
          <span>Субтитры</span>
          <span>{{ subtitleTrackValue ? subtitleTrackValue.displayName : 'Выкл.' }}</span>
        </button>
      </li>

      <li class="player-options__item">
        <!--<button type="button" class="player-options__item-text player-options__item-text_right" @click="menu = 'quality'">-->
        <button type="button" class="player-options__item-text" @click.prevent>
          <span>Качество</span>
          <span>{{ getQualityName(qualityLevelValue) }}</span>
        </button>
      </li>
    </ul>

    <ul class="player-options__list" v-show="menu === 'subtitles'">
      <li class="player-options__title">
        <button type="button" class="player-options__title-text player-options__title-text_left" @click.prevent="menu = 'main'">
          Субтитры
        </button>
      </li>

      <li class="player-options__item" v-for="track of subtitleTracksValue" :key="track.languageCode"
        :class="{ 'player-options__item_checked': subtitleTrackValue && subtitleTrackValue.languageCode === track.languageCode }">
        <button type="button" class="player-options__item-text" @click.prevent="onSubItemClick(track)">
          {{ track.displayName }}
        </button>
      </li>

      <li class="player-options__item" :class="{ 'player-options__item_checked': subtitleTrackValue === null }">
        <button type="button" class="player-options__item-text" @click.prevent="onSubItemClick(null)">
          Выкл.
        </button>
      </li>
    </ul>

    <ul class="player-options__list" v-show="menu === 'quality'">
      <li class="player-options__title">
        <button type="button" class="player-options__title-text player-options__title-text_left" @click.prevent="menu = 'main'">
          Качество
        </button>
      </li>

      <li class="player-options__item" v-for="qualityLevel of qualityLevelsValue" :key="qualityLevel"
        :class="{ 'player-options__item_checked': qualityLevelValue === qualityLevel }">
        <button type="button" class="player-options__item-text" @click.prevent="onQualityItemClick(qualityLevel)">
          {{ getQualityName(qualityLevel) }}
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
import Vue from 'vue';

const qualities = {
  highres: 'Высокое разрешение',
  large: '480p',
  medium: '360p',
  small: '240p',
  tiny: '144p',
  auto: 'Автоматически',
};

function getQualityName(quality) {
  if (typeof qualities[quality] !== 'undefined') {
    return qualities[quality];
  }

  const matches = `${quality}`.match(/^hd(\d+)$/);
  if (matches) {
    return `${matches[1]}p`;
  }

  return quality;
}

export default Vue.extend({
  name: 'PlayerOptions',
  props: {
    isSyncEnabled: { required: true },

    subtitleTracks: { required: true },
    subtitleTrack: { required: true },

    qualityLevels: { required: true },
    qualityLevel: { required: true },
  },
  data() {
    return {
      menu: 'main',
      isSyncEnabledValue: false,

      subtitleTracksValue: [],
      subtitleTrackValue: null,

      qualityLevelsValue: [],
      qualityLevelValue: 'auto',
    };
  },
  beforeMount() {
    this.unsubscribeIsSyncEnabled = this.isSyncEnabled.subscribe(isSyncEnabled => {
      this.isSyncEnabledValue = isSyncEnabled;
    });

    this.unsubscribeSubtitleTracks = this.subtitleTracks.subscribe(subtitleTracks => {
      this.subtitleTracksValue = [...subtitleTracks];
    });

    this.unsubscribeSubtitleTrack = this.subtitleTrack.subscribe(subtitleTrack => {
      this.subtitleTrackValue = subtitleTrack;
    });

    this.unsubscribeQualityLevels = this.qualityLevels.subscribe(qualityLevels => {
      this.qualityLevelsValue = [...qualityLevels];
    });

    this.unsubscribeQualityLevel = this.qualityLevel.subscribe(qualityLevel => {
      this.qualityLevelValue = qualityLevel;
    });

    this.isSyncEnabledValue = this.isSyncEnabled.get();

    this.subtitleTracksValue = this.subtitleTracks.get();
    this.subtitleTrackValue = this.subtitleTrack.get();

    this.qualityLevelsValue = this.qualityLevels.get();
    this.qualityLevelValue = this.qualityLevel.get();
  },
  beforeDestroy() {
    this.unsubscribeIsSyncEnabled();

    this.unsubscribeSubtitleTracks();
    this.unsubscribeSubtitleTrack();

    this.unsubscribeQualityLevels();
    this.unsubscribeQualityLevel();
  },
  methods: {
    getQualityName,
    onSyncClick() {
      this.$emit('syncChange', !this.isSyncEnabledValue);
    },
    onSubItemClick(track) {
      this.$emit('subtitleTrackChange', track);
    },
    onQualityItemClick(quality) {
      this.$emit('qualityChange', quality);
    },
  },
});
</script>
