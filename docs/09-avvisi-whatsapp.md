# 9. Avvisi su WhatsApp

## 9.1 Il contesto: WhatsApp personale

In azienda si usa **WhatsApp personale, non Business**. Questo esclude l'invio automatico: l'API
ufficiale esiste solo per gli account Business, richiede un numero verificato e ha un costo.

Tutto passa quindi dai collegamenti **«click to chat»** (`wa.me`), che funzionano con qualunque
WhatsApp, su telefono e su WhatsApp Web, senza configurare nulla:

| Destinatario | Come funziona |
|---|---|
| Una **persona** con il numero in anagrafica | il collegamento apre **quella conversazione**, messaggio già scritto: resta da premere invio |
| Una **persona senza numero** | si apre l'elenco delle chat e si sceglie |
| Un **gruppo** | si apre l'elenco delle chat, si sceglie il gruppo e si invia |

Nessuna API ufficiale permette di scrivere in un gruppo, nemmeno con un account Business: l'ultimo
tocco resta della persona. Esistono librerie non ufficiali che pilotano un account reale e ci
riescono, ma violano le condizioni d'uso e rischiano il blocco del numero — su numeri personali di
colleghi il rischio ricadrebbe su di loro, e per questo non sono state usate.

**Il numero in anagrafica fa la differenza:** con il numero è un tocco solo, senza sono due. Vale
la pena compilarlo per tutti dalla gestione utenti.

## 9.2 I tre momenti

| Momento | Chi agisce | In app | Su WhatsApp |
|---|---|---|---|
| Opportunità inviata in verifica | Buyer | notifica automatica a tutti i Tecnici | un pulsante **per ciascun Tecnico**, che apre la sua chat |
| Opportunità approvata e aperta | Tecnico | notifica automatica ai capi reparto destinatari | «Condividi nel gruppo WhatsApp» |
| Punto vendita conferma l'ordine | Capo Reparto | notifica automatica a Buyer e Tecnici | «Comunica al gruppo», nella ricevuta |
| Qualcuno non ha ancora risposto | Buyer o Tecnico | sollecito in-app | un pulsante **per ciascun punto vendita mancante**, che apre la chat del suo capo reparto |
| Buyer modifica e ripubblica | Buyer | notifica ai Tecnici e ai punti vendita | pulsante per ciascun Tecnico, con il testo «Modificata, da confermare» |
| Tecnico conferma la modifica | Tecnico | notifica ai punti vendita | «Comunica la modifica nel gruppo», con il testo che invita a ricontrollare |

Ogni messaggio porta il collegamento alla scheda, che richiede autenticazione. WhatsApp resta un
canale di avviso: **l'ordine è valido solo dall'app**, e i messaggi non contengono pulsanti che
registrino quantità.

## 9.2-bis Un solo indirizzo per tutti

Un messaggio finisce in un gruppo misto: Buyer, Tecnici e capi reparto aprono lo **stesso**
collegamento, ma ognuno ha la propria schermata. Mettere nel testo l'indirizzo di una sola vista
condannava tutti gli altri a un `403 — non hai i permessi per accedere a questa sezione`.

Tutti i messaggi e tutte le notifiche usano quindi un indirizzo neutro:

```
https://<dominio>/o/<id>
```

Chi lo apre viene smistato verso la propria vista: i capi reparto alla scheda da cui si ordina,
Buyer, Tecnici e Super Admin alla scheda completa con le risposte. Chi non ha ancora fatto
l'accesso passa dal login e viene poi riportato lì, quindi il collegamento funziona anche da un
telefono che non ha mai aperto l'applicazione.

Un capo reparto di un punto vendita non destinatario riceve un messaggio comprensibile —
«questa opportunità non è destinata al tuo punto vendita» — e non il generico errore di permessi.

## 9.3 Come sono fatti i messaggi

```
🐟 Orata fresca — consegna giovedì
ART10001 · PLU 2101
€ 7,98/kg al pubblico · 6,0 kg per collo
40 colli disponibili
📅 Consegna 20/09/2026
⏱ Rispondere entro il 18/09/2026 18:00

Ordina qui: https://…/o/12

Le risposte valgono solo dall'app.
```

```
✅ PV001 ordina 3 colli (18,0 kg)
Orata fresca · ART10001
Totale finora: 11 colli (66,0 kg)
Restano 29 colli

Scheda: https://…/o/12
```

I testi sono in `App\Support\WhatsApp`: cambiarli non tocca né le notifiche in-app né il resto.
Accanto a ogni pulsante c'è «Copia testo», per chi preferisce incollare a mano.

## 9.4 Se un giorno si passasse a WhatsApp Business

L'integrazione è già pronta e spenta: l'invio automatico 1:1 si attiva senza toccare il codice.

```dotenv
NOTIFY_WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://graph.facebook.com/v20.0
WHATSAPP_API_TOKEN=…
WHATSAPP_PHONE_ID=…
```

Serve un account WhatsApp Business API con un numero verificato. Gli utenti devono avere il
telefono compilato in anagrafica, altrimenti la consegna viene registrata come fallita e la
notifica in-app resta comunque l'unica obbligatoria.

Per i messaggi iniziati dall'azienda fuori dalla finestra di 24 ore Meta richiede modelli
approvati: va previsto in fase di attivazione del numero.

Con un account Business la strada conforme non è comunque il gruppo, ma **scrivere a ciascun capo
reparto**: l'applicazione sa già chi sono e per quale punto vendita rispondono. Stesso risultato,
in automatico, e nessuno resta indietro perché «non ha letto il gruppo».
