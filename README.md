# 🐴 Konik-Website

A website about the wild Konik horses of the Black Forest National Park. The aim is to present information about the habitat, behaviour, and protection of these special animals in a clear and user-friendly way.

---

## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Branch Structure](#branch-structure)
- [CI/CD Workflows](#cicd-workflows)
- [Contributing](#contributing)

---

## About the Project

The Konik-Website provides visitors with information about the wild Konik horses living in the Black Forest National Park. It covers their natural habitat, behaviour patterns, and ongoing conservation efforts.

---

## Features

- **Home page** – Introduction and overview
- **Gallery** – Photo gallery of the Konik horses
- **History** – Historical background of the Konik breed
- **Contact** – Contact form for visitors
- **Imprint** – Legal imprint page
- **Admin Panel** – Password-protected administration dashboard
- **Upload** – File upload functionality

---

## Tech Stack

| Layer    | Technology              |
|----------|-------------------------|
| Backend  | PHP                     |
| Frontend | HTML, CSS, JavaScript   |
| Database | JSON-based data storage |

---

## Project Structure

```
public/
├── admin-panel/       # Administration dashboard
├── contact/           # Contact form (PHP, CSS, JS)
├── datenbank/         # Data storage (images, JSON)
├── footer/            # Shared footer component
├── gallery/           # Photo gallery
├── history/           # History page
├── home/              # Landing page (index)
├── imprint/           # Legal imprint
├── navebar/           # Shared navigation bar
└── upload/            # File upload handler
```

---

## Branch Structure

| Branch        | Purpose                                                       |
|---------------|---------------------------------------------------------------|
| `main`        | Production branch – protected, only accepts reviewed PRs      |
| `testbereich` | Test/staging branch – for testing changes before merging      |
| `feature/*`   | Feature branches – created from `testbereich` for new work    |
| `fix/*`       | Fix branches – created from `testbereich` for bug fixes       |
| `hotfix/*`    | Hotfix branches – created from `main` for urgent production fixes |

**Workflow:**
1. Create a `feature/*` or `fix/*` branch from `testbereich`
2. Open a Pull Request into `testbereich` when work is complete
3. After testing in `testbereich`, open a Pull Request into `main`

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full contribution guide.

---

## CI/CD Workflows

| Workflow                  | Trigger                        | Description                                      |
|---------------------------|--------------------------------|--------------------------------------------------|
| `proof-html.yml`          | Push, pull request, manual     | Validates HTML in the repository                 |
| `discord-notifications.yml` | Push to `main`/`testbereich`, PRs, Issues | Sends Discord notifications for repository events |
| `auto-assign.yml`         | Issue/PR opened                | Auto-assigns issues and PRs to the maintainer   |

---

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting a pull request.

- All pull requests must target `testbereich` (not `main` directly)
- The `main` branch is protected and requires at least one review before merging
- Write clear, descriptive commit messages
