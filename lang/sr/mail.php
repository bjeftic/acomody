<?php

return [

    // Auth
    'verify_email' => [
        'subject' => 'Potvrdite vašu email adresu',
        'title' => 'Potvrdite vašu email adresu',
        'body' => 'Hvala što ste se registrovali na :app. Molimo potvrdite svoju email adresu klikom na dugme ispod.',
        'btn' => 'Potvrdite email adresu',
        'expire_note' => 'Ovaj link ističe za :minutes minuta.',
        'no_account_note' => 'Ako niste kreirali nalog, nije potrebna nikakva dalja radnja.',
        'fallback_note' => 'Ako dugme iznad ne radi, kopirajte i nalepite sledeći link u pregledač:',
    ],

    'reset_password' => [
        'subject' => 'Resetujte svoju lozinku',
        'title' => 'Resetujte svoju lozinku',
        'body' => 'Primili ste ovaj email jer smo dobili zahtev za resetovanje lozinke za vaš nalog. Kliknite na dugme ispod da biste resetovali lozinku.',
        'btn' => 'Resetujte lozinku',
        'expire_note' => 'Ovaj link za resetovanje lozinke ističe za :minutes minuta.',
        'no_request_note' => 'Ako niste tražili resetovanje lozinke, nije potrebna nikakva dalja radnja.',
        'fallback_note' => 'Ako dugme iznad ne radi, kopirajte i nalepite sledeći link u pregledač:',
    ],

    // Shared booking detail labels
    'label_property' => 'Smeštaj',
    'label_guest' => 'Gost',
    'label_check_in' => 'Prijava',
    'label_check_out' => 'Odjava',
    'label_nights' => 'Noći',
    'label_guests' => 'Gosti',
    'label_booking_id' => 'ID rezervacije',
    'label_total' => 'Ukupno',
    'label_estimated_total' => 'Procenjeno ukupno',
    'label_reason' => 'Razlog',
    'label_refund' => 'Povrat',
    'no_refund' => 'Nema povrata (nerefundabilna politika)',
    'guest_notes_label' => 'Napomene gosta:',
    'your_notes_label' => 'Vaše napomene:',

    // Booking — requested (guest)
    'booking_requested_guest' => [
        'subject' => 'Zahtev za rezervaciju je poslat — :title',
        'title' => 'Zahtev za rezervaciju je poslat',
        'body' => 'Vaš zahtev za rezervaciju je poslat domaćinu. Dobićete potvrdu čim oni odgovore.',
        'note' => 'Datumi neće biti blokirani dok domaćin ne potvrdi vaš zahtev.',
    ],

    // Booking — requested (host)
    'booking_requested_host' => [
        'subject' => 'Novi zahtev za rezervaciju — :title',
        'title' => 'Novi zahtev za rezervaciju',
        'body' => 'Imate novi zahtev za rezervaciju za :property. Prijavite se da biste potvrdili ili odbili.',
        'btn' => 'Pregledajte zahtev',
        'dashboard_note' => 'Ili se prijavite na vaš :link da biste potvrdili ili odbili.',
        'dashboard_link' => 'upravljački panel domaćina',
    ],

    // Booking — confirmed (guest)
    'booking_confirmed_guest' => [
        'subject' => 'Rezervacija potvrđena — :title',
        'title' => '✓ Rezervacija potvrđena',
        'body' => 'Vaša rezervacija je potvrđena. Evo detalja:',
        'note' => 'Ako imate pitanja, kontaktirajte domaćina direktno putem Acomody.',
    ],

    // Booking — confirmed (host)
    'booking_confirmed_host' => [
        'subject' => 'Nova rezervacija potvrđena — :title',
        'title' => '✓ Nova rezervacija potvrđena',
        'body' => 'Nova rezervacija za :property je potvrđena. Evo detalja:',
        'btn' => 'Pogledajte u upravljačkom panelu',
    ],

    // Booking — cancelled (guest)
    'booking_cancelled_guest' => [
        'subject' => 'Rezervacija otkazana — :title',
        'title' => 'Rezervacija otkazana',
        'body' => 'Vaša rezervacija za :property je otkazana.',
        'note' => 'Ako smatrate da je ovo greška, kontaktirajte nas na Acomody.',
    ],

    // Booking — cancelled (host)
    'booking_cancelled_host' => [
        'subject' => 'Rezervacija otkazana — :title',
        'title' => 'Rezervaciju je otkazao gost',
        'body' => 'Gost je otkazao rezervaciju za :property. Datumi su sada opet slobodni.',
    ],

    // Booking — declined (guest)
    'booking_declined' => [
        'subject' => 'Zahtev za rezervaciju je odbijen — :title',
        'title' => 'Zahtev za rezervaciju je odbijen',
        'body' => 'Nažalost, domaćin je odbio vaš zahtev za rezervaciju za :property.',
        'footer' => 'Plaćanje nije izvršeno. Možete potražiti druge dostupne smeštaje na Acomody.',
    ],

    // Accommodation — approved
    'accommodation_approved' => [
        'subject' => 'Vaš smeštaj je odobren',
        'title' => '✓ Smeštaj odobren',
        'body' => 'Odlične vesti! Vaš smeštaj :property je pregledan i odobren od strane našeg tima.',
        'now_live' => 'Vaš oglas je sada aktivan na Acomody i gosti mogu početi s pretragom i rezervacijom.',
        'warning_title' => 'Još jedan korak do objave',
        'warning_body' => 'Vaš oglas je odobren, ali neće se pojaviti u rezultatima pretrage dok ne popunite profil domaćina (ime za prikaz, kontakt email i broj telefona). Traje samo minut.',
        'warning_btn' => 'Popunite profil domaćina →',
    ],

    // Accommodation — rejected
    'accommodation_rejected' => [
        'subject' => 'Smeštaj nije odobren',
        'title' => 'Smeštaj nije odobren',
        'body1' => 'Hvala što ste poslali vaš smeštaj :property na pregled.',
        'body2' => 'Nakon pažljivog pregleda, naš tim nije mogao odobriti vaš zahtev u ovom trenutku.',
        'reason_label' => 'Razlog',
        'resubmit' => 'Ako smatrate da je ovo greška ili ste napravili potrebne izmene, slobodni ste da ponovo pošaljete smeštaj na pregled.',
    ],

    // Accommodation — draft submitted
    'draft_submitted' => [
        'subject' => 'Smeštaj je na pregledu',
        'title' => 'Smeštaj je na pregledu',
        'body' => 'Vaš smeštaj :property je uspešno poslat i sada je na pregledu od strane našeg tima.',
        'next' => 'Evo šta se dešava dalje:',
        'step1' => 'Naš tim pregleda vaš oglas — ovo obično traje 1–2 radna dana.',
        'step2' => 'Nakon odobrenja, vaš oglas će automatski postati pretraživ i gosti mogu početi s rezervacijom.',
        'step3' => 'Dobićete email obaveštenje čim oglas bude objavljen.',
        'btn' => 'Idite na upravljački panel →',
    ],

    // Accommodation — draft submitted (profile incomplete)
    'draft_submitted_incomplete' => [
        'subject' => 'Smeštaj je na pregledu',
        'title' => 'Smeštaj je na pregledu',
        'body' => 'Vaš smeštaj :property je uspešno poslat i sada je na pregledu od strane našeg tima. Obavestićemo vas čim bude odobren.',
        'warning_title' => 'Popunite profil domaćina da biste bili vidljivi',
        'warning_body' => 'Nakon odobrenja, vaš oglas se neće pojaviti u rezultatima pretrage dok profil domaćina nije popunjen. Molimo dodajte ime za prikaz, kontakt email i broj telefona — traje samo minut.',
        'warning_btn' => 'Popunite profil domaćina →',
    ],

    // Accommodation — review comment
    'review_comment' => [
        'subject' => 'Komentar recenzenta o vašem zahtevu',
        'title' => 'Komentar recenzenta',
        'body1' => 'Naš tim za pregled je ostavio komentar na vaš zahtev za smeštaj :property.',
        'body2' => 'Molimo pregledajte komentar i napravite sve potrebne izmene na vašem zahtevu.',
    ],

    // Host — listings now live
    'listings_live' => [
        'subject' => 'Vaši oglasi su sada aktivni na Acomody',
        'title' => '✓ Vaši oglasi su sada aktivni!',
        'body_singular' => 'Vaš profil domaćina je sada popunjen — vaš oglas je sada pretraživ na Acomody i gosti mogu početi s rezervacijom.',
        'body_plural' => 'Vaš profil domaćina je sada popunjen — vaših :count oglasa je sada pretraživo na Acomody i gosti mogu početi s rezervacijom.',
        'btn' => 'Idite na upravljački panel →',
    ],

    // Common
    'hi' => 'Zdravo :name,',
    'support_note' => 'Ako imate pitanja, kontaktirajte naš tim za podršku.',
    'copyright' => 'Sva prava zadržana.',

];
