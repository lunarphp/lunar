<div>
  <x-hub::input.text wire:model="{{ $field['signature'] }}" />
  <div class="mt-4" x-data="{
    video: @entangle($field['signature']),
    videoEmbed() {
      if (!this.video) {
        return;
      }

      this.video = this.video
        .replace('https://www.youtube.com/watch?v=', '')
        .replace('https://www.youtube.com/embed/', '')


      return 'https://www.youtube.com/embed/' + this.video
    }
  }">
    <iframe x-show="video" class="w-full aspect-[16/9]" x-bind:src="videoEmbed" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
  </div>
</div>
