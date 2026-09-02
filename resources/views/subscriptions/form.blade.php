<form class="accessible-form" action="{{ route('subscriptions.store') }}" method="post">
    @csrf
    <div class="form-grid">
        <div><label for="subscriber-name">Jméno <span class="optional">(nepovinné)</span></label><input id="subscriber-name" name="name" autocomplete="name" value="{{ old('name') }}"></div>
        <div><label for="subscriber-email">E-mail</label><input id="subscriber-email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="subscriber-email-error" @enderror>@error('email')<p id="subscriber-email-error" class="field-error">{{ $message }}</p>@enderror</div>
    </div>
    <fieldset><legend>Témata</legend>
        @error('topics')<p class="field-error">{{ $message }}</p>@enderror
        <label class="check-label"><input type="checkbox" name="project_news" value="1" @checked(old('project_news', true))> Život projektu a nové články</label>
        <label class="check-label"><input type="checkbox" name="new_expeditions" value="1" @checked(old('new_expeditions', true))> Nové expedice</label>
        @foreach($expeditions as $subscriptionExpedition)
            <label class="check-label"><input type="checkbox" name="expeditions[]" value="{{ $subscriptionExpedition->id }}" @checked(in_array($subscriptionExpedition->id, old('expeditions', [])))> Jen aktuality: {{ $subscriptionExpedition->name }}</label>
        @endforeach
    </fieldset>
    <label class="check-label"><input type="checkbox" name="privacy_consent" value="1" required> Souhlasím se zpracováním e-mailu pro zvolený odběr. Odběr mohu kdykoli ukončit.</label>
    <div class="honeypot" aria-hidden="true"><label for="subscriber-website">Web</label><input id="subscriber-website" name="website" tabindex="-1" autocomplete="off"></div>
    <button class="button button-primary" type="submit">Poslat potvrzovací e-mail</button>
</form>
