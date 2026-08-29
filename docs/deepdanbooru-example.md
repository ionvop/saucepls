# Example of an endpoint response for DeepDanbooru

## Request

```bash
curl -X POST -F "image=@image.png" "https://deepdanbooru.nsk.sh/deepdanbooru?threshold=0.2"
```

## Response

```json
[
    {
        "tag": "headphones",
        "score": 0.9992985129356384
    },
    {
        "tag": "barefoot",
        "score": 0.9988350868225098
    },
    {
        "tag": "1girl",
        "score": 0.9987480044364929
    },
    {
        "tag": "cat_ear_headphones",
        "score": 0.9971675276756287
    },
    {
        "tag": "rating:safe",
        "score": 0.9965282082557678
    },
    {
        "tag": "smile",
        "score": 0.9698184728622437
    },
    {
        "tag": "solo",
        "score": 0.9612724781036377
    },
    {
        "tag": "long_hair",
        "score": 0.9390573501586914
    },
    {
        "tag": "blue_eyes",
        "score": 0.8364390134811401
    },
    {
        "tag": "cat_ears",
        "score": 0.7539623379707336
    },
    {
        "tag": "ahoge",
        "score": 0.7350295782089233
    },
    {
        "tag": "striped",
        "score": 0.6940770149230957
    },
    {
        "tag": "necktie",
        "score": 0.681418240070343
    },
    {
        "tag": "grin",
        "score": 0.6757827997207642
    },
    {
        "tag": "pillow",
        "score": 0.6692177057266235
    },
    {
        "tag": "pink_hair",
        "score": 0.6402966976165771
    },
    {
        "tag": "looking_at_viewer",
        "score": 0.6296774744987488
    },
    {
        "tag": "skirt",
        "score": 0.6049799919128418
    },
    {
        "tag": "animal_ears",
        "score": 0.5780071020126343
    },
    {
        "tag": "jacket",
        "score": 0.5585182309150696
    },
    {
        "tag": "school_uniform",
        "score": 0.48918187618255615
    },
    {
        "tag": "headset",
        "score": 0.4240936040878296
    },
    {
        "tag": "feet",
        "score": 0.4081912338733673
    },
    {
        "tag": "character_name",
        "score": 0.39435771107673645
    },
    {
        "tag": "sitting",
        "score": 0.38209268450737
    },
    {
        "tag": "bare_legs",
        "score": 0.33959513902664185
    },
    {
        "tag": "toes",
        "score": 0.31457585096359253
    },
    {
        "tag": "plaid",
        "score": 0.2817513346672058
    },
    {
        "tag": "blazer",
        "score": 0.2800956070423126
    },
    {
        "tag": "shirt",
        "score": 0.2453780174255371
    },
    {
        "tag": "album_cover",
        "score": 0.24235481023788452
    },
    {
        "tag": "red_necktie",
        "score": 0.22529679536819458
    },
    {
        "tag": "curtains",
        "score": 0.2030104100704193
    }
]
```