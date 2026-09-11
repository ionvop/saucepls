<p align="center">
  <strong>SaucePls</strong>
</p>

<p align="center">
  A community-driven platform for finding the source (<em>"sauce"</em>) of animanga images — artwork, cropped manga panels, and anime screenshots.
</p>

---

**SaucePls** is a social platform where people ask for the source of images they've found. Users post unknown images as *sauce requests*, and the community helps identify them by adding tags, commenting, and providing answers. The original poster (or a moderator) can then accept the correct sauce.

## Features

- **Passwordless authentication** — Sign in with an email one-time password (OTP) or Google OAuth. No passwords required.
- **Sauce requests** — Upload an unknown image and publish it as a request. Before posting, every image runs a four-step pipeline:
  1. **Perceptual hashing** — checks whether the image is a duplicate of an existing request.
  2. **SauceNAO reverse image search** — checks whether the image is easily identifiable.
  3. **OCR** — automatically extracts text from the image.
  4. **Tag inference** — a DeepDanbooru-style model auto-suggests tags.
- **Community tagging** — Add or remove tags on any request. Every change is logged and attributed to the user, with full history and restore support.
- **Editable extracted text** — The community can correct the OCR text, with history and restore support.
- **Answers** — Provide the sauce, like/unlike answers, and accept the correct one (accepted answers are pinned; sortable by likes or recency).
- **Comments** — Discuss on requests (one-level nesting), answers, and user profiles, each with like/unlike.
- **Bookmarks & follows** — Bookmark requests and follow users.
- **Subscription feed** — A feed of posts from the users you follow.
- **Notifications** — Six notification types: new answer, new answer comment, new request comment, new profile comment, answer accepted, and bookmarked request accepted.
- **Profiles** — Markdown bio, avatar, online status, accepted-answers score, and activity sections (requests, answers, bookmarks, comments).
- **NSFW handling** — Per-user `hide_nsfw` setting to filter explicit content.
- **Search** — Word-by-word matching with hyphen exclusion (`-kitty`) and type prefixes (`tag:`, `text:`).
- **Moderation** — Moderator and admin roles with timeouts, soft-deletes, and moderation logs.

## Tech Stack

- **PHP** ^8.3
- **Laravel** ^13.8
- **Blade** templates + Blade components
- **Tailwind CSS** v4
- **Alpine.js** ^3.15
- **Vite** ^8 (via `laravel-vite-plugin`)
- **SQLite** (default database)
- **Pest** ^5 for testing

## Requirements

- PHP ^8.3
- [Composer](https://getcomposer.org/)
- Node.js & npm

## Installation

1. Clone the repository and enter the project directory.
2. Run the setup script, which installs dependencies, copies `.env`, generates an app key, runs migrations, installs npm packages, and builds assets:

   ```bash
   composer setup
   ```

3. Configure the required environment variables in `.env` (see [Environment Variables](#environment-variables) below).

## Development

Run the development servers (Laravel dev server, queue worker, and Vite) concurrently:

```bash
composer dev
```

This runs `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev` together.

## Testing

Run the test suite (Pest):

```bash
composer test
```

Tests live in `tests/Feature/` and `tests/Unit/`.

## Environment Variables

| Variable | Description |
| --- | --- |
| `APP_NAME` | Application name (default `SaucePls`). |
| `APP_URL` | Application URL. |
| `DB_CONNECTION` | Database connection (default `sqlite`). |
| `BREVO_API_KEY` | Brevo transactional email API key, used to send email OTP codes. |
| `BREVO_FROM_ADDRESS` / `BREVO_FROM_NAME` | Sender address/name for OTP emails. |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Google OAuth credentials for "Sign in with Google". |
| `GOOGLE_REDIRECT_URI` | Must be `{APP_URL}/auth/google/callback`. |
| `SAUCENAO_API_KEY` | SauceNAO API key for reverse image search. |
| `SAUCENAO_MIN_SIMILARITY` | Minimum similarity threshold for SauceNAO matches (default `60`). |
| `OCR_SPACE_API_KEY` | OCR.space API key for text extraction. |
| `TAG_INFERENCE_ENDPOINT` | DeepDanbooru-style tag inference endpoint. |
| `TAG_INFERENCE_THRESHOLD` | Confidence threshold for suggested tags (default `0.5`). |
| `TAG_INFERENCE_MAX_TAGS` | Maximum number of auto-suggested tags. |
| `DRAFTS_TTL_HOURS` | Hours before an unpublished draft is purged (default `1`). |

## External Services

- **[Brevo](https://www.brevo.com/)** — transactional email for OTP codes.
- **[Google OAuth](https://console.cloud.google.com/apis/credentials)** — social login.
- **[SauceNAO](https://saucenao.com/)** — reverse image search.
- **[OCR.space](https://ocr.space/)** — cloud OCR for text extraction.
- **DeepDanbooru-style endpoint** — tag inference for auto-suggesting tags.

## Documentation

Additional design and reference documentation is available in the [`docs/`](docs/) directory:

- [`proposal.md`](docs/proposal.md) — project overview and concept.
- [`tech-stack.md`](docs/tech-stack.md) — technology guidelines.
- [`database-schema.md`](docs/database-schema.md) — database schema.
- [`saucenao-example.md`](docs/saucenao-example.md) — SauceNAO example.
- [`deepdanbooru-example.md`](docs/deepdanbooru-example.md) — tag inference example.

## License

SaucePls is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
