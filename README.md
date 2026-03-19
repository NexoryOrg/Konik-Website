# Konik Website

Konik Website is a multi-page PHP website about the wild horses in Germany's Black Forest National Park.
The project focuses on clear information architecture, easy navigation, and a maintainable structure with separate modules for content, uploads, uptime monitoring, and admin features.

## Project Goals

- Provide accessible information about the habitat, behavior, and protection of Konik horses
- Offer a clear page structure for visitors
- Maintain a reliable technical foundation for operations, monitoring, and future development

## Tech Stack

- PHP (server-side pages and endpoints)
- JavaScript (interactive frontend behavior)
- CSS (page-specific styling)
- JSON files for selected application data
- GitHub Actions for quality checks, notifications, and release workflows

## Project Structure (Excerpt)

- `public/home/` Homepage
- `public/gallery/` Gallery
- `public/history/` History section
- `public/contact/` Contact form
- `public/admin-panel/` Admin area including charts and password management
- `public/upload/` Upload logic
- `public/datenbank/` Data and file storage
- `logs/` Runtime and monitoring logs
- `.github/workflows/` CI/CD workflows

## Local Development

### Requirements

- PHP 8.1 or newer
- A local web server (for example Apache via XAMPP/WAMP)
- Write permissions for required data and log directories

## Security Notes

- Never store production secrets (for example webhooks or tokens) in the repository.
- Protect admin and data paths on the server side (authentication, file permissions, access restrictions).
- Prevent indexing of sensitive files and directories.

## CI/CD and Automation

Workflows in `.github/workflows/` support tasks such as:

- HTML/PHP validation
- Auto-assignment for issues and pull requests
- Discord notifications for repository activity
- Release automation

## Contributing

1. Create a branch for your change.
2. Commit your updates with clear commit messages.
3. Open a pull request.
4. Review workflow results and address feedback.

## License

Unless stated otherwise, the license stored in this repository applies.
