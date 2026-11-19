# Project conventions

- Use PHP 8.5, Laravel 13, Livewire 4 and Tailwind CSS 4 APIs matching the lockfiles.
- Keep shared UI in resources/views/components/signal. Keep monitoring logic in app/Monitoring and example forms in their Livewire components.
- Configure branding, navigation and optional modules in config/template.php. Configure monitoring behavior in config/signal.php. Read environment values through config files.
- All workspace routes and Livewire updates require the existing access middleware. Demo access is restricted to local and testing environments. Scope monitoring records by the current user's environment grants.
- Keep credentials, .env files, database contents, logs and generated assets out of Git.
- Add focused regression tests for changes to authentication, access scoping and Livewire form/dialog state.
- Run composer lint, composer test, npm run lint and npm run build before committing.
- Run composer format when PHP formatting needs correction. Keep lockfiles in sync with dependency changes.
