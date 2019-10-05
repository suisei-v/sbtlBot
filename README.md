## SubtitleBot

Telegram bot for converting strings from SubStationAlpha (*.ssa/*.ass) to human-friendly format.

### Usage:

Just send him lines of ssa text (usually starting with 'Dialogue').
Additionally, it can /length input string and /translate input *.ass file to Russian.

### Dependencies:

`apt install php7.0-mysql`

`apt install php7.0-curl`

`apt install php7.0-mbstring`

python3 with 'pysubs2' and 'requests' modules.

### Setup:

`source schema.sql`

`cp config.sample.php config.php`

Create folder 'files' in root with 'w' permission for www-data:www-data (or just chmod 777).

