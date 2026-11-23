# Contributing to Lunar

Thanks for wanting to contribute. Lunar is used in production by real stores, so
we hold a high bar on changes to the core packages.

## Pull requests are limited to collaborators

Pull requests to this repository can only be opened by collaborators. We no
longer accept pull requests from outside the Lunar team.

You contribute through issues and Discussions instead. The Lunar team, working
with Claude, turns accepted reports and ideas into code, tests and docs. A clear,
well-evidenced issue or Discussion is now the most useful contribution you can
make.

## Where things go

- **Bug reports** — a [new issue](https://github.com/lunarphp/lunar/issues/new/choose) using the bug form.
- **Feature requests and enhancements** — [Discussions](https://github.com/lunarphp/lunar/discussions/new/choose), not issues.
- **Documentation corrections** — a [new issue](https://github.com/lunarphp/lunar/issues/new/choose) naming the page and what is wrong or missing. Larger documentation ideas go in [Discussions](https://github.com/lunarphp/lunar/discussions/new/choose).
- **Usage questions and support** — [Discussions](https://github.com/lunarphp/lunar/discussions/new/choose) or [Discord](https://lunarphp.com/discord). Support questions filed as issues will be redirected.
- **Security vulnerabilities** — never a public issue. See [`SECURITY.md`](.github/SECURITY.md).

## Writing a good bug report

The bug form asks for the release line, the exact versions you're running, and
the steps to reproduce. Reports without reproduction steps usually can't be
actioned and will be closed after a fortnight without a response.

The more of these you can include, the faster a fix lands:

- **The smallest reproduction you can manage**, starting from a fresh install.
- **The full error message and stack trace**, if there was one.
- **A failing test**, pasted into the issue as a code block. A Pest test written
  against the monorepo's existing factories is the clearest possible description
  of a bug. See the Local Development page in the docs for setting up the
  monorepo to write one.
- **Which release lines are affected.** If you've confirmed the problem on both
  `1.x` and `2.x`, say so.
- **Where you think the problem is**, if you've traced it. Link the file and lines
  on GitHub.

## Proposing a feature or enhancement

Start a Discussion. Describe the problem you're trying to solve, not only the
solution you have in mind: how your store works today, what's missing, and what
you've tried. If a prototype or a branch in your own fork helps illustrate the
idea, link to it — it's useful context, but it won't be merged as-is.

If the Lunar team accepts the idea, we'll track it and implement it ourselves.

## Your fork and your branches

You're still free to fork Lunar, run it locally, and change it for your own
stores. You can't open a pull request from that fork, but you can link to a
commit or branch in it from an issue or Discussion to show exactly what you mean.
