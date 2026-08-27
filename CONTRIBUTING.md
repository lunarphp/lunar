# Contributing to Lunar

Thanks for wanting to contribute. Lunar is used in production by real stores, so
we hold a high bar on changes to the core packages. This document explains how we
handle contributions so you don't waste effort on work we can't merge.

## Where things go

- **Bug reports** — a [new issue](https://github.com/lunarphp/lunar/issues/new/choose) using the bug form.
- **Usage questions and support** — [Discussions](https://github.com/lunarphp/lunar/discussions/new/choose) or [Discord](https://lunarphp.com/discord). Support questions filed as issues will be redirected.
- **Feature requests and enhancements** — [Discussions](https://github.com/lunarphp/lunar/discussions/new/choose), not issues.

## Before you open a pull request

### Non-trivial changes need acceptance first

If your change is anything more than a typo, a doc fix, or a small isolated bug
fix, get it accepted before you write the code:

- **Bug fixes** — open an issue with the bug form and wait for a maintainer to
  confirm it.
- **Features and enhancements** — open a
  [Discussion](https://github.com/lunarphp/lunar/discussions/new/choose) and wait
  for a maintainer to accept the direction. Feature requests are not tracked as
  issues in this repo.

Link the issue or Discussion in your PR description.

PRs that arrive without an accepted issue or Discussion may be closed without a
full review.
This isn't gatekeeping — it exists so you don't spend a weekend on an approach we
were always going to say no to. A short issue costs you ten minutes and can save
you a lot more than that.

### Target the right branch

| Your change | Base branch |
| --- | --- |
| Bug fix or improvement for the current stable release | `1.x` |
| Work for the next major version | `2.x` |

A bot labels every PR `1.x` or `2.x` based purely on the branch you targeted. It
does not read your description, so if you target the wrong branch the label will
be wrong and we'll ask you to retarget. If your fix applies to both lines, say so
in the description — we track that with a `needs-forward-port` label.

### One concern per pull request

Bundling an unrelated refactor, a formatting sweep, or your IDE config into a bug
fix makes the change much harder to review and much slower to merge. Keep the diff
to the thing you set out to change.

Don't worry about code style. Pint runs automatically and PHPStan runs in CI, so
there's no need to hand-format anything or to correct style in files you're not
otherwise touching.

### Tests

Changes to behaviour need tests. Cover the failure path, not only the happy path,
and use the existing factories and test helpers rather than building new
scaffolding alongside them.

## Open pull request limit

Please keep to a maximum of **5 open pull requests** at a time.

Draft PRs count toward that limit. Opening work as a draft doesn't create extra
allowance, and it doesn't get you an earlier review — see below.

## How your pull request gets reviewed

### An automated review runs first

When you mark a PR as ready for review, an automated reviewer reads the diff
against our review standards and posts a single summary comment: what the change
touches, which of our review criteria it passes or fails, and any questions it
couldn't answer from the diff alone.

Two things to know about it:

- **It's advisory.** It cannot approve, merge, close, or reject anything. A human
  makes every decision. If you think it's wrong, say so in a reply — you may well
  be right.
- **We expect you to address its findings before a maintainer looks.** Working
  through them first is the single fastest way to get a human review.

Draft PRs are labelled but not reviewed. Mark the PR ready when you want the
review to run.

### Review priority

Every PR gets a risk tier, which determines who picks it up:

| Tier | What it means | Reviewed by |
| --- | --- | --- |
| `trivial` | Docs, comments, typos, changelog. No code paths touched. | The wider team |
| `low` | Isolated bug fix, single file or class, has tests, no public API change. | The wider team |
| `medium` | New feature in a contained area, a migration, a new config option, several files in one package. | Maintainer |
| `high` | Core models, ERP integration surfaces, payment or fulfilment logic, cart/order state transitions, public API contracts, or anything that could break existing consumers. | Maintainer |

Higher tiers get more scrutiny and therefore take longer. A `high` tier label
isn't a criticism of your change — it means the change is in a place where a
mistake is expensive.

## A note on generated code

We have no objection to you using AI tooling. We do care about the result. In
practice the PRs we end up closing tend to share the same signals: a large diff
with no tests and a generic description, docblocks added to every trivial method
in a style unlike the surrounding code, or defensive `try`/`catch` blocks that
don't match how the rest of the codebase handles failure.

If you can't explain why each change in your diff is there, it isn't ready for
review yet.
