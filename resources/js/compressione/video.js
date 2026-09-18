/**
 * Compressione dei video nel browser, prima dell'invio.
 *
 * Un video verticale girato con un telefono recente è 4K a 40-60 Mbit/s: un
 * minuto supera i 300 MB. Per mostrare pezzatura e confezionamento bastano
 * 720p a bitrate contenuto, cioè circa 10 MB al minuto.
 *
 * La transcodifica usa WebCodecs (accelerata in hardware dove disponibile)
 * tramite mediabunny, che si occupa anche di demux e mux. Dove WebCodecs non
 * c'è, il file viene inviato com'è: la compressione è un'ottimizzazione, non
 * un requisito per poter lavorare.
 */

const PREDEFINITI = {
    latoMassimo: 1280,
    frameRate: 30,
    // Sotto questa soglia la transcodifica non vale il tempo di attesa.
    sogliaByte: 3 * 1024 * 1024,
}

/** WebCodecs disponibile e capace di produrre H.264? */
export async function supportaCompressioneVideo() {
    if (typeof VideoEncoder === 'undefined' || typeof VideoDecoder === 'undefined') {
        return false
    }

    try {
        const { canEncodeVideo } = await import('mediabunny')

        return await canEncodeVideo('avc')
    } catch {
        return false
    }
}

function dimensioniRidotte(larghezza, altezza, latoMassimo) {
    const lato = Math.max(larghezza, altezza)

    if (!lato || lato <= latoMassimo) {
        return null      // già abbastanza piccolo: nessun ridimensionamento
    }

    const fattore = latoMassimo / lato

    // Le dimensioni devono restare pari: molti encoder H.264 lo richiedono.
    const pari = (valore) => Math.max(2, Math.round((valore * fattore) / 2) * 2)

    return { larghezza: pari(larghezza), altezza: pari(altezza) }
}

/**
 * @param {File} file
 * @param {object} [opzioni]
 * @param {(percentuale: number) => void} [onProgresso]
 * @returns {Promise<{file: File, ridotto: boolean, motivo?: string}>}
 */
export async function comprimiVideo(file, opzioni = {}, onProgresso = null) {
    const config = { ...PREDEFINITI, ...opzioni }

    if (file.size <= config.sogliaByte) {
        return { file, ridotto: false, motivo: 'già leggero' }
    }

    if (!(await supportaCompressioneVideo())) {
        return { file, ridotto: false, motivo: 'browser senza WebCodecs' }
    }

    const {
        Input, Output, Conversion, BufferTarget, BlobSource, Mp4OutputFormat, ALL_FORMATS, QUALITY_LOW,
    } = await import('mediabunny')

    try {
        const input = new Input({ formats: ALL_FORMATS, source: new BlobSource(file) })
        const tracciaVideo = await input.getPrimaryVideoTrack()

        if (!tracciaVideo) {
            return { file, ridotto: false, motivo: 'nessuna traccia video' }
        }

        const opzioniVideo = {
            quality: QUALITY_LOW,
            frameRate: config.frameRate,
        }

        const ridotte = dimensioniRidotte(
            tracciaVideo.displayWidth,
            tracciaVideo.displayHeight,
            config.latoMassimo,
        )

        if (ridotte) {
            opzioniVideo.width = ridotte.larghezza
            opzioniVideo.height = ridotte.altezza
            opzioniVideo.fit = 'contain'
        }

        const output = new Output({ format: new Mp4OutputFormat(), target: new BufferTarget() })

        const conversione = await Conversion.init({
            input,
            output,
            video: opzioniVideo,
            audio: { quality: QUALITY_LOW },
        })

        if (!conversione.isValid) {
            return { file, ridotto: false, motivo: 'traccia non convertibile' }
        }

        if (onProgresso) {
            conversione.onProgress = (avanzamento) => onProgresso(Math.round(avanzamento * 100))
        }

        await conversione.execute()

        const blob = new Blob([output.target.buffer], { type: 'video/mp4' })

        if (blob.size >= file.size) {
            return { file, ridotto: false, motivo: 'originale già compresso' }
        }

        const nome = file.name.replace(/\.[^.]+$/, '') + '.mp4'

        return {
            file: new File([blob], nome, { type: 'video/mp4', lastModified: Date.now() }),
            ridotto: true,
        }
    } catch (errore) {
        // Qualsiasi imprevisto non deve impedire la pubblicazione:
        // si carica l'originale.
        console.warn('Compressione video non riuscita, invio il file originale', errore)

        return { file, ridotto: false, motivo: 'compressione non riuscita' }
    }
}
