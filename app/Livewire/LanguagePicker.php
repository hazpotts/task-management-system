<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class LanguagePicker extends Component
{
    public string $currentLocale;

    public array $availableLocales = [
        'en' => 'English',
        'es' => 'Español',
    ];

    public function mount()
    {
        $this->currentLocale = App::getLocale();
    }

    public function setLocale(string $locale)
    {
        if (array_key_exists($locale, $this->availableLocales)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
            $this->currentLocale = $locale;
            $this->dispatch('refresh-navigation-menu');

            return redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.language-picker');
    }
}
