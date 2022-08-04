# pre-hdo tarif

Přes skript v PHP zjistí stav HDO u PRE.
Do parametru "tarif" lze zadat číslo tarifu.
Parameter JSON vrací pole s časem od-do a stavem.
Skript cachuje html do složky na 10minut.

Příklad:
https://vasedomena.cz/?tarif=493 - vrací 0 nebo 1 
https://vasedomena.cz/?tarif=493&json - vrací pole
