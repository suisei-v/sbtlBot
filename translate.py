import sys
import pysubs2
import urllib
import requests
import os

def translate(text):
    token = sys.argv[2]
    urlbase = "https://translate.yandex.net/api/v1.5/tr.json/translate"
    
    params = {
        "key": token,
        "lang": "ru",
        "text": text
    }
    r = requests.get(urlbase, params = params)
    result = r.json()["text"][0]
    return urllib.parse.unquote(result)

def start():
    subs = pysubs2.load(sys.argv[1])
    i = 0
    chars = 0
    for line in subs:
        chars += len(line.plaintext)
        line.plaintext = translate(line.plaintext)
        i += 1
    filename = os.path.dirname(sys.argv[1]) + '/translated_' + os.path.basename(sys.argv[1])
    subs.save(filename)
    print(str(i))
    print(str(chars))
    print(filename)

start()
