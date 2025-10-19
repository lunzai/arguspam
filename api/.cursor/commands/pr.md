# PR Generation Prompt

Please analyze my current git branch and generate a comprehensive PR description. Here's what I need:

1. **Analyze the current branch**: Get the branch name and compare it against the main/master branch
2. **Review all commits**: Read all commit messages on this branch and identify key themes
3. **Examine code changes**: Look at all modified, added, and deleted files to understand the scope
4. **Generate PR content** with these sections:
   - Clear, descriptive title based on the main theme
   - Overview of what this PR accomplishes
   - Detailed breakdown of changes (categorized by type: models, controllers, migrations, tests, etc.)
   - Technical implementation details
   - Any database migrations or schema changes
   - Testing information (unit tests, integration tests, etc.)
   - Breaking changes (if any)
   - Migration notes (if applicable)

Please be thorough and provide a professional, well-structured PR description that reviewers can easily understand.