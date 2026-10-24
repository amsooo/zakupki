# Zakupki

Prosta wspólna lista zakupów dla dwóch osób (PWA + PHP). Dodawanie ręczne lub głosem, ilość + jednostka,
ptaszek „kupione”. Lista leży na serwerze (`data/list.json`), telefony odpytują `api.php` co 4 s,
więc oba widzą to samo. Działa na Androidzie i iOS (jako strona dodana do ekranu głównego).

## Wdrożenie (dowolny hosting z PHP + HTTPS)

1. Wgraj zawartość repo do osobnego katalogu lub subdomeny, np. `zakupki.twojadomena.pl`.
   HTTPS jest konieczny (mikrofon, instalacja na ekranie głównym).
2. Skopiuj `config.example.php` → `config.php` i wpisz długi, losowy `LIST_KEY`.
3. Upewnij się, że PHP może pisać w `data/`.
4. Na obu telefonach otwórz `https://TWOJA-ADRESACJA/#k=TWÓJ_KOD` (kod zapisze się raz),
   potem „Dodaj do ekranu głównego” (iOS: Safari → Udostępnij → Do ekranu początkowego).

## Dyktowanie

- Android/Chrome: przycisk 🎤 w aplikacji.
- iOS: mikrofon na klawiaturze telefonu w polu „Co kupić?” — tekst zostanie rozbity na pozycje tak samo jak mowa.
- Przykład: „2 kilo jabłek, mleko 1,5 litra i chleb” → trzy pozycje.

## Uwagi

- Kto zna kod, widzi i zmienia listę — kod ma być długi i losowy.
- Offline: zmiany czekają w kolejce i wysyłają się po odzyskaniu sieci.
