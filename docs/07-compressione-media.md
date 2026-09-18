# 7. Compressione di foto e video

## 7.1 Perché nel browser e non sul server

Il Buyer pubblica dal telefono, spesso sulla rete del punto vendita. Un video verticale girato
con uno smartphone recente è 4K a 40-60 Mbit/s: **un minuto supera i 300 MB**. Comprimere dopo
l'upload non risolverebbe il problema vero, che è il tempo di attesa prima di poter inviare
l'opportunità in verifica.

Comprimendo **prima dell'invio**, nel browser:

- l'upload dura una frazione del tempo, anche in 4G;
- lo storage costa meno e i media si aprono più in fretta sui telefoni dei capi reparto;
- si resta sotto il limite di 4,5 MB per richiesta delle funzioni serverless (vedi
  [deploy su Vercel](06-deploy-vercel.md)), senza dipendere dall'upload diretto a S3;
- il server non spende CPU in transcodifica, cosa che su serverless non sarebbe nemmeno possibile.

## 7.2 Come funziona

| | Foto | Video |
|---|---|---|
| Tecnica | ridimensionamento su canvas e ricodifica | transcodifica con **WebCodecs** (accelerata in hardware) tramite [mediabunny](https://mediabunny.dev) |
| Lato lungo | 1920 px | 1280 px |
| Formato in uscita | WebP, JPEG dove WebP non è disponibile | MP4 / H.264, audio conservato |
| Soglia sotto cui non si interviene | 200 kB | 3 MB |
| Riduzione misurata | −80% | −99% su sorgente sintetica; su riprese reali indicativamente −85/95% |

L'orientamento EXIF viene rispettato (`imageOrientation: 'from-image'`), altrimenti le foto
verticali dei telefoni finirebbero ruotate. Le dimensioni dei video sono forzate a valori pari,
requisito di molti encoder H.264.

**La compressione non è mai bloccante.** Se il browser non ha WebCodecs, se il formato non è
decodificabile (tipico dell'HEIC su Chrome), o se la transcodifica fallisce, il file originale
viene inviato così com'è e l'interfaccia lo dice. Se il risultato compresso è più pesante
dell'originale, si tiene l'originale.

## 7.3 Dove sta il codice

```
resources/js/compressione/
  index.js       dispatcher, formattazione e descrizione degli esiti
  immagini.js    ridimensionamento e ricodifica su canvas
  video.js       transcodifica WebCodecs, con rilevamento del supporto
resources/js/app.js                                    componente Alpine "caricatoreMedia"
resources/views/livewire/buyer/partials/media.blade.php   interfaccia di caricamento
```

I file compressi vengono consegnati all'input collegato a `wire:model`, quindi l'upload resta
quello standard di Livewire: chunking, barra di avanzamento e validazione non cambiano.
`mediabunny` è caricato con un `import()` dinamico: chi carica solo foto non scarica mai i
540 kB della libreria video.

## 7.4 Configurazione

In `config/pescheria.php` → `media.compressione`, sovrascrivibile da `.env`:

| Variabile | Predefinito | Effetto |
|---|---|---|
| `MEDIA_IMG_LATO_MAX` | 1920 | lato lungo massimo delle foto |
| `MEDIA_IMG_QUALITA` | 0.82 | qualità di ricodifica (0-1) |
| `MEDIA_IMG_SOGLIA_KB` | 200 | sotto questa dimensione la foto non viene toccata |
| `MEDIA_VIDEO_LATO_MAX` | 1280 | lato lungo massimo dei video |
| `MEDIA_VIDEO_FPS` | 30 | frame rate massimo in uscita |
| `MEDIA_VIDEO_SOGLIA_MB` | 3 | sotto questa dimensione il video non viene transcodificato |

I limiti lato server (`MEDIA_MAX_IMAGE_MB`, `MEDIA_MAX_VIDEO_MB`) restano quelli di prima e
continuano a essere applicati in `MediaService`: la compressione nel browser è un'ottimizzazione,
non un controllo di sicurezza.

## 7.5 Test

```bash
npm run test:js
```

Avvia un Chrome reale in headless, genera una foto 3200×2400 e un video 1920×1080 di rumore
(che comprime male, quindi la riduzione misurata non è gonfiata), li comprime e verifica
peso, dimensioni, proporzioni, formato, durata e che il video prodotto sia ancora decodificabile.
Ultima esecuzione: **13 verifiche superate**.

## 7.6 Supporto dei browser

| Browser | Foto | Video |
|---|---|---|
| Chrome / Edge desktop e Android | sì | sì |
| Safari 16.4+ / iOS 16.4+ | sì | sì |
| Firefox | sì | sì (130+) |
| Browser più vecchi | sì, con JPEG | no: il file parte alla dimensione originale, l'interfaccia avvisa |
