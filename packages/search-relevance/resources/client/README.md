# @lunarphp/search-relevance

Storefront click tracking for the `lunarphp/search-relevance` Composer package. Reads the `meta` the results pipeline stamps on `SearchResults` and each hit and posts clicks to the events endpoint so basket and purchase attribution can follow.

```ts
import { useSearchTracking } from '@lunarphp/search-relevance/vue';

const { track, attrs } = useSearchTracking(() => props.results);
```

```vue
<a v-for="hit in results.hits" v-bind="attrs(hit)" :href="url(hit)" @click="track(hit)">...</a>
```

Framework-agnostic entry: `attachSearchTracking()`, `trackHit()`, `sendSearchEvent()`, `trackingAttributes()` from `@lunarphp/search-relevance`.

See the Lunar docs for the full guide.
