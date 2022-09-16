# Activity Log

[[toc]]

## Overview

We've made a design choice to have activity logging throughout Lunar when it comes to changes happening on Eloquent models. We believe it's important to keep track of what updates are happening and who is making them. It allows us to provide you with an invaluable insight into what's happening in your store.

## How it works

For the actual logging, we have opted to use the incredible package by Spatie, [laravel-activitylog](https://spatie.be/docs/laravel-activitylog). This allows Lunar to keep track changes throughout the system so you can have a full history of what's going on.

## Enabling on your own models

If you want to enable logging on your own models you can simply [follow the guides on their website](https://spatie.be/docs/laravel-activitylog)
