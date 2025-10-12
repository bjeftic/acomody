<template>
  <base-wrapper>
    <template v-slot:content>
      <!-- Hero Section -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl p-12 mb-12">
        <div class="max-w-3xl">
          <h1 class="text-5xl font-bold mb-6">
            Become a Host on Acomody
          </h1>
          <p class="text-xl mb-8 text-blue-50">
            List your property on Acomody and open your doors to millions of travelers worldwide. Start earning today!
          </p>
          <fwb-button color="default" size="xl" class="bg-white text-blue-600 hover:bg-gray-50">
            Start now
          </fwb-button>
        </div>
      </div>

      <!-- Benefits Section -->
      <div class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Why host with Acomody?
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Benefit 1 -->
          <fwb-card class="hover:shadow-lg transition-shadow">
            <div class="flex flex-col items-start p-5">
              <div class="bg-blue-100 p-3 rounded-lg mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Besplatno oglašavanje
              </h3>
              <p class="text-gray-600">
                Postavite bilo koji tip smeštaja na sajt potpuno besplatno.
                Plaćate tek kada gosti rezervišu vaš smeštaj.
              </p>
            </div>
          </fwb-card>

          <!-- Benefit 2 -->
          <fwb-card class="hover:shadow-lg transition-shadow">
            <div class="flex flex-col items-start p-5">
              <div class="bg-green-100 p-3 rounded-lg mb-4">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Proverene rezervacije
              </h3>
              <p class="text-gray-600">
                Sve rezervacije prolaze našu proveru, a vi možete u svakom trenutku
                otkazati rezervacije koje nisu primerene.
              </p>
            </div>
          </fwb-card>

          <!-- Benefit 3 -->
          <fwb-card class="hover:shadow-lg transition-shadow">
            <div class="flex flex-col items-start p-5">
              <div class="bg-purple-100 p-3 rounded-lg mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Vi određujete cene
              </h3>
              <p class="text-gray-600">
                Sami formirate cenu po kojoj gosti mogu da zakupe smeštaj i
                odobrite popuste na duže boravke.
              </p>
            </div>
          </fwb-card>

          <!-- Benefit 4 -->
          <fwb-card class="hover:shadow-lg transition-shadow">
            <div class="flex flex-col items-start p-5">
              <div class="bg-orange-100 p-3 rounded-lg mb-4">
                <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Garancija plaćanja
              </h3>
              <p class="text-gray-600">
                Mogućnost obavezne uplate avansa 30-50% značajno smanjuje
                otkaze rezervacija i nepojavljivanje gostiju.
              </p>
            </div>
          </fwb-card>

          <!-- Benefit 5 -->
          <fwb-card class="hover:shadow-lg transition-shadow">
            <div class="flex flex-col items-start p-5">
              <div class="bg-red-100 p-3 rounded-lg mb-4">
                <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Vi birate goste
              </h3>
              <p class="text-gray-600">
                Omogućili smo da sami postavite pravila zakupa i da goste
                koji ih ne ispunjavaju ne primite u vaš smestaj.
              </p>
            </div>
          </fwb-card>

          <!-- Benefit 6 -->
          <fwb-card class="hover:shadow-lg transition-shadow">
            <div class="flex flex-col items-start p-5">
              <div class="bg-indigo-100 p-3 rounded-lg mb-4">
                <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                  <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Fleksibilno plaćanje
              </h3>
              <p class="text-gray-600">
                Gosti mogu platiti direktno, putem platnih kartica,
                računa firme ili onlajn prilikom rezervacije.
              </p>
            </div>
          </fwb-card>
        </div>
      </div>

      <!-- How It Works Section -->
      <div class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Kako funkcioniše?
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
              1
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Registrujte se</h3>
            <p class="text-gray-600">
              Kreirajte nalog potpuno besplatno i dodajte osnovne informacije o vašem smeštaju
            </p>
          </div>

          <div class="text-center">
            <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
              2
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Postavite oglas</h3>
            <p class="text-gray-600">
              Dodajte fotografije, postavite cene i odredite pravila zakupa po vašoj želji
            </p>
          </div>

          <div class="text-center">
            <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
              3
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Primajte goste</h3>
            <p class="text-gray-600">
              Prihvatite rezervacije, ugostite putnika i ostvarite sigurnu zaradu
            </p>
          </div>
        </div>
      </div>

      <!-- Pricing Section -->
      <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl p-12 mb-16">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-3xl font-bold mb-4">Provizija samo po rezervaciji</h2>
          <p class="text-xl mb-8 text-gray-300">
            Acomody naplaćuje proviziju tek kada dođe do prvih rezervacija.
            U zavisnosti od destinacije na kojoj se smeštaj nalazi.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
            <div class="bg-white/10 backdrop-blur rounded-lg p-6">
              <p class="text-gray-300 mb-2">Gradovi</p>
              <p class="text-4xl font-bold">12-15%</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-6">
              <p class="text-gray-300 mb-2">Planine</p>
              <p class="text-4xl font-bold">10-12%</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-6">
              <p class="text-gray-300 mb-2">More</p>
              <p class="text-4xl font-bold">8-10%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ Section -->
      <div class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Često postavljana pitanja
        </h2>
        <div class="max-w-3xl mx-auto space-y-4">
          <fwb-accordion>
            <fwb-accordion-panel>
              <fwb-accordion-header>
                Ko može da oglasi smeštaj na Acomody?
              </fwb-accordion-header>
              <fwb-accordion-content>
                <p class="text-gray-600">
                  Svako ko poseduje stan, vilu, vikendicu, brvnaru ili bilo koji drugi
                  tip smeštaja može da ga oglasi na našoj platformi. Potrebno je samo
                  da imate vlasništvo ili pravo izdavanja objekta.
                </p>
              </fwb-accordion-content>
            </fwb-accordion-panel>

            <fwb-accordion-panel>
              <fwb-accordion-header>
                Kakvu garanciju imam u slučaju otkazivanja rezervacije?
              </fwb-accordion-header>
              <fwb-accordion-content>
                <p class="text-gray-600">
                  Omogućili smo obaveznu uplatu avansa (30-50%) ili punog iznosa prilikom
                  rezervacije, što značajno smanjuje mogućnost otkazivanja. U slučaju
                  otkazivanja, vaša zarada je zaštićena prema uslovima koje ste postavili.
                </p>
              </fwb-accordion-content>
            </fwb-accordion-panel>

            <fwb-accordion-panel>
              <fwb-accordion-header>
                Koje podatke gosti ostavljaju prilikom rezervacije?
              </fwb-accordion-header>
              <fwb-accordion-content>
                <p class="text-gray-600">
                  Gosti ostavljaju sve neophodne kontakt podatke uključujući ime, prezime,
                  broj telefona, email adresu i podatke o broju osoba. Sve informacije su
                  proverene i dostupne vama pre dolaska gosta.
                </p>
              </fwb-accordion-content>
            </fwb-accordion-panel>

            <fwb-accordion-panel>
              <fwb-accordion-header>
                Kako da odredim cenu za svoj smeštaj?
              </fwb-accordion-header>
              <fwb-accordion-content>
                <p class="text-gray-600">
                  Vi u potpunosti određujete cenu svog smeštaja. Naš tim je tu da vam pruži
                  preporuke kako bi postavili konkurentne cene koje su u skladu sa strukturom
                  i lokacijom vašeg smeštaja. Možete lako ažurirati cene i popuste u bilo kom trenutku.
                </p>
              </fwb-accordion-content>
            </fwb-accordion-panel>

            <fwb-accordion-panel>
              <fwb-accordion-header>
                Koliko košta oglašavanje smeštaja?
              </fwb-accordion-header>
              <fwb-accordion-content>
                <p class="text-gray-600">
                  Oglašavanje je potpuno besplatno! Plaćate samo proviziju kada gost rezerviše
                  vaš smeštaj. Nema skrivenih troškova ili mesečnih naknada.
                </p>
              </fwb-accordion-content>
            </fwb-accordion-panel>

            <fwb-accordion-panel>
              <fwb-accordion-header>
                Da li moram da primim svakog gosta?
              </fwb-accordion-header>
              <fwb-accordion-content>
                <p class="text-gray-600">
                  Ne! Vi postavljate pravila i uslove boravka. Možete odbiti rezervaciju
                  ukoliko gost ne ispunjava vaše uslove ili ako smatrate da rezervacija nije
                  primerena. Imate punu kontrolu nad tim ko boravi u vašem smeštaju.
                </p>
              </fwb-accordion-content>
            </fwb-accordion-panel>
          </fwb-accordion>
        </div>
      </div>

      <!-- Final CTA -->
      <div class="bg-blue-600 text-white rounded-xl p-12 text-center">
        <h2 class="text-4xl font-bold mb-4">
          Spremni da započnete?
        </h2>
        <p class="text-xl mb-8 text-blue-50">
          Pridružite se hiljadama zadovoljnih domaćina koji zarađuju sa Acomody
        </p>
        <fwb-button color="default" size="xl" class="bg-white text-blue-600 hover:bg-gray-50">
          Započnite oglašavanje danas
        </fwb-button>
      </div>
    </template>
  </base-wrapper>
</template>

<script>
export default {
  name: 'BecomeAHost',
}
</script>

<style scoped>
/* Additional transitions */
.transition-shadow {
  transition: box-shadow 0.3s ease;
}
</style>
