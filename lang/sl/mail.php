<?php

return [

    // Auth
    'verify_email' => [
        'subject' => 'Potrdite vaš e-poštni naslov',
        'title' => 'Potrdite vaš e-poštni naslov',
        'body' => 'Hvala za registracijo na :app. Prosimo, potrdite svoj e-poštni naslov s klikom na spodnji gumb.',
        'btn' => 'Potrdite e-poštni naslov',
        'expire_note' => 'Ta povezava poteče v :minutes minutah.',
        'no_account_note' => 'Če niste ustvarili računa, ni potrebno ukrepati.',
        'fallback_note' => 'Če zgornji gumb ne deluje, kopirajte in prilepite naslednjo povezavo v brskalnik:',
    ],

    'reset_password' => [
        'subject' => 'Ponastavite geslo',
        'title' => 'Ponastavite geslo',
        'body' => 'To e-poštno sporočilo ste prejeli, ker smo prejeli zahtevo za ponastavitev gesla za vaš račun. Kliknite spodnji gumb za ponastavitev gesla.',
        'btn' => 'Ponastavi geslo',
        'expire_note' => 'Ta povezava za ponastavitev gesla poteče v :minutes minutah.',
        'no_request_note' => 'Če niste zahtevali ponastavitve gesla, ni potrebno ukrepati.',
        'fallback_note' => 'Če zgornji gumb ne deluje, kopirajte in prilepite naslednjo povezavo v brskalnik:',
    ],

    // Shared booking detail labels
    'label_property' => 'Nastanitev',
    'label_guest' => 'Gost',
    'label_check_in' => 'Prijava',
    'label_check_out' => 'Odjava',
    'label_nights' => 'Noči',
    'label_guests' => 'Gostje',
    'label_booking_id' => 'ID rezervacije',
    'label_total' => 'Skupaj',
    'label_estimated_total' => 'Ocenjeno skupaj',
    'label_reason' => 'Razlog',
    'label_refund' => 'Povračilo',
    'no_refund' => 'Brez povračila (nepovrativa politika)',
    'guest_notes_label' => 'Opombe gosta:',
    'your_notes_label' => 'Vaše opombe:',

    // Booking — requested (guest)
    'booking_requested_guest' => [
        'subject' => 'Zahteva za rezervacijo je poslana — :title',
        'title' => 'Zahteva za rezervacijo je poslana',
        'body' => 'Vaša zahteva za rezervacijo je bila poslana gostitelju. Potrditev boste prejeli, ko se bo odzval(-a).',
        'note' => 'Datumi ne bodo blokirani, dokler gostitelj ne potrdi vaše zahteve.',
    ],

    // Booking — requested (host)
    'booking_requested_host' => [
        'subject' => 'Nova zahteva za rezervacijo — :title',
        'title' => 'Nova zahteva za rezervacijo',
        'body' => 'Prispela je nova zahteva za rezervacijo za :property. Prijavite se za potrditev ali zavrnitev.',
        'btn' => 'Pregledaj zahtevo',
        'dashboard_note' => 'Ali se prijavite v vašo :link za potrditev ali zavrnitev.',
        'dashboard_link' => 'nadzorno ploščo gostitelja',
    ],

    // Booking — confirmed (guest)
    'booking_confirmed_guest' => [
        'subject' => 'Rezervacija potrjena — :title',
        'title' => '✓ Rezervacija potrjena',
        'body' => 'Vaša rezervacija je bila potrjena. Tukaj so podrobnosti:',
        'note' => 'Če imate vprašanja, kontaktirajte gostitelja neposredno prek Acomody.',
    ],

    // Booking — confirmed (host)
    'booking_confirmed_host' => [
        'subject' => 'Nova rezervacija potrjena — :title',
        'title' => '✓ Nova rezervacija potrjena',
        'body' => 'Nova rezervacija za :property je bila potrjena. Tukaj so podrobnosti:',
        'btn' => 'Oglejte si v nadzorni plošči',
    ],

    // Booking — cancelled (guest)
    'booking_cancelled_guest' => [
        'subject' => 'Rezervacija preklicana — :title',
        'title' => 'Rezervacija preklicana',
        'body' => 'Vaša rezervacija za :property je bila preklicana.',
        'note' => 'Če menite, da je prišlo do napake, nas kontaktirajte na Acomody.',
    ],

    // Booking — cancelled (host)
    'booking_cancelled_host' => [
        'subject' => 'Rezervacija preklicana — :title',
        'title' => 'Gost je preklical rezervacijo',
        'body' => 'Gost je preklical rezervacijo za :property. Datumi so zdaj spet na voljo.',
    ],

    // Booking — declined (guest)
    'booking_declined' => [
        'subject' => 'Zahteva za rezervacijo je zavrnjena — :title',
        'title' => 'Zahteva za rezervacijo je zavrnjena',
        'body' => 'Na žalost je gostitelj zavrnil vašo zahtevo za rezervacijo za :property.',
        'footer' => 'Plačilo ni bilo izvedeno. Poiščite druge razpoložljive nastanitve na Acomody.',
    ],

    // Accommodation — approved
    'accommodation_approved' => [
        'subject' => 'Vaša nastanitev je odobrena',
        'title' => '✓ Nastanitev odobrena',
        'body' => 'Odlična novica! Vaša nastanitev :property je bila pregledana in odobrena s strani naše ekipe.',
        'now_live' => 'Vaš oglas je zdaj aktiven na Acomody in gostje lahko začnejo z iskanjem in rezervacijo.',
        'warning_title' => 'Še en korak do objave',
        'warning_body' => 'Vaš oglas je odobren, vendar se ne bo pojavil v rezultatih iskanja, dokler ne izpolnite profila gostitelja (prikazno ime, kontaktni e-naslov in telefonska številka). Traja le minuto.',
        'warning_btn' => 'Izpolnite profil gostitelja →',
    ],

    // Accommodation — rejected
    'accommodation_rejected' => [
        'subject' => 'Nastanitev ni odobrena',
        'title' => 'Nastanitev ni odobrena',
        'body1' => 'Hvala za oddajo vaše nastanitve :property v pregled.',
        'body2' => 'Po natančnem pregledu naša ekipa tokrat ni mogla odobriti vaše prijave.',
        'reason_label' => 'Razlog',
        'resubmit' => 'Če menite, da je prišlo do napake ali ste opravili potrebne spremembe, ste dobrodošli, da znova oddate nastanitev v pregled.',
    ],

    // Accommodation — draft submitted
    'draft_submitted' => [
        'subject' => 'Nastanitev je v pregledu',
        'title' => 'Nastanitev je v pregledu',
        'body' => 'Vaša nastanitev :property je bila uspešno oddana in je zdaj v pregledu naše ekipe.',
        'next' => 'Tukaj je, kaj se zgodi naprej:',
        'step1' => 'Naša ekipa pregleda vaš oglas — to običajno traja 1–2 delovna dneva.',
        'step2' => 'Ko bo odobreno, bo vaš oglas samodejno postal iskljiv in gostje bodo lahko začeli z rezervacijo.',
        'step3' => 'Prejeli boste e-poštno obvestilo, ko bo objavljen.',
        'btn' => 'Pojdite na nadzorno ploščo →',
    ],

    // Accommodation — draft submitted (profile incomplete)
    'draft_submitted_incomplete' => [
        'subject' => 'Nastanitev je v pregledu',
        'title' => 'Nastanitev je v pregledu',
        'body' => 'Vaša nastanitev :property je bila uspešno oddana in je zdaj v pregledu. Obvestili vas bomo, ko bo odobrena.',
        'warning_title' => 'Izpolnite profil gostitelja za objavo',
        'warning_body' => 'Po odobritvi se vaš oglas ne bo pojavil v rezultatih iskanja, dokler profil gostitelja ni izpolnjen. Prosimo, dodajte prikazno ime, kontaktni e-naslov in telefonsko številko — traja le minuto.',
        'warning_btn' => 'Izpolnite profil gostitelja →',
    ],

    // Accommodation — review comment
    'review_comment' => [
        'subject' => 'Komentar recenzenta o vaši prijavi',
        'title' => 'Komentar recenzenta',
        'body1' => 'Naša ekipa za pregled je pustila komentar k vaši prijavi nastanitve :property.',
        'body2' => 'Prosimo, preglejte komentar in opravite vse potrebne spremembe pri vaši prijavi.',
    ],

    // Host — listings now live
    'listings_live' => [
        'subject' => 'Vaši oglasi so zdaj aktivni na Acomody',
        'title' => '✓ Vaši oglasi so zdaj aktivni!',
        'body_singular' => 'Vaš profil gostitelja je zdaj izpolnjen — vaš oglas je zdaj iskljiv na Acomody in gostje lahko začnejo z rezervacijo.',
        'body_plural' => 'Vaš profil gostitelja je zdaj izpolnjen — vaši :count oglasi so zdaj iskljivi na Acomody in gostje lahko začnejo z rezervacijo.',
        'btn' => 'Pojdite na nadzorno ploščo →',
    ],

    // Common
    'hi' => 'Pozdravljeni :name,',
    'support_note' => 'Če imate vprašanja, kontaktirajte našo ekipo za podporo.',
    'copyright' => 'Vse pravice pridržane.',

];
