<div
  x-data="{
    tags: @entangle($attributes->wire('model')),
    value: ''
  }"
  x-init="
    resetValue = () => value = ''

    addTag = (tag) => {
      tag = tag.trim().toUpperCase()
      if (!tags.includes(tag)) {
        tags.push(tag)
      }
      resetValue()
    }

    removeTag = (index) => {
      tags.splice(index, 1)
    }

    $watch('value', (value) => {
      let sanitised = value.trim()
      if (sanitised.endsWith(',')) {
        const tag = sanitised.replace(',', '').trim()
        addTag(tag)
      }
    })
  "
>
  <div class="flex flex-wrap items-center block w-full px-2 border border-gray-300 rounded-md shadow-sm form-input disabled:opacity-50 disabled:cursor-not-allowed">
    <template x-for="(tag, tagIndex) in tags" :key="tagIndex" hidden>
      <span
        class="flex items-center px-2 py-1 my-1 mr-2 space-x-1 text-sm leading-none bg-purple-100 rounded"
      >
        <span x-text="tag" class="text-purple-700"></span>
        <button type="button" @click.prevent="removeTag(tagIndex)" class="text-purple-400 hover:text-purple-900"><x-hub::icon ref="x" style="solid" class="w-4" /></button>
      </span>
    </template>
    <input
      placeholder="Seperate tags with a ,"
      maxlength="255"
      x-model="value"
      x-on:keyup.enter.prevent="addTag($event.target.value)"
      type="text"
      class="flex-1 text-sm leading-none border-none rounded focus:outline-none focus:border-transparent focus:ring-none focus:appearance-none"
    >
  </div>
</div>
