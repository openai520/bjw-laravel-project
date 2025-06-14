<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Language | Choisir la langue</title>
</head>

<body class="language-switcher">
    <div class="language-container">
        <h1>Select Language / Choisir la langue</h1>

        <div class="language-buttons">
            <a href="{{ url('/language/en') }}" class="language-button">
                English
            </a>

            <a href="{{ url('/language/fr') }}" class="language-button">
                Français
            </a>
        </div>
    </div>
</body>

</html>
