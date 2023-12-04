import {defineConfig} from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
    title: "Lunar Panel",
    description: "Filament admin panel for Lunar Headless E-Commerce",
    themeConfig: {
        // https://vitepress.dev/reference/default-theme-config
        nav: [
            {text: 'Home', link: '/'}
        ],

        sidebar: [
            {
                text: 'Getting Started',
                items: [
                    {text: 'Introduction', link: '/'},
                    {text: 'Installation', link: '/installation'}
                ]
            },
            {
                text: 'Upgrading to Panel',
                link: '/upgrading'
            },
            {
                text: 'Extending',
                items: [
                    {text: 'Attributes', link: '/extending/attributes'},
                    {text: 'Panel', link: '/extending/panel'},
                    {text: 'Pages', link: '/extending/pages'},
                    {text: 'Plugin', link: '/extending/plugin'},
                    {text: 'Resources', link: '/extending/resources'},
                    {text: 'Access control', link: '/extending/access-control'}
                ]
            }
        ],

        socialLinks: [
            {icon: 'github', link: 'https://github.com/lunarphp/panel'}
        ]
    }
})
