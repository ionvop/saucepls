<?php

require_once "common.php";

?>

<html>
    <head>
        <title>
            Home | SaucePls
        </title>
        <base href="./">
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>

        </style>
    </head>
    <body>
        <div style="
            display: grid;
            grid-template-columns: max-content 1fr;
            height: 100%;
            box-sizing: border-box;">
            <?= renderNavigation() ?>
            <div style="
                display: grid;
                grid-template-rows: max-content 1fr;">
                <?= renderHeader() ?>
                <div style="
                    display: grid;
                    grid-template-columns: 1fr max-content;">
                    <div>

                    </div>
                    <form style="
                        background-color: #222;
                        border-left: 1px solid #555;
                        width: 15rem;">
                        <div style="
                            padding: 1rem;
                            text-align: center;
                            font-size: 1.5rem;">
                            Search
                        </div>
                        <div style="
                            display: grid;
                            grid-template-columns: max-content 1fr;">
                            <div style="
                                display: flex;
                                align-items: center;
                                padding: 1rem;">
                                Sort:
                            </div>
                            <div style="
                                display: flex;
                                align-items: center;
                                padding: 1rem;
                                padding-left: 0rem;">
                                <select name="sort">
                                    <option value="recent">
                                        Recent
                                    </option>
                                    <option value="trending">
                                        Trending
                                    </option>
                                    <option value="follow">
                                        Popular
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div style="
                            padding: 1rem;">
                            <input type="text"
                                name="q"
                                placeholder="Keywords or tags...">
                        </div>
                        <div style="
                            display: grid;
                            grid-template-columns: 1fr max-content;
                            padding: 1rem;">
                            <div></div>
                            <div>
                                <button>
                                    <div style="
                                        display: grid;
                                        grid-template-columns: max-content 1fr;">
                                        <div style="
                                            display: flex;
                                            align-items: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M380-320q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
                                        </div>
                                        <div style="
                                            display: flex;
                                            align-items: center;
                                            padding-left: 0.5rem;">
                                            Search
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    <script src="script.js"></script>
    <script>

    </script>
</html>