/*
design by Voicu Apostol.
design: https://dribbble.com/shots/3533847-Mini-Music-Player
I can't find any open music api or mp3 api so i have to download all musics as mp3 file.
You can fork on github: https://github.com/muhammederdem/mini-player
*/

new Vue({
  el: "#app",
  data() {
    return {
      audio: null,
      circleLeft: null,
      barWidth: null,
      duration: null,
      currentTime: null,
      isTimerPlaying: false,
      tracks: [
        {
          name: "邵帅",
          artist: "你是人间四月天",
          cover: "/static/music/1.jpg",
          source: "/static/music/1.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "h3R3",
          artist: "他只是经过",
          cover: "/static/music/3.jpg",
          source: "/static/music/2.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: true
        },
        {
          name: "任然",
          artist: "无人之岛",
          cover: "/static/music/3.jpg",
          source: "/static/music/3.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "汪苏泷",
          artist: "万有引力",
          cover: "/static/music/4.jpg",
          source: "/static/music/4.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "要不要买菜",
          artist: "下山",
          cover: "/static/music/5.jpg",
          source: "/static/music/5.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "是你的垚",
          artist: "信仰",
          cover: "/static/music/6.jpg",
          source: "/static/music/6.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "音阙诗听",
          artist: "空山新雨后",
          cover: "/static/music/7.jpg",
          source: "/static/music/7.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "夏天Alex",
          artist: "不再联系",
          cover: "/static/music/8.jpg",
          source: "/static/music/8.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        },
        {
          name: "尚士达",
          artist: "生而为人",
          cover: "/static/music/9.jpg",
          source: "/static/music/9.mp3",
          url: "https://ah.xuyanzu.com",
          favorited: false
        }
      ],
      currentTrack: null,
      currentTrackIndex: 0,
      transitionName: null
    };
  },
  methods: {
    play() {
      if (this.audio.paused) {
        this.audio.play();
        this.isTimerPlaying = true;
      } else {
        this.audio.pause();
        this.isTimerPlaying = false;
      }
    },
    generateTime() {
      let width = (100 / this.audio.duration) * this.audio.currentTime;
      this.barWidth = width + "%";
      this.circleLeft = width + "%";
      let durmin = Math.floor(this.audio.duration / 60);
      let dursec = Math.floor(this.audio.duration - durmin * 60);
      let curmin = Math.floor(this.audio.currentTime / 60);
      let cursec = Math.floor(this.audio.currentTime - curmin * 60);
      if (durmin < 10) {
        durmin = "0" + durmin;
      }
      if (dursec < 10) {
        dursec = "0" + dursec;
      }
      if (curmin < 10) {
        curmin = "0" + curmin;
      }
      if (cursec < 10) {
        cursec = "0" + cursec;
      }

      //播放的时长
      if(localStorage.getItem('bar') != null){
        let bar = JSON.parse(localStorage.getItem('bar'));
        this.currentTime = bar.currentTime;
      }else {
        this.currentTime = curmin + ":" + cursec;
      }

      this.duration = durmin + ":" + dursec;

      let bar = JSON.stringify({
        index:this.currentTrackIndex,
        barWidth:width + "%",
        circleLeft:width + "%",
        currentTime:curmin + ":" + cursec,
        audioCurrentTime:this.audio.currentTime,
        isPlay:this.isTimerPlaying
      })
      localStorage.setItem('bar',bar );
    },
    updateBar(x) {
      let progress = this.$refs.progress;
      let maxduration = this.audio.duration;
      let position = x - progress.offsetLeft;
      let percentage = (100 * position) / progress.offsetWidth;

      if (percentage > 100) {
        percentage = 100;
      }
      if (percentage < 0) {
        percentage = 0;
      }
      if(this.checkStorageBarIndex()){
        let bar = JSON.stringify({
          index:this.currentTrackIndex,
          barWidth:percentage + "%",
          circleLeft:percentage + "%",
          currentTime:(maxduration * percentage) / 100,
          audioCurrentTime:this.audio.currentTime,
          isPlay:this.isTimerPlaying
        })
        localStorage.setItem('bar',bar );
      }
      this.barWidth = percentage + "%";
      this.circleLeft = percentage + "%";
      this.audio.currentTime = (maxduration * percentage) / 100;
      this.audio.play();
    },
    clickProgress(e) {
      this.isTimerPlaying = true;
      this.audio.pause();
      this.updateBar(e.pageX);
    },
    prevTrack() {
      this.transitionName = "scale-in";
      this.isShowCover = false;
      if (this.currentTrackIndex > 0) {
        this.currentTrackIndex--;
      } else {
        this.currentTrackIndex = this.tracks.length - 1;
      }
      this.currentTrack = this.tracks[this.currentTrackIndex];
      this.resetPlayer();
    },
    nextTrack() {
      this.transitionName = "scale-out";
      this.isShowCover = false;
      if (this.currentTrackIndex < this.tracks.length - 1) {
        this.currentTrackIndex++;
      } else {
        this.currentTrackIndex = 0;
      }
      this.currentTrack = this.tracks[this.currentTrackIndex];
      this.resetPlayer();
    },
    checkStorageBarIndex(){
      let bar = JSON.parse(localStorage.getItem('bar'));
      if(bar.index == this.currentTrackIndex){
        return true;
      }
      return false;
    },
    resetPlayer() {
      //this.upStorageBar(this.currentTrackIndex);
      this.barWidth = 0;
      this.circleLeft = 0;
      this.audio.currentTime = 0;
      this.audio.src = this.currentTrack.source;

      setTimeout(() => {
        if(this.isTimerPlaying) {
          this.audio.play();
        } else {
          this.audio.pause();
        }
      }, 300);
    },
    favorite() {
      this.tracks[this.currentTrackIndex].favorited = !this.tracks[
        this.currentTrackIndex
      ].favorited;
    }
  },
  created() {
    let vm = this;
    this.audio = new Audio();
    if(localStorage.getItem('bar') != null){
      let bar = JSON.parse(localStorage.getItem('bar'));
        this.currentTrackIndex = bar.index;
        this.currentTrack = this.tracks[bar.index];
        this.audio.src = this.currentTrack.source;
        this.audio.currentTime = bar.audioCurrentTime;
        if(bar.isPlay){
          this.audio.play();
          this.isTimerPlaying = true;
        }
    }else {
      this.currentTrack = this.tracks[0];
      this.audio.src = this.currentTrack.source;
    }

    this.audio.ontimeupdate = function() {
      vm.generateTime();
    };
    this.audio.onloadedmetadata = function() {
      vm.generateTime();
    };
    this.audio.onended = function() {
      vm.nextTrack();
      this.isTimerPlaying = true;
    };

    // this is optional (for preload covers)
    for (let index = 0; index < this.tracks.length; index++) {
      const element = this.tracks[index];
      let link = document.createElement('link');
      link.rel = "prefetch";
      link.href = element.cover;
      link.as = "image"
      document.head.appendChild(link)
    }
  }
});
