const { description } = require('../../package')

module.exports = {
  /**
   * Ref：https://v1.vuepress.vuejs.org/config/#title
   */
  title: 'GetCandy 2 Docs',
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
    ['meta', { name: "theme-color", content: "#ffffff"}],
    ['script', {
        async: true,
        src: 'https://www.googletagmanager.com/gtag/js?id=G-QXEXZY8MY8'
    }],
    ['script', {}, `
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-QXEXZY8MY8');
    `],
  ],

  /**
   * Theme configuration, here is the default theme configuration for VuePress.
   *
   * ref：https://v1.vuepress.vuejs.org/theme/default-theme-config.html
   */
  themeConfig: {
    logo: '/getcandy_icon.svg',
    repo: 'getcandy/getcandy',
    editLinks: false,
    docsDir: 'docs/src',
    docsRepo: 'getcandy/getcandy',
    docsBranch: 'main',
    editLinks: true,
    editLinkText: 'Help us improve this page!',
    smoothScroll: true,
    lastUpdated: false,
    algolia: {
      apiKey: '5b837c3914609051127d86be919e1724',
      indexName: 'getcandy',
      appId: 'LRDQ9JSQEG'
    },
    nav: [
      {
        text: 'Main Site',
        link: 'https://getcandy.io',
      },
      {
        text: 'Roadmap',
        link: 'https://portal.productboard.com/getcandy/1-getcandy',
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
          '/installation',
          '/configuration',
          ['/upgrading', 'Upgrade Guide'],
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
          '/getcandy/activity-log',
          '/getcandy/addresses',
          '/getcandy/associations',
          '/getcandy/attributes',
          '/getcandy/carts',
          '/getcandy/channels',
          '/getcandy/collections',
          '/getcandy/currencies',
          '/getcandy/customers',
          '/getcandy/images',
          '/getcandy/languages',
          '/getcandy/orders',
          '/getcandy/products',
          '/getcandy/search',
          '/getcandy/tags',
          '/getcandy/taxation',
          '/getcandy/urls'
        ]
      },
      {
        title: 'Extending GetCandy',
        collapsable: false, // optional, defaults to true
        children: [
          '/extending/field-types',
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
