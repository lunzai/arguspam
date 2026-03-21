# PR from Changes Skill

Generates PR descriptions from git changes, branch name, and commit messages.

## How to use

Ask in chat with phrases like:

- **"Write a PR"** / **"Create a PR description"**
- **"Draft PR from my changes"**
- **"Generate PR content for this branch"**

The agent will use your current branch, gather the diff and commits, and output a structured PR description. Copy it into GitHub when opening the PR.

## Requirements

- You must be on a feature branch with commits ahead of `master` (or `main`)
- Changes should be committed (staged changes are not included)
