// Espone il modulo di compressione alla pagina di test.
import * as compressione from '../../../resources/js/compressione/index.js'

window.compressione = compressione

window.capacita = async () => {
    const supporti = { webcodecs: typeof VideoEncoder !== 'undefined' }

    try {
        const { canEncodeVideo } = await import('mediabunny')
        supporti.avc = await canEncodeVideo('avc')
        supporti.vp9 = await canEncodeVideo('vp9')
    } catch (e) {
        supporti.errore = String(e)
    }

    return supporti
}

/** Immagine sintetica rumorosa: comprime male, quindi la riduzione è reale. */
window.creaImmagine = async (larghezza, altezza) => {
    const canvas = document.createElement('canvas')
    canvas.width = larghezza
    canvas.height = altezza
    const ctx = canvas.getContext('2d')
    const dati = ctx.createImageData(larghezza, altezza)

    for (let i = 0; i < dati.data.length; i += 4) {
        dati.data[i] = Math.random() * 255
        dati.data[i + 1] = Math.random() * 255
        dati.data[i + 2] = Math.random() * 255
        dati.data[i + 3] = 255
    }

    ctx.putImageData(dati, 0, 0)

    const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', 0.95))

    return new File([blob], 'scatto.jpg', { type: 'image/jpeg' })
}

/** Video sintetico registrato dal canvas, con movimento e rumore. */
window.creaVideo = async (larghezza, altezza, secondi) => {
    const canvas = document.createElement('canvas')
    canvas.width = larghezza
    canvas.height = altezza
    const ctx = canvas.getContext('2d')

    const stream = canvas.captureStream(30)
    const tipi = ['video/mp4;codecs=avc1', 'video/webm;codecs=vp9', 'video/webm']
    const mime = tipi.find((t) => MediaRecorder.isTypeSupported(t))
    const recorder = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 12_000_000 })
    const pezzi = []

    recorder.ondataavailable = (e) => e.data.size && pezzi.push(e.data)

    const finito = new Promise((r) => { recorder.onstop = r })
    recorder.start()

    const inizio = performance.now()

    await new Promise((risolvi) => {
        const disegna = () => {
            const t = performance.now() - inizio
            const dati = ctx.createImageData(larghezza, altezza)

            for (let i = 0; i < dati.data.length; i += 4) {
                dati.data[i] = Math.random() * 255
                dati.data[i + 1] = (Math.random() * 255 + t / 10) % 255
                dati.data[i + 2] = Math.random() * 255
                dati.data[i + 3] = 255
            }

            ctx.putImageData(dati, 0, 0)

            if (t < secondi * 1000) {
                requestAnimationFrame(disegna)
            } else {
                risolvi()
            }
        }

        disegna()
    })

    recorder.stop()
    await finito

    const tipo = mime.startsWith('video/mp4') ? 'video/mp4' : 'video/webm'
    const blob = new Blob(pezzi, { type: tipo })

    return new File([blob], tipo === 'video/mp4' ? 'ripresa.mp4' : 'ripresa.webm', { type: tipo })
}

/** Rilegge un video prodotto, per verificare che sia davvero decodificabile. */
window.ispezionaVideo = async (file) => {
    const { Input, ALL_FORMATS, BlobSource } = await import('mediabunny')
    const input = new Input({ formats: ALL_FORMATS, source: new BlobSource(file) })
    const traccia = await input.getPrimaryVideoTrack()

    return {
        larghezza: traccia?.displayWidth ?? null,
        altezza: traccia?.displayHeight ?? null,
        durata: await input.computeDuration(),
    }
}
