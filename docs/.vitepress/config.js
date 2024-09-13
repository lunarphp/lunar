import {defineConfig} from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "Lunar",
    description: "Laravel Headless E-Commerce",
    head: [
        [
            'link',
            {rel: 'icon', href: '/icon.svg', type: 'image/svg'}
        ],
        [
            'link',
            {rel: 'preconnect', href: 'https://fonts.googleapis.com'}
        ],
        [
            'link',
            {rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: ''}
        ],
        [
            'link',
            {
                rel: 'stylesheet',
                href: 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap'
            }
        ]
        // would render: <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    ],

    appearance: 'dark',

    themeConfig: {
        // https://vitepress.dev/reference/default-theme-config
        logo: {
            light: '/icon.svg',
            dark: '/icon-dark.svg',
        },

        nav: [
            {text: 'Core', link: '/core/overview', activeMatch: '/core/'},
            {text: 'Admin Panel', link: '/admin/overview', activeMatch: '/admin/'},
            {
                text: 'Resources',
                items: [
                    {text: 'Add-ons', link: 'https://github.com/lunarphp/awesome'},
                    {text: 'Discord', link: 'https://discord.gg/v6qVWaf'},
                    {text: 'Discussions', link: 'https://github.com/lunarphp/lunar/discussions'}
                ]
            },
            {
                text: '1.x',
                items: [
                    {text: 'Changelog', link: '/core/upgrading'},
                    {text: 'Contributing', link: '/core/contributing'},
                    {text: 'Roadmap', link: 'https://github.com/orgs/lunarphp/projects/8'},
                    {text: '0.x Docs', link: 'https://v0.lunarphp.io/'},
                ]
            }
        ],

        sidebar: {
            // This sidebar gets displayed when a user
            // is on `guide` directory.
            '/core/': [
                {
                    text: 'Getting Started',
                    collapsed: false,
                    items: [
                        {text: 'Overview', link: '/core/overview'},
                        {text: 'Installation', link: '/core/installation'},
                        {text: 'Starter Kits', link: '/core/starter-kits'},
                        {text: 'Configuration', link: '/core/configuration'},
                        {text: 'Initial Set-Up', link: '/core/set-up'},
                        {text: 'Upgrade Guide', link: '/core/upgrading'},
                        {text: 'Release Schedule', link: '/core/release-schedule'},
                        {text: 'Security', link: '/core/securing-your-site'},
                        {text: 'Contributing', link: '/core/contributing'}
                    ]
                },
                {
                    text: 'Reference',
                    collapsed: false,
                    items: [
                        {text: 'Activity Log', link: '/core/reference/activity-log'},
                        {text: 'Addresses', link: '/core/reference/addresses'},
                        {text: 'Associations', link: '/core/reference/associations'},
                        {text: 'Attributes', link: '/core/reference/attributes'},
                        {text: 'Carts', link: '/core/reference/carts'},
                        {text: 'Channels', link: '/core/reference/channels'},
                        {text: 'Collections', link: '/core/reference/collections'},
                        {text: 'Currencies', link: '/core/reference/currencies'},
                        {text: 'Customers', link: '/core/reference/customers'},
                        {text: 'Discounts', link: '/core/reference/discounts'},
                        {text: 'Languages', link: '/core/reference/languages'},
                        {text: 'Media', link: '/core/reference/media'},
                        {text: 'Orders', link: '/core/reference/orders'},
                        {text: 'Payments', link: '/core/reference/payments'},
                        {text: 'Pricing', link: '/core/reference/pricing'},
                        {text: 'Products', link: '/core/reference/products'},
                        {text: 'Search', link: '/core/reference/search'},
                        {text: 'Tags', link: '/core/reference/tags'},
                        {text: 'Taxation', link: '/core/reference/taxation'},
                        {text: 'URLs', link: '/core/reference/urls'}
                    ]
                },
                {
                    text: 'Storefront',
                    collapsed: false,
                    items: [
                        {text: 'Storefront Session', link: '/core/storefront-utils/storefront-session'},
                    ]
                },
                {
                    text: 'Extending',
                    collapsed: false,
                    items: [
                        {text: 'Carts', link: '/core/extending/carts'},
                        {text: 'Discounts', link: '/core/extending/discounts'},
                        {text: 'Models', link: '/core/extending/models'},
                        {text: 'Orders', link: '/core/extending/orders'},
                        {text: 'Payments', link: '/core/extending/payments'},
                        {text: 'Search', link: '/core/extending/search'},
                        {text: 'Shipping', link: '/core/extending/shipping'},
                        {text: 'Taxation', link: '/core/extending/taxation'}
                    ]
                }
            ],

            '/admin/': [
                {
                    text: 'Getting Started',
                    collapsed: false,
                    items: [
                        {text: 'Overview', link: '/admin/overview'},
                    ]
                },
                {
                    text: 'Extending',
                    collapsed: false,
                    items: [
                        {text: 'Overview', link: '/admin/extending/overview'},
                        {text: 'Access Control', link: '/admin/extending/access-control'},
                        {text: 'Add-ons', link: '/admin/extending/addons'},
                        {text: 'Attributes', link: '/admin/extending/attributes'},
                        {text: 'Panel', link: '/admin/extending/panel'},
                        {text: 'Pages', link: '/admin/extending/pages'},
                        {text: 'Resources', link: '/admin/extending/resources'},
                        {text: 'Relation Managers', link: '/admin/extending/relation-managers'},
                        {text: 'Order Management', link: '/admin/extending/order-management'}
                    ]
                }
            ],

        },

        socialLinks: [
            {icon: 'github', link: 'https://github.com/lunarphp/lunar'},
            {icon: 'twitter', link: 'https://twitter.com/lunarphp'},
            {icon: 'discord', link: 'https://discord.gg/v6qVWaf'},
        ],

        algolia: {
            appId: 'ZHX0K72823',
            apiKey: '42f3d86ed75f289e5cb75e9d7c6f43f9',
            indexName: 'lunarphp'
        },
    }
})
