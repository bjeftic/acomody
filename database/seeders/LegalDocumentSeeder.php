<?php

namespace Database\Seeders;

use App\Enums\LegalDocument\DocumentType;
use App\Enums\LegalDocument\SectionType;
use App\Models\LegalDocument;
use App\Models\LegalDocumentSection;
use App\Models\User;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::withoutAuthorization(fn () => User::where('is_superadmin', true)->first())
            ?? User::withoutAuthorization(fn () => User::first());

        if (! $admin) {
            $this->command->warn('No users found. Skipping LegalDocumentSeeder.');

            return;
        }

        $this->seedTerms($admin->id);
        $this->seedPrivacyPolicy($admin->id);

        $this->command->info('Legal documents seeded.');
    }

    private function seedTerms(int $createdBy): void
    {
        if (LegalDocument::withoutAuthorization(fn () => LegalDocument::where('type', DocumentType::Terms)->exists())) {
            return;
        }

        $doc = LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
            'type' => DocumentType::Terms,
            'version' => 1,
            'title' => [
                'en' => 'Terms & Conditions',
                'sr' => 'Uslovi korišćenja',
                'de' => 'Allgemeine Geschäftsbedingungen',
            ],
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $createdBy,
        ]));

        $sections = [
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '1. Introduction',
                    'sr' => '1. Uvod',
                    'de' => '1. Einführung',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Welcome to Acomody. By accessing or using our platform, you agree to be bound by these Terms & Conditions. Please read them carefully before using our services. If you do not agree to these terms, you may not use the platform.',
                    'sr' => 'Dobrodošli na Acomody. Pristupanjem ili korišćenjem naše platforme, prihvatate da ste vezani ovim Uslovima korišćenja. Pažljivo ih pročitajte pre korišćenja naših usluga. Ukoliko se ne slažete sa ovim uslovima, ne smete koristiti platformu.',
                    'de' => 'Willkommen bei Acomody. Durch den Zugriff auf unsere Plattform oder deren Nutzung stimmen Sie diesen Allgemeinen Geschäftsbedingungen zu. Bitte lesen Sie diese sorgfältig durch, bevor Sie unsere Dienste nutzen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '2. Definitions',
                    'sr' => '2. Definicije',
                    'de' => '2. Definitionen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => '"Platform" refers to the Acomody website and mobile applications. "Host" refers to any user who lists an accommodation on the platform. "Guest" refers to any user who searches for or books an accommodation. "Listing" refers to any accommodation posted by a Host.',
                    'sr' => '"Platforma" se odnosi na veb sajt i mobilne aplikacije Acomody. "Domaćin" se odnosi na svakog korisnika koji postavlja smeštaj na platformu. "Gost" se odnosi na svakog korisnika koji traži ili rezerviše smeštaj. "Oglas" se odnosi na svaki smeštaj koji objavi domaćin.',
                    'de' => '"Plattform" bezieht sich auf die Acomody-Website und mobilen Anwendungen. "Gastgeber" bezeichnet jeden Nutzer, der eine Unterkunft auf der Plattform anbietet. "Gast" bezeichnet jeden Nutzer, der eine Unterkunft sucht oder bucht.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '3. User Accounts',
                    'sr' => '3. Korisnički nalozi',
                    'de' => '3. Benutzerkonten',
                ],
            ],
            [
                'section_type' => SectionType::Subheading,
                'content' => [
                    'en' => '3.1 Registration',
                    'sr' => '3.1 Registracija',
                    'de' => '3.1 Registrierung',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'You must create an account to access certain features of the platform. You agree to provide accurate, current, and complete information during registration and to keep your account information up to date. You are responsible for maintaining the confidentiality of your account credentials.',
                    'sr' => 'Morate kreirati nalog da biste pristupili određenim funkcijama platforme. Slažete se da ćete pružiti tačne, aktuelne i potpune informacije tokom registracije i da ćete održavati ažurne podatke o nalogu. Odgovorni ste za čuvanje poverljivosti podataka o svom nalogu.',
                    'de' => 'Sie müssen ein Konto erstellen, um auf bestimmte Funktionen der Plattform zugreifen zu können. Sie stimmen zu, bei der Registrierung genaue, aktuelle und vollständige Informationen bereitzustellen.',
                ],
            ],
            [
                'section_type' => SectionType::Subheading,
                'content' => [
                    'en' => '3.2 Account Security',
                    'sr' => '3.2 Bezbednost naloga',
                    'de' => '3.2 Kontosicherheit',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'You are solely responsible for all activity that occurs under your account. You must immediately notify us of any unauthorized use of your account. Acomody will not be liable for any loss or damage arising from your failure to comply with this security obligation.',
                    'sr' => 'Isključivo ste odgovorni za sve aktivnosti koje se odvijaju pod vašim nalogom. Morate nas odmah obavestiti o svakom neovlašćenom korišćenju vašeg naloga. Acomody neće biti odgovoran za bilo kakvu štetu koja proistekne iz vašeg nepoštovanja ove bezbednosne obaveze.',
                    'de' => 'Sie sind allein verantwortlich für alle Aktivitäten, die unter Ihrem Konto stattfinden. Sie müssen uns unverzüglich über jede unbefugte Nutzung Ihres Kontos benachrichtigen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '4. Host Responsibilities',
                    'sr' => '4. Obaveze domaćina',
                    'de' => '4. Pflichten des Gastgebers',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Hosts are responsible for ensuring that their listings are accurate, up to date, and comply with all applicable local laws and regulations. Hosts must have the legal right to rent the listed property and must not discriminate against guests based on race, religion, national origin, gender, disability, sexual orientation, or any other protected characteristic.',
                    'sr' => 'Domaćini su odgovorni za tačnost i ažurnost svojih oglasa, kao i za usklađenost sa svim primenjivim lokalnim zakonima i propisima. Domaćini moraju imati zakonsko pravo da iznajmljuju oglasirani smeštaj i ne smeju diskriminisati goste na osnovu rase, vere, nacionalnog porekla, pola, invaliditeta, seksualne orijentacije ili bilo koje druge zaštićene karakteristike.',
                    'de' => 'Gastgeber sind dafür verantwortlich, dass ihre Inserate korrekt und aktuell sind und alle geltenden lokalen Gesetze und Vorschriften einhalten.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '5. Booking and Payments',
                    'sr' => '5. Rezervacije i plaćanja',
                    'de' => '5. Buchungen und Zahlungen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'When a Guest makes a booking request, the Host may accept or decline. Once a booking is confirmed, both parties are bound by its terms. Payments are processed through our secure payment system. Acomody charges a service fee on each confirmed booking, which is clearly disclosed before checkout.',
                    'sr' => 'Kada Gost podnese zahtev za rezervaciju, Domaćin može prihvatiti ili odbiti. Nakon potvrde rezervacije, obe strane su vezane njenim uslovima. Plaćanja se vrše putem našeg sigurnog sistema za plaćanje. Acomody naplaćuje naknadu za uslugu za svaku potvrđenu rezervaciju, koja je jasno prikazana pre finalizacije.',
                    'de' => 'Wenn ein Gast eine Buchungsanfrage stellt, kann der Gastgeber diese annehmen oder ablehnen. Nach Bestätigung einer Buchung sind beide Parteien an deren Bedingungen gebunden.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '6. Cancellations and Refunds',
                    'sr' => '6. Otkazivanja i povraćaji',
                    'de' => '6. Stornierungen und Rückerstattungen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Cancellation policies are set by Hosts and are clearly displayed on each listing page. Guests should review the cancellation policy before completing a booking. Refunds, if applicable, will be processed within 5–10 business days depending on the payment method used.',
                    'sr' => 'Politike otkazivanja postavljaju domaćini i jasno su prikazane na stranici svakog oglasa. Gosti treba da pregledaju politiku otkazivanja pre finalizacije rezervacije. Povraćaji, ako su primenljivi, biće obrađeni u roku od 5–10 radnih dana, u zavisnosti od korišćene metode plaćanja.',
                    'de' => 'Stornierungsrichtlinien werden von Gastgebern festgelegt und sind auf jeder Inseratsseite deutlich angezeigt. Gäste sollten die Stornierungsrichtlinie vor Abschluss einer Buchung prüfen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '7. Limitation of Liability',
                    'sr' => '7. Ograničenje odgovornosti',
                    'de' => '7. Haftungsbeschränkung',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Acomody acts as an intermediary between Hosts and Guests and is not a party to any rental agreements. To the maximum extent permitted by law, Acomody shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the platform.',
                    'sr' => 'Acomody deluje kao posrednik između Domaćina i Gostiju i nije strana u bilo kakvim ugovorima o iznajmljivanju. U najvećoj meri dozvoljenoj zakonom, Acomody neće biti odgovoran za nikakve indirektne, slučajne, posebne, posledične ili kaznene štete koje proisteknu iz vašeg korišćenja platforme.',
                    'de' => 'Acomody fungiert als Vermittler zwischen Gastgebern und Gästen und ist keine Partei von Mietverträgen. Acomody haftet nicht für indirekte, zufällige, besondere oder Folgeschäden.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '8. Changes to Terms',
                    'sr' => '8. Izmene uslova',
                    'de' => '8. Änderungen der Bedingungen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Acomody reserves the right to update these Terms & Conditions at any time. We will notify you of significant changes via email or a prominent notice on the platform. Your continued use of the platform after such changes constitutes your acceptance of the new terms.',
                    'sr' => 'Acomody zadržava pravo da u bilo kom trenutku ažurira ove Uslove korišćenja. O značajnim promenama ćemo vas obavešavati putem e-pošte ili istaknutim obaveštenjem na platformi. Vaše nastavljeno korišćenje platforme nakon takvih promena smatra se prihvatanjem novih uslova.',
                    'de' => 'Acomody behält sich das Recht vor, diese AGB jederzeit zu aktualisieren. Wir werden Sie über wesentliche Änderungen per E-Mail oder durch einen prominenten Hinweis auf der Plattform benachrichtigen.',
                ],
            ],
        ];

        LegalDocument::withoutAuthorization(function () use ($doc, $sections) {
            foreach ($sections as $order => $data) {
                LegalDocumentSection::create([
                    'legal_document_id' => $doc->id,
                    'section_type' => $data['section_type'],
                    'content' => $data['content'],
                    'sort_order' => $order,
                ]);
            }
        });
    }

    private function seedPrivacyPolicy(int $createdBy): void
    {
        if (LegalDocument::withoutAuthorization(fn () => LegalDocument::where('type', DocumentType::PrivacyPolicy)->exists())) {
            return;
        }

        $doc = LegalDocument::withoutAuthorization(fn () => LegalDocument::create([
            'type' => DocumentType::PrivacyPolicy,
            'version' => 1,
            'title' => [
                'en' => 'Privacy Policy',
                'sr' => 'Politika privatnosti',
                'de' => 'Datenschutzrichtlinie',
            ],
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $createdBy,
        ]));

        $sections = [
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '1. Introduction',
                    'sr' => '1. Uvod',
                    'de' => '1. Einführung',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Acomody ("we", "us", or "our") is committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform. By using Acomody, you consent to the practices described in this policy.',
                    'sr' => 'Acomody ("mi", "nas" ili "naš") je posvećen zaštiti vaših ličnih podataka. Ova Politika privatnosti objašnjava kako prikupljamo, koristimo, otkrivamo i čuvamo vaše informacije kada koristite našu platformu. Korišćenjem Acomody-ja pristajete na prakse opisane u ovoj politici.',
                    'de' => 'Acomody ("wir", "uns" oder "unser") ist dem Schutz Ihrer persönlichen Daten verpflichtet. Diese Datenschutzrichtlinie erläutert, wie wir Ihre Informationen erfassen, verwenden, offenlegen und schützen, wenn Sie unsere Plattform nutzen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '2. Information We Collect',
                    'sr' => '2. Informacije koje prikupljamo',
                    'de' => '2. Informationen, die wir erfassen',
                ],
            ],
            [
                'section_type' => SectionType::Subheading,
                'content' => [
                    'en' => '2.1 Information You Provide',
                    'sr' => '2.1 Informacije koje vi pružate',
                    'de' => '2.1 Von Ihnen bereitgestellte Informationen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'We collect information you provide directly, including: name, email address, phone number, profile photo, payment information, government-issued ID (for identity verification), accommodation details (for Hosts), and communications you send us.',
                    'sr' => 'Prikupljamo informacije koje direktno pružate, uključujući: ime, adresu e-pošte, broj telefona, profilnu fotografiju, informacije o plaćanju, dokument izdat od strane vlade (za proveru identiteta), podatke o smeštaju (za Domaćine) i komunikacije koje nam šaljete.',
                    'de' => 'Wir erfassen Informationen, die Sie direkt bereitstellen, einschließlich: Name, E-Mail-Adresse, Telefonnummer, Profilfoto, Zahlungsinformationen und Unterkunftsdetails (für Gastgeber).',
                ],
            ],
            [
                'section_type' => SectionType::Subheading,
                'content' => [
                    'en' => '2.2 Information Collected Automatically',
                    'sr' => '2.2 Automatski prikupljene informacije',
                    'de' => '2.2 Automatisch erfasste Informationen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'When you use our platform, we automatically collect certain information, including: IP address, browser type and version, pages visited, time spent on pages, referring URL, device type, and operating system. We use cookies and similar tracking technologies to collect this information.',
                    'sr' => 'Kada koristite našu platformu, automatski prikupljamo određene informacije, uključujući: IP adresu, tip i verziju pretraživača, posećene stranice, vreme provedeno na stranicama, referentni URL, tip uređaja i operativni sistem. Koristimo kolačiće i slične tehnologije praćenja za prikupljanje ovih informacija.',
                    'de' => 'Wenn Sie unsere Plattform nutzen, erfassen wir automatisch bestimmte Informationen, einschließlich: IP-Adresse, Browsertyp und -version, besuchte Seiten, Verweildauer auf Seiten und Gerätetyp.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '3. How We Use Your Information',
                    'sr' => '3. Kako koristimo vaše informacije',
                    'de' => '3. Wie wir Ihre Informationen verwenden',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'We use the information we collect to: provide and improve our services, process transactions and send related information, send promotional and informational messages (with your consent), respond to comments and questions, monitor and analyze usage patterns, detect and prevent fraudulent transactions, and comply with legal obligations.',
                    'sr' => 'Informacije koje prikupljamo koristimo za: pružanje i poboljšanje naših usluga, obradu transakcija i slanje relevantnih informacija, slanje promotivnih i informativnih poruka (uz vašu saglasnost), odgovaranje na komentare i pitanja, praćenje i analizu obrazaca korišćenja, otkrivanje i sprečavanje lažnih transakcija i ispunjavanje zakonskih obaveza.',
                    'de' => 'Wir verwenden die erfassten Informationen, um unsere Dienste bereitzustellen und zu verbessern, Transaktionen zu verarbeiten, Werbenachrichten zu senden (mit Ihrer Einwilligung) und gesetzlichen Verpflichtungen nachzukommen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '4. Sharing Your Information',
                    'sr' => '4. Deljenje vaših informacija',
                    'de' => '4. Weitergabe Ihrer Informationen',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'We do not sell your personal information. We may share your information with: Hosts or Guests (as necessary to facilitate a booking), service providers who assist in our operations (subject to confidentiality agreements), law enforcement or government authorities (when required by law), and other parties with your explicit consent.',
                    'sr' => 'Ne prodajemo vaše lične podatke. Vaše informacije možemo deliti sa: Domaćinima ili Gostima (po potrebi radi olakšavanja rezervacije), pružaocima usluga koji pomažu u našem poslovanju (podložno ugovorima o poverljivosti), organima za sprovođenje zakona ili državnim organima (kada to zahteva zakon) i drugim stranama uz vašu izričitu saglasnost.',
                    'de' => 'Wir verkaufen Ihre persönlichen Daten nicht. Wir können Ihre Informationen mit Gastgebern oder Gästen, Dienstleistern und Strafverfolgungsbehörden (wenn gesetzlich vorgeschrieben) teilen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '5. Cookies',
                    'sr' => '5. Kolačići',
                    'de' => '5. Cookies',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'We use cookies and similar tracking technologies to enhance your experience on our platform. Essential cookies are required for the platform to function properly. You may disable non-essential cookies through your browser settings, though this may affect certain features of the platform.',
                    'sr' => 'Koristimo kolačiće i slične tehnologije praćenja kako bismo poboljšali vaše iskustvo na našoj platformi. Esencijalni kolačići su neophodni za pravilno funkcionisanje platforme. Neesencijalne kolačiće možete onemogućiti putem podešavanja pretraživača, mada to može uticati na određene funkcije platforme.',
                    'de' => 'Wir verwenden Cookies und ähnliche Tracking-Technologien, um Ihre Erfahrung auf unserer Plattform zu verbessern. Wesentliche Cookies sind für das ordnungsgemäße Funktionieren der Plattform erforderlich.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '6. Data Retention',
                    'sr' => '6. Čuvanje podataka',
                    'de' => '6. Datenspeicherung',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'We retain your personal information for as long as your account is active or as needed to provide you with our services. You may request deletion of your account and associated data at any time through your account settings. We may retain certain information as required by law or for legitimate business purposes.',
                    'sr' => 'Čuvamo vaše lične podatke dok je vaš nalog aktivan ili koliko je potrebno za pružanje naših usluga. Brisanje naloga i povezanih podataka možete zatražiti u bilo kom trenutku putem podešavanja naloga. Možemo zadržati određene informacije kako to zahteva zakon ili za legitimne poslovne svrhe.',
                    'de' => 'Wir speichern Ihre persönlichen Daten, solange Ihr Konto aktiv ist oder wie es zur Bereitstellung unserer Dienste erforderlich ist. Sie können jederzeit die Löschung Ihres Kontos und der zugehörigen Daten anfordern.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '7. Your Rights',
                    'sr' => '7. Vaša prava',
                    'de' => '7. Ihre Rechte',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'Depending on your location, you may have the following rights regarding your personal data: the right to access and receive a copy of your data, the right to correct inaccurate data, the right to request deletion of your data, the right to restrict or object to processing, and the right to data portability. To exercise these rights, please contact us at privacy@acomody.com.',
                    'sr' => 'U zavisnosti od vaše lokacije, možda imate sledeća prava u pogledu vaših ličnih podataka: pravo na pristup i kopiju vaših podataka, pravo na ispravku netačnih podataka, pravo na zahtev za brisanje vaših podataka, pravo na ograničenje ili prigovor na obradu i pravo na prenosivost podataka. Da biste ostvarili ova prava, kontaktirajte nas na privacy@acomody.com.',
                    'de' => 'Je nach Ihrem Standort haben Sie möglicherweise folgende Rechte bezüglich Ihrer persönlichen Daten: Recht auf Auskunft, Recht auf Berichtigung, Recht auf Löschung, Recht auf Einschränkung der Verarbeitung und Recht auf Datenübertragbarkeit.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '8. Security',
                    'sr' => '8. Bezbednost',
                    'de' => '8. Sicherheit',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.',
                    'sr' => 'Primenjujemo odgovarajuće tehničke i organizacione mere bezbednosti kako bismo zaštitili vaše lične podatke od neovlašćenog pristupa, izmene, otkrivanja ili uništavanja. Međutim, nijedna metoda prenosa putem Interneta ili elektronskog skladištenja nije 100% sigurna i ne možemo garantovati apsolutnu bezbednost.',
                    'de' => 'Wir implementieren geeignete technische und organisatorische Sicherheitsmaßnahmen, um Ihre persönlichen Daten vor unbefugtem Zugriff, Änderung, Offenlegung oder Zerstörung zu schützen.',
                ],
            ],
            [
                'section_type' => SectionType::Heading,
                'content' => [
                    'en' => '9. Contact Us',
                    'sr' => '9. Kontaktirajte nas',
                    'de' => '9. Kontaktieren Sie uns',
                ],
            ],
            [
                'section_type' => SectionType::Paragraph,
                'content' => [
                    'en' => 'If you have any questions about this Privacy Policy or our data practices, please contact us at privacy@acomody.com. We will respond to your inquiry within 30 days.',
                    'sr' => 'Ako imate bilo kakvih pitanja o ovoj Politici privatnosti ili našim praksama u pogledu podataka, kontaktirajte nas na privacy@acomody.com. Odgovorićemo na vaš upit u roku od 30 dana.',
                    'de' => 'Wenn Sie Fragen zu dieser Datenschutzrichtlinie oder unseren Datenpraktiken haben, kontaktieren Sie uns bitte unter privacy@acomody.com. Wir werden Ihre Anfrage innerhalb von 30 Tagen beantworten.',
                ],
            ],
        ];

        LegalDocument::withoutAuthorization(function () use ($doc, $sections) {
            foreach ($sections as $order => $data) {
                LegalDocumentSection::create([
                    'legal_document_id' => $doc->id,
                    'section_type' => $data['section_type'],
                    'content' => $data['content'],
                    'sort_order' => $order,
                ]);
            }
        });
    }
}
