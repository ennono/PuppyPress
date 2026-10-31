# Changelog

Alle nennenswerten Änderungen an diesem Projekt. Format nach [Keep a Changelog](https://keepachangelog.com/de/1.1.0/), Versionierung nach [SemVer](https://semver.org/lang/de/).

## [1.0.0] – 2026-10-31

### Hinzugefügt
- Erste Version. Die Fachlogik des Child-Themes *Extra Child* ist vollständig als Plugin umgesetzt: 11 Post Types, 19 Taxonomien, Meta-Boxen, Term-Felder, Abstammungsraster, 7 Widgets, Sidebar für Ergebnisseiten.
- Feld-Schema als einzige Quelle für Meta-Registrierung, Formulare und Sanitizing.
- Theme-unabhängige Detailansichten über überschreibbare Templates (`{theme}/puppypress/…`).
- Shortcodes für alle Übersichtsseiten sowie `[puppypress_stud_dogs filter="yes"]` und `[puppypress_litters]`.
- Kompatibilität mit den alten Seitentemplates: Seiten funktionieren ohne Änderung weiter.
- Deckrüdenfilter über GET-Parameter, auch im Deckrüden-Archiv.
- Mehrfach-Fotoauswahl mit Vorschau für Vater- und Mutterfotos.
- Eigene SVG-Icons statt Abhängigkeit von Font Awesome.
- Deutsche Übersetzung inkl. unveränderter URL-Slugs, `.mo` und `.l10n.php`.
- Admin-Hinweis, solange das alte Theme noch dieselben Inhalte registriert.
- Abhängigkeitsfreie Tests (`php tests/run.php`) und i18n-Werkzeug (`php bin/i18n.php`).

### Geändert (gegenüber dem Theme)
- Englischer Standard-Slug der Ergebnisse ist `exam-results` statt `approvals` (die deutsche Übersetzung `pruefungen` bleibt).
- Deckrüdenfilter nutzt GET statt POST; Prüfungen und Titel werden per Checkbox gewählt.
- Ein Wurf heißt im Backend „Wurf“ statt „Welpen“ (das Menü heißt weiterhin „Welpen“).

### Behoben (gegenüber dem Theme)
- Beim Speichern eines Deckrüden wurden `hd`/`ed` geleert, beim Speichern eines Wurfs `address`/`phone`/`mobile`/`fax`.
- Zeilenumbrüche in den Abstammungsbeschreibungen gingen verloren.
- Archive der Taxonomie „Titel“ waren immer leer (reservierte Query-Variable `title`).
- Ein Link zum Körbericht der ersten Urgroßmutter war doppelt verschachtelt und damit kaputt.
- Leere Wurftage wurden als „01.01.70“ angezeigt.
- Tierbetreuungen zeigten die Städte der Trimmnachweise.
- Das Widget „Recent Results“ fragte einen nicht existierenden Post Type ab.
- Fehlende Escapes, Nonces und Berechtigungsprüfungen bei Term-Feldern.
