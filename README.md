# Processes

Pacchetto Laravel per processi, operatori, strumenti, tempi pianificati ed effettivi,
stati e storico delle transizioni. Si integra con i pacchetti IlBronza CRUD, Form,
FormField, Menu e Operators dell'applicazione ospite.

## Scrittura dei processi

Usare gli helper anche nelle integrazioni applicative:

```php
use IlBronza\Processes\Helpers\ProcessCreatorHelper;
use IlBronza\Processes\Helpers\ProcessUpdaterHelper;
use IlBronza\Processes\Helpers\ProcessCloserHelper;

$process = ProcessCreatorHelper::create([
    'type' => 'production',
    'operator_id' => $operator->getKey(),
    'planned_starts_at' => '2026-10-16 09:00:00',
    'planned_ends_at' => '2026-10-16 10:00:00',
], ['user' => auth()->user()]);

$process = ProcessUpdaterHelper::update($process, [
    'process_status_id' => $plannedStatus->getKey(),
], ['reason' => 'Pianificazione confermata', 'user' => auth()->user()]);

$process = ProcessCloserHelper::close($process, [
    'status' => $finalStatus,
    'reason' => 'Lavoro terminato',
    'user' => auth()->user(),
    'set_ends_at' => true,
]);
```

La transizione richiesta deve essere configurata e consentita per lo stato di
partenza e la tipologia. `ProcessCloserHelper` salva chiusura, eventuale stato e
storico nella stessa transazione. Lo stato finale non implica automaticamente
una chiusura: usare l'helper o compilare `closed_at` nel CRUD.

Il CRUD usa questi helper nei form e nell'editor delle tabelle. `status_reason`
finisce nello storico, non nella tabella dei processi. Se lo stato iniziale è
omesso, viene scelto quello predefinito della tipologia, oppure uno stato iniziale
attivo. `processes.behavior.create_initial_status_log` controlla il log iniziale.

## Regole di validazione

- Operatore, strumento e oggetto collegato rispettano i requisiti della tipologia.
  Per le relazioni polimorfiche devono essere presenti sia tipo sia ID.
- La fine non precede l'inizio; una fine richiede il corrispondente inizio.
- Le sovrapposizioni sono verificate separatamente sui tempi effettivi e su quelli
  pianificati, per operatore, strumento e oggetto. Un inizio senza fine rappresenta
  un intervallo aperto. Intervalli adiacenti e durate nulle non si sovrappongono.
- La verifica esclude il processo in modifica e si ripete quando cambiano date,
  risorse o tipologia. `allows_overlaps` della tipologia e
  `processes.behavior.validate_overlaps` permettono di disabilitarla.
- Un processo non può essere padre di sé stesso o creare cicli nella gerarchia.

Questi controlli vengono eseguiti anche dal normale `save()` Eloquent. Gli update
SQL diretti e le scritture che disattivano gli eventi Eloquent li aggirano.
Le verifiche sulle sovrapposizioni non costituiscono un vincolo univoco del database:
prenotazioni concorrenti della stessa risorsa richiedono una serializzazione
nell'applicazione ospite.

## Blocco e storico

Nel CRUD, `is_locked = false` sblocca esplicitamente il processo e consente di
modificarlo nella stessa transazione, con i normali permessi di aggiornamento.
I campi di autore e data del blocco e della chiusura sono gestiti dagli helper.
Rimuovere `closed_at` azzera anche `closed_by_user_id`.

Una chiusura tramite helper su un processo bloccato richiede `force => true`.
Questo non aggira i permessi o le regole della transizione di stato.
I log non utilizzano soft delete: la tabella non contiene `deleted_at` e la
cancellazione diretta tramite il modello è rifiutata. La cancellazione fisica del
processo elimina lo storico tramite il vincolo del database.

## Test

Dalla cartella che contiene i pacchetti IlBronza:

```sh
python3 .ibtest/bin/ibtest.py Processes
```

La suite usa il vendor condiviso, Laravel Testbench e SQLite in memoria, con le
migrazioni reali del pacchetto. I test HTTP esercitano la persistenza del controller;
la fixture sostituisce autorizzazione e presentazione specifiche dell'app ospite.
Non verifica il rendering completo del gestionale o la concorrenza su MySQL.

La generazione dei form per i campi personalizzati per tipologia e la timeline
visuale restano da implementare.
