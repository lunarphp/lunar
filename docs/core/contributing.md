# Contributing

## Overview

Lunar is an open source project, and so by its very nature, welcomes contributions.

You can contribute to the project in many different ways. Reporting bugs, fixing bugs, helping with the documentation, making suggestions and submitting improvements to the software.

## Monorepo

Lunar uses a monorepo [lunarphp/lunar](https://github.com/lunarphp/lunar) approach to maintaining its codebase. [Monorepos](https://en.wikipedia.org/wiki/Monorepo) are quite common, but may not be familiar to some. The monorepo helps us to organise the code for ease of development.

## Contributing Code

The basic process for contributing to Lunar is as follows...

1. Fork the monorepo
2. Clone your fork locally
3. Make your changes
4. Ensure the tests run and complete successfully
5. Submit a pull request

However, if you're not used to working with monorepo's and setting them up inside a test Laravel application, no problem!

::: tip
Here's a guide on how to set-up your development environment ready for contributing to Lunar.
[Setting Up Lunar For Local Development](/core/local-development)
:::

## Found a Bug?

If you find a bug in the software please raise a GitHub Issue on the [lunarphp/lunar](https://github.com/lunarphp/lunar/issues) repository. Please ensure that your issue includes the following:

**Minimum**

- Clear title and description of the issue
- Steps on how to reproduce the issue

**Ideal**

- An accompanying Pull Request with a test to demonstrate the issue.

Lunar is an open source project and as such we want contribution to be as accessible as possible and to enable contributors to actively collaborate on features and issues. By making sure you provide as much information as possible you are giving your issue the best chance to get the attention it needs.

Be aware that creating an issue does not mean it will get activity straight away, please be patient and understand we will do our best to look into it as soon as possible.

Open source code belongs to all of us, and it's all of our responsibility to push it forward.

## Proposing a Feature

Before you start coding away on the next awesome feature, we highly recommend starting a [discussion](https://github.com/lunarphp/lunar/discussions) to check that your contribution will be welcomed. We would hate for you to spend valuable time on something that won't be merged into Lunar.

However, you're more than welcome to code away on your idea if you think it will help the discussion.

## Making a Pull Request

When making a [pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request), there should be a suitable template for you to follow to ensure the bug or feature can be reviewed in a timely manner.
If the pull request is missing information or unclear as to what it offers or solves, it could any delay movement or be closed.

A PR should be able to include the following:

- The title should be relevant and quickly explain what to expect inside
- A clear description of the feature or fix
- References to any issues the PR resolves
- Label as either a `bug`, `enhancement`, `feature` or `documentation`
- Any relevant documentation updates
- Unit tests with adequate code coverage

## Code Styles

Lunar currently uses [Laravel Pint](https://laravel.com/docs/pint) for code styling. This is not automatically triggered, so you will need to run `vendor/bin/pint` on your branch.

## Asset compiling

The Lunar hub and some other add-ons/utils will provide their own assets. Please do not commit these files as they will be overwritten by the core team when the release is being finalised.

## Documentation Updates

When providing updates to the documentation, your pull request should target the relevant branch for the version you are updating. For documentation of new features in an upcoming release, target the `main` branch.

If you would like to contribute to the documentation you can do easily by following these instructions...

1. Fork the monorepo `lunarphp/lunar`
2. Clone your fork locally
3. In your terminal change to the `/docs` directory
4. Run `npm install`
5. Run `npm run docs:dev` to preview the documentation locally
6. Make your changes
7. Submit a pull request

Lunar uses [VitePress](https://vitepress.dev/) for our documentation site which uses [Markdown](https://www.markdownguide.org/basic-syntax/) files to store the content. You'll find these Markdown files in the `/docs` directory.
