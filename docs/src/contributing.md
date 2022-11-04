# Contributing

[[toc]]

## Overview

Lunar is an open source project, and so by its very nature, welcomes contributions.

You can contribute to the project in many different ways. Reporting bugs, fixing bugs, helping with the documentation, making suggestions and submitting improvements to the software.

## Monorepo

Lunar uses a monorepo [lunarphp/lunar](https://github.com/lunarphp/lunar) to house the core, admin hub and documentation. [Monorepos](https://en.wikipedia.org/wiki/Monorepo) are quite common, but may not be familiar to some. The monorepo helps us to organise the code for ease of development.

## Repository Branching

There are two branches you need to be aware of when contributing to Lunar - `main` and `develop`.

The `main` branch hosts the latest stable version of the software and documentation. Whereas the `develop` branch hosts new features and updates in active development between releases.

## Documentation

If you would like to contribute to the documentation you can do easily by following these instructions...

1. Fork the monorepo `lunarphp/lunar`
2. Clone your fork locally
3. In your terminal change to the `/docs` directory
4. Run `npm install`
5. Run `npm run dev` to preview the documentation locally
6. Make your changes
7. Submit a pull request

Lunar uses [VuePress](https://vuepress.vuejs.org/) for our documentation site which uses [Markdown](https://www.markdownguide.org/basic-syntax/) files to store the content. You'll find these Markdown files in the `/docs/src` directory.

## Found a Bug?

If you find a bug in the software please raise a GitHub Issue on the [lunarphp/lunar](https://github.com/lunarphp/lunar/issues) repository.

Even better would be a pull request with a test that fails demonstrating the bug.

## Proposing a Feature

Before you start coding away on the next awesome feature, we highly recommend starting a [discussion](https://github.com/lunarphp/lunar/issues/new/choose) to check that your contribution will be welcomed. We would hate for you to spend valuable time on something that won't be merged into Lunar.

However, you're more than welcome to code away on your idea if you think it will help the discussion.

## Issue Not Getting Attention?

If you need a bug fixed and nobody is fixing it, your best bet is to provide a fix for it and make a [pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request). Open source code belongs to all of us, and it's all of our responsibility to push it forward.

## Contributing Code

The basic process for contributing to Lunar is as follows...

1. Fork the monorepo
2. Clone your fork locally
3. Make your changes
4. Ensure the tests run and complete successfully
5. Submit a pull request

However, if you're not used to working with monorepo's and setting them up inside a test Laravel application, no problem!

::: tip Development Guide
Here's a guide on how to set-up your development environment ready for contributing to Lunar.

[Setting Up Lunar For Local Development](/local-development.html)
:::

## Making a Pull Request

When making a [pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request), you will want to target the correct branch.

* If you are contributing documentation or a hotfix, the PR should be targeted straight to the `main` branch.
* However, new feature or enhancement contributions should target the `develop` branch for the next release.

Please include a good description of what your pull request offers.

When contributing code, please ensure you include suitable tests, documentation and changelog entries, as applicable.
