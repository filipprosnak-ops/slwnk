# Hockey League Manager for Blocksy

WordPress plugin pre hokejovú ligu kompatibilný s Blocksy (aj inými témami).

## Funkcie
- Vlastné typy obsahu: **Tímy**, **Hráči**, **Zápasy**.
- Taxonómia **Sezóny** pre filtrovanie tímov, hráčov a zápasov.
- Prepojenie hráča na tím.
- Zápas obsahuje: domáci/hosťujúci tím, kolo, dátum, skóre, stav odohrania.
- Štatistiky hráčov po zápase vo formáte `player_id;goals;assists;penalty_minutes`.
- Automatický výpočet tabuľky sezóny z odohraných zápasov.
- Klikateľné karty tímu, hráča aj detail zápasu.

## Shortcody
- `[hockey_table season="2024-2025"]` – tabuľka sezóny.
- `[hockey_matches season="2024-2025" limit="20"]` – zoznam zápasov.
- `[hockey_team_card id="123"]` – karta tímu.
- `[hockey_player_card id="456"]` – karta hráča.

> Parameter `season` môže byť slug sezóny alebo ID termínu.

## Inštalácia
1. Nahraj plugin súbor `hockey-league-manager.php` do WordPress `wp-content/plugins/`.
2. Aktivuj plugin v administrácii.
3. Vytvor sezóny, tímy, hráčov a zápasy.
4. Vlož shortcody na stránku (napr. “Tabuľka” alebo “Výsledky”).
