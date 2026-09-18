import { comprimiFile, descriviEsito, formattaByte, supportaCompressioneVideo } from './compressione/index.js'

/**
 * Componente Alpine del caricatore media.
 *
 * Comprime i file scelti dal Buyer e solo dopo li consegna all'input collegato
 * a Livewire, che si occupa dell'upload vero e proprio. Se la compressione non
 * è possibile, l'originale passa comunque: nessun percorso è bloccato.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('caricatoreMedia', (config = {}) => ({
        config,
        elaborazione: false,
        supportoVideo: true,
        elementi: [],

        async init() {
            this.supportoVideo = await supportaCompressioneVideo()
        },

        async seleziona(evento) {
            const scelti = Array.from(evento.target.files ?? [])

            if (scelti.length === 0) {
                return
            }

            this.elaborazione = true
            this.elementi = scelti.map((file) => ({
                nome: file.name,
                stato: 'In coda',
                percentuale: 0,
                dettaglio: formattaByte(file.size),
            }))

            const pronti = []

            for (const [indice, file] of scelti.entries()) {
                this.elementi[indice].stato = file.type.startsWith('video/') ? 'Comprimo il video…' : 'Comprimo…'

                const esito = await comprimiFile(
                    file,
                    this.config,
                    (percentuale) => { this.elementi[indice].percentuale = percentuale },
                )

                this.elementi[indice].stato = esito.ridotto ? 'Compresso' : 'Pronto'
                this.elementi[indice].percentuale = 100
                this.elementi[indice].dettaglio = descriviEsito(esito)

                pronti.push(esito.file)
            }

            this.consegnaALivewire(pronti)
            this.elaborazione = false

            // Permette di riselezionare lo stesso file dopo un annullamento.
            evento.target.value = ''
        },

        /**
         * Passa i file compressi all'input con wire:model, così l'upload resta
         * quello standard di Livewire (chunking, progresso, validazione).
         */
        consegnaALivewire(file) {
            const trasferimento = new DataTransfer()
            file.forEach((f) => trasferimento.items.add(f))

            const input = this.$refs.inputLivewire
            input.files = trasferimento.files
            input.dispatchEvent(new Event('change', { bubbles: true }))
        },

        pulisci() {
            this.elementi = []
        },
    }))
})
