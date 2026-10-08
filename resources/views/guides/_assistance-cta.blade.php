<aside class="guide-assistance-cta" aria-labelledby="guide-assistance-title">
    <div>
        <p class="eyebrow">{{ app()->isLocale('en') ? 'Plan your journey' : 'Cesta na míru' }}</p>
        <h2 id="guide-assistance-title">{{ app()->isLocale('en') ? 'Would you like help arranging a trip?' : 'Chcete cestu zařídit společně?' }}</h2>
        <p>{{ app()->isLocale('en') ? 'We can help choose connections, arrange assistance, plan transfers and prepare a journey with a companion.' : 'Pomůžeme vybrat spoj, objednat asistenci, promyslet přestupy i připravit cestu s parťákem.' }}</p>
    </div>
    <div class="guide-assistance-actions">
        <a class="button button-primary" href="tel:+420605800798">{{ app()->isLocale('en') ? 'Call' : 'Zavolat' }} +420 605 800 798</a>
        <a class="button button-quiet" href="https://wa.me/420605800798">{{ app()->isLocale('en') ? 'Message on WhatsApp' : 'Napsat na WhatsApp' }}</a>
        <a class="guide-email-link" href="mailto:asistence@slepeslunce.cz">asistence@slepeslunce.cz</a>
    </div>
</aside>
