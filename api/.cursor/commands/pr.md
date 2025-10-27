# PR Generation and Submission Prompt

Please analyze my current git branch, generate a comprehensive PR description, and submit the PR. Here's what I need:

## Step 1: Analyze the current branch
1. Get the branch name and compare it against the main/master branch
2. Review all commits on this branch and identify key themes
3. Examine code changes: Look at all modified, added, and deleted files to understand the scope

## Step 2: Generate PR content
Create a comprehensive PR description with these sections:
- Clear, descriptive title based on the main theme
- Overview of what this PR accomplishes
- Detailed breakdown of changes (categorized by type: models, controllers, migrations, tests, etc.)
- Technical implementation details
- Any database migrations or schema changes
- Testing information (unit tests, integration tests, etc.)
- Breaking changes (if any)
- Migration notes (if applicable)

## Step 3: Submit the PR
After generating the PR description, submit it using the following steps:

1. **Check if branch is pushed**: First verify the current branch is pushed to remote
   ```bash
   git push --set-upstream origin $(git branch --show-current)
   ```

2. **Determine the base branch**: Check if this is a fork or the main repository, and identify the appropriate base branch (typically `main` or `master`)

3. **Submit the PR using GitHub CLI**:
   - If available, use: `gh pr create --title "<generated-title>" --body-file <temp-file-with-description>`
   - Or alternatively: `gh pr create --title "<generated-title>" --body "$(cat <temp-file>)"`
   - Make sure to set the base branch appropriately

4. **Handle errors gracefully**: If the PR already exists, inform the user and provide a link to the existing PR

Please be thorough and provide a professional, well-structured PR description that reviewers can easily understand, then successfully submit the PR.