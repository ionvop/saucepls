# SaucePls

## Introduction

We're building a social platform called SaucePls where people can ask for the source (or in slang, sauce) of the images they found, more specifically animanga-related whether it's an artwork, a cropped manga panel, or a screenshot of an anime episode.

- Users login/register with email OTP or Google OAuth. No passwords needed. Accounts are identified by email, so if a user logs in with a Google account that shares an email with an existing account, that existing account will be logged in.
- Users post unknown images as a sauce request.
- Before a sauce request is posted, it runs a four step process:
  - A reverse image search to check if there's already an existing sauce request for that image. (Perceptual Hashing will be used for this.)
    - If an existing sauce request is found, the user may view the existing sauce request or continue to the next step.
  - SauceNAO is run on the image to check if it's easily identifiable. (See `docs/saucenao-example.md`)
    - If SauceNAO finds a match, the user may view the result or continue to the next step.
  - The image is scanned with OCR to automatically extract text from the image.
  - The image is sent to an external API for model inference to automatically provide possible tags for the image. (See `docs/deepdanbooru-example.md`)
- The community may add missing tags or remove irrelevant tags, however these changes are logged and attributed to the user making the change incase of griefing or abuse.
- The community may comment for discussion or provide the sauce if they recognize the image.
- Original poster or a moderator can choose the accepted sauce.
- A tagging system to easily search for possibly solved sauce requests.

## Tech Stack

- Laravel 13
- SQLite3
- Tailwind CSS
- Alpine.js
- Laravel Blade Components
- Laravel Blade Icons (Lucide or Heroicons)
- Laravel Vite

## Database

This is the database schema that I currently have in mind: (See `docs/database-schema.md`)

## Tags field processing

Tags are separated by spaces.
Tags must be unique. (Duplicates will be ignored.)
Tags must be lowercase. (Uppercase will be converted to lowercase.)
Tags must only contain alphanumeric, hyphens, and underscores. (Any other characters will be ignored.)
Tags must not start with a hyphen. (These hyphens will be ignored. Reason is they are used for exclusions in searches.)

## User types

### Admin

- Same privileges as a moderator.
- Simply an indicator that they have internal access of the system and the database.

### Moderator

- Can time out users.
- Can soft delete sauce requests, answers, and comments.
- Can accept sauce answers regardless if they're the original poster or not.

### Member

- Can't do what moderators can.

## Notifications

A user may receive a notification if:

- Someone provides an answer to your sauce request.
- Someone comments on your sauce request.
- Someone comments on your profile.
- A sauce request you liked has accepted an answer.
- Someone mentioned you in a comment.
- Other things that I haven't thought of yet.

## Searching

A search entry is parsed word by word and filters in posts that contain all of those words. Each word assumes that it could be a part of the title, description, text, or tags.

```sql
WHERE CONCAT(title, description, text, tags) LIKE "%:word%"
```

Exclude results containing a word by prefixing it with a hyphen. e.g. `coconut -kitty`.

Explicitly search by prefixing a word with its type. e.g. `tag:1girl text:"coconut doggy" -text:"kitty"`.

## Themes

Primary color: #5555AA

Background color: #111111

You may adjust to what you think is best.

### Desktop

- Left navigation panel
- Header
- Main content

### Mobile

- Header
- Main content
- Bottom navigation bar

## Profile page

- Profile picture
- Username
- Markdown-supported bio
- Online status (inferred from last seen)
- Following / Followers
- User type
- Main profile score: number of accepted sauce answers
- Visitor
  - Follow/unfollow button
  - Report button
- Owner
  - Edit profile button
- Sauce requests by the user
- Sauce answers by the user
- Sauce requests liked by the user
- Profile comments