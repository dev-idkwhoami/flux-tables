@props([
    'src' => null,
    'type'
])
@php
    $classes = $attributes->class('w-full h-full');
@endphp
<div
    {{ $classes }}
    x-modelable="src"
    x-on:contextmenu.prevent
    x-ref="media"
    x-data="{
        src: @js($src),
        playing: false,
        muted: false,
        progress: 0,
        init() {
            /* required to pause media play when wrapping dialog is closed */
            this.$refs.media.closest('dialog').addEventListener('close', () => $refs.player.pause());

            this.$refs.player.addEventListener('play', () => {
                this.playing = true;
            });
            this.$refs.player.addEventListener('pause', () => {
                this.playing = false;
            });
            this.$refs.player.addEventListener('timeupdate', () => {
                this.progress = this.calculateCurrentTimeToPercentageWidth();
            });
            this.$refs.player.volume = 0.05;
            this.$refs.player.muted = this.muted;


            this.listenForTimeOnMarkers();
            this.listenForVideoControls();
        },
        toggleMute() {
            this.$refs.player.muted = !this.$refs.player.muted;
            this.muted = this.$refs.player.muted;
        },
        calculateCurrentTimeToPercentageWidth() {
            return (this.$refs.player.currentTime / this.$refs.player.duration) * 100 + '%';
        },
        calculatePercentageToCurrentTime(percentage) {
            return (percentage / 100) * this.$refs.player.duration;
        },
        seekToFromClickedDivWidth(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const offsetX = event.clientX - rect.left;
            const percentage = (offsetX / rect.width) * 100;
            this.$refs.player.currentTime = this.calculatePercentageToCurrentTime(percentage);
        },
        hideCursorPosition() {
            this.$refs.cursorPosition.style.display = 'none';
        },
        showCursorPosition() {
            this.$refs.cursorPosition.style.display = 'block';
        },
        getCursorPosition(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const offsetX = event.clientX - rect.left;
            const percentage = (offsetX / rect.width) * 100;
            return Math.min(Math.max(percentage, 0), 100);
        },
        moveCursorPosition(event) {
            const percentage = this.getCursorPosition(event);
            this.$refs.cursorPosition.style.left = `${percentage}%`;

            if(percentage < parseFloat(this.progress)) {
                this.$refs.cursorPosition.style.filter = 'invert(1)';
            } else {
                this.$refs.cursorPosition.style.filter = 'invert(0)';
            }
        },
        videoControlsVisible: true,
        hoveringVideo: false,
        hoverTimeout: null,
        showVideoControls() {
            this.videoControlsVisible = true;
        },
        hideVideoControls() {
            this.videoControlsVisible = false;
        },
        listenForVideoControls() {
            $watch('hoveringVideo', (hoveringVideo) => {
                clearTimeout($data.hoverTimeout);
                if(hoveringVideo) {
                    $data.showVideoControls();
                }
                $data.hoverTimeout = setTimeout(() => {
                    if($data.playing && !$data.hoveringVideo) {
                        $data.hideVideoControls()
                    }
                }, !$data.playing ? 3500 : 800);
            });
        },
        repeatMarkerA: 0,
        repeatMarkerB: 0,
        addMarker(event) {
            const percentage = this.getCursorPosition(event);
            if(this.repeatMarkerA === 0) {
                this.repeatMarkerA = percentage;
            }
            else if(percentage < this.repeatMarkerA) {
                this.repeatMarkerB = repeatMarkerA;
                this.repeatMarkerA = percentage;
            }
            else if(this.repeatMarkerB === 0) {
                this.repeatMarkerB = percentage;
            }

            this.$refs.repeatMarkerA.style.left = `${this.repeatMarkerA}%`;
            this.$refs.repeatMarkerB.style.left = `${this.repeatMarkerB}%`;
        },
        clearMarkers() {
            this.repeatMarkerA = 0;
            this.repeatMarkerB = 0;
        },
        listenForTimeOnMarkers() {
            this.$refs.player.addEventListener('timeupdate', () => {
                if(this.repeatMarkerA > 0 && this.repeatMarkerB > 0) {
                    const currentTime = this.$refs.player.currentTime;
                    const timeA = this.calculatePercentageToCurrentTime(this.repeatMarkerA);
                    const timeB = this.calculatePercentageToCurrentTime(this.repeatMarkerB);
                    if(currentTime < timeA || currentTime > timeB) {
                        this.$refs.player.currentTime = timeA + 0.000001;
                    }
                }
            });
        },
    }"
