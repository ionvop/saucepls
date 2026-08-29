```
users
id: int, pk
username: str, unique
email: str, unique
description: str, default = "# About Me\n\nThis user has not written a bio yet." // A Markdown-supported bio of the user.
type: str, default = "member", enum = ["member", "moderator", "admin"] // The type of user.
remember_token: str, default = null // Laravel's "remember me" persistent token.
avatar_path: str, default = null // The path to the user's avatar image.
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
is_explicit: bool, default = true // Whether the image contains explicit content.
deleted_at: datetime, default = null
created_at: datetime
updated_at: datetime

tags
id: int, pk
name: str, unique // The name of the tag. e.g. "1girl", "black_hair", "red_eyes", etc.
description: str, default = "A wiki has not been written for this tag yet." // A Markdown-supported wiki entry of the tag.
created_at: datetime
updated_at: datetime

sauce_request_bookmarks
id: int, pk
sauce_request_id: int, fk = sauce_requests.id
user_id: int, fk = users.id // The user who bookmarked the sauce request to track its progress.
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
// I'll let you handle how tagging is recorded.
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

sauce_request_comment_likes
id: int, pk
sauce_request_comment_id: int, fk = sauce_request_comments.id
user_id: int, fk = users.id
created_at: datetime
updated_at: datetime
unique(sauce_request_comment_id, user_id)

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

user_comment_likes
id: int, pk
user_comment_id: int, fk = user_comments.id
user_id: int, fk = users.id
created_at: datetime
updated_at: datetime
unique(user_comment_id, user_id)

user_follows
id: int, pk
user_id: int, fk = users.id // The user who is following another user.
target_user_id: int, fk = users.id // The user being followed.
created_at: datetime
updated_at: datetime
unique(user_id, target_user_id)

moderation_logs
id: int, pk
user_id: int, fk = users.id // The moderator who performed the action.
action_type: str, enum = ["delete", "timeout", "restore"] // The type of action performed.
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
category: str, enum = ["spam", "harassment", "other"] // The category of the report.
reason: str // The reason for the report.
details: json // Additional details about the report such as the screenshot, etc.
status: str, enum = ["pending", "resolved", "dismissed"] // The status of the report.
resolved_by: int, fk = users.id, default = null // The user who resolved the report.
resolved_at: datetime, default = null
created_at: datetime
updated_at: datetime

notifications
// use Laravel's native notifications table
```