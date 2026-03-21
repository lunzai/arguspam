---
name: pr-from-changes
description: Generates pull request descriptions from git changes, branch name, and commit messages. Use when the user asks to write a PR, create a PR description, or draft PR content from current branch changes.
---

# PR Description from Changes

## Purpose

Generates a structured PR description by synthesizing:
- **Git diff** – `git diff master..HEAD` (or `main`)
- **Branch name** – `git branch --show-current`
- **Commit messages** – `git log master..HEAD --pretty=format:"%s%n%b"` (raw notes)

## Workflow

### 1. Gather inputs

```bash
git branch --show-current
git log master..HEAD --pretty=format:"%s%n%b---"
git diff master..HEAD --stat
```

Use `main` instead of `master` if that is the base branch.

### 2. Generate content

Synthesize a PR description using:

- **Summary** – From branch name + commit messages
- **Changes** – From `git diff --stat` grouped by directory (exclude `composer.lock`):
  - `api/` → API
  - `web/` → Web (Svelte)
  - `database/` or `*migration*` → Database / Migrations
  - `tests/`, `*Test.php` → Tests
- **Testing** – Only if there is concrete info (changed test files, explicit commit notes about testing). Do not assume or invent testing steps.
- **Notes** – Breaking changes, migration steps, follow-ups. Omit if none.

### 3. Output format

Use this template; omit any section that has no information:

```markdown
## Summary
[1–2 sentences: what this PR does and why]

## Changes

### API
- 

### Web (Svelte)
- 

### Database / Migrations
- 

### Tests
- 

## Testing
[Only include if concrete info exists: test file changes or explicit commit notes]

## Notes
[Only include if breaking changes, migration steps, or follow-ups exist]
```

Rules:
- Remove empty sections entirely. Do not output `## Testing` or `### Tests` if nothing was found.
- Exclude `composer.lock` from change analysis.
- Be specific; derive bullets from actual file changes, not generic statements.
- Do not assume or invent testing steps.
