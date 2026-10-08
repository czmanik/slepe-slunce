<form class="accessible-form" action="{{ route('subscriptions.store') }}" method="post">
    @csrf
    <div class="form-grid">
        <div><label for="subscriber-name">{{ app()->isLocale('en') ? 'Name' : 'Jméno' }} <span class="optional">{{ app()->isLocale('en') ? '(optional)' : '(nepovinné)' }}</span></label><input id="subscriber-name" name="name" autocomplete="name" value="{{ old('name') }}"></div>
        <div><label for="subscriber-email">E-mail</label><input id="subscriber-email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="subscriber-email-error" @enderror>@error('email')<p id="subscriber-email-error" class="field-error">{{ $message }}</p>@enderror</div>
    </div>
    <div><label for="subscriber-locale">{{ app()->isLocale('en') ? 'Email language' : 'Jazyk e-mailů' }}</label><select id="subscriber-locale" name="locale" required><option value="cs" @selected(old('locale', app()->getLocale()) === 'cs')>Česky</option><option value="en" @selected(old('locale', app()->getLocale()) === 'en')>English</option></select></div>
    <fieldset><legend>{{ app()->isLocale('en') ? 'Topics' : 'Témata' }}</legend>
        @error('topics')<p class="field-error">{{ $message }}</p>@enderror
        <label class="check-label"><input type="checkbox" name="project_news" value="1" @checked(old('project_news', true))> {{ app()->isLocale('en') ? 'Project updates and new stories' : 'Život projektu a nové články' }}</label>
        <label class="check-label"><input type="checkbox" name="new_expeditions" value="1" @checked(old('new_expeditions', true))> {{ app()->isLocale('en') ? 'New expeditions' : 'Nové expedice' }}</label>
        <label class="check-label"><input type="checkbox" name="shop_news" value="1" @checked(old('shop_news'))> {{ app()->isLocale('en') ? 'Wine shop news' : 'Novinky z obchodu s víny' }}</label>
        @foreach($expeditions as $subscriptionExpedition)
            <label class="check-label"><input type="checkbox" name="expeditions[]" value="{{ $subscriptionExpedition->id }}" @checked(in_array($subscriptionExpedition->id, old('expeditions', [])))> {{ app()->isLocale('en') ? 'Only updates from:' : 'Jen aktuality:' }} {{ $subscriptionExpedition->name }}</label>
        @endforeach
    </fieldset>
    <label class="check-label"><input type="checkbox" name="privacy_consent" value="1" required> {{ app()->isLocale('en') ? 'I agree to the use of my email address for the selected updates. I can unsubscribe at any time.' : 'Souhlasím se zpracováním e-mailu pro zvolený odběr. Odběr mohu kdykoli ukončit.' }}</label>
    <div class="honeypot" aria-hidden="true"><label for="subscriber-website">Web</label><input id="subscriber-website" name="website" tabindex="-1" autocomplete="off"></div>
    <button class="button button-primary" type="submit">{{ app()->isLocale('en') ? 'Send confirmation email' : 'Poslat potvrzovací e-mail' }}</button>
</form>
