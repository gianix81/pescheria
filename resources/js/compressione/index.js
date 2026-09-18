/**
 * Riduzione del peso dei file prima dell'invio.
 *
 * Tutto avviene nel browser: quello che parte verso il server è già leggero,
 * quindi l'upload è più veloce sulla rete del punto vendita, lo storage costa
 * meno e non si incontrano i limiti di dimensione delle richieste.
 *
 * La compressione non è mai bloccante: se il browser non la supporta o
 * qualcosa va storto, il file originale viene inviato così com'è.
 */

import { comprimiImmagine } from './immagini.js'
import { comprimiVideo, supportaCompressioneVideo } from './video.js'

export { comprimiImmagine, comprimiVideo, supportaCompressioneVideo }

export function formattaByte(byte) {
    if (byte < 1024) {
        return `${byte} B`
    }

    const unita = ['kB', 'MB', 'GB']
    let valore = byte / 1024
    let indice = 0

    while (valore >= 1024 && indice < unita.length - 1) {
        valore /= 1024
        indice++
    }

    return `${valore.toFixed(valore >= 10 || indice === 0 ? 0 : 1).replace('.', ',')} ${unita[indice]}`
}

/**
 * @param {File} file
 * @param {object} [config] soglie e limiti, forniti dal server
 * @param {(percentuale: number) => void} [onProgresso]
 * @returns {Promise<{file: File, originale: number, finale: number, ridotto: boolean, risparmio: number, motivo?: string}>}
 */
export async function comprimiFile(file, config = {}, onProgresso = null) {
    const originale = file.size
    let esito

    if (file.type.startsWith('image/')) {
        esito = await comprimiImmagine(file, config.immagini ?? {})
    } else if (file.type.startsWith('video/')) {
        esito = await comprimiVideo(file, config.video ?? {}, onProgresso)
    } else {
        esito = { file, ridotto: false, motivo: 'tipo non gestito' }
    }

    const finale = esito.file.size

    return {
        ...esito,
        originale,
        finale,
        risparmio: originale > 0 ? Math.max(0, Math.round((1 - finale / originale) * 100)) : 0,
    }
}

/** Descrizione leggibile, mostrata accanto a ogni file nella schermata del Buyer. */
export function descriviEsito(esito) {
    if (!esito.ridotto) {
        return `${formattaByte(esito.originale)} — inviato senza compressione (${esito.motivo ?? 'non necessaria'})`
    }

    return `${formattaByte(esito.originale)} → ${formattaByte(esito.finale)} (−${esito.risparmio}%)`
}
