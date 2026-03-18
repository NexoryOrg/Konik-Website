# Contributing to Konik-Website

Thank you for contributing to the Konik-Website project! Please follow these guidelines to keep the repository clean and consistent.

---

## Branch Workflow

```
main
 └── testbereich
       ├── feature/your-feature-name
       └── fix/your-fix-name
```

1. **Never commit directly to `main`.**
2. Create your branch from `testbereich`:
   ```bash
   git checkout testbereich
   git pull origin testbereich
   git checkout -b feature/your-feature-name
   ```
3. Make your changes, then open a **Pull Request → `testbereich`**.
4. After successful review and testing in `testbereich`, the maintainer merges `testbereich` → `main`.
5. For **urgent production fixes**, create a `hotfix/*` branch from `main` and open a PR directly against `main`.

---

## Branch Naming Conventions

| Prefix       | Use case                                      | Example                     |
|--------------|-----------------------------------------------|-----------------------------|
| `feature/`   | New feature or enhancement                    | `feature/gallery-lightbox`  |
| `fix/`       | Bug fix in `testbereich`                      | `fix/contact-form-email`    |
| `hotfix/`    | Urgent fix applied directly to `main`         | `hotfix/security-patch`     |

---

## Commit Message Conventions

Use clear, descriptive commit messages. Recommended format:

```
<type>: <short summary>

[Optional longer description]
```

| Type       | When to use                              |
|------------|------------------------------------------|
| `feat`     | Adding a new feature                     |
| `fix`      | Fixing a bug                             |
| `docs`     | Documentation changes only               |
| `style`    | Formatting, whitespace (no logic change) |
| `refactor` | Code refactoring without feature change  |
| `chore`    | Maintenance tasks (CI, dependencies)     |

**Examples:**
```
feat: add lightbox to gallery page
fix: correct broken link in navbar
docs: update README with branch structure
```

---

## Pull Request Guidelines

- Describe **what** you changed and **why**
- Reference any related issue (e.g. `Closes #42`)
- Request at least **one review** before merging into `main`
- Make sure the `Proof HTML` workflow passes before merging

---

## Protected Branch Rules

| Branch        | Rules                                                        |
|---------------|--------------------------------------------------------------|
| `main`        | Requires 1 approving review · No direct pushes              |
| `testbereich` | Open for team pushes · Used as staging/integration branch   |
