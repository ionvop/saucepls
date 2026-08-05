# Tech Stack

- You are building a Laravel application using Blade templates.
- Use Blade components for reusable UI.
- Use Laravel's routing, controllers, validation, authentication, and session features.
- Use Tailwind CSS for all styling.
- Use Vite for asset compilation.
- Use Alpine.js whenever client-side interactivity is needed. Prefer Alpine over larger JavaScript frameworks unless the feature genuinely requires one.
- Keep JavaScript minimal and progressively enhance server-rendered pages.

Recommended packages and libraries:

- Tailwind CSS
- Alpine.js
- Laravel Blade Components
- Laravel Blade Icons (Lucide or Heroicons)
- Laravel Vite

Guidelines:

- Create reusable Blade components in `resources/views/components`.
- Extend layouts using Blade layouts and sections.
- Use Laravel form validation with `@error` and `old()`.
- Prefer server-side rendering over AJAX unless there is a clear UX benefit.
- Use RESTful routes in `routes/web.php`.
- Follow Laravel conventions instead of recreating SPA patterns.