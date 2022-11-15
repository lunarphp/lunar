const { description } = require('../../package')

module.exports = {
  /**
   * Ref：https://v1.vuepress.vuejs.org/config/#title
   */
  title: 'Lunar Docs',
  /**
   * Ref：https://v1.vuepress.vuejs.org/config/#description
   */
  description: description,

  /**
   * Extra tags to be injected to the page HTML `<head>`
   *
   * ref：https://v1.vuepress.vuejs.org/config/#head
   */
  head: [
    ['meta', { name: 'apple-mobile-web-app-capable', content: 'yes' }],
    ['meta', { name: 'apple-mobile-web-app-status-bar-style', content: 'black' }],
    ['link', { rel: "apple-touch-icon", type: "image/png", sizes: "180x180", href: "/apple-touch-icon.png"}],
    ['link', { rel: "icon", type: "image/png", sizes: "16x16", href: "/favicon-16x16.png"}],
    ['link', { rel: "icon", type: "image/png", sizes: "32x32", href: "/favicon-32x32.png"}],
    ['link', { rel: "manifest", href: "/site.webmanifest"}],
    ['link', { rel: "mask-icon", href: "/safari-pinned-tab.svg", color: "#5bbad5"}],
    ['meta', { name: "msapplication-TileColor", content: "#da532c"}],
    ['meta', { property: 'og:image', content: '/images/og.jpg' }],
    ['meta', { name: "theme-color", content: "#ffffff"}],
    ['script', {
        src: 'https://cdn.usefathom.com/script.js',
        'data-spa': 'auto',
        'data-site': 'IKSUMZLE',
        defer: true
    }],
  ],

  /**
   * Theme configuration, here is the default theme configuration for VuePress.
   *
   * ref：https://v1.vuepress.vuejs.org/theme/default-theme-config.html
   */
  themeConfig: {
    logo: '/images/lunar-icon.svg',
    repo: 'lunarphp/lunar',
    docsDir: 'docs/src',
    docsRepo: 'lunarphp/lunar',
    docsBranch: 'main',
    editLinks: true,
    editLinkText: 'Help us improve this page!',
    smoothScroll: true,
    lastUpdated: false,
    algolia: {
      apiKey: '42f3d86ed75f289e5cb75e9d7c6f43f9',
      indexName: 'lunarphp',
      appId: 'ZHX0K72823'
    },
    nav: [
      {
        text: 'Roadmap',
        link: 'https://github.com/orgs/lunarphp/projects/1',
      },
      {
        text: 'Discord',
        link: 'https://discord.gg/v6qVWaf'
      }
    ],
    sidebar: [
      {
        title: 'Getting Started',   // required
        collapsable: false, // optional, defaults to true
        children: [
          ['/', 'Overview'],
          ['/quickstart', 'Quick Start'],
          '/installation',
          '/configuration',
          ['/upgrading', 'Upgrade Guide'],
          ['/securing-your-site', 'Security'],
          ['/contributing', 'Contributing'],
        ]
      },
      {
        title: 'Admin Hub',
        collapsable: false, // optional, defaults to true
        children: [
          '/admin-hub/overview',
          '/admin-hub/staff'
        ]
      },
      {
        title: 'Guide',
        collapsable: false, // optional, defaults to true
        children: [
          '/lunar/activity-log',
          '/lunar/addresses',
          '/lunar/associations',
          '/lunar/attributes',
          '/lunar/carts',
          '/lunar/channels',
          '/lunar/collections',
          '/lunar/currencies',
          '/lunar/customers',
          '/lunar/images',
          '/lunar/languages',
          '/lunar/orders',
          '/lunar/payments',
          '/lunar/products',
          '/lunar/search',
          '/lunar/tags',
          '/lunar/taxation',
          '/lunar/urls'
        ]
      },
      {
        title: 'Extending Lunar',
        collapsable: false, // optional, defaults to true
        children: [
          '/extending/activity-log',
          '/extending/admin-hub',
          '/extending/assets',
          '/extending/carts',
          '/extending/field-types',
          '/extending/models',
          '/extending/order-modifiers',
          '/extending/payments',
          '/extending/search',
          '/extending/shipping',
          '/extending/tables',
          '/extending/taxation',
        ]
      }
    ],
  },

  /**
   * Apply plugins，ref：https://v1.vuepress.vuejs.org/zh/plugin/
   */
  plugins: [
    '@vuepress/plugin-back-to-top',
    '@vuepress/plugin-medium-zoom',
  ]
}
