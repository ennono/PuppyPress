# PuppyPress Migrator

**Zieht alle Vereinsdaten von der alten Website (Theme „Extra Child“) auf eine neue Website mit [PuppyPress](../README.md) um.**

Der Standard-Export von WordPress (WXR) reicht dafür nicht: Beim Import bekommen alle Einträge neue IDs, und viele Vereinsdaten verweisen per ID aufeinander. Der Migrator schreibt diese Verweise beim Import um.

| Verweis | Beispiel |
| --- | --- |
| Deckrüde → Zuchtzulassung, Körbericht | `license`, `approval` |
| Abstammung → Körberichte der 14 Ahnen | `father_approval` … `greatgrandmother_4_approval` |
| Wurf → Vater-Deckrüde, Fotos von Vater und Mutter | `father_stud_dog_id`, `father_photo`, `mother_photo` |
| Züchter → Zwinger | Term-Meta `breeder_kennel` |
| Beitragsbilder, Anhänge, Seitenhierarchie | `_thumbnail_id`, `post_parent` |
| Menüs | Menüpunkte, Eltern, Menüpositionen |
| Inhalte | `[gallery ids="…"]`, Bild- und Galerie-Blöcke, `wp-image-123` |
| Widgets | Menü-Widget, Bild- und Galerie-Widgets, URLs |

## Was übertragen wird

- **Vereinsdaten (immer):** alle 11 Inhaltstypen, alle 19 Taxonomien (auch Begriffe ohne Einträge) mit Hierarchie und Feldern, alle Meta-Felder
- **Optional:** Beiträge und Seiten (inkl. Seitentemplates), Menüs, Kommentare (ohne Spam und Papierkorb), Widgets
- **Medien:** die gesamte Mediathek, nur verwendete Medien oder keine. Übertragen werden Originaldateien und alle erzeugten Bildgrößen; Bilder müssen also nicht neu berechnet werden.
- **Einstellungen:** Startseite, Beitragsseite, oben gehaltene Beiträge, Menüpositionen
- **Autoren:** werden über Login oder E-Mail zugeordnet; fehlende Autoren werden optional angelegt.

Nicht übertragen werden Revisionen, automatische Entwürfe, Plugin- und Theme-Einstellungen sowie Benutzerkonten, die keine Inhalte haben.

## Ablauf

1. **Alte Website:** Plugin installieren und aktivieren, dann *Werkzeuge → PuppyPress-Migration → Export erstellen*. Das ZIP wird heruntergeladen.
2. **Neue Website:** PuppyPress **und** den Migrator installieren und aktivieren. Unter *Werkzeuge → PuppyPress-Migration* das ZIP hochladen und *Import starten*.
3. Danach *Einstellungen → Permalinks* einmal speichern und die Prüfliste in [docs/MIGRATION.md](../docs/MIGRATION.md) durchgehen.
4. Den Migrator auf beiden Websites deaktivieren und löschen.

Große Pakete, die das Upload-Limit überschreiten, per FTP/SFTP nach `wp-content/uploads/puppypress-migrator/incoming/` hochladen; sie erscheinen dann im Importformular.

### Per WP-CLI (empfohlen für große Websites)

```bash
wp puppypress-migrator export --dir=/tmp
wp puppypress-migrator import /tmp/puppypress-export-alte-domain.de-2026-10-31.zip --author=admin
```

Weitere Optionen: `wp help puppypress-migrator export` und `wp help puppypress-migrator import`.

## Sicherheit und Robustheit

- **Läuft in Etappen:** Export und Import arbeiten in kurzen Schritten (ca. 15 s pro Anfrage). Es gibt keine Timeouts, und ein abgebrochener Vorgang läuft per „Erneut versuchen“ weiter.
- **Keine Duplikate:** Jeder importierte Eintrag merkt sich seine Herkunft (`_ppm_source`). Wird dasselbe Paket erneut importiert, überspringt der Migrator vorhandene Einträge. Vorhandene Begriffe mit gleichem Slug werden wiederverwendet.
- **Keine überschriebenen Dateien:** Liegt auf der neuen Website schon eine andere Datei unter demselben Pfad, landet die importierte Datei samt allen Bildgrößen in `uploads/ppm-import-…/`. Links und Metadaten werden entsprechend angepasst.
- **Geprüfte Pakete:** Entpackt werden nur Medien mit erlaubten Dateitypen und sauberen Pfaden. Pfade wie `../`, PHP-Dateien oder doppelte Endungen werden abgewiesen (Schutz vor Zip-Slip). Serialisierte Daten werden ohne Objekte entpackt.
- **Datenschutz:** Exporte enthalten personenbezogene Daten. Sie liegen in einem gegen Webzugriff geschützten Ordner mit zufälligem Namen, lassen sich nur von Administratoren herunterladen und werden nach drei Tagen automatisch gelöscht.
- Die Inhalte werden unverändert übernommen. Damit nichts verloren geht, ist der HTML-Filter (kses) während des Imports abgeschaltet.

## Anpassen

Die Liste der Inhaltstypen, Taxonomien und ID-Felder ist über Filter erweiterbar, z. B. für eigene Felder:

```php
add_filter( 'ppm_post_id_meta_keys', function ( $keys ) {
	$keys[] = 'mein_verweis_auf_einen_beitrag';
	return $keys;
} );
```

Verfügbare Filter: `ppm_club_post_types`, `ppm_club_taxonomies`, `ppm_post_statuses`, `ppm_post_id_meta_keys`, `ppm_post_id_list_meta_keys`, `ppm_term_id_term_meta_keys`, `ppm_excluded_post_meta_keys`, `ppm_step_seconds`.

## Entwicklung

```bash
php puppypress-migrator/tests/run.php                       # Roundtrip-Tests mit simuliertem WordPress
php bin/i18n.php pot puppypress-migrator                    # Übersetzungen extrahieren
php bin/i18n.php compile puppypress-migrator                # PO → MO und .l10n.php
```

Die Tests simulieren zwei Websites im Speicher: Sie exportieren eine „alte“ Website mit Deckrüden, Abstammung, Würfen, Zwingern, Menüs, Kommentaren und Widgets und importieren sie in eine „neue“. Danach prüfen sie, ob alle Verweise stimmen, ob sich ein Import wiederholen und fortsetzen lässt und ob manipulierte Pakete abgewiesen werden.
