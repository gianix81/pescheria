/**
 * Test della compressione lato browser, eseguito in un Chrome reale (headless).
 *
 * Non basta che il codice non sollevi eccezioni: qui si verifica che i file
 * escano davvero più leggeri, con le dimensioni previste, e che il video
 * prodotto sia ancora decodificabile.
 *
 * Esecuzione: npm run test:js
 */

import { createServer } from 'node:http'
import { readFile } from 'node:fs/promises'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { build } from 'vite'
import puppeteer from 'puppeteer'

const qui = dirname(fileURLToPath(import.meta.url))
const radice = join(qui, '../..')
const cartellaHarness = join(qui, 'harness')
const cartellaBundle = join(cartellaHarness, 'dist')

let falliti = 0
let passati = 0

function verifica(descrizione, condizione, dettaglio = '') {
    if (condizione) {
        passati++
        console.log(`  ✓ ${descrizione}${dettaglio ? ` — ${dettaglio}` : ''}`)
    } else {
        falliti++
        console.error(`  ✗ ${descrizione}${dettaglio ? ` — ${dettaglio}` : ''}`)
    }
}

function mb(byte) {
    return `${(byte / 1024 / 1024).toFixed(2)} MB`
}

async function compilaHarness() {
    await build({
        root: cartellaHarness,
        logLevel: 'error',
        resolve: { alias: { mediabunny: join(radice, 'node_modules/mediabunny') } },
        build: {
            outDir: cartellaBundle,
            emptyOutDir: true,
            rollupOptions: { input: join(cartellaHarness, 'entry.js') },
            lib: { entry: join(cartellaHarness, 'entry.js'), formats: ['es'], fileName: 'entry' },
        },
    })
}

function avviaServer(porta) {
    const server = createServer(async (richiesta, risposta) => {
        const percorso = richiesta.url === '/' ? '/index.html' : richiesta.url.split('?')[0]
        const base = percorso === '/index.html' ? cartellaHarness : cartellaBundle

        try {
            const contenuto = await readFile(join(base, percorso))
            risposta.writeHead(200, {
                'Content-Type': percorso.endsWith('.html') ? 'text/html' : 'text/javascript',
            })
            risposta.end(contenuto)
        } catch {
            risposta.writeHead(404).end('non trovato')
        }
    })

    return new Promise((risolvi) => server.listen(porta, () => risolvi(server)))
}

const server = await avviaServer(8123)
await compilaHarness()

const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--autoplay-policy=no-user-gesture-required'],
})

try {
    const pagina = await browser.newPage()
    pagina.on('console', (m) => m.type() === 'error' && console.error('    [browser]', m.text()))
    await pagina.goto('http://localhost:8123/', { waitUntil: 'networkidle0' })
    await pagina.waitForFunction('window.compressione !== undefined', { timeout: 15000 })

    const capacita = await pagina.evaluate(() => window.capacita())
    console.log('\nCapacità del browser di test:', JSON.stringify(capacita))

    // ------------------------------------------------------------- immagini
    console.log('\nImmagini')

    const immagine = await pagina.evaluate(async () => {
        const file = await window.creaImmagine(3200, 2400)
        const esito = await window.compressione.comprimiFile(file, {
            immagini: { latoMassimo: 1920, qualita: 0.82, sogliaByte: 200 * 1024 },
        })

        const bitmap = await createImageBitmap(esito.file)

        return {
            originale: esito.originale,
            finale: esito.finale,
            ridotto: esito.ridotto,
            risparmio: esito.risparmio,
            motivo: esito.motivo ?? null,
            larghezza: bitmap.width,
            altezza: bitmap.height,
            tipo: esito.file.type,
        }
    })

    verifica('la foto viene compressa', immagine.ridotto, immagine.motivo ?? '')
    verifica(
        'il peso cala di almeno il 60%',
        immagine.risparmio >= 60,
        `${mb(immagine.originale)} → ${mb(immagine.finale)} (−${immagine.risparmio}%)`,
    )
    verifica(
        'il lato lungo è ridotto a 1920 px',
        Math.max(immagine.larghezza, immagine.altezza) === 1920,
        `${immagine.larghezza}×${immagine.altezza}`,
    )
    verifica('le proporzioni sono mantenute', Math.abs(immagine.larghezza / immagine.altezza - 3200 / 2400) < 0.01)
    verifica('il risultato è un formato ammesso dal server', ['image/webp', 'image/jpeg'].includes(immagine.tipo), immagine.tipo)

    const piccola = await pagina.evaluate(async () => {
        const file = await window.creaImmagine(160, 120)

        return window.compressione.comprimiFile(file, { immagini: { sogliaByte: 200 * 1024 } })
    })

    verifica('una foto già leggera non viene ritoccata', !piccola.ridotto, piccola.motivo ?? '')

    // ---------------------------------------------------------------- video
    console.log('\nVideo')

    const video = await pagina.evaluate(async () => {
        const file = await window.creaVideo(1920, 1080, 3)
        const esito = await window.compressione.comprimiFile(
            file,
            { video: { latoMassimo: 640, frameRate: 24, sogliaByte: 1024 } },
            () => {},
        )

        const ispezione = esito.ridotto ? await window.ispezionaVideo(esito.file) : null

        return {
            originale: esito.originale,
            finale: esito.finale,
            ridotto: esito.ridotto,
            risparmio: esito.risparmio,
            motivo: esito.motivo ?? null,
            tipo: esito.file.type,
            ispezione,
        }
    })

    verifica('il video viene compresso', video.ridotto, video.motivo ?? '')
    verifica(
        'il peso cala di almeno il 70%',
        video.risparmio >= 70,
        `${mb(video.originale)} → ${mb(video.finale)} (−${video.risparmio}%)`,
    )
    verifica('il risultato è un MP4', video.tipo === 'video/mp4', video.tipo)
    verifica(
        'il video prodotto è decodificabile',
        video.ispezione !== null && video.ispezione.larghezza > 0,
        video.ispezione ? `${video.ispezione.larghezza}×${video.ispezione.altezza}` : 'non ispezionabile',
    )
    verifica(
        'il lato lungo è ridotto a 640 px',
        video.ispezione !== null && Math.max(video.ispezione.larghezza, video.ispezione.altezza) === 640,
        video.ispezione ? `${video.ispezione.larghezza}×${video.ispezione.altezza}` : '',
    )
    verifica(
        'la durata è preservata',
        video.ispezione !== null && Math.abs(video.ispezione.durata - 3) < 1.2,
        video.ispezione ? `${video.ispezione.durata.toFixed(2)} s` : '',
    )

    const videoPiccolo = await pagina.evaluate(async () => {
        const file = await window.creaVideo(320, 240, 1)

        return window.compressione.comprimiFile(file, { video: { sogliaByte: 50 * 1024 * 1024 } })
    })

    verifica('un video già leggero non viene transcodificato', !videoPiccolo.ridotto, videoPiccolo.motivo ?? '')
} finally {
    await browser.close()
    server.close()
}

console.log(`\nRisultato: ${passati} verifiche superate, ${falliti} fallite\n`)
process.exit(falliti > 0 ? 1 : 0)
