## SubtitleBot

Telegram bot for converting strings from SubStationAlpha (*.ssa/*.ass) to human-friendly format.
Bot available here [@sbtlBot](https://t.me/sbtlBot)

### Usage:

Just send him lines of ssa text (usually starting with 'Dialogue').
It can also return length of string it's got.

### Dependencies:

`apt install php7.0-mysql`

`apt install php7.0-curl`

`apt install php7.0-mbstring`

### Setup:

`source schema.sql`

`cp config.sample.php config.php`

Files functionality is not finished. It can just save text file you're sending him. To prevent errors create folder 'files' in root with 'w' permission for www-data:www-data (or just chmod 777).

