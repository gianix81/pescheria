/**
 * Compressione delle immagini nel browser, prima dell'invio.
 *
 * Una foto scattata da uno smartphone pesa 3-8 MB a 4000 px di lato: per una
 * scheda prodotto sono inutili. Ridimensionando a 1920 px e ricodificando in
 * WebP (o JPEG dove WebP non è disponibile) si scende tipicamente sotto i 400 kB
 * senza differenze percepibili a schermo.
 */

const PREDEFINITI = {
    latoMassimo: 1920,
    qualita: 0.82,
    // Sotto questa soglia non vale la pena ricodificare.
    sogliaByte: 200 * 1024,
}

/** Il browser sa produrre WebP da canvas? (Safari lo fa dalla 14.) */
let supportoWebp = null

function supportaWebp() {
    if (supportoWebp === null) {
        const canvas = document.createElement('canvas')
        canvas.width = canvas.height = 1
        supportoWebp = canvas.toDataURL('image/webp').startsWith('data:image/webp')
    }

    return supportoWebp
}

function dimensioniRidotte(larghezza, altezza, latoMassimo) {
    const lato = Math.max(larghezza, altezza)

    if (lato <= latoMassimo) {
        return { larghezza, altezza, ridimensionata: false }
    }

    const fattore = latoMassimo / lato

    return {
        larghezza: Math.round(larghezza * fattore),
        altezza: Math.round(altezza * fattore),
        ridimensionata: true,
    }
}

async function inBlob(canvas, mime, qualita) {
    if (typeof canvas.convertToBlob === 'function') {
        return canvas.convertToBlob({ type: mime, quality: qualita })
    }

    return new Promise((risolvi) => canvas.toBlob(risolvi, mime, qualita))
}

function creaCanvas(larghezza, altezza) {
    if (typeof OffscreenCanvas === 'function') {
        return new OffscreenCanvas(larghezza, altezza)
    }

    const canvas = document.createElement('canvas')
    canvas.width = larghezza
    canvas.height = altezza

    return canvas
}

/**
 * @param {File} file
 * @param {object} [opzioni]
 * @returns {Promise<{file: File, ridotto: boolean, motivo?: string}>}
 */
export async function comprimiImmagine(file, opzioni = {}) {
    const config = { ...PREDEFINITI, ...opzioni }

    if (file.size <= config.sogliaByte) {
        return { file, ridotto: false, motivo: 'già leggera' }
    }

    let bitmap

    try {
        // imageOrientation rispetta l'EXIF: senza, le foto verticali dei
        // telefoni finirebbero ruotate.
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' })
    } catch {
        // Formati che il browser non sa decodificare (tipicamente HEIC su Chrome).
        return { file, ridotto: false, motivo: 'formato non comprimibile dal browser' }
    }

    try {
        const { larghezza, altezza } = dimensioniRidotte(bitmap.width, bitmap.height, config.latoMassimo)
        const canvas = creaCanvas(larghezza, altezza)
        const contesto = canvas.getContext('2d', { alpha: false })

        contesto.drawImage(bitmap, 0, 0, larghezza, altezza)

        const mime = supportaWebp() ? 'image/webp' : 'image/jpeg'
        const blob = await inBlob(canvas, mime, config.qualita)

        // Se la ricodifica non ha guadagnato nulla, si tiene l'originale.
        if (!blob || blob.size >= file.size) {
            return { file, ridotto: false, motivo: 'originale già ottimizzato' }
        }

        const estensione = mime === 'image/webp' ? 'webp' : 'jpg'
        const nome = file.name.replace(/\.[^.]+$/, '') + '.' + estensione

        return {
            file: new File([blob], nome, { type: mime, lastModified: Date.now() }),
            ridotto: true,
        }
    } finally {
        bitmap.close?.()
    }
}
