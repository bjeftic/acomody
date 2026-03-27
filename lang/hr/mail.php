<?php

return [

    // Auth
    'verify_email' => [
        'subject' => 'Potvrdite svoju email adresu',
        'title' => 'Potvrdite svoju email adresu',
        'body' => 'Hvala što ste se registrirali na :app. Molimo potvrdite svoju email adresu klikom na gumb ispod.',
        'btn' => 'Potvrdite email adresu',
        'expire_note' => 'Ovaj link ističe za :minutes minuta.',
        'no_account_note' => 'Ako niste kreirali račun, nije potrebna nikakva daljnja radnja.',
        'fallback_note' => 'Ako gumb iznad ne radi, kopirajte i zalijepite sljedeći link u preglednik:',
    ],

    'reset_password' => [
        'subject' => 'Resetirajte svoju lozinku',
        'title' => 'Resetirajte svoju lozinku',
        'body' => 'Primili ste ovaj email jer smo primili zahtjev za resetiranje lozinke za vaš račun. Kliknite gumb ispod za resetiranje lozinke.',
        'btn' => 'Resetirajte lozinku',
        'expire_note' => 'Ovaj link za resetiranje lozinke ističe za :minutes minuta.',
        'no_request_note' => 'Ako niste tražili resetiranje lozinke, nije potrebna nikakva daljnja radnja.',
        'fallback_note' => 'Ako gumb iznad ne radi, kopirajte i zalijepite sljedeći link u preglednik:',
    ],

    // Shared booking detail labels
    'label_property' => 'Smještaj',
    'label_guest' => 'Gost',
    'label_check_in' => 'Prijava',
    'label_check_out' => 'Odjava',
    'label_nights' => 'Noći',
    'label_guests' => 'Gosti',
    'label_booking_id' => 'ID rezervacije',
    'label_total' => 'Ukupno',
    'label_estimated_total' => 'Procijenjeno ukupno',
    'label_reason' => 'Razlog',
    'label_refund' => 'Povrat',
    'no_refund' => 'Nema povrata (nerefundabilna politika)',
    'guest_notes_label' => 'Napomene gosta:',
    'your_notes_label' => 'Vaše napomene:',

    // Booking — requested (guest)
    'booking_requested_guest' => [
        'subject' => 'Zahtjev za rezervaciju je poslan — :title',
        'title' => 'Zahtjev za rezervaciju je poslan',
        'body' => 'Vaš zahtjev za rezervaciju je poslan domaćinu. Dobit ćete potvrdu čim oni odgovore.',
        'note' => 'Datumi neće biti blokirani dok domaćin ne potvrdi vaš zahtjev.',
    ],

    // Booking — requested (host)
    'booking_requested_host' => [
        'subject' => 'Novi zahtjev za rezervaciju — :title',
        'title' => 'Novi zahtjev za rezervaciju',
        'body' => 'Imate novi zahtjev za rezervaciju za :property. Prijavite se za potvrdu ili odbijanje.',
        'btn' => 'Pregledajte zahtjev',
        'dashboard_note' => 'Ili se prijavite na vašu :link za potvrdu ili odbijanje.',
        'dashboard_link' => 'upravljačku ploču domaćina',
    ],

    // Booking — confirmed (guest)
    'booking_confirmed_guest' => [
        'subject' => 'Rezervacija potvrđena — :title',
        'title' => '✓ Rezervacija potvrđena',
        'body' => 'Vaša rezervacija je potvrđena. Evo detalja:',
        'note' => 'Ako imate pitanja, kontaktirajte domaćina izravno putem Acomody.',
    ],

    // Booking — confirmed (host)
    'booking_confirmed_host' => [
        'subject' => 'Nova rezervacija potvrđena — :title',
        'title' => '✓ Nova rezervacija potvrđena',
        'body' => 'Nova rezervacija za :property je potvrđena. Evo detalja:',
        'btn' => 'Pogledajte u upravljačkoj ploči',
    ],

    // Booking — cancelled (guest)
    'booking_cancelled_guest' => [
        'subject' => 'Rezervacija otkazana — :title',
        'title' => 'Rezervacija otkazana',
        'body' => 'Vaša rezervacija za :property je otkazana.',
        'note' => 'Ako smatrate da je ovo pogreška, kontaktirajte nas na Acomody.',
    ],

    // Booking — cancelled (host)
    'booking_cancelled_host' => [
        'subject' => 'Rezervacija otkazana — :title',
        'title' => 'Rezervaciju je otkazao gost',
        'body' => 'Gost je otkazao rezervaciju za :property. Datumi su sada opet slobodni.',
    ],

    // Booking — declined (guest)
    'booking_declined' => [
        'subject' => 'Zahtjev za rezervaciju je odbijen — :title',
        'title' => 'Zahtjev za rezervaciju je odbijen',
        'body' => 'Nažalost, domaćin je odbio vaš zahtjev za rezervaciju za :property.',
        'footer' => 'Plaćanje nije izvršeno. Možete potražiti druge dostupne smještaje na Acomody.',
    ],

    // Accommodation — approved
    'accommodation_approved' => [
        'subject' => 'Vaš smještaj je odobren',
        'title' => '✓ Smještaj odobren',
        'body' => 'Odlične vijesti! Vaš smještaj :property je pregledan i odobren od strane našeg tima.',
        'now_live' => 'Vaš oglas je sada aktivan na Acomody i gosti mogu početi s pretragom i rezervacijom.',
        'warning_title' => 'Još jedan korak do objave',
        'warning_body' => 'Vaš oglas je odobren, ali neće se pojaviti u rezultatima pretrage dok ne ispunite profil domaćina (ime za prikaz, kontakt email i broj telefona). Traje samo minutu.',
        'warning_btn' => 'Ispunite profil domaćina →',
    ],

    // Accommodation — rejected
    'accommodation_rejected' => [
        'subject' => 'Smještaj nije odobren',
        'title' => 'Smještaj nije odobren',
        'body1' => 'Hvala što ste poslali vaš smještaj :property na pregled.',
        'body2' => 'Nakon pažljivog pregleda, naš tim nije mogao odobriti vaš zahtjev u ovom trenutku.',
        'reason_label' => 'Razlog',
        'resubmit' => 'Ako smatrate da je ovo pogreška ili ste napravili potrebne izmjene, slobodni ste ponovo poslati smještaj na pregled.',
    ],

    // Accommodation — draft submitted
    'draft_submitted' => [
        'subject' => 'Smještaj je na pregledu',
        'title' => 'Smještaj je na pregledu',
        'body' => 'Vaš smještaj :property je uspješno poslan i sada je na pregledu od strane našeg tima.',
        'next' => 'Evo što se događa dalje:',
        'step1' => 'Naš tim pregledava vaš oglas — ovo obično traje 24 časa.',
        'step2' => 'Nakon odobrenja, vaš oglas će automatski postati pretraživ i gosti mogu početi s rezervacijom.',
        'step3' => 'Dobit ćete email obavijest čim oglas bude objavljen.',
        'btn' => 'Idite na upravljačku ploču →',
    ],

    // Accommodation — draft submitted (profile incomplete)
    'draft_submitted_incomplete' => [
        'subject' => 'Smještaj je na pregledu',
        'title' => 'Smještaj je na pregledu',
        'body' => 'Vaš smještaj :property je uspješno poslan i sada je na pregledu od strane našeg tima. Obavijestit ćemo vas čim bude odobren.',
        'warning_title' => 'Ispunite profil domaćina da biste bili vidljivi',
        'warning_body' => 'Nakon odobrenja, vaš oglas se neće pojaviti u rezultatima pretrage dok profil domaćina nije ispunjen. Molimo dodajte ime za prikaz, kontakt email i broj telefona — traje samo minutu.',
        'warning_btn' => 'Ispunite profil domaćina →',
    ],

    // Accommodation — review comment
    'review_comment' => [
        'subject' => 'Komentar recenzenta o vašem zahtjevu',
        'title' => 'Komentar recenzenta',
        'body1' => 'Naš tim za pregled je ostavio komentar na vaš zahtjev za smještaj :property.',
        'body2' => 'Molimo pregledajte komentar i napravite sve potrebne izmjene na vašem zahtjevu.',
    ],

    // Host — listings now live
    'listings_live' => [
        'subject' => 'Vaši oglasi su sada aktivni na Acomody',
        'title' => '✓ Vaši oglasi su sada aktivni!',
        'body_singular' => 'Vaš profil domaćina je sada ispunjen — vaš oglas je sada pretraživ na Acomody i gosti mogu početi s rezervacijom.',
        'body_plural' => 'Vaš profil domaćina je sada ispunjen — vaših :count oglasa je sada pretraživo na Acomody i gosti mogu početi s rezervacijom.',
        'btn' => 'Idite na upravljačku ploču →',
    ],

    // Common
    'hi' => 'Zdravo :name,',
    'support_note' => 'Ako imate pitanja, kontaktirajte naš tim za podršku.',
    'copyright' => 'Sva prava pridržana.',

];
