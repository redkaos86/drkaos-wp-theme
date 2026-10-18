# DR.KAOS WP THEME

Tema WordPress **vuoto e leggero**, pensato per [Elementor](https://elementor.com) e per la massima compatibilità con i plugin (WooCommerce, Yoast, Rank Math, WPML, Polylang…). Nessuno stile imposto: il design si costruisce con Elementor. In più, strumenti di branding e di gestione del sito inclusi nel tema.

> English: a blank, lightweight WordPress theme for Elementor, with a branded dark dashboard, custom login URL, maintenance mode and custom cursor.

![Anteprima](screenshot.jpg)

## Requisiti

- WordPress 5.9 o superiore
- PHP 7.4 o superiore
- Permalink diversi da "Semplice" (solo per l'indirizzo di accesso personalizzato)

## Installazione

1. Scarica il file `.zip` dalla sezione *Releases* (oppure comprimi la cartella `drkaos-wp-theme`).
2. In WordPress: **Aspetto → Temi → Aggiungi nuovo → Carica tema**, scegli lo zip e attiva.
3. Installa Elementor e costruisci header, footer e pagine.

## Funzioni

| Funzione | Dove si imposta |
|---|---|
| **Compatibilità Elementor Pro** – Theme Locations (header, footer, single, archive) | Theme Builder di Elementor |
| **Header e footer del tema disattivabili** (globale e per singola pagina) | Personalizza → *Header e footer del tema*, riquadro in ogni pagina |
| **Dashboard scura brandizzata** (verde, angoli arrotondati, logo cliccabile) | Automatico; colore accento in *Brand DR.KAOS* |
| **Pagina di login brandizzata** + **verifica matematica anti-bot** | Automatico |
| **Indirizzo di accesso personalizzato** (`/dklogin`) | Personalizza → *Indirizzo di accesso* |
| **Cursore personalizzato** (pallina, anello, icone, logo, ingrandimento, effetto negativo) | Personalizza → *Cursore personalizzato* |
| **Modalità manutenzione** con interruttore nella barra admin e pagina personalizzabile | Barra admin → *Manutenzione* / Aspetto → *Manutenzione* |
| **Favicon del sito** (scelta diretta, senza ritaglio) | Personalizza → *Brand DR.KAOS* |
| **Popup nella Bacheca** per invitare a scaricare KaosSlider | Personalizza → *Brand DR.KAOS* |

## Configurazione avanzata

Costanti da inserire in `wp-config.php` (utili come uscite di emergenza):

```php
define( 'DRKAOS_DISABLE_LOGIN_URL', true );        // ripristina wp-login.php
define( 'DRKAOS_LOGIN_SLUG', 'miologin' );          // cambia l'indirizzo di accesso
define( 'DRKAOS_DISABLE_LOGIN_MATH', true );        // disattiva la verifica matematica
define( 'DRKAOS_DISABLE_MAINTENANCE', true );       // disattiva la modalità manutenzione
define( 'DRKAOS_DISABLE_ADMIN_STYLE', true );       // dashboard standard di WordPress
```

Filtri disponibili: `drkaos_cursor_enabled`, `drkaos_login_url_enabled`, `drkaos_login_math`, `drkaos_maintenance_block`, `drkaos_admin_style`, `drkaos_admin_contrast_fix`, `drkaos_promo_kaosslider`, `drkaos_kaosslider_url`, `drkaos_logo_url`, `drkaos_enable_header`, `drkaos_enable_footer`.

## Sicurezza

- Tutti i file PHP con logica bloccano l'accesso diretto (guardia `ABSPATH`); nonce e controllo dei permessi su ogni azione che modifica dati.
- Input sanificati al salvataggio, output escapato; le configurazioni JSON stampate negli script usano i flag `JSON_HEX_*`.
- La verifica matematica del login usa risposte monouso salvate sul server.
- Vedi [SECURITY.md](SECURITY.md) per segnalare vulnerabilità.

## Prestazioni

- Sul sito pubblico viene caricato **un solo foglio di stile** (~3,5 KB); gli script del cursore, della manutenzione e della barra admin partono solo quando servono.
- Nessuna libreria esterna. I Google Fonts si caricano solo nella pagina di manutenzione, e solo se scegli un font non di sistema.

## Struttura

```
drkaos-wp-theme/
├── functions.php        # setup del tema e caricamento dei moduli
├── inc/                 # moduli: branding, layout, admin, login, cursore, manutenzione, ...
├── template-parts/      # contenuti di header, footer, pagine, archivi, 404
├── assets/              # css, js, immagini
└── *.php                # template standard di WordPress
```

## Licenza

Codice sotto [GPL v3 o successiva](LICENSE). Nome, logo e immagini DR.KAOS sono marchi del proprietario e non rientrano nella licenza del codice: vedi [NOTICE.md](NOTICE.md).
