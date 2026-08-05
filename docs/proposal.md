# SaucePls

## Introduction

I'm building a social platform called SaucePls where people can ask for the source (or in slang, sauce) of the images they found, more specifically animanga-related whether it's an artwork, a cropped manga panel, or a screenshot of an anime episode.

- Users login/register with email OTP or Google OAuth. No passwords needed. Accounts are identified by email, so if a user logs in with a Google account that shares an email with an existing account, that existing account will be logged in.
- Users post unknown images as a sauce request.
- Before a sauce request is posted, it runs a four step process:
  - A reverse image search to check if there's already an existing sauce request for that image. (Perceptual Hashing will be used for this.)
    - If an existing sauce request is found, the user may view the existing sauce request or continue to the next step.
  - SauceNAO is run on the image to check if it's easily identifiable. (I'll be using `ClarityCafe/Sagiri` for this.)
    - If SauceNAO finds a match, the user may view the result or continue to the next step.
  - The image is scanned with OCR to automatically extract text from the image.
  - The image is sent to an external API for model inference to automatically provide possible tags for the image. (I'll be using `SmilingWolf/wd-swinv2-tagger-v3` for this running on a Huggingface Space.)
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

This is the database schema that I currently have in mind:

```
users
id: int, pk
username: str, unique
email: str, unique
description: str, default = "# About Me\n\nThis user has not written a bio yet." // A Markdown-supported bio of the user.
type: str, default = "member", enum = ["member", "moderator", "admin"] // The type of user.
remember_token: str, default = null // Laravel's "remember me" persistent token.
last_seen_at: datetime, default = null // The last time the user was seen online.
banned_until: datetime, default = null // The future time in which the user will be unbanned.
deleted_at: datetime, default = null // Soft delete indicator.
created_at: datetime
updated_at: datetime

email_codes
id: int, pk
email: str // The email being verified.
code_hash: str // The hash of the code.
created_at: datetime // If time exceeds 5 minutes, the code will be deleted.
updated_at: datetime

sauce_requests
id: int, pk
user_id: int, fk = users.id
title: str, default = "Sauce pls" // The title of the sauce request. e.g. "Who drew this?"
description: str, default = "" // Additional context for the image. e.g. "I found this on Discord and it looks so cute."
text: str, default = "" // The text extracted from the image if it contains any. e.g. "capybara ?! capybara ! !! ! coconute doggy o my gosh"
image_path: str // The path to the image file.
accepted_sauce: int, fk = sauce_answers.id, default = null // The accepted sauce.
phash64: str // Used if someone tries to upload a duplicate request to an already existing sauce request.
is_explicit: bool, default = false // Whether the image contains explicit content.
deleted_at: datetime, default = null
created_at: datetime
updated_at: datetime

tags
id: int, pk
name: str, unique // The name of the tag. e.g. "1girl", "black_hair", "red_eyes", etc.
description: str, default = "A wiki has not been written for this tag yet." // A Markdown-supported wiki entry of the tag.
created_at: datetime
updated_at: datetime

sauce_request_likes
id: int, pk
sauce_request_id: int, fk = sauce_requests.id
user_id: int, fk = users.id
created_at: datetime
updated_at: datetime
unique(sauce_request_id, user_id)

sauce_request_tags
id: int, pk
sauce_request_id: int, fk = sauce_requests.id
tag_id: int, fk = tags.id
created_at: datetime
updated_at: datetime
unique(sauce_request_id, tag_id)

sauce_request_tagging_history
id: int, pk
sauce_request_id: int, fk = sauce_requests.id
user_id: int, fk = users.id // The user who made the change.
before_tags: str // The tags before the change. Used to revert changes. e.g. "1girl red_eyes"
after_tags: str // The tags after the change. Used to check for griefing or abuse. e.g. "1girl black_hair red_eyes"
created_at: datetime
updated_at: datetime

sauce_request_comments
id: int, pk
sauce_request_id: int, fk = sauce_requests.id
user_id: int, fk = users.id
content: str // e.g. "This looks like the artstyle of Snale on Twitter."
deleted_at: datetime, default = null
created_at: datetime
updated_at: datetime

sauce_answers
id: int, pk
sauce_request_id: int, fk = sauce_requests.id
user_id: int, fk = users.id
content: str // e.g. "Artist is Snale."
url: str // Link to the source if applicable such as Pixiv, Twitter, etc. e.g. "https://x.com/04119__snail/status/1414620876159418370". Not applicable for manga panels, anime screenshots, etc. since links to possible piracy sites are not allowed.
deleted_at: datetime, default = null
created_at: datetime
updated_at: datetime

sauce_answer_likes
id: int, pk
sauce_answer_id: int, fk = sauce_answers.id
user_id: int, fk = users.id
created_at: datetime
updated_at: datetime
unique(sauce_answer_id, user_id)

user_comments
id: int, pk
user_id: int, fk = users.id // The user who made the comment.
target_user_id: int, fk = users.id // The user being commented on.
content: str // e.g. "Thank you for your service, sauceman."
deleted_at: datetime, default = null
created_at: datetime
updated_at: datetime

user_follows
id: int, pk
user_id: int, fk = users.id // The user who is following another user.
target_user_id: int, fk = users.id // The user being followed.
created_at: datetime
updated_at: datetime
unique(user_id, target_user_id)

notifications
id: int, pk
user_id: int, fk = users.id
content: str // e.g. "Your sauce request has received an answer! Check if it's correct.", "John Doe has followed you.", etc.
type: str // Used for icon e.g. "sauce_answer", "follow", etc.
url: str // Where the user will be redirected to if they click on the notification.
read_at: datetime, default = null
created_at: datetime
updated_at: datetime

moderation_logs
id: int, pk
user_id: int, fk = users.id // The moderator who performed the action.
action_type: str, enum = ["accept", "delete", "timeout"] // The type of action performed.
target_type: str, enum = ["user", "sauce_request", "sauce_answer", "sauce_comment", "user_comment"] // The type of target.
target_id: int // The ID of the target.
details: json // Additional details about the action such as the reason, timeout duration, etc.
created_at: datetime
updated_at: datetime

user_reports
id: int, pk
user_id: int, fk = users.id // The user who reported an issue.
target_type: str, enum = ["user", "sauce_request", "sauce_answer", "sauce_comment", "user_comment"] // The type of target.
target_id: int // The ID of the target.
reason: str // The reason for the report.
created_at: datetime
updated_at: datetime
```

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

Exclude results by prefixing a word with a hyphen.

Explicitly search by prefixing a word with its type. e.g. `tag:1girl text:"coconute doggy"`.

## Themes

Primary color: #5555AA
Background color: #111111

You may adjust to what you think is best.