# Changelog

Format nach [Keep a Changelog](https://keepachangelog.com/de/1.1.0/), Versionierung nach [SemVer](https://semver.org/lang/de/).

## [1.0.0] – 2026-10-31

### Hinzugefügt
- Export aller Vereinsdaten einer Website mit dem Theme „Extra Child“ (Inhaltstypen, Taxonomien inkl. leerer Begriffe, Meta-Felder, Beiträge, Seiten, Menüs, Kommentare, Widgets, Einstellungen, Medien mit allen Bildgrößen) in ein ZIP-Paket.
- Import in eine Website mit PuppyPress in fortsetzbaren Etappen. Alle ID-Verweise werden umgeschrieben (Zuchtzulassung, Körberichte, Abstammung, Vater-Deckrüde, Elternfotos, Züchter-Zwinger, Beitragsbilder, Hierarchien, Menüs, Galerien, Bild-Blöcke, Widgets).
- Schutz vor Duplikaten, keine überschriebenen Dateien, Prüfung der Paketinhalte (Zip-Slip, Dateitypen), URL-Ersetzung auch in serialisierten Daten.
- Admin-Oberfläche unter *Werkzeuge*, Eingangsordner für große Pakete, WP-CLI-Befehle, geschützter Speicher mit automatischer Bereinigung, deutsche Übersetzung.
- Roundtrip-Tests mit simuliertem WordPress.
