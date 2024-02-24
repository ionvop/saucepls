<?php

include("common.php");
Debug();

?>

<html>
    <head>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            .popular {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .popular__likes {
                padding: 1rem;
            }

            .popular__likes__title {
                padding: 1rem;
                font-size: 2rem;
            }

            .popular__trending {
                padding: 1rem;
            }

            .popular__trending__title {
                padding: 1rem;
                font-size: 2rem;
            }

            .recent {
                padding: 1rem;
            }

            .recent__title {
                padding: 1rem;
                font-size: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="main">
            <?=SetHeader()?>
            <div class="popular">
                <div class="popular__likes">
                    <div class="popular__likes__title -center">
                        Popular unsolved:
                    </div>
                    <div class="-posts__render">
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--solved -center--flex">
                                    <span class="material-symbols-rounded">
                                        done
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--unsolved -center--flex">
                                    <span class="material-symbols-rounded">
                                        close
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--solved -center--flex">
                                    <span class="material-symbols-rounded">
                                        done
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--unsolved -center--flex">
                                    <span class="material-symbols-rounded">
                                        close
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--solved -center--flex">
                                    <span class="material-symbols-rounded">
                                        done
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="popular__likes__more -center">
                        <button class="-button">
                            Show more
                        </button>
                    </div>
                </div>
                <div class="popular__trending">
                    <div class="popular__trending__title -center">
                        Trending unsolved:
                    </div>
                    <div class="-posts__render">
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--solved -center--flex">
                                    <span class="material-symbols-rounded">
                                        done
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--unsolved -center--flex">
                                    <span class="material-symbols-rounded">
                                        close
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--solved -center--flex">
                                    <span class="material-symbols-rounded">
                                        done
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--unsolved -center--flex">
                                    <span class="material-symbols-rounded">
                                        close
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="item__container">
                                <div class="item__image">
                                    <img src="uploads/default.jpg">
                                </div>
                                <div class="item__info">
                                    <div class="item__info__text">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__tags">
                                        The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                    </div>
                                    <div class="item__info__details">
                                        2024-02-23 | Uploaded by: ionvop
                                    </div>
                                </div>
                                <div class="item__status--solved -center--flex">
                                    <span class="material-symbols-rounded">
                                        done
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="popular__trending__more -center">
                        <button class="-button">
                            Show more
                        </button>
                    </div>
                </div>
            </div>
            <div class="recent">
                <div class="recent__title -center">
                    Recent posts:
                </div>
                <div class="-posts__render">
                    <div class="item">
                        <div class="item__container">
                            <div class="item__image">
                                <img src="uploads/default.jpg">
                            </div>
                            <div class="item__info">
                                <div class="item__info__text">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__tags">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__details">
                                    2024-02-23 | Uploaded by: ionvop
                                </div>
                            </div>
                            <div class="item__status--solved -center--flex">
                                <span class="material-symbols-rounded">
                                    done
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__container">
                            <div class="item__image">
                                <img src="uploads/default.jpg">
                            </div>
                            <div class="item__info">
                                <div class="item__info__text">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__tags">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__details">
                                    2024-02-23 | Uploaded by: ionvop
                                </div>
                            </div>
                            <div class="item__status--unsolved -center--flex">
                                <span class="material-symbols-rounded">
                                    close
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__container">
                            <div class="item__image">
                                <img src="uploads/default.jpg">
                            </div>
                            <div class="item__info">
                                <div class="item__info__text">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__tags">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__details">
                                    2024-02-23 | Uploaded by: ionvop
                                </div>
                            </div>
                            <div class="item__status--solved -center--flex">
                                <span class="material-symbols-rounded">
                                    done
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__container">
                            <div class="item__image">
                                <img src="uploads/default.jpg">
                            </div>
                            <div class="item__info">
                                <div class="item__info__text">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__tags">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__details">
                                    2024-02-23 | Uploaded by: ionvop
                                </div>
                            </div>
                            <div class="item__status--unsolved -center--flex">
                                <span class="material-symbols-rounded">
                                    close
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="item__container">
                            <div class="item__image">
                                <img src="uploads/default.jpg">
                            </div>
                            <div class="item__info">
                                <div class="item__info__text">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__tags">
                                    The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog! The quick brown fox, jumps over the lazy dog!
                                </div>
                                <div class="item__info__details">
                                    2024-02-23 | Uploaded by: ionvop
                                </div>
                            </div>
                            <div class="item__status--solved -center--flex">
                                <span class="material-symbols-rounded">
                                    done
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="recent__more -center">
                    <button class="-button">
                        Show more
                    </button>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>

    </script>
</html>