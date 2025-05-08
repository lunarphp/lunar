<script setup>
import DefaultTheme from 'vitepress/theme'

const { Layout } = DefaultTheme
</script>

<template>
    <Layout>
        <template #aside-ads-before>
            <a href="/support.html#premium-support">
                <div class="banner">
                    <div class="banner-title">Lunar Premium Support</div>
                    <div>Get direct access to the Lunar team to support your project.</div>
                    <div class="banner-btn">Find out more</div>
                </div>
            </a>
        </template>
    </Layout>
</template>
