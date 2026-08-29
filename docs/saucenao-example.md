# Example of an endpoint response for SauceNao

## Request

```bash
curl -X POST "https://saucenao.com/search.php" -F "api_key=YOUR_API_KEY" -F "output_type=2" -F "numres=5" -F "file=@hiraken.png"
```

## Response

```json
{
    "header": {
        "user_id": "35974",
        "account_type": "1",
        "short_limit": "4",
        "long_limit": "100",
        "long_remaining": 99,
        "short_remaining": 3,
        "status": 0,
        "results_requested": "8",
        "index": {
            "0": {
                "status": 0,
                "parent_id": 0,
                "id": 0,
                "results": 8
            },
            "2": {
                "status": 0,
                "parent_id": 2,
                "id": 2,
                "results": 8
            },
            "5": {
                "status": 0,
                "parent_id": 5,
                "id": 5,
                "results": 8
            },
            "51": {
                "status": 0,
                "parent_id": 5,
                "id": 51,
                "results": 8
            },
            "52": {
                "status": 0,
                "parent_id": 5,
                "id": 52,
                "results": 8
            },
            "53": {
                "status": 0,
                "parent_id": 5,
                "id": 53,
                "results": 8
            },
            "6": {
                "status": 0,
                "parent_id": 6,
                "id": 6,
                "results": 8
            },
            "8": {
                "status": 0,
                "parent_id": 8,
                "id": 8,
                "results": 8
            },
            "9": {
                "status": 0,
                "parent_id": 9,
                "id": 9,
                "results": 16
            },
            "10": {
                "status": 0,
                "parent_id": 10,
                "id": 10,
                "results": 8
            },
            "11": {
                "status": 0,
                "parent_id": 11,
                "id": 11,
                "results": 8
            },
            "12": {
                "status": 0,
                "parent_id": 9,
                "id": 12,
                "results": 16
            },
            "16": {
                "status": 0,
                "parent_id": 16,
                "id": 16,
                "results": 8
            },
            "18": {
                "status": 0,
                "parent_id": 18,
                "id": 18,
                "results": 8
            },
            "19": {
                "status": 0,
                "parent_id": 19,
                "id": 19,
                "results": 8
            },
            "20": {
                "status": 0,
                "parent_id": 20,
                "id": 20,
                "results": 8
            },
            "21": {
                "status": 0,
                "parent_id": 21,
                "id": 21,
                "results": 8
            },
            "211": {
                "status": 0,
                "parent_id": 21,
                "id": 211,
                "results": 8
            },
            "22": {
                "status": 0,
                "parent_id": 22,
                "id": 22,
                "results": 8
            },
            "23": {
                "status": 0,
                "parent_id": 23,
                "id": 23,
                "results": 8
            },
            "24": {
                "status": 0,
                "parent_id": 24,
                "id": 24,
                "results": 8
            },
            "25": {
                "status": 0,
                "parent_id": 9,
                "id": 25,
                "results": 16
            },
            "26": {
                "status": 0,
                "parent_id": 9,
                "id": 26,
                "results": 16
            },
            "27": {
                "status": 0,
                "parent_id": 9,
                "id": 27,
                "results": 16
            },
            "28": {
                "status": 0,
                "parent_id": 9,
                "id": 28,
                "results": 16
            },
            "29": {
                "status": 0,
                "parent_id": 9,
                "id": 29,
                "results": 16
            },
            "30": {
                "status": 0,
                "parent_id": 9,
                "id": 30,
                "results": 16
            },
            "31": {
                "status": 0,
                "parent_id": 31,
                "id": 31,
                "results": 8
            },
            "32": {
                "status": 0,
                "parent_id": 32,
                "id": 32,
                "results": 8
            },
            "33": {
                "status": 0,
                "parent_id": 33,
                "id": 33,
                "results": 8
            },
            "34": {
                "status": 0,
                "parent_id": 34,
                "id": 34,
                "results": 8
            },
            "341": {
                "status": 0,
                "parent_id": 341,
                "id": 341,
                "results": 8
            },
            "35": {
                "status": 0,
                "parent_id": 35,
                "id": 35,
                "results": 8
            },
            "36": {
                "status": 0,
                "parent_id": 36,
                "id": 36,
                "results": 8
            },
            "37": {
                "status": 0,
                "parent_id": 37,
                "id": 37,
                "results": 8
            },
            "371": {
                "status": 0,
                "parent_id": 371,
                "id": 371,
                "results": 8
            },
            "38": {
                "status": 0,
                "parent_id": 38,
                "id": 38,
                "results": 8
            },
            "39": {
                "status": 0,
                "parent_id": 39,
                "id": 39,
                "results": 8
            },
            "40": {
                "status": 0,
                "parent_id": 40,
                "id": 40,
                "results": 8
            },
            "41": {
                "status": 0,
                "parent_id": 41,
                "id": 41,
                "results": 8
            },
            "42": {
                "status": 0,
                "parent_id": 42,
                "id": 42,
                "results": 8
            },
            "43": {
                "status": 0,
                "parent_id": 43,
                "id": 43,
                "results": 8
            },
            "44": {
                "status": 0,
                "parent_id": 44,
                "id": 44,
                "results": 8
            }
        },
        "search_depth": "128",
        "minimum_similarity": 33.81,
        "query_image_display": "\/userdata\/MKLX2yGnV.png.png",
        "query_image": "MKLX2yGnV.png",
        "results_returned": 8
    },
    "results": [
        {
            "header": {
                "similarity": "92.77",
                "thumbnail": "https:\/\/img1.saucenao.com\/res\/pixiv\/10342\/103420163_p0_master1200.jpg?auth=nS6texnj09YYBE2EazzKNg\u0026exp=1788289200",
                "index_id": 5,
                "index_name": "Index #5: Pixiv Images - 103420163_p0_master1200.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "ext_urls": [
                    "https:\/\/www.pixiv.net\/member_illust.php?mode=medium\u0026illust_id=103420163"
                ],
                "title": "\u73e0\u624b \u8a95",
                "pixiv_id": 103420163,
                "member_name": "\u3072\u3089\u3051\u3093",
                "member_id": 1093660
            }
        },
        {
            "header": {
                "similarity": "92.60",
                "thumbnail": "https:\/\/img3.saucenao.com\/booru\/b\/4\/b453ab72dcb99c0885f23f86f0274021_0.jpg?auth=jV3TOGl1Kv-ZebFmyyBNTA\u0026exp=1788289200",
                "index_id": 9,
                "index_name": "Index #9: Danbooru - b453ab72dcb99c0885f23f86f0274021_0.jpg",
                "dupes": 1,
                "hidden": 0
            },
            "data": {
                "ext_urls": [
                    "https:\/\/danbooru.donmai.us\/post\/show\/5880914",
                    "https:\/\/gelbooru.com\/index.php?page=post\u0026s=view\u0026id=8001106"
                ],
                "danbooru_id": 5880914,
                "gelbooru_id": 8001106,
                "creator": "hiraken",
                "material": "bang dream!",
                "characters": "tamade chiyu",
                "source": "https:\/\/i.pximg.net\/img-original\/img\/2022\/12\/07\/21\/46\/33\/103420163"
            }
        },
        {
            "header": {
                "similarity": "32.81",
                "thumbnail": "https:\/\/img1.saucenao.com\/res\/pixiv\/8251\/82516267_p0_master1200.jpg?auth=YbZvJIsckuBknJL8bmNrtg\u0026exp=1788289200",
                "index_id": 5,
                "index_name": "Index #5: Pixiv Images - 82516267_p0_master1200.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "ext_urls": [
                    "https:\/\/www.pixiv.net\/member_illust.php?mode=medium\u0026illust_id=82516267"
                ],
                "title": "Lilly adventures 3",
                "pixiv_id": 82516267,
                "member_name": "cardium",
                "member_id": 20583887
            }
        },
        {
            "header": {
                "similarity": "32.50",
                "thumbnail": "https:\/\/img1.saucenao.com\/res\/nhentai\/131940%20%28805315%29%20--%20%28C87%29%20%5BMataro%20%28Mataro%29%5D%20Moshimo%20Minami%20Kotori%20ga%20Kanojo%20Dattara%20%28Love%20Live%21%29\/15.jpg?auth=MDODpCct0IJzBeverxnaBQ\u0026exp=1788289200",
                "index_id": 18,
                "index_name": "Index #18: H-Misc (nhentai) - 15.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "source": "Moshimo Minami Kotori ga Kanojo Dattara",
                "creator": [
                    "mataro",
                    "mataro"
                ],
                "eng_name": "(C87) [Mataro (Mataro)] Moshimo Minami Kotori ga Kanojo Dattara (Love Live!)",
                "jp_name": "(C87) [\u9b54\u592a\u90ce (\u9b54\u592a\u90ce)] \u3082\u3057\u3082\u5357\u3053\u3068\u308a\u304c\u5f7c\u5973\u3060\u3063\u305f\u3089 (\u30e9\u30d6\u30e9\u30a4\u30d6!)"
            }
        },
        {
            "header": {
                "similarity": "32.25",
                "thumbnail": "https:\/\/img3.saucenao.com\/skeb\/258\/258105-616689-ssohsn%2Bworks%2B44-0.jpg?auth=mjK-xM_u8yQ3IW3bxVtiJA\u0026exp=1788289200",
                "index_id": 44,
                "index_name": "Index #44: Skeb - 258105-616689-ssohsn+works+44-0.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "ext_urls": [
                    "https:\/\/skeb.jp\/\/@ssohsn\/works\/44"
                ],
                "path": "\/@ssohsn\/works\/44",
                "creator": "@ssohsn",
                "creator_name": "\u306e\u307b\u3057\u304a\ud83d\udcab",
                "author_name": null,
                "author_url": "https:\/\/skeb.jp\/@ssohsn"
            }
        },
        {
            "header": {
                "similarity": "31.73",
                "thumbnail": "https:\/\/img3.saucenao.com\/dA\/18881\/188813615.jpg?auth=oUU5l-mugufVWpBanR8WQA\u0026exp=1788289200",
                "index_id": 34,
                "index_name": "Index #34: deviantArt - 188813615.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "ext_urls": [
                    "https:\/\/deviantart.com\/view\/188813615"
                ],
                "title": "Respect",
                "da_id": "188813615",
                "author_name": "darthfury02",
                "author_url": "http:\/\/darthfury02.deviantart.com"
            }
        },
        {
            "header": {
                "similarity": "31.45",
                "thumbnail": "https:\/\/img3.saucenao.com\/ehentai\/37\/fa\/37faba31a4b9e52329e13bc2d2ebb498df599825.jpg?auth=z-DL0Hqc01euRHJNEThpMw\u0026exp=1788289200",
                "index_id": 38,
                "index_name": "Index #38: H-Misc (E-Hentai) - 37faba31a4b9e52329e13bc2d2ebb498df599825.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "source": "Night Shifter",
                "creator": [
                    "four-nine"
                ],
                "eng_name": "[Fournine] Night Shifter",
                "jp_name": "[\u30d5\u30a9\u30a2\u30fb\u30ca\u30a4\u30f3] \u30ca\u30a4\u30c8\u30b7\u30d5\u30bf\u30fc"
            }
        },
        {
            "header": {
                "similarity": "31.42",
                "thumbnail": "https:\/\/img1.saucenao.com\/res\/nhentai\/255813%20%281328500%29%20--%20Yuina%20_%20kuon%28sengoku%20koihime%29\/1.jpg?auth=CSHOTtrxfJzcLIBbfIOnRA\u0026exp=1788289200",
                "index_id": 18,
                "index_name": "Index #18: H-Misc (nhentai) - 1.jpg",
                "dupes": 0,
                "hidden": 0
            },
            "data": {
                "source": "Yuina _ kuon(sengoku koihime)",
                "creator": [
                    "yasu rintarou",
                    "yasrin-do"
                ],
                "eng_name": "Yuina _ kuon(sengoku koihime)",
                "jp_name": "\u591c\u8776\uff0a\u6c38\u9060(\u6226\u56fd\u604b\u59eb\uff09"
            }
        }
    ]
}
```