>
    @if($type === 'audio')
        <div class="h-max flex gap-x-2 justify-between">
            <audio x-bind:src="src" preload x-ref="player"></audio>
            <template x-if="!playing">
                <flux:button variant="subtle" icon="play" x-on:click="$refs.player.play()"/>
            </template>
            <template x-if="playing">
                <flux:button variant="subtle" icon="pause" x-on:click="$refs.player.pause()"/>
            </template>
            <div
                x-on:mouseover="showCursorPosition"
                x-on:mouseleave="hideCursorPosition"
                x-on:mousemove="moveCursorPosition($event)"
                x-on:click="seekToFromClickedDivWidth($event)"
                x-on:click.shift="addMarker($event)"
                x-on:click.ctrl="clearMarkers()"
                class="relative w-full min-h-full bg-white/5 rounded">
                <div x-ref="repeatMarkerA" x-cloak x-show="repeatMarkerA > 0"
                     class="absolute z-30 -mt-3 h-full w-0.5 bg-red-400"></div>
                <div x-ref="repeatMarkerB" x-show="repeatMarkerB > 0"
                     class="absolute z-30 -mt-3 h-full w-0.5 bg-red-400"></div>
                <div x-ref="cursorPosition" style="display: none"
                     class="absolute z-20 h-full w-[2px] bg-[var(--color-accent)]"></div>
                <div
                    x-ref="progress"
                    x-cloak
                    :style="{ width: progress }"
                    class="z-10 h-full rounded bg-[var(--color-accent)] transition-all duration-200 ease-linear"
                >
                </div>
            </div>
            <flux:text
                class="group hover:text-white flex gap-x-2 items-center"
            >
                <flux:input
                    class="hidden group-hover:block"
                    variant="filled"
                    min="0" max="1"
                    step="0.05"
                    type="range"
                    x-model="$refs.player.volume"
                />
                <div
                    class="cursor-pointer"
                    x-on:click="toggleMute"
                >
                    <template x-if="!muted">
                        <flux:icon.speaker-wave size="xs"/>
                    </template>
                    <template x-if="muted">
                        <flux:icon.speaker-x-mark size="xs"/>
                    </template>
                </div>
            </flux:text>
        </div>
    @endif

    @if($type === 'video')
        <div class="relative w-full h-auto">
            <video
                x-ref="player"
                x-on:mouseover="hoveringVideo = true"
                x-on:mouseleave="hoveringVideo = false"
                x-bind:src="src"
                preload>
            </video>
            <div
                x-ref="controls"
                x-on:mouseover="hoveringVideo = true"
                x-on:mouseleave="hoveringVideo = false"
                x-show="videoControlsVisible"
                class="isolate z-10 absolute bottom-0 left-0 px-1 pb-2 w-full h-8">
                <div
                    class="z-20 w-full h-full bg-zinc-800/45 text-white rounded flex justify-between items-center gap-x-2">
                    <div class="px-2 content-center min-h-full cursor-pointer hover:bg-zinc-800/25 rounded">
                        <template x-if="!playing">
                            <flux:icon.play class="!size-4" x-on:click="$refs.player.play()"/>
                        </template>
                        <template x-if="playing">
                            <flux:icon.pause class="!size-4" x-on:click="$refs.player.pause()"/>
                        </template>
                    </div>
                    <div
                        x-on:mouseover="showCursorPosition"
                        x-on:mouseleave="hideCursorPosition"
                        x-on:mousemove="moveCursorPosition($event)"
                        x-on:click="seekToFromClickedDivWidth($event)"
                        x-on:click.shift="addMarker($event)"
                        x-on:click.ctrl="clearMarkers()"
                        class="relative block w-full max-h-full h-full bg-white/5 rounded">
                        <div x-ref="repeatMarkerA" x-cloak x-show="repeatMarkerA > 0"
                             class="absolute z-30 -mt-3 h-full w-0.5 bg-red-400"></div>
                        <div x-ref="repeatMarkerB" x-show="repeatMarkerB > 0"
                             class="absolute z-30 -mt-3 h-full w-0.5 bg-red-400"></div>

                        <div x-ref="cursorPosition" style="display: none"
                             class="absolute z-20 h-full w-[2px] bg-[var(--color-accent)]"></div>

                        <div
                            x-ref="progress"
                            x-cloak
                            :style="{ width: progress }"
                            class="z-10 h-full rounded bg-[var(--color-accent)] transition-all duration-200 ease-linear"
                        >
                        </div>
                    </div>
                    <flux:text
                        class="pr-2 hover:bg-zinc-800/25 rounded group hover:text-white flex gap-x-2 items-center"
                    >
                        <flux:input
                            class="hidden group-hover:block"
                            size="xs"
                            variant="filled"
                            min="0" max="1"
                            step="0.05"
                            type="range"
                            x-model="$refs.player.volume"
                        />
                        <div
                            class="cursor-pointer"
                            x-on:click="toggleMute"
                        >
                            <template x-if="!muted">
                                <flux:icon.speaker-wave class="!size-4"/>
                            </template>
                            <template x-if="muted">
                                <flux:icon.speaker-x-mark class="!size-4"/>
                            </template>
                        </div>
                    </flux:text>
                </div>
            </div>
        </div>
    @endif

    @if($type === 'image')
        <img class="max-w-full max-h-full object-contain" alt="{{ $src }}" src="{{ $src }}">
    @endif
</div>
